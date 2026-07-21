<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreFreelanceRequest;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'freelances_count' => User::where('role', UserRole::Freelance)->count(),
            'clients_count' => Client::count(),
            'projects_count' => Project::count(),
            'devis_count' => Devis::count(),
        ]);
    }

    public function freelances(): JsonResponse
    {
        $freelances = User::where('role', UserRole::Freelance)
            ->get(['id', 'nom', 'prenom', 'email', 'telephone', 'statut', 'taux_horaire']);

        return response()->json($freelances);
    }

    public function storeFreelance(StoreFreelanceRequest $request): JsonResponse
    {
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'telephone' => $request->telephone,
            'taux_horaire' => $request->taux_horaire,
            'role' => UserRole::Freelance,
        ]);

        return response()->json($user, 201);
    }

    public function updateFreelance(Request $request, User $user): JsonResponse
    {
        abort_if($user->role !== UserRole::Freelance, 404);

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'telephone' => ['nullable', 'string', 'max:20'],
            'taux_horaire' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function toggleStatut(User $user): JsonResponse
    {
        abort_if($user->role !== UserRole::Freelance, 404);

        $user->update([
            'statut' => $user->statut === 'actif' ? 'inactif' : 'actif',
        ]);

        return response()->json($user);
    }

    public function destroyFreelance(User $user): JsonResponse
    {
        abort_if($user->role !== UserRole::Freelance, 404);

        $user->delete();

        return response()->json(null, 204);
    }
}
