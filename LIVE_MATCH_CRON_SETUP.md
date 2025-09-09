# Live Match WebSocket Setup Guide

This guide explains how to set up real-time live match updates using WebSocket functionality.

## Features Implemented

### 1. Real-time Live Match Updates
- **Automatic Updates**: Live matches update every 20 seconds without page refresh
- **Visual Indicators**: Animated score changes and update indicators
- **Connection Status**: Shows connection status (Connected/Disconnected/Connecting)
- **Smart Polling**: Reduces update frequency when page is hidden

### 2. WebSocket Infrastructure
- **LiveMatchService**: Handles fetching and caching live match data
- **LiveMatchUpdate Event**: Broadcasts updates to connected clients
- **API Endpoints**: RESTful API for live match data
- **Cron Job**: Periodic updates every 20 seconds

### 3. Client-side Features
- **LiveMatchUpdater Class**: JavaScript class for handling real-time updates
- **Score Animations**: Smooth animations when scores change
- **Update Indicators**: Visual feedback for updated elements
- **Error Handling**: Automatic retry with exponential backoff

## Setup Instructions

### 1. Install Dependencies

```bash
# Install Pusher for WebSocket functionality (optional)
composer require pusher/pusher-php-server

# Install ReactPHP for custom WebSocket server (optional)
composer require react/socket
```

### 2. Configure Broadcasting

Add to your `.env` file:

```env
# Pusher Configuration (if using Pusher)
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=ap2

# Broadcasting Configuration
BROADCAST_DRIVER=pusher
```

### 3. Set Up Cron Jobs

Add to your crontab:

```bash
# Update live matches every 20 seconds
* * * * * php /path/to/your/project/artisan cricket:update-live-matches
* * * * * sleep 20; php /path/to/your/project/artisan cricket:update-live-matches
* * * * * sleep 40; php /path/to/your/project/artisan cricket:update-live-matches
```

### 4. Start WebSocket Server (Optional)

If using the custom WebSocket server:

```bash
# Start the WebSocket server
php websocket-server.php
```

### 5. Configure Laravel Broadcasting

Publish the broadcasting configuration:

```bash
php artisan vendor:publish --provider="Pusher\PusherServiceProvider"
```

## API Endpoints

### Get All Live Matches
```
GET /api/live-matches
```

### Get Specific Live Match
```
GET /api/live-matches/{matchKey}
```

### Check if Match is Live
```
GET /api/live-matches/{matchKey}/is-live
```

### Update Live Matches (Cron)
```
POST /api/live-matches/update
```

## JavaScript Usage

The live match updater automatically initializes on pages with live matches:

```javascript
// Check if updater is running
if (window.liveMatchUpdater) {
    console.log('Live match updater is running');
    
    // Stop updates
    window.liveMatchUpdater.stopUpdates();
    
    // Start updates
    window.liveMatchUpdater.startPeriodicUpdates();
}
```

## CSS Classes

### Live Update Animations
- `.score-updated` - Animated score changes
- `.live-updated` - General update indicator
- `.live-match-card.updating` - Card update animation
- `.connection-status` - Connection status indicator

### Data Attributes
- `data-match-key` - Unique match identifier
- `data-match-type="live"` - Identifies live matches
- `.home-score` - Home team score element
- `.away-score` - Away team score element
- `.match-status` - Match status element

## Customization

### Update Frequency
Modify the update interval in the JavaScript:

```javascript
// Change from 15 seconds to 10 seconds
this.updateInterval = setInterval(() => {
    this.fetchLiveMatches();
}, 10000);
```

### Visual Animations
Customize animations in the CSS:

```css
.score-updated {
    animation: scorePulse 0.8s ease-in-out; /* Longer animation */
    background-color: #fef3c7;
}
```

### Error Handling
Modify retry logic:

```javascript
this.maxRetries = 10; // Increase max retries
```

## Monitoring

### Check Live Matches
```bash
# Test API endpoint
curl http://your-domain.com/api/live-matches

# Check cron job
php artisan cricket:update-live-matches
```

### Debug Mode
Enable console logging by opening browser developer tools and checking the console for live update messages.

## Troubleshooting

### Common Issues

1. **Updates Not Working**
   - Check if cron job is running
   - Verify API endpoints are accessible
   - Check browser console for errors

2. **Connection Status Shows Offline**
   - Verify API endpoint is working
   - Check network connectivity
   - Ensure CSRF token is valid

3. **Animations Not Showing**
   - Check if CSS is loaded
   - Verify element classes are correct
   - Check for JavaScript errors

### Performance Optimization

1. **Reduce Update Frequency**
   - Increase interval time for less frequent updates
   - Use WebSocket instead of polling for better performance

2. **Optimize Data Transfer**
   - Only send changed data
   - Compress API responses
   - Use efficient data structures

## Security Considerations

1. **Rate Limiting**
   - Implement rate limiting on API endpoints
   - Use authentication for sensitive operations

2. **Data Validation**
   - Validate all incoming data
   - Sanitize user inputs
   - Use CSRF protection

3. **Error Handling**
   - Don't expose sensitive error messages
   - Log errors securely
   - Implement proper fallbacks

## Future Enhancements

1. **WebSocket Integration**
   - Replace polling with WebSocket connections
   - Implement real-time ball-by-ball updates
   - Add push notifications

2. **Advanced Features**
   - Match prediction updates
   - Live commentary integration
   - Social media integration

3. **Performance Improvements**
   - Server-sent events (SSE)
   - WebSocket clustering
   - CDN integration
