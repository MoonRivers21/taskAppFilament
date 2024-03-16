<?php

use Illuminate\Support\Facades\Route;



// Default Route to login page
Route::get('/', function () {
   return redirect()->route('filament.admin.auth.login');
});
