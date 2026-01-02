<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get("/test-tenant/{slug}", function($slug) { return \App\Models\Client::where("slug", $slug)->firstOrFail(); });
