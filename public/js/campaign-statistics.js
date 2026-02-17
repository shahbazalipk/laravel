/**
 * Campaign Statistics Dashboard
 * Real-time statistics updates with Chart.js visualizations
 */

class CampaignStatistics {
    constructor(options = {}) {
        this.options = {
            campaignId: options.campaignId,
            refreshInterval: options.refreshInterval || 30000, // 30 seconds
            statisticsEndpoint: options.statisticsEndpoint,
            autoRefresh: options.autoRefresh !== false,
            ...options
        };
        
        this.charts = {};
        this.refreshTimer = null;
        this.isRefreshing = false;
        
        this.init();
    }
    
    init() {
        this.initCharts();
        
        if (this.options.autoRefresh) {
            this.startAutoRefresh();
        }
        
        // Manual refresh button
        const refreshBtn = document.querySelector('[onclick*="window.location.reload"]');
        if (refreshBtn) {
            refreshBtn.onclick = (e) => {
                e.preventDefault();
                this.refreshStatistics();
            };
        }
    }
    
    /**
     * Initialize Chart.js charts
     */
    initCharts() {
        this.initDeliveryChart();
        this.initEngagementChart();
        this.initTimelineChart();
    }
    
    /**
     * Initialize delivery funnel chart
     */
    initDeliveryChart() {
        const canvas = document.getElementById('delivery-chart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Get data from page
        const sent = parseInt(canvas.dataset.sent || 0);
        const delivered = parseInt(canvas.dataset.delivered || 0);
        const bounced = parseInt(canvas.dataset.bounced || 0);
        const failed = parseInt(canvas.dataset.failed || 0);
        
        this.charts.delivery = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Sent', 'Delivered', 'Bounced', 'Failed'],
                datasets: [{
                    label: 'Email Delivery',
                    data: [sent, delivered, bounced, failed],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',  // Blue
                        'rgba(16, 185, 129, 0.8)',  // Green
                        'rgba(251, 146, 60, 0.8)',  // Orange
                        'rgba(239, 68, 68, 0.8)'    // Red
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(251, 146, 60)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Delivery Funnel'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Initialize engagement pie chart
     */
    initEngagementChart() {
        const canvas = document.getElementById('engagement-chart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Get data from page
        const delivered = parseInt(canvas.dataset.delivered || 0);
        const opened = parseInt(canvas.dataset.opened || 0);
        const clicked = parseInt(canvas.dataset.clicked || 0);
        const notOpened = delivered - opened;
        
        this.charts.engagement = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Clicked', 'Opened (not clicked)', 'Not Opened'],
                datasets: [{
                    data: [clicked, opened - clicked, notOpened],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',   // Indigo
                        'rgba(168, 85, 247, 0.8)',   // Purple
                        'rgba(209, 213, 219, 0.8)'   // Gray
                    ],
                    borderColor: [
                        'rgb(99, 102, 241)',
                        'rgb(168, 85, 247)',
                        'rgb(209, 213, 219)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Engagement Breakdown'
                    }
                }
            }
        });
    }
    
    /**
     * Initialize timeline chart
     */
    initTimelineChart() {
        const canvas = document.getElementById('timeline-chart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        // Get data from page (would come from server in real implementation)
        const timelineData = this.parseTimelineData(canvas.dataset.timeline);
        
        this.charts.timeline = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timelineData.labels,
                datasets: [
                    {
                        label: 'Sent',
                        data: timelineData.sent,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Delivered',
                        data: timelineData.delivered,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Opened',
                        data: timelineData.opened,
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Clicked',
                        data: timelineData.clicked,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Campaign Performance Over Time'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Parse timeline data from dataset
     */
    parseTimelineData(dataString) {
        if (!dataString) {
            // Return empty data if no timeline available
            return {
                labels: [],
                sent: [],
                delivered: [],
                opened: [],
                clicked: []
            };
        }
        
        try {
            return JSON.parse(dataString);
        } catch (e) {
            console.error('Failed to parse timeline data:', e);
            return {
                labels: [],
                sent: [],
                delivered: [],
                opened: [],
                clicked: []
            };
        }
    }
    
    /**
     * Start auto-refresh timer
     */
    startAutoRefresh() {
        const self = this;
        
        this.refreshTimer = setInterval(() => {
            self.refreshStatistics();
        }, this.options.refreshInterval);
        
        // Show countdown
        this.showRefreshCountdown();
    }
    
    /**
     * Stop auto-refresh timer
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }
    
    /**
     * Show refresh countdown
     */
    showRefreshCountdown() {
        const countdownEl = document.getElementById('refresh-countdown');
        if (!countdownEl) return;
        
        let seconds = this.options.refreshInterval / 1000;
        const self = this;
        
        const countdownTimer = setInterval(() => {
            seconds--;
            countdownEl.textContent = `Next refresh in ${seconds}s`;
            
            if (seconds <= 0) {
                clearInterval(countdownTimer);
                setTimeout(() => {
                    self.showRefreshCountdown();
                }, 1000);
            }
        }, 1000);
    }
    
    /**
     * Refresh statistics from server
     */
    async refreshStatistics() {
        if (this.isRefreshing || !this.options.statisticsEndpoint) return;
        
        this.isRefreshing = true;
        this.showRefreshIndicator();
        
        try {
            const response = await fetch(this.options.statisticsEndpoint);
            
            if (response.ok) {
                const data = await response.json();
                this.updateStatistics(data);
            }
        } catch (error) {
            console.error('Failed to refresh statistics:', error);
        } finally {
            this.isRefreshing = false;
            this.hideRefreshIndicator();
        }
    }
    
    /**
     * Update statistics on page
     */
    updateStatistics(data) {
        // Update stat cards
        this.updateStatCard('total_recipients', data.total_recipients);
        this.updateStatCard('sent_count', data.sent_count);
        this.updateStatCard('delivered_count', data.delivered_count, data.delivery_rate);
        this.updateStatCard('opened_count', data.opened_count, data.open_rate);
        this.updateStatCard('clicked_count', data.clicked_count, data.click_rate);
        this.updateStatCard('bounced_count', data.bounced_count);
        this.updateStatCard('failed_count', data.failed_count);
        this.updateStatCard('unsubscribed_count', data.unsubscribed_count);
        
        // Update progress bar if sending
        if (data.status === 'sending') {
            this.updateProgressBar(data.sent_count, data.total_recipients);
        }
        
        // Update charts
        this.updateCharts(data);
    }
    
    /**
     * Update stat card
     */
    updateStatCard(key, value, percentage = null) {
        const card = document.querySelector(`[data-stat="${key}"]`);
        if (!card) return;
        
        const valueEl = card.querySelector('.stat-value');
        if (valueEl) {
            valueEl.textContent = value.toLocaleString();
        }
        
        if (percentage !== null) {
            const percentEl = card.querySelector('.stat-percent');
            if (percentEl) {
                percentEl.textContent = `${percentage.toFixed(1)}%`;
            }
        }
    }
    
    /**
     * Update progress bar
     */
    updateProgressBar(sent, total) {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress-text');
        
        if (progressBar && total > 0) {
            const percentage = (sent / total) * 100;
            progressBar.style.width = `${percentage}%`;
            
            if (progressText) {
                progressText.textContent = `${sent.toLocaleString()} of ${total.toLocaleString()} sent (${percentage.toFixed(1)}%)`;
            }
        }
    }
    
    /**
     * Update charts with new data
     */
    updateCharts(data) {
        // Update delivery chart
        if (this.charts.delivery) {
            this.charts.delivery.data.datasets[0].data = [
                data.sent_count,
                data.delivered_count,
                data.bounced_count,
                data.failed_count
            ];
            this.charts.delivery.update();
        }
        
        // Update engagement chart
        if (this.charts.engagement) {
            const notOpened = data.delivered_count - data.opened_count;
            this.charts.engagement.data.datasets[0].data = [
                data.clicked_count,
                data.opened_count - data.clicked_count,
                notOpened
            ];
            this.charts.engagement.update();
        }
        
        // Update timeline chart (if timeline data provided)
        if (this.charts.timeline && data.timeline) {
            this.charts.timeline.data.labels = data.timeline.labels;
            this.charts.timeline.data.datasets[0].data = data.timeline.sent;
            this.charts.timeline.data.datasets[1].data = data.timeline.delivered;
            this.charts.timeline.data.datasets[2].data = data.timeline.opened;
            this.charts.timeline.data.datasets[3].data = data.timeline.clicked;
            this.charts.timeline.update();
        }
    }
    
    /**
     * Show refresh indicator
     */
    showRefreshIndicator() {
        const indicator = document.getElementById('refresh-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
        }
    }
    
    /**
     * Hide refresh indicator
     */
    hideRefreshIndicator() {
        const indicator = document.getElementById('refresh-indicator');
        if (indicator) {
            indicator.classList.add('hidden');
        }
    }
    
    /**
     * Cleanup on destroy
     */
    destroy() {
        this.stopAutoRefresh();
        
        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
        
        this.charts = {};
    }
}

// Global instance
let campaignStatistics = null;
