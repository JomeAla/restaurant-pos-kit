<?php

use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/admin/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->validate(['locale' => 'required|in:en,fr,es,de,pt,it,nl,pl,ru,zh,ja,ko,ar,tr'])['locale'];
    Setting::setValue('locale', $locale, 'general');
    App::setLocale($locale);
    return redirect()->back();
})->middleware('web');

Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
