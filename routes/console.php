<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Tani pintar, hasil melimpah.');
})->purpose('Display an inspiring quote');
