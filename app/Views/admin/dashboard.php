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
            background: #f5f6fa;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            font-size: 2.5rem;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="header-buttons">
            <a href="<?= base_url('admin/kiosk') ?>" class="btn btn-primary">Kiosk</a>
            <a href="<?= base_url('admin/display') ?>" class="btn btn-primary">Display</a>
            <form action="<?= base_url('admin/logout') ?>" method="POST" style="display: inline;">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title">Window Status</h2>
        <div class="windows-grid">
            <?php foreach ($windows as $window): ?>
            <div class="window-widget">
                <div class="window-header">
                    <h3>Window <?= $window['window_number'] ?></h3>
                    <div class="prefix"><?= $window['window_name'] ?> (<?= $window['prefix'] ?>)</div>
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
                    <?php if ($window['serving_queue_id']): ?>
                    <button class="btn btn-success btn-small" onclick="completeQueue(<?= $window['serving_queue_id'] ?>)">Complete</button>
                    <button class="btn btn-danger btn-small" onclick="skipQueue(<?= $window['serving_queue_id'] ?>)">Skip</button>
                    <?php endif; ?>
                    <a href="<?= base_url('window/' . $window['window_number']) ?>?from_admin=true" class="btn-go-window btn-small">Go to Window <?= $window['window_number'] ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title">Daily Statistics (<?= date('F d, Y') ?>)</h2>
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

        <h2 class="section-title">Queue Data Table</h2>
        <div class="table-container">
            <table id="queueTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Ticket Number</th>
                        <th>Window</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody id="queueTableBody">
                    <!-- Data will be loaded via DataTables AJAX -->
                </tbody>
            </table>
        </div>

        <div class="reset-section">
            <h3>System Controls</h3>
            <div class="reset-buttons">
                <button class="btn btn-danger" onclick="confirmResetWindows()">Reset Windows & Queues</button>
                <button class="btn btn-danger" onclick="confirmResetNumbers()">Reset Released Numbers</button>
            </div>
        </div>
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
    <!-- DataTables JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        
    <script>
        let confirmCallback = null;

        function confirmResetWindows() {
            document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all windows and clear all queues?';
            confirmCallback = resetWindows;
            document.getElementById('confirmModal').classList.add('active');
        }

        function confirmResetNumbers() {
            document.getElementById('confirmMessage').textContent = 'Are you sure you want to reset all released numbers back to 001?';
            confirmCallback = resetNumbers;
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
            const url = '/queueing/admin/reset-windows';
            console.log("Calling URL:", url);
            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                console.log("Response received:", r);
                return r.json();
            })
            .then(data => {
                console.log("Response data:", data);
                if (data.success) {
                    showNotification('✅ Queues Reset Done');
                    // Refresh data dynamically instead of page reload
                    refreshData();
                    // Refresh DataTables
                    $('#queueTable').DataTable().ajax.reload();
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
            const url = '/queueing/admin/reset-numbers';
            console.log("Calling URL:", url);
            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                console.log("Response received:", r);
                return r.json();
            })
            .then(data => {
                console.log("Response data:", data);
                if (data.success) {
                    showNotification('✅ Numbers Reset Done');
                    // Refresh data dynamically instead of page reload
                    refreshData();
                    // Refresh DataTables
                    $('#queueTable').DataTable().ajax.reload();
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

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.style.cssText = 'position:fixed;top:20px;right:20px;background:#27ae60;color:white;padding:15px 20px;border-radius:8px;z-index:10000;font-size:16px;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        function completeQueue(id) {
            fetch('<?= base_url('admin/complete/') ?>' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) refreshData();
            });
        }

        function skipQueue(id) {
            fetch('<?= base_url('admin/skip/') ?>' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) refreshData();
            });
        }

        function refreshData() {
            fetch('/queueing/admin/get-data', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateWindows(data.windows);
                    updateStats(data.daily_stats, data.monthly_stats);
                    updateTable(data.queue_data);
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
                        if (window.serving_queue_id) {
                            actionsDiv.innerHTML = `
                                <button class="btn btn-success btn-small" onclick="completeQueue(${window.serving_queue_id})">Complete</button>
                                <button class="btn btn-danger btn-small" onclick="skipQueue(${window.serving_queue_id})">Skip</button>
                                <a href="<?= base_url('window/') ?>${window.window_number}?from_admin=true" class="btn-go-window btn-small">Go to Window ${window.window_number}</a>
                            `;
                        } else {
                            actionsDiv.innerHTML = `
                                <a href="<?= base_url('window/') ?>${window.window_number}?from_admin=true" class="btn-go-window btn-small">Go to Window ${window.window_number}</a>
                            `;
                        }
                    }
                }
            });
        }

        function updateStats(dailyStats, monthlyStats) {
            // Update daily stats (first stats grid)
            const dailyCards = document.querySelectorAll('.stats-grid .stat-card');
            let dailyCompleted = 0;
            let dailySkipped = 0;
            
            dailyStats.forEach((stat, index) => {
                dailyCompleted += stat.completed;
                dailySkipped += stat.skipped;
                if (dailyCards[index]) {
                    dailyCards[index].querySelector('.stat-value').textContent = stat.completed;
                }
            });
            
            // Update daily totals
            const dailyTotalCards = dailyCards.length - 2;
            if (dailyCards[dailyTotalCards]) dailyCards[dailyTotalCards].querySelector('.stat-value').textContent = dailyCompleted;
            if (dailyCards[dailyTotalCards + 1]) dailyCards[dailyTotalCards + 1].querySelector('.stat-value').textContent = dailySkipped;
            
            // Update monthly stats (second stats grid)
            const allStatsCards = document.querySelectorAll('.stats-grid .stat-card');
            const monthlyCards = Array.from(allStatsCards).slice(dailyCards.length);
            let monthlyCompleted = 0;
            let monthlySkipped = 0;
            
            monthlyStats.forEach((stat, index) => {
                monthlyCompleted += stat.completed;
                monthlySkipped += stat.skipped;
                if (monthlyCards[index]) {
                    monthlyCards[index].querySelector('.stat-value').textContent = stat.completed;
                }
            });
            
            // Update monthly totals
            const monthlyTotalCards = monthlyCards.length - 2;
            if (monthlyCards[monthlyTotalCards]) monthlyCards[monthlyTotalCards].querySelector('.stat-value').textContent = monthlyCompleted;
            if (monthlyCards[monthlyTotalCards + 1]) monthlyCards[monthlyTotalCards + 1].querySelector('.stat-value').textContent = monthlySkipped;
        }

        function updateTable(queueData) {
            const tbody = document.getElementById('queueTableBody');
            const searchTerm = document.getElementById('tableSearch').value.toLowerCase();
            
            tbody.innerHTML = queueData.map(queue => `
                <tr>
                    <td>${queue.ticket_number}</td>
                    <td>${queue.window_name}</td>
                    <td><span class="status-badge status-${queue.status}">${queue.status.charAt(0).toUpperCase() + queue.status.slice(1)}</span></td>
                    <td>${queue.created_at}</td>
                    <td>${queue.completed_at || '-'}</td>
                </tr>
            `).join('');
            
            // Reapply search filter if search term exists
            if (searchTerm) {
                const tableRows = document.querySelectorAll('#queueTableBody tr');
                tableRows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        }

        // Auto refresh disabled to prevent spam
        // setInterval(refreshData, 3000);

        // DataTables initialization
       $(document).ready(function () {
            const table = $('#queueTable').DataTable({

                ajax: {
                    url: "/queueing/admin/get-queue-data",
                    dataSrc: "data"
                },

                columns: [
                    { data: "ticket_number" },
                    { data: "window_name" },
                    {
                        data: "status",
                        render: function (data) {

                            let badgeClass = "";

                            if (data === "waiting") badgeClass = "status-waiting";
                            if (data === "serving") badgeClass = "status-serving";
                            if (data === "completed") badgeClass = "status-completed";
                            if (data === "skipped") badgeClass = "status-skipped";

                            return '<span class="status-badge ' + badgeClass + '">' +
                                data.charAt(0).toUpperCase() + data.slice(1) +
                                '</span>';
                        }
                    },
                    { data: "created_at" },
                    {
                        data: "completed_at",
                        render: function (data) {
                            return data ? data : "-";
                        }
                    }
                ],

                pageLength: 10,

                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],

                order: [[0, "desc"]],

                responsive: true

            });

        });
    </script>
</body>
</html>
