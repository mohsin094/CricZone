# Cricbuzz Cricket API Integration Setup

## Overview
This application has been updated to use the Cricbuzz Cricket API from RapidAPI instead of the previous cricket API. The new API provides comprehensive cricket data including live scores, match details, team information, and more.

## API Endpoints Available

### Series & Tournaments
- **Get All Series**: `/series/v1/international`
- **Get Series Details**: `/series/v1/{seriesId}`
- **Get Series Standings**: `/series/v1/{seriesId}/points`

### Matches
- **Get Live Matches**: `/matches/v1/live`
- **Get Upcoming Matches**: `/matches/v1/upcoming`
- **Get Recent Matches**: `/matches/v1/recent`
- **Get Match Details**: `/mcenter/v1/{matchId}`

### Match Details
- **Get Scorecard**: `/mcenter/v1/{matchId}/scard`
- **Get Commentary**: `/mcenter/v1/{matchId}/comm`
- **Get Lineups**: `/mcenter/v1/{matchId}/lineup`
- **Get Statistics**: `/mcenter/v1/{matchId}/stats`

### Teams & Players
- **Get Teams**: `/teams/v1/international`
- **Get Team Details**: `/teams/v1/{teamId}`
- **Get Players**: `/players/v1/international`
- **Get Player Details**: `/players/v1/{playerId}`

### News & Search
- **Get News**: `/news/v1/index`
- **Search**: `/search/v1/{query}`

## Environment Variables Required

Add these to your `.env` file:

```env
# Cricbuzz Cricket API Configuration
CRICBUZZ_API_KEY=your_rapidapi_key_here
CRICBUZZ_BASE_URL=https://cricbuzz-cricket.p.rapidapi.com
CRICBUZZ_CACHE_TTL=300

# Legacy Cricket API (kept for backward compatibility)
CRICKET_API_KEY=your_legacy_api_key_here
CRICKET_BASE_URL=https://apiv2.api-cricket.com/cricket/
CRICKET_CACHE_TTL=60
```

## Getting Your RapidAPI Key

1. Go to [RapidAPI Cricbuzz Cricket API](https://rapidapi.com/apiservicesprovider/api/cricbuzz-cricket2/)
2. Sign up or log in to RapidAPI
3. Subscribe to the Cricbuzz Cricket API
4. Copy your API key from the dashboard
5. Add it to your `.env` file as `CRICBUZZ_API_KEY`

## Code Structure

### Services Updated
- **CricketApiService**: Now uses Cricbuzz API endpoints
- **CricketDataService**: Updated to work with new API structure
- **TeamController**: Updated to use new field names

### Key Changes
- API base URL changed to Cricbuzz endpoints
- Field names updated to match Cricbuzz API response structure
- Enhanced caching strategy for different data types
- Legacy method compatibility maintained

### Field Name Mapping

| Old API Field | New Cricbuzz Field |
|---------------|-------------------|
| `event_home_team` | `homeTeam` or `team1` |
| `event_away_team` | `awayTeam` or `team2` |
| `event_status` | `status` or `matchStatus` |
| `event_date_start` | `date` or `startDate` |
| `event_key` | `id` or `matchId` |
| `team_key` | `id` |
| `team_name` | `name` |

## Usage Examples

### Get Live Matches
```php
$liveMatches = $cricketData->getLiveMatches();
```

### Get Series Information
```php
$series = $cricketData->getAllSeries();
$seriesDetails = $cricketData->getSeriesDetails($seriesId);
```

### Get Match Details
```php
$matchDetails = $cricketData->getComprehensiveMatchData($matchId);
```

### Get Team Information
```php
$teams = $cricketData->getTeamsFromApi();
```

## Caching Strategy

- **Live Data**: 5 minutes (matches, scores)
- **Series Data**: 1 hour (tournaments, teams)
- **News**: 30 minutes
- **Search Results**: 30 minutes

## Error Handling

The service includes comprehensive error handling:
- API failures are logged with detailed information
- Fallback mechanisms for critical data
- Graceful degradation when API is unavailable

## Testing

To test the integration:

1. Set up your API key in `.env`
2. Test the API status using the command:
   ```bash
   php artisan cricbuzz:test --type=status
   ```
3. Test all API endpoints:
   ```bash
   php artisan cricbuzz:test --type=all
   ```
4. Run the application
5. Check the teams page to see if data loads
6. Monitor logs for any API errors
7. Use the sync endpoint to populate the database

## Troubleshooting

### Common Issues

#### 1. "You are not subscribed to this API" (403 Error)
- **Solution**: Subscribe to the Cricbuzz Cricket API at RapidAPI
- **Steps**:
  1. Visit [RapidAPI Cricbuzz Cricket API](https://rapidapi.com/apiservicesprovider/api/cricbuzz-cricket2/)
  2. Sign up or log in to RapidAPI
  3. Subscribe to the API (free tier available)
  4. Copy your API key to `.env` file

#### 2. "Too many requests" (429 Error)
- **Solution**: Increase cache duration or upgrade your plan
- **Steps**:
  1. Increase `CRICBUZZ_CACHE_TTL` in `.env` (default: 300 seconds)
  2. Consider upgrading your RapidAPI plan for higher rate limits
  3. Use the test command to check current status

#### 3. API Key Not Working
- **Solution**: Verify your API key and configuration
- **Steps**:
  1. Check that `CRICBUZZ_API_KEY` is set in `.env`
  2. Verify the API key is correct in RapidAPI dashboard
  3. Run `php artisan cricbuzz:test --type=status` to check configuration

### Testing Commands

```bash
# Log API configuration (no API calls)
php artisan cricbuzz:config

# Check API status and subscription
php artisan cricbuzz:test --type=status

# Test matches API
php artisan cricbuzz:test --type=matches

# Test teams API
php artisan cricbuzz:test --type=teams

# Test all endpoints
php artisan cricbuzz:test --type=all

# Enable mock data (no API calls)
php artisan cricbuzz:mock enable

# Disable mock data (use real API)
php artisan cricbuzz:mock disable

# Show available mock data
php artisan cricbuzz:mock:show

# Test mock data functionality
php artisan cricbuzz:mock:test
```

### Debugging with Logs

The API now logs comprehensive information including:
- Complete API URLs being called
- Request headers and configuration
- Response status codes and headers
- Error details with full stack traces
- API configuration details

Check your logs at `storage/logs/laravel.log` for detailed API call information.

### Testing with Mock Data

To test the application without hitting the Cricbuzz API:

1. **Enable Mock Data:**
   ```bash
   php artisan cricbuzz:mock enable
   ```

2. **Test the Application:**
   - Visit your cricket pages
   - All data will come from mock data
   - No API calls will be made
   - Perfect for development and testing

3. **Disable Mock Data:**
   ```bash
   php artisan cricbuzz:mock disable
   ```

**Mock Data Includes:**
- Live matches (India vs Australia, England vs South Africa)
- Upcoming matches (Pakistan vs New Zealand, West Indies vs Bangladesh)
- Completed matches (Sri Lanka vs Afghanistan)
- Sample teams (India, Australia, England)

This allows you to test the UI and functionality without API subscription issues.

## Support

For API-related issues:
- Check RapidAPI dashboard for quota and status
- Review application logs for detailed error messages
- Verify API key is correctly set in environment variables
