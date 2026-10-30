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
        // Adhesive label: 50 x 30 mm, printed at actual size.
        $pdf->setPaper([0, 0, 141.73, 85.04]);
        $pdf->render();

        return $pdf->output();
    }
}
