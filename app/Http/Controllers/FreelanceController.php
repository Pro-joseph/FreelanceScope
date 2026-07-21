<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class FreelanceController extends Controller
{
    public function profile(): JsonResponse
    {
        $user = auth()->user();

        return response()->json($user->only(
            'id', 'nom', 'prenom', 'email', 'telephone', 'taux_horaire', 'statut'
        ));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        $user->update($request->validated());

        return response()->json($user->only(
            'id', 'nom', 'prenom', 'email', 'telephone', 'taux_horaire', 'statut'
        ));
    }

    public function dashboard(): JsonResponse
    {
        $userId = auth()->id();

        $clientIds = Client::where('user_id', $userId)->pluck('id');

        return response()->json([
            'clients_count' => $clientIds->count(),
            'projects_count' => Project::whereIn('client_id', $clientIds)->count(),
            'devis_count' => Devis::whereIn('client_id', $clientIds)->count(),
        ]);
    }
}
