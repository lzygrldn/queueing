<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Queueing System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .admin-container {
            width: 100%;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
            box-sizing: border-box;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .search-section {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .search-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .search-group label {
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }

        .search-group input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s ease;
            width: 100%;
            max-width: none;
        }

        .search-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .filters-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filters-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }

        .filter-group input, .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .filter-group input:focus, .filter-group select:focus {
            outline: none;
            border-color: #007bff;
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn-filter {
            background: #007bff;
            color: white;
        }

        .btn-filter:hover {
            background: #0056b3;
        }

        .btn-clear {
            background: #6c757d;
            color: white;
        }

        .btn-clear:hover {
            background: #5a6268;
        }

        .data-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .table-container {
            overflow-x: auto;
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 1rem;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: nowrap;
            line-height: 1.4;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Column widths for new table structure */
        th:nth-child(1), td:nth-child(1) { min-width: 200px; max-width: 250px; } /* Transaction No. */
        th:nth-child(2), td:nth-child(2) { min-width: 200px; max-width: 250px; } /* Name of Customer */
        th:nth-child(3), td:nth-child(3) { min-width: 200px; max-width: 250px; } /* Name in Document */
        th:nth-child(4), td:nth-child(4) { min-width: 180px; max-width: 220px; } /* Service */
        th:nth-child(5), td:nth-child(5) { min-width: 250px; max-width: 300px; } /* Remarks */
        th:nth-child(6), td:nth-child(6) { min-width: 150px; max-width: 180px; } /* Window */
        th:nth-child(7), td:nth-child(7) { min-width: 100px; max-width: 120px; } /* Status */
        th:nth-child(8), td:nth-child(8) { min-width: 180px; max-width: 200px; } /* Queueing Time */
        th:nth-child(9), td:nth-child(9) { min-width: 180px; max-width: 200px; } /* Service Start Time */
        th:nth-child(10), td:nth-child(10) { min-width: 180px; max-width: 200px; } /* Service End Time */
        th:nth-child(11), td:nth-child(11) { min-width: 120px; max-width: 140px; text-align: center; } /* Waiting Time */
        th:nth-child(12), td:nth-child(12) { min-width: 120px; max-width: 140px; text-align: center; } /* Service Time */

        tr:hover {
            background: #e3f2fd;
            cursor: pointer;
        }

        tr.selected {
            background: #bbdefb;
            border-left: 4px solid #2196f3;
        }

        tr.selected:hover {
            background: #90caf9;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-serving { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }

        .btn-export {
            background: #28a745;
            color: white;
        }

        .btn-export:hover {
            background: #1e7e34;
        }

        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }

        .nav-btn {
            padding: 8px 16px;
            border: 1px solid #007bff;
            background: white;
            color: #007bff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background: #007bff;
            color: white;
        }

        .nav-btn:active {
            transform: translateY(1px);
        }

        @media (max-width: 768px) {
            .admin-container { padding: 10px; }
            header { flex-direction: column; gap: 15px; text-align: center; }
            .filter-row { grid-template-columns: 1fr; }
            .filters-header { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header>
            <h1><?= $title ?></h1>
            <div class="header-actions">
                <button class="btn btn-export" onclick="exportData()">📥 Export CSV</button>
                <a href="<?= base_url('admin') ?>" class="btn btn-secondary">
                    ← Back to Dashboard
                </a>
            </div>
        </header>

        <div class="filters-section">
            <div class="search-section">
                <div class="search-group">
                    <input type="text" id="tableSearch" placeholder="🔍 Search across all columns" style="width: 100%; max-width: none;">
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="windowFilter">Window</label>
                    <select id="windowFilter">
                        <option value="">All Windows</option>
                        <option value="1">Window 1 - BREQS</option>
                        <option value="2">Window 2 - Birth Registration</option>
                        <option value="3">Window 3 - Death Registration</option>
                        <option value="4">Window 4 - Marriage Registration</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="filter-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>

        <div class="data-table">
            <div class="table-container">
                <table id="customerRecordsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Transaction No.</th>
                            <th>Name of Customer</th>
                            <th>Name in Document</th>
                            <th>Service</th>
                            <th>Remarks</th>
                            <th>Window</th>
                            <th>Status</th>
                            <th>Queueing Time</th>
                            <th>Service Start Time</th>
                            <th>Service End Time</th>
                            <th>Waiting Time</th>
                            <th>Service Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
                    }
                },
                columns: [
                    { 
                        data: 'transaction_number',
                        render: function(data) {
                            return '<strong>' + data + '</strong>';
                        }
                    },
                    { data: 'customer_name' },
                    { data: 'document_name' },
                    { data: 'service' },
                    { data: 'remarks' },
                    { data: 'window_name' },
                    { 
                        data: 'status',
                        render: function(data) {
                            // Only show Completed and Skipped, hide Serving
                            if (data === 'serving') {
                                return '<span class="status-badge status-completed">Completed</span>';
                            }
                            const statusClass = 'status-' + data.toLowerCase();
                            return '<span class="status-badge ' + statusClass + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                        }
                    },
                    { 
                        data: 'queueing_time',
                        render: function(data) {
                            if (!data) return 'N/A';
                            // Handle time-only format (HH:MM:SS)
                            if (data.includes(':')) {
                                const timeParts = data.split(':');
                                const hours = parseInt(timeParts[0]);
                                const minutes = timeParts[1];
                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
                                return displayHours + ':' + minutes + ' ' + ampm;
                            }
                            return data;
                        }
                    },
                    { 
                        data: 'start_time',
                        render: function(data) {
                            if (!data) return 'N/A';
                            // Handle time-only format (HH:MM:SS)
                            if (data.includes(':')) {
                                const timeParts = data.split(':');
                                const hours = parseInt(timeParts[0]);
                                const minutes = timeParts[1];
                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
                                return displayHours + ':' + minutes + ' ' + ampm;
                            }
                            return data;
                        }
                    },
                    { 
                        data: 'end_time',
                        render: function(data) {
                            if (!data) return 'N/A';
                            // Handle time-only format (HH:MM:SS)
                            if (data.includes(':')) {
                                const timeParts = data.split(':');
                                const hours = parseInt(timeParts[0]);
                                const minutes = timeParts[1];
                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                const displayHours = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
                                return displayHours + ':' + minutes + ' ' + ampm;
                            }
                            return data;
                        }
                    },
                    { 
                        data: 'waiting_time',
                        render: function(data) {
                            if (!data || data === 'N/A') return 'N/A';
                            
                            // If it's already in the new format, return as is
                            if (data.includes('hours') && data.includes('minutes')) {
                                return data;
                            }
                            
                            // Convert old time format (HH:MM:SS) to minutes
                            if (data.includes(':')) {
                                const timeParts = data.split(':');
                                const hours = parseInt(timeParts[0]) || 0;
                                const minutes = parseInt(timeParts[1]) || 0;
                                
                                if (hours > 0) {
                                    return hours + ' hours ' + minutes + ' minutes';
                                } else {
                                    return minutes + ' minutes';
                                }
                            }
                            
                            return data;
                        }
                    },
                    { 
                        data: 'serving_time',
                        render: function(data) {
                            if (!data || data === 'N/A') return 'N/A';
                            
                            // If it's already in the new format, return as is
                            if (data.includes('hours') && data.includes('minutes')) {
                                return data;
                            }
                            
                            // Convert old time format (HH:MM:SS) to minutes
                            if (data.includes(':')) {
                                const timeParts = data.split(':');
                                const hours = parseInt(timeParts[0]) || 0;
                                const minutes = parseInt(timeParts[1]) || 0;
                                
                                if (hours > 0) {
                                    return hours + ' hours ' + minutes + ' minutes';
                                } else {
                                    return minutes + ' minutes';
                                }
                            }
                            
                            return data;
                        }
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
                responsive: true,
                searching: false, // Disable built-in search, use our custom search
                order: [[3, 'desc']], // Sort by Created At descending
                language: {
                    emptyTable: 'No customer records found',
                    zeroRecords: 'No matching records found'
                },
                initComplete: function() {
                    // Add Excel-like row selection
                    $('#customerRecordsTable tbody').on('click', 'tr', function() {
                        // Remove selected class from other rows
                        $('#customerRecordsTable tbody tr').removeClass('selected');
                        
                        // Add selected class to clicked row
                        $(this).addClass('selected');
                    });
                }
            });

            // Custom search functionality
            function performSearch() {
                const searchTerm = document.getElementById('tableSearch').value;
                table.search(searchTerm).draw();
            }

            // Auto-refresh when filters change
            document.getElementById('windowFilter').addEventListener('change', function() {
                performSearch();
            });

            document.getElementById('startDate').addEventListener('change', function() {
                performSearch();
            });

            document.getElementById('endDate').addEventListener('change', function() {
                performSearch();
            });

            // Search on input with debounce
            let searchTimeout;
            document.getElementById('tableSearch').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });

            // Keyboard navigation for Excel-like experience
            $(document).on('keydown', function(e) {
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    const $selected = $('#customerRecordsTable tbody tr.selected');
                    let $target;
                    
                    if (e.key === 'ArrowUp') {
                        $target = $selected.length ? $selected.prev() : $('#customerRecordsTable tbody tr:last');
                    } else {
                        $target = $selected.length ? $selected.next() : $('#customerRecordsTable tbody tr:first');
                    }
                    
                    if ($target.length) {
                        $('#customerRecordsTable tbody tr').removeClass('selected');
                        $target.addClass('selected');
                        
                        // Scroll to selected row if needed
                        const tableContainer = $('.table-container');
                        const targetTop = $target.position().top;
                        const containerScroll = tableContainer.scrollTop();
                        const containerHeight = tableContainer.height();
                        const targetHeight = $target.height();
                        
                        if (targetTop < 0) {
                            tableContainer.scrollTop(containerScroll + targetTop);
                        } else if (targetTop + targetHeight > containerHeight) {
                            tableContainer.scrollTop(containerScroll + targetTop + targetHeight - containerHeight);
                        }
                    }
                }
            });
        });

        function exportData() {
            const windowId = document.getElementById('windowFilter').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            let url = '<?= base_url('customerRecords/export') ?>?';
            if (windowId) url += 'window_id=' + windowId + '&';
            if (startDate) url += 'start_date=' + startDate + '&';
            if (endDate) url += 'end_date=' + endDate + '&';
            
            window.location.href = url;
        }
    </script>
</body>
</html>
