<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * @group Auth
 *
 * Inscription, connexion, déconnexion et récupération de l'utilisateur connecté.
 * Tous les endpoints retournent un token Sanctum à utiliser dans le header `Authorization: Bearer {token}`.
 */
class ApiAuthController extends Controller
{
    /**
     * Inscription
     *
     * Crée un nouveau compte utilisateur et retourne un token d'authentification.
     *
     * @bodyParam nom string required Le nom de famille. Example: Doe
     * @bodyParam prenom string required Le prénom. Example: John
     * @bodyParam email string required L'adresse email. Example: john@example.com
     * @bodyParam password string required Le mot de passe (min. 8 caractères). Example: password
     * @bodyParam role string Le rôle de l'utilisateur. Possibilités : `admin`, `freelance`. Par défaut : `freelance`. Example: freelance
     *
     * @response 201 {
     *   "user": { "id": 1, "nom": "Doe", "prenom": "John", "email": "john@example.com", "role": "freelance" },
     *   "token": "1|abc123..."
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['sometimes', 'string', 'in:admin,freelance'],
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'role' => $request->input('role', 'freelance'),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Connexion
     *
     * Authentifie un utilisateur existant et retourne un token Sanctum.
     *
     * @bodyParam email string required L'adresse email. Example: john@example.com
     * @bodyParam password string required Le mot de passe. Example: password
     *
     * @response 200 {
     *   "user": { "id": 1, "nom": "Doe", "prenom": "John", "email": "john@example.com", "role": "freelance", "statut": "actif" },
     *   "token": "2|xyz789..."
     * }
     * @response 422 {
     *   "message": "Les identifiants sont incorrects.",
     *   "errors": { "email": ["Les identifiants sont incorrects."] }
     * }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'role' => $user->role,
                'statut' => $user->statut,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion
     *
     * Révoque le token d'accès actuel.
     *
     * @authenticated
     *
     * @response 200 { "message": "Déconnecté." }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }
}
