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
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .left-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .three-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .list-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .list-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .queue-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .queue-item {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            position: relative;
        }
        
        .queue-item:hover {
            background: #e8f4fd;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        
        .queue-item.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 3px solid #5568d3;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            transform: translateY(-3px);
            font-weight: 600;
        }
        
        .queue-item.selected::before {
            content: '✓';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.2);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .queue-item.selected.waiting {
            border-color: #27ae60;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        }
        
        .queue-item.selected.skipped {
            border-color: #e67e22;
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
        }
        
        .queue-item.selected.completed {
            border-color: #3498db;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .queue-item.waiting {
            border-left: 4px solid #27ae60;
        }
        
        .queue-item.skipped {
            border-left: 4px solid #e67e22;
        }
        
        .queue-item.completed {
            border-left: 4px solid #3498db;
        }
        
        .actions-inline {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .actions-inline .btn {
            flex: 1;
            max-width: 200px;
        }
        
        .right-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .now-serving-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .search-bar {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 25px;
        }
        
        .form-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
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
        
        .btn-call {
            background: #3498db;
            color: white;
        }
        
        .btn-call:hover {
            background: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-skip {
            background: #e67e22;
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
        <!-- Left Section - Reorganized Layout -->
        <div class="left-section">
            <!-- 1. Now Serving Ticket Number -->
            <div class="now-serving-card">
                <h2>Now Serving</h2>
                <div class="ticket-number" id="nowServing">
                    <?= $now_serving ? $now_serving['ticket_number'] : 'None' ?>
                </div>
            </div>

            <!-- 2. Action Buttons Side by Side -->
            <div class="actions-card">
                <h3>Actions</h3>
                <div class="actions-inline">
                    <button class="btn btn-call" id="callBtn" 
                            <?= $waiting_count > 0 ? '' : 'disabled' ?>
                            onclick="callNext()">
                        CALL NEXT
                    </button>
                    <button class="btn btn-skip" id="skipBtn" 
                            <?= $now_serving ? '' : 'disabled' ?>
                            onclick="skipCurrent()">
                        SKIP
                    </button>
                </div>
            </div>

            <!-- 3. Three Column Lists -->
            <div class="three-column-layout">
                <!-- Left: Waiting Queue -->
                <div class="list-section">
                    <h3>Waiting Queue</h3>
                    <div class="queue-list" id="waitingList">
                        <?php if (empty($waiting_list)): ?>
                            <div class="empty-state">No customers waiting</div>
                        <?php else: ?>
                            <?php foreach ($waiting_list as $waiting): ?>
                                <div class="queue-item waiting" data-id="<?= $waiting['id'] ?>" data-ticket="<?= $waiting['ticket_number'] ?>">
                                    <?= $waiting['ticket_number'] ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Center: Skipped List -->
                <div class="list-section">
                    <h3>Skipped</h3>
                    <div class="queue-list" id="skippedList">
                        <?php if (empty($skipped_list)): ?>
                            <div class="empty-state">No skipped customers</div>
                        <?php else: ?>
                            <?php foreach ($skipped_list as $skipped): ?>
                                <div class="queue-item skipped" data-id="<?= $skipped['id'] ?>" data-ticket="<?= $skipped['ticket_number'] ?>">
                                    <?= $skipped['ticket_number'] ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Completed List -->
                <div class="list-section">
                    <h3>Completed</h3>
                    <div class="queue-list" id="completedList">
                        <?php if (empty($completed_list)): ?>
                            <div class="empty-state">No completed customers</div>
                        <?php else: ?>
                            <?php foreach ($completed_list as $completed): ?>
                                <div class="queue-item completed" data-id="<?= $completed['id'] ?>" data-ticket="<?= $completed['ticket_number'] ?>">
                                    <?= $completed['ticket_number'] ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section - Customer Information Form -->
        <div class="right-section">
            <div class="form-header">Customer Information</div>
            
            <!-- Search Bar -->
            <input type="text" class="search-bar" placeholder="Search by ticket number or transaction number..." id="searchBar">
            
            <!-- Customer Form -->
            <form id="customerForm">
                <div class="form-group">
                    <label for="customerName">Name of Customer *</label>
                    <input type="text" id="customerName" name="customerName" required>
                </div>
                
                <div class="form-group">
                    <label for="documentName">Name in Document *</label>
                    <input type="text" id="documentName" name="documentName" required>
                </div>
                
                <div class="form-group">
                    <label for="service">Service *</label>
                    <select id="service" name="service" required>
                        <option value="">Select Service</option>
                        <option value="BREQS">BREQS</option>
                        <option value="BIRTH-REGULAR">Birth Registration - Regular</option>
                        <option value="BIRTH-DELAYED">Birth Registration - Delayed</option>
                        <option value="BIRTH-OUT-OF-TOWN">Birth Registration - Out-of-Town</option>
                        <option value="DEATH-REGULAR">Death Registration - Regular</option>
                        <option value="DEATH-DELAYED">Death Registration - Delayed</option>
                        <option value="MARRIAGE-REGULAR">Marriage Registration - Regular</option>
                        <option value="MARRIAGE-DELAYED">Marriage Registration - Delayed</option>
                        <option value="MARRIAGE-LICENSE-ENDORSEMENT">Marriage License Endorsement</option>
                        <option value="MARRIAGE-LICENSE-APPLICATION">Marriage License Application</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" placeholder="Enter any additional notes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="transactionNumber">Transaction Number</label>
                    <input type="text" id="transactionNumber" name="transactionNumber" readonly>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-call">Save Customer Information</button>
                    <button type="button" class="btn btn-skip" onclick="clearForm()">Clear Form</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const windowId = <?= $window['id'] ?>;
        let currentQueueId = <?= $now_serving ? $now_serving['id'] : 'null' ?>;
        let selectedQueueId = null;
        let selectedTicketNumber = null;

        // Queue item click handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('queue-item')) {
                const queueId = e.target.dataset.id;
                const ticketNumber = e.target.dataset.ticket;
                
                // Check if clicking the same item (unselect)
                if (selectedQueueId === queueId) {
                    // Unselect the item
                    e.target.classList.remove('selected');
                    selectedQueueId = null;
                    selectedTicketNumber = null;
                    
                    // Disable call button if no waiting customers
                    const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
                    document.getElementById('callBtn').disabled = waitingCount === 0;
                    
                    console.log('Unselected queue item:', ticketNumber);
                } else {
                    // Remove previous selection from all items
                    document.querySelectorAll('.queue-item').forEach(item => {
                        item.classList.remove('selected');
                    });
                    
                    // Add selection to clicked item
                    e.target.classList.add('selected');
                    
                    // Store selected data
                    selectedQueueId = queueId;
                    selectedTicketNumber = ticketNumber;
                    
                    // Enable call button
                    document.getElementById('callBtn').disabled = false;
                    
                    // Update customer form
                    autoPopulateService(selectedTicketNumber);
                    const transactionNumber = generateTransactionNumber(selectedTicketNumber);
                    document.getElementById('transactionNumber').value = transactionNumber;
                    
                    console.log('Selected queue item:', ticketNumber, 'ID:', queueId);
                }
            }
        });

        function clearSelection() {
            selectedQueueId = null;
            selectedTicketNumber = null;
            document.querySelectorAll('.queue-item').forEach(item => {
                item.classList.remove('selected');
            });
            console.log('Cleared all selections');
        }

        function restoreSelection() {
            if (selectedQueueId) {
                const selectedItem = document.querySelector(`.queue-item[data-id="${selectedQueueId}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('selected');
                    console.log('Restored selection for:', selectedTicketNumber);
                } else {
                    // Item no longer exists (was called/served)
                    clearSelection();
                }
            }
        }

        function callNext() {
            let targetQueueId = currentQueueId;
            
            // If a specific queue item is selected, call that one instead
            if (selectedQueueId) {
                targetQueueId = selectedQueueId;
            }
            
            if (!targetQueueId) return;
            
            console.log('Calling queue item:', targetQueueId, selectedTicketNumber || 'next in line');
            
            fetch('<?= base_url('window/callNext/') ?>' + windowId, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'queue_id=' + targetQueueId
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Clear selection after successful call
                    clearSelection();
                    refreshData();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                alert('Network error: ' + error.message);
            });
        }

        function clearSelection() {
            selectedQueueId = null;
            selectedTicketNumber = null;
            document.querySelectorAll('.queue-item').forEach(item => {
                item.classList.remove('selected');
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
                if (data.success) {
                    refreshData();
                }
            });
        }

        function refreshData() {
            fetch('<?= base_url('window/data/') ?>' + windowId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Update now serving
                    document.getElementById('nowServing').textContent = data.now_serving || 'None';
                    currentQueueId = data.current_queue_id || null;
                    
                    // Update waiting list
                    const waitingList = document.getElementById('waitingList');
                    if (data.waiting_list.length === 0) {
                        waitingList.innerHTML = '<div class="empty-state">No customers waiting</div>';
                    } else {
                        waitingList.innerHTML = data.waiting_list.map(w => 
                            `<div class="queue-item waiting" data-id="${w.id}" data-ticket="${w.ticket_number}">${w.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Update skipped list
                    const skippedList = document.getElementById('skippedList');
                    if (data.skipped_list.length === 0) {
                        skippedList.innerHTML = '<div class="empty-state">No skipped customers</div>';
                    } else {
                        skippedList.innerHTML = data.skipped_list.map(s => 
                            `<div class="queue-item skipped" data-id="${s.id}" data-ticket="${s.ticket_number}">${s.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Update completed list
                    const completedList = document.getElementById('completedList');
                    if (data.completed_list.length === 0) {
                        completedList.innerHTML = '<div class="empty-state">No completed customers</div>';
                    } else {
                        completedList.innerHTML = data.completed_list.map(c => 
                            `<div class="queue-item completed" data-id="${c.id}" data-ticket="${c.ticket_number}">${c.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Restore selection after updating lists
                    setTimeout(restoreSelection, 100);
                    
                    // Enable/disable call button based on waiting count and selection
                    const callBtn = document.getElementById('callBtn');
                    callBtn.disabled = data.waiting_count === 0 && !selectedQueueId;
                    
                    // Update customer info if there's a current serving customer
                    if (data.now_serving && data.now_serving !== 'None') {
                        updateCustomerInfo();
                    }
                }
            });
        }

        // Auto refresh every 2 seconds
        setInterval(refreshData, 2000);

        // Form functionality
        function generateTransactionNumber(ticketNumber) {
            const today = new Date();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const year = today.getFullYear().toString().slice(-2);
            
            // Get window name from the page header
            const windowNameElement = document.querySelector('.header-info h1');
            let windowName = 'WINDOW';
            if (windowNameElement) {
                const fullWindowName = windowNameElement.textContent;
                // Extract window name (e.g., "BREQS" from "Window 1 - BREQS")
                windowName = fullWindowName.split(' - ')[1] || 'WINDOW';
            }
            
            // Remove any spaces and convert to uppercase
            windowName = windowName.replace(/\s+/g, '').toUpperCase();
            
            // Extract ticket number from the end
            const ticketNum = ticketNumber.split('-').pop();
            
            return `${windowName}${year}-${month}${day}-${ticketNum}`;
        }

        function autoPopulateService(ticketNumber) {
            const serviceSelect = document.getElementById('service');
            
            // Map ticket numbers to service options
            if (ticketNumber.startsWith('BREQS-')) {
                serviceSelect.value = 'BREQS';
            } else if (ticketNumber.startsWith('BIRTH-REGULAR-')) {
                serviceSelect.value = 'BIRTH-REGULAR';
            } else if (ticketNumber.startsWith('BIRTH-DELAYED-')) {
                serviceSelect.value = 'BIRTH-DELAYED';
            } else if (ticketNumber.startsWith('BIRTH-OUT-OF-TOWN-')) {
                serviceSelect.value = 'BIRTH-OUT-OF-TOWN';
            } else if (ticketNumber.startsWith('DEATH-REGULAR-')) {
                serviceSelect.value = 'DEATH-REGULAR';
            } else if (ticketNumber.startsWith('DEATH-DELAYED-')) {
                serviceSelect.value = 'DEATH-DELAYED';
            } else if (ticketNumber.startsWith('MARRIAGE-REGULAR-')) {
                serviceSelect.value = 'MARRIAGE-REGULAR';
            } else if (ticketNumber.startsWith('MARRIAGE-DELAYED-')) {
                serviceSelect.value = 'MARRIAGE-DELAYED';
            } else if (ticketNumber.startsWith('MARRIAGE-LICENSE-ENDORSEMENT-')) {
                serviceSelect.value = 'MARRIAGE-LICENSE-ENDORSEMENT';
            } else if (ticketNumber.startsWith('MARRIAGE-LICENSE-APPLICATION-')) {
                serviceSelect.value = 'MARRIAGE-LICENSE-APPLICATION';
            }
        }

        function clearForm() {
            document.getElementById('customerForm').reset();
            document.getElementById('transactionNumber').value = '';
        }

        function searchCustomer() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            
            if (!searchTerm) {
                clearForm();
                return;
            }
            
            // Search in waiting list
            const waitingItems = document.querySelectorAll('.waiting-item');
            let found = false;
            
            waitingItems.forEach(item => {
                if (item.textContent.toLowerCase().includes(searchTerm)) {
                    const ticketNumber = item.textContent;
                    
                    // Auto-populate service
                    autoPopulateService(ticketNumber);
                    
                    // Generate and set transaction number
                    const transactionNumber = generateTransactionNumber(ticketNumber);
                    document.getElementById('transactionNumber').value = transactionNumber;
                    
                    found = true;
                }
            });
            
            // Also check currently serving
            const nowServing = document.getElementById('nowServing').textContent;
            if (nowServing.toLowerCase().includes(searchTerm) && nowServing !== 'None') {
                autoPopulateService(nowServing);
                const transactionNumber = generateTransactionNumber(nowServing);
                document.getElementById('transactionNumber').value = transactionNumber;
                found = true;
            }
            
            if (!found) {
                alert('Customer not found. Please check the ticket number or transaction number.');
            }
        }

        // Form submission
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            const customerName = document.getElementById('customerName').value.trim();
            const documentName = document.getElementById('documentName').value.trim();
            const service = document.getElementById('service').value;
            const transactionNumber = document.getElementById('transactionNumber').value;
            
            if (!customerName || !documentName || !service || !transactionNumber) {
                alert('Please fill in all required fields (marked with *).');
                return;
            }
            
            // Get window information
            const windowNameElement = document.querySelector('.header-info h1');
            let windowName = '';
            let currentWindowId = windowId; // Use global windowId
            
            if (windowNameElement) {
                const fullWindowName = windowNameElement.textContent;
                windowName = fullWindowName.split(' - ')[1] || '';
            }
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Add window information
            data.window_id = currentWindowId;
            data.window_name = windowName;
            
            // Send data to server
            fetch('<?= base_url('window/saveCustomer') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Customer information saved successfully!');
                    
                    // Optional: Clear form after saving
                    if (confirm('Clear form after saving?')) {
                        clearForm();
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error: Failed to save customer information.');
            });
        });

        // Search functionality
        document.getElementById('searchBar').addEventListener('input', searchCustomer);
        document.getElementById('searchBar').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchCustomer();
            }
        });

        // Auto-populate when a customer is called
        function updateCustomerInfo() {
            const nowServing = document.getElementById('nowServing').textContent;
            if (nowServing !== 'None') {
                autoPopulateService(nowServing);
                const transactionNumber = generateTransactionNumber(nowServing);
                document.getElementById('transactionNumber').value = transactionNumber;
            }
        }

        // Update customer info when data refreshes
        const originalRefreshData = refreshData;
        refreshData = function() {
            originalRefreshData();
            setTimeout(updateCustomerInfo, 100);
        };

        // Initial update
        updateCustomerInfo();
    </script>
</body>
</html>
