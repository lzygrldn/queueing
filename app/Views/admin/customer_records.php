<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Queueing System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #fff;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        header h1 {
            font-size: 24px;
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: 1px solid #333;
            background: #fff;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .btn:hover {
            background: #333;
            color: #fff;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            font-size: 14px;
            min-width: 150px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #333;
        }

        #tableSearch {
            min-width: 250px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f5f5f5;
            font-weight: 600;
            border-top: 1px solid #ddd;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 14px;
            margin-top: 15px;
        }

        .dataTables_wrapper .dataTables_paginate a {
            border: 1px solid #ddd;
            padding: 6px 12px;
            margin: 0 2px;
            text-decoration: none;
            color: #333;
        }

        .dataTables_wrapper .dataTables_paginate a:hover {
            background: #f5f5f5;
        }

        .dataTables_wrapper .dataTables_paginate .current {
            background: #333;
            color: #fff;
            border-color: #333;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            .filter-group input,
            .filter-group select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $title ?></h1>
            <div class="actions">
                <button class="btn" onclick="exportData()">Export CSV</button>
                <a href="<?= base_url('admin') ?>" class="btn">Back</a>
            </div>
        </header>

        <div class="filters">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="tableSearch" placeholder="Search all columns...">
            </div>
            <div class="filter-group">
                <label>Window</label>
                <select id="windowFilter">
                    <option value="">All Windows</option>
                    <option value="1">Window 1 - BREQS</option>
                    <option value="2">Window 2 - Birth</option>
                    <option value="3">Window 3 - Death</option>
                    <option value="4">Window 4 - Marriage</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" id="startDate" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" id="endDate" value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <table id="customerRecordsTable">
            <thead>
                <tr>
                    <th>Transaction No.</th>
                    <th>Customer Name</th>
                    <th>Document Name</th>
                    <th>Service</th>
                    <th>Remarks</th>
                    <th>Window</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#customerRecordsTable').DataTable({
                ajax: {
                    url: '<?= base_url('customerRecords/getData') ?>',
                    data: function(d) {
                        d.window_id = document.getElementById('windowFilter').value;
                        d.start_date = document.getElementById('startDate').value;
                        d.end_date = document.getElementById('endDate').value;
                        d.search = document.getElementById('tableSearch').value;
                    },
                    dataSrc: function(json) {
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'transaction_number' },
                    { data: 'customer_name' },
                    { data: 'document_name' },
                    { data: 'service' },
                    { data: 'remarks' },
                    { data: 'window_name' },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            if (!data) return 'N/A';
                            const date = new Date(data);
                            return date.toLocaleString('en-US', { 
                                month: 'short', 
                                day: 'numeric', 
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                searching: false,
                order: [[6, 'desc']],
                language: {
                    emptyTable: 'No records found',
                    zeroRecords: 'No matching records'
                }
            });

            function performSearch() {
                table.ajax.reload();
            }

            document.getElementById('windowFilter').addEventListener('change', performSearch);
            document.getElementById('startDate').addEventListener('change', performSearch);
            document.getElementById('endDate').addEventListener('change', performSearch);

            let searchTimeout;
            document.getElementById('tableSearch').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
        });

        function exportData() {
            const windowId = document.getElementById('windowFilter').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            let url = '<?= base_url('customerRecords/export') ?>?';
            if (windowId) url += 'window_id=' + windowId + '&';
            if (startDate) url += 'start_date=' + startDate + '&';
            if (endDate) url += 'end_date=' + endDate;
            
            window.location.href = url;
        }
    </script>
</body>
</html>
