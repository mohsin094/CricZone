<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CricketController;

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
    Route::post('/sync-teams', [CricketController::class, 'syncTeams'])->name('sync-teams');
    Route::get('/teams/league/{leagueKey}', [CricketController::class, 'getTeamsByLeague'])->name('teams-by-league');
    Route::get('/leagues', [CricketController::class, 'leagues'])->name('leagues');
    Route::get('/league/{leagueKey}', [CricketController::class, 'leagueDetail'])->name('league-detail');
    Route::get('/series', [CricketController::class, 'series'])->name('series');
    Route::get('/series/{seriesKey}', [CricketController::class, 'seriesDetail'])->name('series-detail');
    Route::get('/search', [CricketController::class, 'search'])->name('search');
    Route::post('/refresh', [CricketController::class, 'refreshData'])->name('refresh');
    Route::get('/debug', [CricketController::class, 'debug'])->name('debug');
    Route::get('/test-api', [CricketController::class, 'testApi'])->name('test-api');
    Route::get('/debug-api-calls', [CricketController::class, 'debugApiCalls'])->name('debug-api-calls');
    Route::get('/test-domain-connectivity', [CricketController::class, 'testDomainConnectivity'])->name('test-domain-connectivity');
});

// Redirect root to cricket index
Route::redirect('/', '/cricket');
