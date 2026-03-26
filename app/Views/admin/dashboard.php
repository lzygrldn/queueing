<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Queueing System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://www.baltana.com/files/wallpapers-2/Simple-Powerpoint-Background-Pics-07280.jpg') center center/cover no-repeat fixed;
            background-color: #f5f6fa;
            min-height: 100vh;
            padding-top: 0; /* No padding needed since smooth scroll accounts for header height */
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .header:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #fff;
            color: #667eea;
        }
        
        .btn-primary:hover {
            background: #f0f0f0;
        }
        
        .btn-call-next {
            background: #27ae60;
            color: white;
            border: none;
        }
        
        .btn-call-next:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        /* Window Widgets */
        .windows-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .window-widget {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .window-header {
            background: #667eea;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: -20px -20px 20px -20px;
        }
        
        .window-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .window-header .prefix {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .window-info {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .now-serving {
            font-size: 1.8rem;
            font-weight: bold;
            color: #27ae60;
            margin: 10px 0;
        }
        
        .waiting-count {
            font-size: 1.2rem;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        .window-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .window-controls {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 0.8rem;
        }
        
        .btn-go-window {
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-go-window:hover {
            background: #2980b9;
            color: white;
        }
        
        /* Data Table */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-waiting { background: #ffeaa7; color: #d63031; }
        .status-serving { background: #74b9ff; color: #0984e3; }
        .status-completed { background: #55efc4; color: #00b894; }
        .status-skipped { background: #fab1a0; color: #e17055; }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        /* Reset Buttons */
        .reset-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .reset-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        
        .modal h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .modal p {
            margin-bottom: 25px;
            color: #7f8c8d;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .waiting-list {
            max-height: 150px;
            overflow-y: auto;
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .waiting-item {
            padding: 5px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .waiting-item:last-child {
            border-bottom: none;
        }
        
        .window-filter-dropdown {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
            min-width: 150px;
        }
        
        .window-filter-dropdown:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        /* System Controls Drawer */
        #system-controls-container {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            z-index: 999;
            background: #76717185;
            border-bottom: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-100%);
            transition: transform 0.3s ease-in-out;
            padding: 0;
        }
        
        #system-controls-container.show {
            transform: translateY(0);
            padding: 15px 0;
        }
        
        #system-controls-container h3 {
            color: #495057;
            font-size: 1.2rem;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        #system-controls-container .dev-warning {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 12px 16px;
            margin: 20px 20px 16px 20px;
            color: #000000;
            font-size: 0.95rem;
            text-align: center;
            font-weight: 600;
        }
        
        #system-controls-container .reset-buttons {
            padding: 0 20px;
            text-align: center;
        }
        
        /* Glass-like Reset Buttons */
        #system-controls-container .btn-reset {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            color: #495057;
            border: 1px solid rgba(220, 53, 69, 0.5);
            border-radius: 8px;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 8px 8px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        #system-controls-container .btn-reset:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(220, 53, 69, 0.8);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
        
        #system-controls-container .btn-reset:active {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(0);
        }
        
        /* Button Variants - All Red Borders */
        #system-controls-container .btn-reset-windows {
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        
        #system-controls-container .btn-reset-windows:hover {
            border-color: rgba(220, 53, 69, 0.8);
            background: rgba(220, 53, 69, 0.05);
        }
        
        #system-controls-container .btn-reset-numbers {
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        
        #system-controls-container .btn-reset-numbers:hover {
            border-color: rgba(220, 53, 69, 0.8);
            background: rgba(220, 53, 69, 0.05);
        }
        
        #system-controls-container .btn-reset-daily {
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        
        #system-controls-container .btn-reset-daily:hover {
            border-color: rgba(220, 53, 69, 0.8);
            background: rgba(220, 53, 69, 0.05);
        }
        
        #system-controls-container .btn-reset-monthly {
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        
        #system-controls-container .btn-reset-monthly:hover {
            border-color: rgba(220, 53, 69, 0.8);
            background: rgba(220, 53, 69, 0.05);
        }
        
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="header-buttons">
            <button class="btn btn-primary" onclick="toggleSystemControls(event)">System Controls</button>
            <a href="<?= base_url('admin/customer-records') ?>" class="btn btn-primary">Customer Records</a>
            <a href="<?= base_url('queue') ?>" class="btn btn-primary">Queue</a>
            <a href="<?= base_url('admin/display') ?>" class="btn btn-primary">Display</a>
            <form action="<?= base_url('admin/logout') ?>" method="POST" style="display: inline;">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>

    <!-- System Controls Drawer -->
    <div class="container" id="system-controls-container">
        <div class="dev-warning">
            ⚠️ <strong>Development Use Only</strong> - These controls are for development/testing purposes and should not be used when the system is deployed.
        </div>
        <div class="reset-buttons">
            <button class="btn-reset btn-reset-windows" onclick="confirmResetWindows()">Reset Windows & Queues</button>
            <button class="btn-reset btn-reset-numbers" onclick="confirmResetNumbers()">Reset Released Numbers</button>
            <button class="btn-reset btn-reset-daily" onclick="confirmResetDailyStats()">Reset Daily Statistics</button>
            <button class="btn-reset btn-reset-monthly" onclick="confirmResetMonthlyStats()">Reset Monthly Statistics</button>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title" id="window-status">Window Status</h2>
        <div class="windows-grid">
            <?php foreach ($windows as $window): ?>
            <div class="window-widget">
                <div class="window-header">
                    <h3>Window <?= $window['window_number'] ?></h3>
                    <div class="prefix"><?= $window['window_name'] ?></div>
                </div>
                <div class="window-info">
                    <div>Now Serving</div>
                    <div class="now-serving"><?= $window['now_serving'] ?></div>
                    <div class="waiting-count">Waiting: <?= $window['waiting_count'] ?></div>
                    <?php if (!empty($window['waiting_list'])): ?>
                    <div class="waiting-list">
                        <?php foreach ($window['waiting_list'] as $waiting): ?>
                            <div class="waiting-item"><?= $waiting['ticket_number'] ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="window-actions">
                    <button class="btn btn-call-next btn-small" onclick="callNext(<?= $window['id'] ?>)">Call Next</button>
                    <?php if ($window['serving_queue_id']): ?>
                    <button class="btn btn-danger btn-small" onclick="skipQueue(<?= $window['serving_queue_id'] ?>)">Skip</button>
                    <?php endif; ?>
                    <a href="<?= base_url('window/' . $window['window_number']) ?>?from_admin=true" class="btn-go-window btn-small">Go to Window <?= $window['window_number'] ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title" id="statistics">Daily Statistics (<?= date('F d, Y') ?>)</h2>
        <div class="stats-grid">
            <?php 
            $totalCompleted = 0;
            $totalSkipped = 0;
            foreach ($daily_stats as $stat): 
                $totalCompleted += $stat['completed'];
                $totalSkipped += $stat['skipped'];
            ?>
            <div class="stat-card">
                <div class="stat-value"><?= $stat['completed'] ?></div>
                <div class="stat-label"><?= $stat['window_name'] ?> Completed</div>
            </div>
            <?php endforeach; ?>
            <div class="stat-card">
                <div class="stat-value"><?= $totalCompleted ?></div>
                <div class="stat-label">Total Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalSkipped ?></div>
                <div class="stat-label">Total Skipped</div>
            </div>
        </div>

        <h2 class="section-title">Monthly Statistics (<?= date('F Y') ?>)</h2>
        <div class="stats-grid">
            <?php 
            $monthlyCompleted = 0;
            $monthlySkipped = 0;
            error_log("PHP Monthly Stats Raw: " . json_encode($monthly_stats));
            foreach ($monthly_stats as $stat): 
                $monthlyCompleted += $stat['completed'];
                $monthlySkipped += $stat['skipped'];
            ?>
            <div class="stat-card">
                <div class="stat-value"><?= $stat['completed'] ?></div>
                <div class="stat-label"><?= $stat['window_name'] ?> Completed</div>
            </div>
            <?php endforeach; ?>
            <div class="stat-card">
                <div class="stat-value"><?= $monthlyCompleted ?></div>
                <div class="stat-label">Monthly Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $monthlySkipped ?></div>
                <div class="stat-label">Monthly Skipped</div>
            </div>
        </div>
        <?php error_log("PHP Monthly Totals - Completed: " . $monthlyCompleted . ", Skipped: " . $monthlySkipped); ?>

    </div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <h3>Confirm Action</h3>
            <p id="confirmMessage">Are you sure you want to do this?</p>
            <div class="modal-buttons">
                <button class="btn btn-secondary" onclick="closeConfirmModal()">No</button>
                <button class="btn btn-danger" id="confirmYes">Yes</button>
            </div>
        </div>
    </div>
        
    <script>
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

        document.getElementById('confirmYes').addEventListener('click', function() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        });

        function resetWindows() {
            console.log("resetWindows function called");
            const url = '<?= base_url('admin/reset-windows') ?>';
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
                    // Refresh data dynamically instead of page reload
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
            const url = '<?= base_url('admin/reset-numbers') ?>';
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
                    // Refresh data dynamically instead of page reload
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
            const url = '<?= base_url('admin/reset-daily-stats') ?>';
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
                    // Refresh data dynamically instead of page reload
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
            const url = '<?= base_url('admin/reset-monthly-stats') ?>';
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
                    // Refresh data dynamically instead of page reload
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
            const url = '<?= base_url('window/callNext/') ?>' + windowId;
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
                    // Refresh DataTables
                    $('#queueTable').DataTable().ajax.reload();
                    
                    // Trigger blinking on display dashboard using localStorage
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
            const url = '<?= base_url('admin/skip/') ?>' + id;
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
                    refreshData(); // This will update statistics in real-time
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
            fetch('<?= base_url('admin/get-data') ?>', {
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
                // Find the specific window widget by window number
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
                    
                    // Update action buttons
                    if (actionsDiv) {
                        actionsDiv.innerHTML = `
                            <button class="btn btn-call-next btn-small" onclick="callNext(${window.id})">Call Next</button>
                            ${window.serving_queue_id ? `<button class="btn btn-danger btn-small" onclick="skipQueue(${window.serving_queue_id})">Skip</button>` : ''}
                            <a href="<?= base_url('window/') ?>${window.window_number}?from_admin=true" class="btn-go-window btn-small">Go to Window ${window.window_number}</a>
                        `;
                    }
                }
            });
        }

        function updateStats(dailyStats, monthlyStats) {
            console.log("updateStats called - Daily:", dailyStats, "Monthly:", monthlyStats);
            
            // Update daily stats (FIRST stats grid only)
            const statsGrids = document.querySelectorAll('.stats-grid');
            const dailyGrid = statsGrids[0]; // First grid is daily stats
            const dailyCards = dailyGrid.querySelectorAll('.stat-card');
            
            let dailyCompleted = 0;
            let dailySkipped = 0;
            
            // Update individual window stats (excluding the last 2 total cards)
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
            
            // Update daily totals (last 2 cards in daily grid)
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
            
            // Update monthly stats (SECOND stats grid)
            if (statsGrids[1]) {
                const monthlyGrid = statsGrids[1]; // Second grid is monthly stats
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
                
                // Update monthly totals (last 2 cards in monthly grid)
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
    </script>
</body>
</html>
