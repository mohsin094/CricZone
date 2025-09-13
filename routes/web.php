<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CricketController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Api\LiveMatchController;

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

Route::get('/', function () {
    return view('welcome');
});

// Cricket Routes
Route::prefix('cricket')->name('cricket.')->group(function () {
    Route::get('/', [CricketController::class, 'index'])->name('index');
    Route::get('/fixtures', [CricketController::class, 'fixtures'])->name('fixtures');
    Route::get('/match/{eventKey}/{iid?}', [CricketController::class, 'matchDetail'])->name('match-detail');

    // News Routes
    Route::get('/news', [NewsController::class, 'index'])->name('news');
    Route::get('/news/latest', [NewsController::class, 'getLatest'])->name('news.latest');
    Route::get('/news/search', [NewsController::class, 'search'])->name('news.search');
});

// Ranking Routes
Route::prefix('rankings')->name('rankings.')->group(function () {
    Route::get('/', [RankingController::class, 'index'])->name('index');
    Route::get('/update', [RankingController::class, 'updateRankings'])->name('update');
});

// Redirect root to cricket index
Route::redirect('/', '/cricket');

// Live Match API Routes
Route::prefix('api')->group(function () {
    Route::get('/live-matches/{matchKey}', [LiveMatchController::class, 'show']);
    Route::get('/live-matches/{matchKey}/is-live', [LiveMatchController::class, 'isLive']);
    Route::post('/live-matches/update', [LiveMatchController::class, 'update']);
});
