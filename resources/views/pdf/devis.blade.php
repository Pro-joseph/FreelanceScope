<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #2563eb; }
        .header p { margin: 2px 0; color: #666; }
        .client-info { margin-bottom: 20px; }
        .client-info h3 { margin: 0 0 5px; color: #333; }
        .client-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #2563eb; color: #fff; padding: 8px 12px; text-align: left; font-size: 11px; }
        td { padding: 8px 12px; border-bottom: 1px solid #ddd; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; font-size: 14px; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 11px; color: #666; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #f3f4f6; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DEVIS</h1>
        <p>{{ $user?->nom ?? 'N/A' }} {{ $user?->prenom ?? '' }}</p>
        <p>{{ $user?->email ?? 'N/A' }}</p>
    </div>

    <div class="client-info">
        <h3>Client</h3>
        <p><strong>{{ $devis->client?->company_name ?? 'N/A' }}</strong></p>
        <p>{{ $devis->client?->email ?? 'N/A' }}</p>
        @if ($devis->client?->phone)
            <p>{{ $devis->client->phone }}</p>
        @endif
    </div>

    <h3>{{ $devis->project?->name ?? 'N/A' }}</h3>
    <p>{{ $devis->project?->description ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th>Fonctionnalité</th>
                <th>Complexité</th>
                <th class="right">Heures</th>
                <th class="right">Taux/h</th>
                <th class="right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($devis->project?->features ?? [] as $feature)
                <tr>
                    <td>{{ $feature->name }}</td>
                    <td><span class="badge">{{ $feature->complexity ?? '-' }}</span></td>
                    <td class="right">{{ $feature->estimate?->total_hours ? number_format($feature->estimate->total_hours, 2) : '-' }}</td>
                    <td class="right">{{ $feature->estimate?->hourly_rate ? number_format($feature->estimate->hourly_rate, 2) . ' DH' : '-' }}</td>
                    <td class="right">{{ $feature->estimate?->total_amount ? number_format($feature->estimate->total_amount, 2) . ' DH' : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="right">Aucune fonctionnalité</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">Total</td>
                <td class="right">{{ number_format($devis->total_amount, 2) }} DH</td>
            </tr>
        </tbody>
    </table>

    @if ($devis->conditions)
        <h4>Conditions</h4>
        <p>{{ $devis->conditions }}</p>
    @endif

    <div class="footer">
        <p>Devis généré le {{ $devis->created_at->format('d/m/Y') }}</p>
    </div>
</body>
</html>
