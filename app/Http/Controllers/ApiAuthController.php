<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'freelance',
        ]);

        Auth::login($user);

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
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        Auth::login($user);

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
        if ($token = $request->user()->currentAccessToken()) {
            $token->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->validated());

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
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
