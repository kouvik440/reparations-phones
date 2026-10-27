<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\URL::forceRootUrl('http://reparations-phones.test');
$repair = new App\Models\Reparation;
$repair->forceFill(['code' => 'A1B2C3D4', 'marque' => 'Samsung', 'modele' => 'Galaxy A15', 'date_depot' => '2026-10-27']);
file_put_contents(__DIR__.'/ticket-demo.pdf', app(App\Actions\Reparations\GenerateRepairTicket::class)->handle($repair));
$repair->marque = str_repeat('W', 100);
$repair->modele = str_repeat('M', 100);
file_put_contents(__DIR__.'/ticket-long.pdf', app(App\Actions\Reparations\GenerateRepairTicket::class)->handle($repair));