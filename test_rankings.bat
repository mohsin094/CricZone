@echo off
echo 🏏 ICC Rankings System - Quick Test
echo =====================================
echo.

echo 1. Testing Laravel Environment...
php artisan --version
if %errorlevel% neq 0 (
    echo ❌ Laravel not found. Make sure you're in the project directory.
    pause
    exit /b 1
)
echo ✅ Laravel is working

echo.
echo 2. Testing Database Connection...
php artisan tinker --execute="echo 'Team Rankings: ' . \App\Models\TeamRanking::count() . ' records'; echo 'Player Rankings: ' . \App\Models\PlayerRanking::count() . ' records';"
if %errorlevel% neq 0 (
    echo ❌ Database connection failed. Run: php artisan migrate
    pause
    exit /b 1
)
echo ✅ Database is connected

echo.
echo 3. Testing API Configuration...
php artisan tinker --execute="echo 'API Key: ' . (config('services.cricbuzz.api_key') ? 'Configured' : 'Not configured'); echo 'Base URL: ' . config('services.cricbuzz.base_url');"

echo.
echo 4. Testing Ranking Service...
php artisan tinker --execute="$service = app(\App\Services\RankingService::class); $teams = $service->getTeamRankings('men', 'odi', 5); echo 'Men ODI Teams: ' . $teams->count() . ' records';"

echo.
echo 5. Testing Routes...
php artisan route:list --name=rankings

echo.
echo =====================================
echo 🏏 TESTING SUMMARY
echo =====================================
echo.
echo 📋 Next Steps:
echo 1. Visit: http://localhost/rankings
echo 2. Test all category and type combinations
echo 3. Try the 'View Full List ^>' buttons
echo 4. Run: php artisan rankings:update --force
echo 5. Check logs: tail -f storage/logs/laravel.log
echo.
echo 🔧 If you see issues:
echo 1. Run: php artisan migrate
echo 2. Check .env file for API credentials
echo 3. Verify cron job is set up
echo 4. Check storage/logs/laravel.log for errors
echo.
echo 📚 Documentation:
echo - Testing Guide: RANKINGS_TESTING_GUIDE.md
echo - Setup Guide: RANKINGS_CRON_SETUP.md
echo.
echo ✅ Testing completed!
echo.
pause
