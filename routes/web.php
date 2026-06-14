<?php

use \Illuminate\Support\Facades\Route;
use \App\Livewire\QrScanner;
use \App\Livewire\VisitorQrPage;

Route::redirect('/', '/admin');
Route::get('/scan-presence/{uuid}', QrScanner::class)->name('presence.scanner');
Route::get('/visitor-qr/{code_uuid}', VisitorQrPage::class)->name('visitor.qr');
