<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Devis;
use App\Models\Estimate;
use App\Models\ProjectFeature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class DevisService
{
    public function listForUser(int $userId): LengthAwarePaginator
    {
        $clientIds = Client::where('user_id', $userId)->pluck('id');

        return Devis::whereIn('client_id', $clientIds)
            ->with(['client', 'project'])
            ->latest()
            ->paginate(15);
    }

    public function generate(int $clientId, int $projectId, ?string $conditions): Devis
    {
        $features = ProjectFeature::where('project_id', $projectId)
            ->with('estimate')
            ->get();

        $totalAmount = $features->sum(fn ($f) => $f->estimate?->total_amount ?? 0);

        $estimateId = $features->first()?->estimate?->id
            ?? Estimate::whereHas('feature', fn ($q) => $q->where('project_id', $projectId))->first()?->id
            ?? 1;

        return Devis::create([
            'client_id' => $clientId,
            'project_id' => $projectId,
            'estimate_id' => $estimateId,
            'total_amount' => $totalAmount,
            'conditions' => $conditions,
            'status' => 'draft',
        ]);
    }

    public function generatePdf(Devis $devis): string
    {
        $filename = "devis_{$devis->id}.pdf";

        $devis->load(['client', 'project.features.estimate']);
        $user = $devis->client->user;

        $pdf = Pdf::loadView('pdf.devis', [
            'devis' => $devis,
            'user' => $user,
        ]);

        Storage::disk('devis')->put($filename, $pdf->output());

        $devis->update([
            'pdf_path' => $filename,
            'pdf_generated_at' => now(),
        ]);

        return $filename;
    }

    public function needsRegeneration(Devis $devis): bool
    {
        if (! $devis->pdf_path) {
            return true;
        }

        if (! $devis->pdf_generated_at) {
            return true;
        }

        return $devis->updated_at->gt($devis->pdf_generated_at);
    }

    public function getPdfPath(Devis $devis): ?string
    {
        if (! $devis->pdf_path) {
            return null;
        }

        $fullPath = Storage::disk('devis')->path($devis->pdf_path);

        return file_exists($fullPath) ? $fullPath : null;
    }

}
