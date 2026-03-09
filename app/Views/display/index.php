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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            margin: 0 auto;
            padding: 0 20px;
            justify-content: space-between;
        }
        
        .windows-section {
            flex: 1;
        }
        
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
            align-items: left;
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
                    <div class="now-serving-label">Now Serving</div>
                    <div class="ticket-display <?= $window['now_serving'] === 'None' ? 'none' : '' ?>" id="window<?= $window['window_number'] ?>">
                        <?= $window['now_serving'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="waiting-queue-section">
            <div class="queue-header">
                <div class="queue-title">Waiting Queue</div>
                <button class="sort-btn" onclick="toggleSort()">
                    <span id="sortIcon">▼</span> Sort
                </button>
            </div>
            <div class="waiting-list" id="waitingList">
                <!-- Queue items will be populated by JavaScript -->
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
                    });
                    
                    // Update waiting queue
                    allWaitingData = [];
                    data.windows.forEach(window => {
                        if (window.waiting_list && window.waiting_list.length > 0) {
                            window.waiting_list.forEach(item => {
                                allWaitingData.push({
                                    ticket_number: item.ticket_number,
                                    created_at: item.created_at,
                                    window_prefix: window.prefix
                                });
                            });
                        }
                    });
                    
                    updateWaitingQueue();
                }
            });
        }
        
        function updateWaitingQueue() {
            const waitingList = document.getElementById('waitingList');
            
            // Sort data based on current order
            let sortedData = [...allWaitingData];
            if (currentSortOrder === 'newest') {
                sortedData.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else {
                sortedData.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            }
            
            // Generate HTML
            waitingList.innerHTML = sortedData.map((item, index) => `
                <div class="queue-item">
                    <div class="queue-ticket">${item.window_prefix}-${item.ticket_number}</div>
                    <div class="queue-time">${formatTime(item.created_at)}</div>
                </div>
            `).join('');
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
            updateWaitingQueue();
        }
        
        // Refresh data every 2 seconds
        setInterval(refreshData, 2000);
        
        // Initial load
        refreshData();
    </script>
</body>
</html>
