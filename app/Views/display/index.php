<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Monitor - Queueing System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://images.pexels.com/photos/326055/pexels-photo-326055.jpeg?cs=srgb&dl=pexels-pixabay-326055.jpg&fm=jpg') center center/cover no-repeat fixed;
            background-color: #1e3c72;
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .header h1 {
            color: white;
            font-size: 2rem;
        }
        
        .header h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: normal;
            margin-top: 0;
        }
        
        .office-info {
            text-align: left;
        }
        
        .datetime {
            text-align: right;
            color: white;
        }
        
        .date {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .time {
            font-size: 2.5rem;
            font-weight: bold;
            color: #f1c40f;
        }
        
        .back-btn {
            padding: 12px 25px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: white;
            color: #2a5298;
        }
        
        .main-layout {
            display: flex;
            gap: 40px;
            max-width: 1600px;
            padding: 0 20px;
            justify-content: center;
        }
        
        .windows-section {
            flex: 1;
        }
        
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-top: 30px;
            width: 100%;
            max-width: 1400px;
        }
        
        .waiting-queue-section {
            flex: 0 0 400px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .queue-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .sort-btn {
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .sort-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .waiting-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .queue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
            transition: all 0.3s;
        }
        
        .queue-item:hover {
            background: #f8f9fa;
        }
        
        .queue-ticket {
            font-weight: bold;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .queue-time {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .service-section {
            margin-bottom: 30px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .service-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .service-header h3 {
            font-size: 1.3rem;
            font-weight: bold;
            margin: 0;
        }
        
        .service-count {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .service-queue {
            padding: 0;
        }
        
        .no-queue {
            text-align: center;
            color: #7f8c8d;
            font-size: 1.2rem;
            padding: 40px;
            font-style: italic;
        }
        
        .window-display {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-align: center;
            transition: all 0.3s;
            width: 700px;
        }
        
        .window-display:hover {
            transform: scale(1.02);
        }
        
        .next-compact {
            margin-top: 15px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .next-label {
            font-size: 1.2rem;
            color: #95a5a6;
            font-weight: normal;
        }
        
        .next-ticket {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e74c3c;
            background: none;
            padding: 0;
            border-radius: 0;
            min-height: auto;
            display: inline;
        }
        
        .next-ticket.none {
            color: #bdc3c7;
            font-size: 1.1rem;
        }
        
        .blink {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { 
                background: #f8f9fa; 
                color: #e74c3c;
            }
            25%, 75% { 
                background: #e74c3c; 
                color: white; 
            }
        }
        
        .window-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: -30px -30px 20px -30px;
        }
        
        .window-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .window-header p {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .now-serving-label {
            font-size: 1.2rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 15px;
        }
        
        .ticket-display {
            font-size: 4rem;
            font-weight: bold;
            color: #27ae60;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 20px 0;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .ticket-display.none {
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .windows-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .datetime {
                text-align: center;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="office-info">
            <h1>OFFICE OF THE LOCAL CIVIL REGISTRAR</h1>
            <h2>GENERAL SANTOS CITY</h2>
        </div>
        <div class="datetime">
            <div class="date" id="currentDate"><?= date('F d, Y') ?></div>
            <div class="time" id="currentTime"><?= date('h:i:s A') ?></div>
        </div>
        <?php if (isset($from_admin) && $from_admin): ?>
            <a href="<?= base_url('admin') ?>" class="back-btn">Back to Admin</a>
        <?php endif; ?>
    </div>

    <div class="main-layout">
        <div class="windows-section">
            <div class="windows-grid">
                <?php foreach ($windows as $window): ?>
                <div class="window-display">
                    <div class="window-header">
                        <h2 style="font-size: 2.5rem; font-weight: bold; text-align: center;">Window <?= $window['window_number'] ?> - <?= $window['prefix'] ?></h2>
                    </div>
                    <div class="now-serving-label">Now Serving:</div>
                    <div class="ticket-display <?= $window['now_serving'] === 'None' ? 'none' : '' ?>" id="window<?= $window['window_number'] ?>">
                        <?= $window['now_serving'] ?>
                    </div>
                    
                    <div class="next-compact">
                        <span class="next-label">Next:</span>
                        <span class="next-ticket" id="next<?= $window['window_number'] ?>">
                            <?= $window['next_ticket'] ?? 'None' ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Real-time clock
        function updateClock() {
            const now = new Date();
            
            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', dateOptions);
            
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('currentTime').textContent = timeString;
        }
        
        // Update clock every millisecond for smooth display
        setInterval(updateClock, 100);
        
        // Fetch queue data every 2 seconds
        let currentSortOrder = 'newest'; // 'newest' or 'oldest'
        let allWaitingData = [];
        
        function refreshData() {
            fetch('<?= base_url('display/data') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update window displays
                    data.windows.forEach(window => {
                        const element = document.getElementById('window' + window.window_number);
                        if (element) {
                            element.textContent = window.now_serving;
                            if (window.now_serving === 'None') {
                                element.classList.add('none');
                            } else {
                                element.classList.remove('none');
                            }
                        }
                        
                        // Update next ticket display
                        const nextElement = document.getElementById('next' + window.window_number);
                        if (nextElement) {
                            nextElement.textContent = window.next_ticket || 'None';
                            if (window.next_ticket === 'None' || !window.next_ticket) {
                                nextElement.classList.add('none');
                            } else {
                                nextElement.classList.remove('none');
                            }
                        }
                    });
                }
            });
        }
        
        // Function to handle blinking when called
        function blinkTicket(windowNumber) {
            const ticketElement = document.getElementById('window' + windowNumber);
            if (ticketElement && ticketElement.textContent !== 'None') {
                ticketElement.classList.add('blink');
                setTimeout(() => {
                    ticketElement.classList.remove('blink');
                }, 3000);
            }
        }
        
        // Listen for blink events using localStorage
        window.addEventListener('storage', function(e) {
            console.log('Storage event detected:', e.key, e.newValue);
            if (e.key === 'blinkTicket') {
                try {
                    const data = JSON.parse(e.newValue);
                    console.log('Parsed blink data:', data);
                    if (data.windowNumber && (Date.now() - data.timestamp) < 5000) {
                        console.log('Triggering blink for window:', data.windowNumber);
                        blinkTicket(data.windowNumber);
                    } else {
                        console.log('Blink event too old or missing window number');
                    }
                } catch (err) {
                    console.error('Error parsing blink event:', err);
                }
            }
        });
        
        // Also check for recent blink events on page load
        const recentBlink = localStorage.getItem('blinkTicket');
        if (recentBlink) {
            console.log('Recent blink found:', recentBlink);
            try {
                const data = JSON.parse(recentBlink);
                if (data.windowNumber && (Date.now() - data.timestamp) < 5000) {
                    console.log('Triggering delayed blink for window:', data.windowNumber);
                    setTimeout(() => blinkTicket(data.windowNumber), 1000);
                }
            } catch (err) {
                console.error('Error parsing recent blink event:', err);
            }
        }
        
        // Test function - you can run this in console to test blinking
        window.testBlink = function(windowNumber) {
            console.log('Test blink called for window:', windowNumber);
            blinkTicket(windowNumber);
        };
        
        // Test localStorage event
        window.testLocalStorage = function() {
            console.log('Testing localStorage event...');
            localStorage.setItem('blinkTicket', JSON.stringify({
                windowNumber: 1,
                timestamp: Date.now()
            }));
        };
        
        function updateWaitingQueue() {
            const waitingList = document.getElementById('waitingList');
            
            // Group data by service type
            const groupedData = {};
            allWaitingData.forEach(item => {
                const serviceType = item.window_prefix;
                if (!groupedData[serviceType]) {
                    groupedData[serviceType] = [];
                }
                groupedData[serviceType].push(item);
            });
            
            // Sort each group based on current order
            Object.keys(groupedData).forEach(serviceType => {
                if (currentSortOrder === 'newest') {
                    groupedData[serviceType].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                } else {
                    groupedData[serviceType].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                }
            });
            
            // Generate HTML with service type headers
            let html = '';
            Object.keys(groupedData).forEach(serviceType => {
                if (groupedData[serviceType].length > 0) {
                    html += `
                        <div class="service-section">
                            <div class="service-header">
                                <h3>${getServiceName(serviceType)}</h3>
                                <span class="service-count">${groupedData[serviceType].length} waiting</span>
                            </div>
                            <div class="service-queue">
                                ${groupedData[serviceType].map(item => `
                                    <div class="queue-item">
                                        <div class="queue-ticket">${item.window_prefix}-${item.ticket_number}</div>
                                        <div class="queue-time">${formatTime(item.created_at)}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
            });
            
            waitingList.innerHTML = html || '<div class="no-queue">No customers waiting</div>';
        }
        
        function getServiceName(prefix) {
            const serviceNames = {
                'BREQS': 'BREQS Services',
                'BIRTH': 'Birth Registration',
                'DEATH': 'Death Registration',
                'MARRIAGE': 'Marriage Registration'
            };
            return serviceNames[prefix] || prefix;
        }
        
        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true
            });
        }
        
        function toggleSort() {
            const sortIcon = document.getElementById('sortIcon');
            if (currentSortOrder === 'newest') {
                currentSortOrder = 'oldest';
                sortIcon.textContent = '▲';
            } else {
                currentSortOrder = 'newest';
                sortIcon.textContent = '▼';
            }
            // Refresh data to apply new sort order to all windows
            refreshData();
        }
        
        // Refresh data every 2 seconds
        setInterval(refreshData, 2000);
        
        // Initial load
        refreshData();
    </script>
</body>
</html>
