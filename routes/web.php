<?php
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// web.php (route redirect token ke FE)
Route::get('/reset-password/{token}', function ($token) {
    return redirect('http://localhost:5173/reset-password/' . $token);
})->name('password.reset');

Route::get('/test-mail', function () {
    Mail::raw('Ini email test Laravel', function ($message) {
        $message->to('test@example.com') // ganti dengan email di Mailtrap sandbox
                ->subject('Test Email Laravel');
    });
    return 'Email test dikirim!';
});
