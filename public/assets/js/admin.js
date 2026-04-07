let confirmCallback = null;

// Smooth scrolling for navigation links
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to all anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

function confirmResetWindows() {
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all windows and clear all queues?';
    confirmCallback = resetWindows;
    document.getElementById('confirmModal').classList.add('active');
}

function confirmResetNumbers() {
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all released numbers?';
    confirmCallback = resetNumbers;
    document.getElementById('confirmModal').classList.add('active');
}

function confirmResetDailyStats() {
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all daily statistics?';
    confirmCallback = resetDailyStats;
    document.getElementById('confirmModal').classList.add('active');
}

function confirmResetMonthlyStats() {
    document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all monthly statistics?';
    confirmCallback = resetMonthlyStats;
    document.getElementById('confirmModal').classList.add('active');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
}

function resetWindows() {
    console.log("resetWindows function called");
    const url = window.baseUrl + 'admin/reset-windows';
    console.log("Calling URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(r => {
        console.log("Response received:", r);
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            showNotification('✅ Queues Reset Done');
            refreshData();
        } else {
            console.error("Reset failed:", data.message);
            alert('Reset failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Reset windows error:', err);
        alert('Error resetting windows. Please try again.');
    });
}

function resetNumbers() {
    console.log("resetNumbers function called");
    const url = window.baseUrl + 'admin/reset-numbers';
    console.log("Calling URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(r => {
        console.log("Response received:", r);
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            showNotification('✅ Numbers Reset Done');
            refreshData();
        } else {
            console.error("Reset failed:", data.message);
            alert('Reset failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Reset numbers error:', err);
        alert('Error resetting numbers. Please try again.');
    });
}

function resetDailyStats() {
    console.log("resetDailyStats function called");
    const url = window.baseUrl + 'admin/reset-daily-stats';
    console.log("Calling URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(r => {
        console.log("Response received:", r);
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            showNotification('✅ Daily Statistics Reset Done');
            refreshData();
        } else {
            console.error("Reset failed:", data.message);
            alert('Reset failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Reset daily stats error:', err);
        alert('Error resetting daily statistics. Please try again.');
    });
}

function resetMonthlyStats() {
    console.log("resetMonthlyStats function called");
    const url = window.baseUrl + 'admin/reset-monthly-stats';
    console.log("Calling URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(r => {
        console.log("Response received:", r);
        if (!r.ok) {
            throw new Error(`HTTP error! status: ${r.status}`);
        }
        return r.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            showNotification('✅ Monthly Statistics Reset Done');
            refreshData();
        } else {
            console.error("Reset failed:", data.message);
            alert('Reset failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Reset monthly stats error:', err);
        alert('Error resetting monthly statistics. Please try again.');
    });
}

function showNotification(message) {
    const notif = document.createElement('div');
    notif.style.cssText = 'position:fixed;top:20px;right:20px;background:#27ae60;color:white;padding:15px 20px;border-radius:8px;z-index:10000;font-size:16px;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
    notif.textContent = message;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}

function callNext(windowId) {
    console.log("callNext called with windowId:", windowId);
    const url = window.baseUrl + 'window/callNext/' + windowId;
    console.log("Call Next URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        console.log("Call Next response received:", r);
        return r.json();
    })
    .then(data => {
        console.log("Call Next response data:", data);
        if (data.success) {
            showNotification('✅ Next Customer Called Successfully');
            refreshData();
            if (typeof $ !== 'undefined' && $('#queueTable').length) {
                $('#queueTable').DataTable().ajax.reload();
            }
            
            if (data.window_number) {
                console.log('Setting blink event for window:', data.window_number);
                localStorage.setItem('blinkTicket', JSON.stringify({
                    windowNumber: data.window_number,
                    timestamp: Date.now()
                }));
                console.log('Blink event set in localStorage');
            }
        } else {
            console.error("Call Next failed:", data.message);
            alert('Call Next failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Call Next error:', err);
        alert('Error calling next customer. Please try again.');
    });
}

function skipQueue(id) {
    console.log("skipQueue called with id:", id);
    const url = window.baseUrl + 'admin/skip/' + id;
    console.log("Skip URL:", url);
    fetch(url, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        console.log("Skip response received:", r);
        return r.json();
    })
    .then(data => {
        console.log("Skip response data:", data);
        if (data.success) {
            showNotification('⏭️ Queue Skipped Successfully');
            refreshData();
        } else {
            console.error("Skip failed:", data.message);
            alert('Skip failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Skip queue error:', err);
        alert('Error skipping queue. Please try again.');
    });
}

function refreshData() {
    console.log("refreshData called - updating all statistics in real-time");
    fetch(window.baseUrl + 'admin/get-data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        console.log("refreshData response:", data);
        if (data.success) {
            console.log("Updating windows and stats...");
            updateWindows(data.windows);
            updateStats(data.daily_stats, data.monthly_stats);
            console.log("Real-time statistics update completed");
        } else {
            console.error("refreshData failed:", data);
        }
    })
    .catch(err => {
        console.error('Refresh data error:', err);
    });
}

function updateWindows(windows) {
    windows.forEach(window => {
        const windowWidgets = document.querySelectorAll('.window-widget');
        let targetWidget = null;
        
        windowWidgets.forEach(widget => {
            const header = widget.querySelector('.window-header h3');
            if (header && header.textContent.includes('Window ' + window.window_number)) {
                targetWidget = widget;
            }
        });
        
        if (targetWidget) {
            const nowServing = targetWidget.querySelector('.now-serving');
            const waitingCount = targetWidget.querySelector('.waiting-count');
            const waitingList = targetWidget.querySelector('.waiting-list');
            const actionsDiv = targetWidget.querySelector('.window-actions');
            
            if (nowServing) nowServing.textContent = window.now_serving;
            if (waitingCount) waitingCount.textContent = 'Waiting: ' + window.waiting_count;
            
            if (waitingList && window.waiting_list && window.waiting_list.length > 0) {
                waitingList.innerHTML = window.waiting_list
                    .map(item => `<div class="waiting-item">${item.ticket_number}</div>`)
                    .join('');
            } else if (waitingList) {
                waitingList.innerHTML = '';
            }
            
            if (actionsDiv) {
                actionsDiv.innerHTML = `
                    <button class="btn btn-call-next btn-small" onclick="callNext(${window.id})">Call Next</button>
                    ${window.serving_queue_id ? `<button class="btn btn-danger btn-small" onclick="skipQueue(${window.serving_queue_id})">Skip</button>` : ''}
                    <a href="${window.baseUrl}window/${window.window_number}?from_admin=true" class="btn-go-window btn-small">Go to Window ${window.window_number}</a>
                `;
            }
        }
    });
}

function updateStats(dailyStats, monthlyStats) {
    console.log("updateStats called - Daily:", dailyStats, "Monthly:", monthlyStats);
    
    const statsGrids = document.querySelectorAll('.stats-grid');
    const dailyGrid = statsGrids[0];
    const dailyCards = dailyGrid.querySelectorAll('.stat-card');
    
    let dailyCompleted = 0;
    let dailySkipped = 0;
    
    dailyStats.forEach((stat, index) => {
        dailyCompleted += parseInt(stat.completed) || 0;
        dailySkipped += parseInt(stat.skipped) || 0;
        if (dailyCards[index]) {
            const valueElement = dailyCards[index].querySelector('.stat-value');
            if (valueElement) {
                valueElement.textContent = stat.completed || 0;
                console.log(`Updated ${stat.window_name} completed to:`, stat.completed);
            }
        }
    });
    
    const totalCompletedIndex = dailyCards.length - 2;
    const totalSkippedIndex = dailyCards.length - 1;
    
    if (dailyCards[totalCompletedIndex]) {
        dailyCards[totalCompletedIndex].querySelector('.stat-value').textContent = dailyCompleted;
        console.log("Updated total completed to:", dailyCompleted);
    }
    if (dailyCards[totalSkippedIndex]) {
        dailyCards[totalSkippedIndex].querySelector('.stat-value').textContent = dailySkipped;
        console.log("Updated total skipped to:", dailySkipped);
    }
    
    console.log("Daily stats updated - Completed:", dailyCompleted, "Skipped:", dailySkipped);
    
    if (statsGrids[1]) {
        const monthlyGrid = statsGrids[1];
        const monthlyCards = monthlyGrid.querySelectorAll('.stat-card');
        let monthlyCompleted = 0;
        let monthlySkipped = 0;
        
        monthlyStats.forEach((stat, index) => {
            monthlyCompleted += parseInt(stat.completed) || 0;
            monthlySkipped += parseInt(stat.skipped) || 0;
            if (monthlyCards[index]) {
                const valueElement = monthlyCards[index].querySelector('.stat-value');
                if (valueElement) {
                    valueElement.textContent = stat.completed || 0;
                    console.log(`Updated monthly ${stat.window_name} completed to:`, stat.completed);
                }
            }
        });
        
        const monthlyTotalCompletedIndex = monthlyCards.length - 2;
        const monthlyTotalSkippedIndex = monthlyCards.length - 1;
        
        if (monthlyCards[monthlyTotalCompletedIndex]) {
            monthlyCards[monthlyTotalCompletedIndex].querySelector('.stat-value').textContent = monthlyCompleted;
            console.log("Updated monthly total completed to:", monthlyCompleted);
        }
        if (monthlyCards[monthlyTotalSkippedIndex]) {
            monthlyCards[monthlyTotalSkippedIndex].querySelector('.stat-value').textContent = monthlySkipped;
            console.log("Updated monthly total skipped to:", monthlySkipped);
        }
        
        console.log("Monthly stats updated - Completed:", monthlyCompleted, "Skipped:", monthlySkipped);
    }
}

// Auto refresh disabled to prevent spam
// setInterval(refreshData, 3000);

// Toggle System Controls drawer
function toggleSystemControls(event) {
    event.preventDefault();
    console.log("Toggle System Controls clicked");
    const container = document.getElementById('system-controls-container');
    console.log("Container found:", container);
    
    if (container.classList.contains('show')) {
        container.classList.remove('show');
        console.log("Hiding System Controls");
    } else {
        container.classList.add('show');
        console.log("Showing System Controls");
    }
}

// Setup confirm button listener
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmYes');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        });
    }
});
