<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Queueing System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <style>
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn-filter {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-filter:hover {
            background: #5568d3;
        }
        
        .btn-export {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .btn-export:hover {
            background: #229954;
        }
        
        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-container {
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-serving {
            background: #667eea;
            color: white;
        }
        
        .status-completed {
            background: #27ae60;
            color: white;
        }
        
        .status-pending {
            background: #e67e22;
            color: white;
        }
        
        .time-info {
            font-family: monospace;
            font-size: 0.9rem;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header>
            <h1><?= $title ?></h1>
            <a href="<?= base_url('admin') ?>" class="btn btn-secondary">Back to Dashboard</a>
        </header>

        <div class="filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="windowFilter">Window</label>
                    <select id="windowFilter">
                        <option value="">All Windows</option>
                        <?php foreach ($windows as $window): ?>
                            <option value="<?= $window['id'] ?>"><?= $window['window_name'] ?> (Window <?= $window['window_number'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                </div>
                
                <div class="filter-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" value="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="filter-group">
                    <button class="btn-filter" onclick="loadData()">Filter</button>
                    <button class="btn-export" onclick="exportData()">Export CSV</button>
                </div>
            </div>
        </div>

        <div class="data-table">
            <div class="table-container">
                <table id="recordsTable">
                    <thead>
                        <tr>
                            <th>Transaction Number</th>
                            <th>Window</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Queueing Time</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Waiting Time</th>
                            <th>Serving Time</th>
                            <th>Ticket Number</th>
                            <th>Customer Name</th>
                            <th>Document Name</th>
                            <th>Service</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="recordsBody">
                        <tr>
                            <td colspan="14" style="text-align: center; padding: 40px;">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function loadData() {
            const windowId = document.getElementById('windowFilter').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            const params = new URLSearchParams();
            if (windowId) params.append('window_id', windowId);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            fetch('<?= base_url('customerRecords/getData') ?>?' + params.toString())
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        displayData(result.data);
                    } else {
                        alert('Error loading data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error');
                });
        }
        
        function displayData(data) {
            const tbody = document.getElementById('recordsBody');
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="14" style="text-align: center; padding: 40px;">No records found</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.map(record => `
                <tr>
                    <td><strong>${record.transaction_number}</strong></td>
                    <td>${record.window_name} (Window ${record.window_number})</td>
                    <td><span class="status-badge status-${record.status}">${record.status}</span></td>
                    <td class="time-info">${formatDateTime(record.created_at)}</td>
                    <td class="time-info">${record.queueing_time ? formatDateTime(record.queueing_time) : '-'}</td>
                    <td class="time-info">${record.start_time ? formatDateTime(record.start_time) : '-'}</td>
                    <td class="time-info">${record.end_time ? formatDateTime(record.end_time) : '-'}</td>
                    <td class="time-info">${record.waiting_time || '-'}</td>
                    <td class="time-info">${record.serving_time || '-'}</td>
                    <td><strong>${record.ticket_number}</strong></td>
                    <td>${record.customer_name}</td>
                    <td>${record.document_name}</td>
                    <td>${record.service}</td>
                    <td>${record.remarks || '-'}</td>
                </tr>
            `).join('');
        }
        
        function formatDateTime(dateTime) {
            if (!dateTime) return '-';
            const date = new Date(dateTime);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        
        function exportData() {
            const windowId = document.getElementById('windowFilter').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            const params = new URLSearchParams();
            if (windowId) params.append('window_id', windowId);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            
            window.open('<?= base_url('customerRecords/export') ?>?' + params.toString());
        }
        
        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>
