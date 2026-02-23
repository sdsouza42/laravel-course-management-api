<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'courses.index')->name('home');
Route::view('/courses', 'courses.index')->name('courses.ui');
