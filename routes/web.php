<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CricketController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\NewsController;

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
    Route::get('/live-scores', [CricketController::class, 'liveScores'])->name('live-scores');
    Route::get('/match/{eventKey}', [CricketController::class, 'matchDetail'])->name('match-detail');
    Route::get('/match/{eventKey}/live-update', [CricketController::class, 'liveUpdate'])->name('match-live-update');
    Route::get('/fixtures', [CricketController::class, 'fixtures'])->name('fixtures');
    Route::get('/results', [CricketController::class, 'results'])->name('results');
    Route::get('/teams', [CricketController::class, 'teams'])->name('teams');
    Route::get('/team/{teamKey}', [CricketController::class, 'teamDetail'])->name('team-detail');
    Route::get('/search', [CricketController::class, 'search'])->name('search');
    Route::post('/refresh', [CricketController::class, 'refreshData'])->name('refresh');
    Route::get('/mock/enable', [CricketController::class, 'enableMock'])->name('mock-enable');
    Route::get('/mock/disable', [CricketController::class, 'disableMock'])->name('mock-disable');
    
    // News Routes
    Route::get('/news', [NewsController::class, 'index'])->name('news');
    Route::get('/news/latest', [NewsController::class, 'getLatest'])->name('news.latest');
    Route::get('/news/search', [NewsController::class, 'search'])->name('news.search');
});

// Ranking Routes
Route::prefix('rankings')->name('rankings.')->group(function () {
    Route::get('/', [RankingController::class, 'index'])->name('index');
    Route::get('/update', [RankingController::class, 'updateRankings'])->name('update');
    Route::get('/stats', [RankingController::class, 'getRankingStats'])->name('stats');
});

// Redirect root to cricket index
Route::redirect('/', '/cricket');
