<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
