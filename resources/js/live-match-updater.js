class LiveMatchUpdater {
    constructor() {
        this.pusher = null;
        this.channel = null;
        this.liveMatches = new Map();
        this.updateInterval = null;
        this.isConnected = false;
        
        this.init();
    }

    init() {
        // Initialize Pusher
        this.initializePusher();
        
        // Start periodic updates
        this.startPeriodicUpdates();
        
        // Listen for visibility changes
        this.handleVisibilityChange();
    }

    initializePusher() {
        try {
            // Check if Pusher is available
            if (typeof Pusher === 'undefined') {
                console.warn('Pusher not loaded, falling back to polling');
                this.startPolling();
                return;
            }

            this.pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY || 'your-pusher-key', {
                cluster: process.env.MIX_PUSHER_APP_CLUSTER || 'ap2',
                encrypted: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }
            });

            // Subscribe to live matches channel
            this.channel = this.pusher.subscribe('live-matches');
            
            // Listen for match updates
            this.channel.bind('match.updated', (data) => {
                this.handleMatchUpdate(data);
            });

            // Listen for live matches updates
            this.channel.bind('live_matches_updated', (data) => {
                this.handleLiveMatchesUpdate(data);
            });

            this.isConnected = true;
            console.log('Connected to WebSocket for live updates');
        } catch (error) {
            console.error('Error initializing Pusher:', error);
            this.startPolling();
        }
    }

    startPolling() {
        console.log('Starting polling for live updates');
        
        // Poll every 15 seconds
        this.updateInterval = setInterval(() => {
            this.fetchLiveMatches();
        }, 15000);
        
        // Initial fetch
        this.fetchLiveMatches();
    }

    async fetchLiveMatches() {
        try {
            const response = await fetch('/api/live-matches', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.handleLiveMatchesUpdate(data);
        } catch (error) {
            console.error('Error fetching live matches:', error);
        }
    }

    handleMatchUpdate(data) {
        const { match_key, match_data, timestamp } = data;
        
        console.log('Match updated:', match_key, match_data);
        
        // Update the specific match
        this.liveMatches.set(match_key, {
            ...match_data,
            last_updated: timestamp
        });
        
        // Update the UI
        this.updateMatchInUI(match_key, match_data);
    }

    handleLiveMatchesUpdate(data) {
        const { live_matches, timestamp } = data;
        
        console.log('Live matches updated:', live_matches.length, 'matches');
        
        // Update all live matches
        live_matches.forEach(match => {
            this.liveMatches.set(match.event_key, {
                ...match,
                last_updated: timestamp
            });
        });
        
        // Update the UI
        this.updateAllMatchesInUI(live_matches);
    }

    updateMatchInUI(matchKey, matchData) {
        // Find the match card element
        const matchCard = document.querySelector(`[data-match-key="${matchKey}"]`);
        if (!matchCard) {
            return;
        }

        // Update scores
        this.updateMatchScores(matchCard, matchData);
        
        // Update status
        this.updateMatchStatus(matchCard, matchData);
        
        // Update overs
        this.updateMatchOvers(matchCard, matchData);
        
        // Add visual indicator for update
        this.showUpdateIndicator(matchCard);
    }

    updateAllMatchesInUI(liveMatches) {
        liveMatches.forEach(match => {
            this.updateMatchInUI(match.event_key, match);
        });
    }

    updateMatchScores(matchCard, matchData) {
        // Update home team score
        const homeScoreElement = matchCard.querySelector('.home-score');
        if (homeScoreElement && matchData.event_home_final_result) {
            const newScore = matchData.event_home_final_result;
            if (homeScoreElement.textContent !== newScore) {
                this.animateScoreChange(homeScoreElement, newScore);
            }
        }

        // Update away team score
        const awayScoreElement = matchCard.querySelector('.away-score');
        if (awayScoreElement && matchData.event_away_final_result) {
            const newScore = matchData.event_away_final_result;
            if (awayScoreElement.textContent !== newScore) {
                this.animateScoreChange(awayScoreElement, newScore);
            }
        }
    }

    updateMatchStatus(matchCard, matchData) {
        const statusElement = matchCard.querySelector('.match-status');
        if (statusElement) {
            const newStatus = matchData.status || matchData.event_status_info || matchData.event_state_title || 'Match in Progress';
            if (statusElement.textContent !== newStatus) {
                statusElement.textContent = newStatus;
                this.showUpdateIndicator(statusElement);
            }
        }
    }

    updateMatchOvers(matchCard, matchData) {
        // Update home team overs
        const homeOversElement = matchCard.querySelector('.home-overs');
        if (homeOversElement && matchData.event_home_overs) {
            const newOvers = this.formatOvers(matchData.event_home_overs);
            if (homeOversElement.textContent !== newOvers) {
                homeOversElement.textContent = newOvers;
                this.showUpdateIndicator(homeOversElement);
            }
        }

        // Update away team overs
        const awayOversElement = matchCard.querySelector('.away-overs');
        if (awayOversElement && matchData.event_away_overs) {
            const newOvers = this.formatOvers(matchData.event_away_overs);
            if (awayOversElement.textContent !== newOvers) {
                awayOversElement.textContent = newOvers;
                this.showUpdateIndicator(awayOversElement);
            }
        }
    }

    formatOvers(overs) {
        if (!overs || overs === '0.0') return '';
        
        const decimalOvers = parseFloat(overs);
        const fullOvers = Math.floor(decimalOvers);
        const balls = (decimalOvers - fullOvers) * 10;
        
        if (balls >= 6) {
            return (fullOvers + 1).toString();
        }
        
        return fullOvers.toString();
    }

    animateScoreChange(element, newScore) {
        // Add animation class
        element.classList.add('score-updated');
        
        // Update the score
        element.textContent = newScore;
        
        // Remove animation class after animation
        setTimeout(() => {
            element.classList.remove('score-updated');
        }, 1000);
    }

    showUpdateIndicator(element) {
        // Add update indicator
        element.classList.add('live-updated');
        
        // Remove after animation
        setTimeout(() => {
            element.classList.remove('live-updated');
        }, 2000);
    }

    startPeriodicUpdates() {
        // Update every 30 seconds as fallback
        setInterval(() => {
            if (!this.isConnected) {
                this.fetchLiveMatches();
            }
        }, 30000);
    }

    handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Page is hidden, reduce update frequency
                if (this.updateInterval) {
                    clearInterval(this.updateInterval);
                    this.updateInterval = setInterval(() => {
                        this.fetchLiveMatches();
                    }, 60000); // 1 minute when hidden
                }
            } else {
                // Page is visible, resume normal updates
                if (this.updateInterval) {
                    clearInterval(this.updateInterval);
                    this.updateInterval = setInterval(() => {
                        this.fetchLiveMatches();
                    }, 15000); // 15 seconds when visible
                }
                
                // Immediate update when page becomes visible
                this.fetchLiveMatches();
            }
        });
    }

    destroy() {
        if (this.pusher) {
            this.pusher.disconnect();
        }
        
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on pages with live matches
    if (document.querySelector('.live-match-card') || document.querySelector('[data-match-type="live"]')) {
        window.liveMatchUpdater = new LiveMatchUpdater();
    }
});

// Cleanup when page unloads
window.addEventListener('beforeunload', function() {
    if (window.liveMatchUpdater) {
        window.liveMatchUpdater.destroy();
    }
});
