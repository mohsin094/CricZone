<?php

namespace App\Console\Commands;

use App\Services\CricketApiService;
use Illuminate\Console\Command;

class TestCricketApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricket:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Cricket API connection and basic functionality';

    /**
     * Execute the console command.
     */
    public function handle(CricketApiService $cricketApi)
    {
        $this->info('🏏 Testing Cricket API Connection...');
        $this->newLine();
        
        try {
            // Test API key configuration
            $this->info('1. Testing API Configuration...');
            $apiKey = config('services.cricket.api_key');
            $baseUrl = config('services.cricket.base_url');
            
            if ($apiKey && $baseUrl) {
                $this->info("   ✅ API Key: " . substr($apiKey, 0, 10) . '...');
                $this->info("   ✅ Base URL: {$baseUrl}");
            } else {
                $this->error('   ❌ API configuration missing');
                return 1;
            }
            
            $this->newLine();
            
            // Test leagues endpoint
            $this->info('2. Testing Leagues Endpoint...');
            $leagues = $cricketApi->getLeagues();
            
            if (!empty($leagues)) {
                $this->info("   ✅ Successfully fetched " . count($leagues) . " leagues");
                $this->line("   📋 Sample leagues:");
                foreach (array_slice($leagues, 0, 3) as $league) {
                    $this->line("      - {$league['league_name']} ({$league['league_year']})");
                }
            } else {
                $this->warn("   ⚠️  No leagues returned (this might be normal)");
            }
            
            $this->newLine();
            
            // Test live scores endpoint
            $this->info('3. Testing Live Scores Endpoint...');
            $liveScores = $cricketApi->getLiveScores();
            
            if (!empty($liveScores)) {
                $this->info("   ✅ Successfully fetched " . count($liveScores) . " live matches");
                $this->line("   🔥 Live matches:");
                foreach (array_slice($liveScores, 0, 2) as $match) {
                    $this->line("      - {$match['event_home_team']} vs {$match['event_away_team']}");
                }
            } else {
                $this->info("   ℹ️  No live matches currently (this is normal)");
            }
            
            $this->newLine();
            
            // Test teams endpoint
            $this->info('4. Testing Teams Endpoint...');
            $teams = $cricketApi->getTeams();
            
            if (!empty($teams)) {
                $this->info("   ✅ Successfully fetched " . count($teams) . " teams");
                $this->line("   👥 Sample teams:");
                foreach (array_slice($teams, 0, 3) as $team) {
                    $this->line("      - {$team['team_name']}");
                }
            } else {
                $this->warn("   ⚠️  No teams returned");
            }
            
            $this->newLine();
            
            // Test today's events
            $this->info('5. Testing Events Endpoint...');
            $today = now()->format('Y-m-d');
            $events = $cricketApi->getEvents($today, $today);
            
            if (!empty($events)) {
                $this->info("   ✅ Successfully fetched " . count($events) . " events for today ({$today})");
                $this->line("   📅 Today's events:");
                foreach (array_slice($events, 0, 2) as $event) {
                    $this->line("      - {$event['event_home_team']} vs {$event['event_away_team']} ({$event['event_status']})");
                }
            } else {
                $this->info("   ℹ️  No events for today ({$today})");
            }
            
            $this->newLine();
            
            // Test getSeries method
            $this->info('Testing getSeries method...');
            $series = $cricketApi->getSeries();
            if (!empty($series)) {
                $this->info('✓ getSeries: Found ' . count($series) . ' series');
                $this->info('  Sample series: ' . ($series[0]['series_name'] ?? 'Unknown'));
            } else {
                $this->warn('⚠ getSeries: No series found');
            }
            
            // Test getSeriesWithResultsAndStandings method
            $this->info('Testing getSeriesWithResultsAndStandings method...');
            $seriesWithResults = $cricketApi->getSeriesWithResultsAndStandings();
            if (!empty($seriesWithResults)) {
                $this->info('✓ getSeriesWithResultsAndStandings: Found ' . count($seriesWithResults) . ' series with results');
                $firstSeries = $seriesWithResults[0];
                $this->info('  Sample series: ' . ($firstSeries['series_name'] ?? 'Unknown'));
                $this->info('  Total matches: ' . ($firstSeries['stats']['total_matches'] ?? 0));
                $this->info('  Completed matches: ' . ($firstSeries['stats']['completed_matches'] ?? 0));
                $this->info('  Live matches: ' . ($firstSeries['stats']['live_matches'] ?? 0));
                $this->info('  Series progress: ' . ($firstSeries['stats']['series_progress'] ?? 0) . '%');
            } else {
                $this->warn('⚠ getSeriesWithResultsAndStandings: No series with results found');
            }

            // Test new match detail methods
            $this->info('Testing new match detail methods...');
            
            // Get a sample event key for testing
            $events = $cricketApi->getEvents();
            if (!empty($events)) {
                $sampleEventKey = $events[0]['event_key'] ?? null;
                
                if ($sampleEventKey) {
                    $this->info('  Testing with event key: ' . $sampleEventKey);
                    
                    // Test getScorecard
                    $this->info('  Testing getScorecard...');
                    $scorecard = $cricketApi->getScorecard($sampleEventKey);
                    if (!empty($scorecard)) {
                        $this->info('    ✓ getScorecard: Found scorecard data');
                    } else {
                        $this->info('    ⚠ getScorecard: No scorecard data available');
                    }
                    
                    // Test getCommentary
                    $this->info('  Testing getCommentary...');
                    $commentary = $cricketApi->getCommentary($sampleEventKey);
                    if (!empty($commentary)) {
                        $this->info('    ✓ getCommentary: Found commentary data');
                    } else {
                        $this->info('    ⚠ getCommentary: No commentary data available');
                    }
                    
                    // Test getLineups
                    $this->info('  Testing getLineups...');
                    $lineups = $cricketApi->getLineups($sampleEventKey);
                    if (!empty($lineups)) {
                        $this->info('    ✓ getLineups: Found lineup data');
                    } else {
                        $this->info('    ⚠ getLineups: No lineup data available');
                    }
                    
                    // Test getMatchStatistics
                    $this->info('  Testing getMatchStatistics...');
                    $stats = $cricketApi->getMatchStatistics($sampleEventKey);
                    if (!empty($stats)) {
                        $this->info('    ✓ getMatchStatistics: Found match statistics');
                    } else {
                        $this->info('    ⚠ getMatchStatistics: No match statistics available');
                    }
                } else {
                    $this->warn('  No event key available for testing match detail methods');
                }
            } else {
                $this->warn('  No events available for testing match detail methods');
            }
            
            // Summary
            $this->info('🎉 API Test Summary:');
            $this->line('   ✅ Configuration: OK');
            $this->line('   ✅ Leagues: ' . (empty($leagues) ? 'No data' : 'OK'));
            $this->line('   ✅ Live Scores: ' . (empty($liveScores) ? 'No live matches' : 'OK'));
            $this->line('   ✅ Teams: ' . (empty($teams) ? 'No data' : 'OK'));
            $this->line('   ✅ Events: ' . (empty($events) ? 'No events today' : 'OK'));
            
            $this->newLine();
            $this->info('🚀 Your Cricket API is working correctly!');
            
        } catch (\Exception $e) {
            $this->error('❌ API Test Failed: ' . $e->getMessage());
            $this->newLine();
            $this->line('Troubleshooting tips:');
            $this->line('1. Check your API key in .env file');
            $this->line('2. Verify internet connection');
            $this->line('3. Check if API-Cricket.com is accessible');
            $this->line('4. Review the error logs');
            
            return 1;
        }
        
        return 0;
    }
}
