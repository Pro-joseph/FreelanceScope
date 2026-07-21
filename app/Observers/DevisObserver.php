<?php

namespace App\Observers;

use App\Models\Devis;
use Illuminate\Support\Facades\Storage;

class DevisObserver
{
    public function deleted(Devis $devis): void
    {
        if ($devis->pdf_path) {
            Storage::disk('devis')->delete($devis->pdf_path);
        }
    }
}
