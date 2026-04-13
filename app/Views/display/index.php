<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Monitor - Queueing System</title>
    <style>
        /* CSS Variables for Theme Support */
        :root {
            --accent-color: #667eea;
            --accent-hover: #5568d3;
            --header-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: white;
            --text-primary: #2c3e50;
            --text-muted: #7f8c8d;
            --border-color: #ecf0f1;
            --bg-light: #f8f9fa;
        }
        
        [data-theme="dark"] {
            --accent-color: #666666;
            --accent-hover: #808080;
            --header-gradient: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
            --card-bg: rgba(30, 30, 30, 0.85);
            --text-primary: #eaeaea;
            --text-muted: #a0a0a0;
            --border-color: #404040;
            --bg-light: #1a1a1a;
        }
        
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
            padding: 10px;
            overflow-x: hidden;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 20px;
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
        
        .fullscreen-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.3);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
        }
        
        .fullscreen-btn.visible {
            opacity: 0.6;
            pointer-events: auto;
        }
        
        .fullscreen-btn.visible:hover {
            opacity: 1;
            background: rgba(255,255,255,0.5);
        }
        
        .fullscreen-btn:hover {
            background: rgba(255,255,255,0.5);
        }
        
        .fullscreen-btn svg {
            width: 20px;
            height: 20px;
            fill: white;
        }
        
        .fullscreen-btn.exit {
            background: rgba(231, 76, 60, 0.3);
        }
        
        .fullscreen-btn.exit:hover {
            background: rgba(231, 76, 60, 0.5);
        }
        
        /* Hover area to trigger button visibility */
        .fullscreen-hover-area {
            position: fixed;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            z-index: 99;
        }
        
        .main-layout {
            display: flex;
            gap: 20px;
            width: 100%;
            max-width: 100%;
            padding: 0 20px;
            justify-content: center;
        }
        
        .windows-section {
            flex: 1;
        }
        
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
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
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .sort-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            background: var(--header-gradient);
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
        
        /* Marquee Styles */
        .marquee-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px 0;
            overflow: hidden;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        
        .marquee-wrapper {
            display: flex;
            animation: marquee 40s linear infinite;
            width: max-content;
        }
        
        .marquee-wrapper:hover {
            animation-play-state: paused;
        }
        
        .marquee-text {
            display: flex;
            white-space: nowrap;
        }
        
        .marquee-text span {
            padding: 0 40px;
            font-size: 1.4rem;
            font-weight: 500;
        }
        
        @keyframes marquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        .window-display {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-align: center;
            transition: all 0.3s;
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
            background: var(--header-gradient);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: -30px -30px 20px -30px;
        }
        
        .window-header h2 {
            font-size: 1.8rem;
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
            font-size: 3rem;
            font-weight: bold;
            color: #27ae60;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 15px 0;
            min-height: 100px;
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

    <!-- Fullscreen Toggle Button -->
    <div class="fullscreen-hover-area" id="fullscreenHoverArea"></div>
    <button class="fullscreen-btn visible" id="fullscreenBtn" onclick="toggleFullscreen()" title="Toggle Fullscreen">
        <svg id="fullscreenIcon" viewBox="0 0 24 24">
            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
        </svg>
        <svg id="exitFullscreenIcon" viewBox="0 0 24 24" style="display: none;">
            <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
        </svg>
    </button>

    <!-- Scrolling Marquee -->
    <div class="marquee-container">
        <div class="marquee-wrapper">
            <div class="marquee-text">
                <span>Local Civil Registry Queue System</span>
                <span>Take Your Number and Wait for Your Turn</span>
                <span>PSA Window 1</span>
                <span>Birth Certificate Window 2</span>
                <span>Death Certificate Window 3</span>
                <span>Marriage Certificate Window 4</span>
                <span>Thank You for Your Patience</span>
                <span>LCR - Serving the Community</span>
            </div>
            <div class="marquee-text">
                <span>Local Civil Registry Queue System</span>
                <span>Take Your Number and Wait for Your Turn</span>
                <span>PSA Window 1</span>
                <span>Birth Certificate Window 2</span>
                <span>Death Certificate Window 3</span>
                <span>Marriage Certificate Window 4</span>
                <span>Thank You for Your Patience</span>
                <span>LCR - Serving the Community</span>
            </div>
        </div>
    </div>

    <script>
        // Fullscreen toggle function
        function toggleFullscreen() {
            const btn = document.getElementById('fullscreenBtn');
            
            if (!document.fullscreenElement && 
                !document.mozFullScreenElement && 
                !document.webkitFullscreenElement && 
                !document.msFullscreenElement) {
                // Enter fullscreen
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                }
                const fullscreenIcon = document.getElementById('fullscreenIcon');
                const exitFullscreenIcon = document.getElementById('exitFullscreenIcon');
                if (fullscreenIcon) fullscreenIcon.style.display = 'none';
                if (exitFullscreenIcon) exitFullscreenIcon.style.display = 'block';
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                const fullscreenIcon = document.getElementById('fullscreenIcon');
                const exitFullscreenIcon = document.getElementById('exitFullscreenIcon');
                if (fullscreenIcon) fullscreenIcon.style.display = 'block';
                if (exitFullscreenIcon) exitFullscreenIcon.style.display = 'none';
            }
        }
        
        // Listen for fullscreen change events (handles ESC key)
        document.addEventListener('fullscreenchange', updateFullscreenButton);
        document.addEventListener('mozfullscreenchange', updateFullscreenButton);
        document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
        document.addEventListener('msfullscreenchange', updateFullscreenButton);
        
        function updateFullscreenButton() {
            const btn = document.getElementById('fullscreenBtn');
            const fullscreenIcon = document.getElementById('fullscreenIcon');
            const exitFullscreenIcon = document.getElementById('exitFullscreenIcon');
            if (document.fullscreenElement || 
                document.mozFullScreenElement || 
                document.webkitFullscreenElement || 
                document.msFullscreenElement) {
                btn.classList.add('exit');
                if (fullscreenIcon) fullscreenIcon.style.display = 'none';
                if (exitFullscreenIcon) exitFullscreenIcon.style.display = 'block';
            } else {
                btn.classList.remove('exit');
                if (fullscreenIcon) fullscreenIcon.style.display = 'block';
                if (exitFullscreenIcon) exitFullscreenIcon.style.display = 'none';
            }
        }

        // Auto-hide fullscreen button after 5 seconds
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const fullscreenHoverArea = document.getElementById('fullscreenHoverArea');
        let hideTimeout;
        
        function showFullscreenButton() {
            fullscreenBtn.classList.add('visible');
        }
        
        function hideFullscreenButton() {
            fullscreenBtn.classList.remove('visible');
        }
        
        // Initial hide after 5 seconds
        setTimeout(hideFullscreenButton, 5000);
        
        // Show button when hovering over the hover area or the button itself
        fullscreenHoverArea.addEventListener('mouseenter', showFullscreenButton);
        fullscreenBtn.addEventListener('mouseenter', showFullscreenButton);
        
        // Hide button when leaving the hover area (but not when moving to the button)
        fullscreenHoverArea.addEventListener('mouseleave', function() {
            if (!fullscreenBtn.matches(':hover')) {
                hideFullscreenButton();
            }
        });
        
        fullscreenBtn.addEventListener('mouseleave', function() {
            hideFullscreenButton();
        });

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
