<?php

namespace App\Actions\Reparations;

use App\Models\Reparation;
use Dompdf\Dompdf;
use Dompdf\Options;

final class GenerateRepairTicket
{
    public function handle(Reparation $repair): string
    {
        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('reparations.ticket', ['repair' => $repair])->render(), 'UTF-8');
        // Receipt format: 80 × 120 mm, independent of the browser's print settings.
        $pdf->setPaper([0, 0, 226.77, 340.16]);
        $pdf->render();

        return $pdf->output();
    }
}
