<?php

use Illuminate\Support\Facades\Route;

// CompanyProfile is now the site's home page.
Route::view('/', 'CompanyProfile')->name('home');

Route::view('/mondstadt', 'Mondstadt')->name('mondstadt');
Route::view('/liyue', 'Liyue')->name('liyue');
Route::view('/inazuma', 'Inazuma')->name('inazuma');
Route::view('/sumeru', 'Sumeru')->name('sumeru');
Route::view('/fontaine', 'Fontaine')->name('fontaine');
Route::view('/natlan', 'Natlan')->name('natlan');
Route::view('/snezhnaya', 'Snezhnaya')->name('snezhnaya');
