<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Window <?= $window['window_number'] ?> - <?= $window['window_name'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .header-info h1 {
            color: #667eea;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .header-info p {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .back-btn {
            padding: 12px 25px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #c0392b;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .now-serving-card {
            grid-column: 1 / -1;
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .now-serving-card h2 {
            color: #7f8c8d;
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .ticket-number {
            font-size: 5rem;
            font-weight: bold;
            color: #27ae60;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .waiting-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .waiting-card h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #e74c3c;
        }
        
        .waiting-count {
            font-size: 4rem;
            font-weight: bold;
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
        }
        
        .actions-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .actions-card h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            flex-direction: column;
        }
        
        .btn {
            padding: 20px;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-complete {
            background: #27ae60;
            color: white;
        }
        
        .btn-complete:hover {
            background: #219a52;
            transform: translateY(-3px);
        }
        
        .btn-skip {
            background: #e74c3c;
            color: white;
        }
        
        .btn-skip:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }
        
        .btn:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }
        
        .waiting-list {
            max-height: 300px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .waiting-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            font-size: 1.1rem;
            color: #2c3e50;
            border-left: 4px solid #667eea;
        }
        
        .empty-state {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-info">
            <h1>Window <?= $window['window_number'] ?> - <?= $window['window_name'] ?></h1>
        </div>
        <a href="<?= isset($from_admin) && $from_admin ? base_url('admin') : base_url() ?>" class="back-btn">
            <?= isset($from_admin) && $from_admin ? 'Back to Admin' : 'Back' ?>
        </a>
    </div>

    <div class="main-grid">
        <div class="now-serving-card">
            <h2>Now Serving</h2>
            <div class="ticket-number" id="nowServing">
                <?= $now_serving ? $now_serving['ticket_number'] : 'None' ?>
            </div>
        </div>

        <div class="waiting-card">
            <h3>Waiting Queue</h3>
            <div class="waiting-count" id="waitingCount"><?= $waiting_count ?></div>
            <p style="text-align: center; color: #7f8c8d;">Total Waiting</p>
            
            <div class="waiting-list" id="waitingList">
                <?php if (empty($waiting_list)): ?>
                    <div class="empty-state">No customers waiting</div>
                <?php else: ?>
                    <?php foreach ($waiting_list as $waiting): ?>
                        <div class="waiting-item"><?= $waiting['ticket_number'] ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions-card">
            <h3>Actions</h3>
            <div class="btn-group">
                <button class="btn btn-complete" id="completeBtn" 
                        <?= $now_serving ? '' : 'disabled' ?>
                        onclick="completeCurrent()">
                    COMPLETE
                </button>
                <button class="btn btn-skip" id="skipBtn" 
                        <?= $now_serving ? '' : 'disabled' ?>
                        onclick="skipCurrent()">
                    SKIP
                </button>
            </div>
        </div>
    </div>

    <script>
        const windowId = <?= $window['id'] ?>;
        let currentQueueId = <?= $now_serving ? $now_serving['id'] : 'null' ?>;

        function completeCurrent() {
            if (!currentQueueId) return;
            
            fetch('<?= base_url('window/complete/') ?>' + currentQueueId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) refreshData();
            });
        }

        function skipCurrent() {
            if (!currentQueueId) return;
            
            fetch('<?= base_url('window/skip/') ?>' + currentQueueId, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) refreshData();
            });
        }

        function refreshData() {
            fetch('<?= base_url('window/data/') ?>' + windowId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('nowServing').textContent = data.now_serving;
                    document.getElementById('waitingCount').textContent = data.waiting_count;
                    currentQueueId = data.serving_queue_id;
                    
                    const completeBtn = document.getElementById('completeBtn');
                    const skipBtn = document.getElementById('skipBtn');
                    
                    if (currentQueueId) {
                        completeBtn.disabled = false;
                        skipBtn.disabled = false;
                    } else {
                        completeBtn.disabled = true;
                        skipBtn.disabled = true;
                    }
                    
                    // Update waiting list
                    const waitingList = document.getElementById('waitingList');
                    if (data.waiting_list.length === 0) {
                        waitingList.innerHTML = '<div class="empty-state">No customers waiting</div>';
                    } else {
                        waitingList.innerHTML = data.waiting_list.map(w => 
                            `<div class="waiting-item">${w.ticket_number}</div>`
                        ).join('');
                    }
                }
            });
        }

        // Auto refresh every 2 seconds
        setInterval(refreshData, 2000);
    </script>
</body>
</html>
