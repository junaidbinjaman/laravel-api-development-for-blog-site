<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyTestEmail;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tesrroute', function () {
    $name = 'Junaid Bin Jaman';

    Mail::to('junaid@allnextver.com')->send(new \App\Mail\MyTestEmail($name));
});
