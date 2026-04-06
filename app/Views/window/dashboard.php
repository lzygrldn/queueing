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
            font-size: 12px;
        }

        .queue-item.search-highlight {
            border: 3px solid #f39c12;
            box-shadow: 0 0 15px rgba(243, 156, 18, 0.4);
            transform: translateY(-2px);
        }

        .queue-item.search-highlight::after {
            content: '🔍';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
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
        }
        
        .search-container {
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-container {
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 20px;
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

        .btn-call:disabled {
            background: #95a5a6;
            color: #ecf0f1;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
            box-shadow: none;
        }

        .btn-call:disabled:hover {
            background: #95a5a6;
            transform: none;
            box-shadow: none;
        }
        
        .btn-skip {
            background: #e67e22;
            color: white;
        }
        
        .btn-skip:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }
        
        .btn-complete {
            background: #27ae60;
            color: white;
        }
        
        .btn-complete:hover {
            background: #229954;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
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
        
        /* New separate card styles for right column */
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .search-card {
            background: transparent;
            padding: 0;
            box-shadow: none;
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e1e8ed;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-result-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e1e8ed;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-result-item:hover {
            background: #f8f9fa;
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        .search-result-details {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 3px;
        }
        
        .search-no-results {
            padding: 15px;
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .search-card .form-header {
            display: none;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            flex: 1;
        }
        
        .form-card .form-header {
            border-bottom-color: #667eea;
        }
        
        .form-header {
            font-size: 1.3rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
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
                    <button class="btn btn-call" id="callBtn" disabled
                            onclick="callNext()">
                        CALL NEXT
                    </button>
                    <button class="btn btn-complete" id="completeBtn" disabled
                            onclick="completeCurrent()">
                        COMPLETE
                    </button>
                    <button class="btn btn-skip" id="skipBtn" 
                            <?= ($now_serving && !$is_serving_from_completed) ? '' : 'disabled' ?>
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
                                <div class="queue-item waiting" data-id="<?= $waiting['id'] ?>" data-ticket="<?= $waiting['ticket_number'] ?>" data-service-type="<?= $waiting['service_type'] ?? '' ?>">
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
                                <div class="queue-item skipped" data-id="<?= $skipped['id'] ?>" data-ticket="<?= $skipped['ticket_number'] ?>" data-service-type="<?= $skipped['service_type'] ?? '' ?>">
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
                                <div class="queue-item completed" data-id="<?= $completed['id'] ?>" data-ticket="<?= $completed['ticket_number'] ?>" data-service-type="<?= $completed['service_type'] ?? '' ?>">
                                    <?= $completed['ticket_number'] ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section - Search and Customer Form -->
        <div class="right-column">
            <!-- Search Card -->
            <div class="search-card">
                <input type="text" class="search-bar" placeholder="Search by transaction number or name in document..." id="searchBar" autocomplete="off">
                <!-- Search Results Dropdown -->
                <div id="searchResults" class="search-results" style="display: none;"></div>
            </div>
            
            <!-- Customer Form Card -->
            <div class="form-card">
                <div class="form-header">Customer Information</div>
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
                            <option value="" disabled selected>Select Service</option>
                            <?php 
                            $windowPrefix = $window['prefix'] ?? '';
                            if ($windowPrefix === 'BREQS'): ?>
                                <option value="BREQS">BREQS</option>
                            <?php elseif ($windowPrefix === 'BIRTH'): ?>
                                <option value="BIRTH-REGULAR">Birth - Regular</option>
                                <option value="BIRTH-DELAYED">Birth - Delayed</option>
                                <option value="BIRTH-OUT-OF-TOWN">Birth - Out-of-Town</option>
                            <?php elseif ($windowPrefix === 'DEATH'): ?>
                                <option value="DEATH-REGULAR">Death - Regular</option>
                                <option value="DEATH-DELAYED">Death - Delayed</option>
                            <?php elseif ($windowPrefix === 'MARRIAGE'): ?>
                                <option value="MARRIAGE-REGULAR">Marriage - Regular</option>
                                <option value="MARRIAGE-DELAYED">Marriage - Delayed</option>
                                <option value="MARRIAGE-LICENSE-ENDORSEMENT">Marriage - License Endorsement</option>
                                <option value="MARRIAGE-LICENSE-APPLICATION">Marriage - License Application</option>
                            <?php else: ?>
                                <option value="BREQS">BREQS</option>
                                <option value="BIRTH-REGULAR">Birth - Regular</option>
                                <option value="BIRTH-DELAYED">Birth - Delayed</option>
                                <option value="BIRTH-OUT-OF-TOWN">Birth - Out-of-Town</option>
                                <option value="DEATH-REGULAR">Death - Regular</option>
                                <option value="DEATH-DELAYED">Death - Delayed</option>
                                <option value="MARRIAGE-REGULAR">Marriage - Regular</option>
                                <option value="MARRIAGE-DELAYED">Marriage - Delayed</option>
                                <option value="MARRIAGE-LICENSE-ENDORSEMENT">Marriage - License Endorsement</option>
                                <option value="MARRIAGE-LICENSE-APPLICATION">Marriage - License Application</option>
                            <?php endif; ?>
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
    </div>

    <!-- Custom Modal Popup -->
    <div id="customModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); text-align: center; max-width: 400px; width: 90%;">
            <div id="modalMessage" style="font-size: 16px; color: #333; margin-bottom: 20px; line-height: 1.5;"></div>
            <div id="modalButtons" style="display: flex; gap: 10px; justify-content: center;">
                <button id="modalOkBtn" style="padding: 10px 30px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">OK</button>
                <button id="modalCancelBtn" style="padding: 10px 30px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; display: none;">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Custom Modal Functions
        function showModal(message, type = 'alert', callback = null) {
            const modal = document.getElementById('customModal');
            const modalMessage = document.getElementById('modalMessage');
            const okBtn = document.getElementById('modalOkBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');
            
            modalMessage.textContent = message;
            modal.style.display = 'flex';
            
            if (type === 'confirm') {
                cancelBtn.style.display = 'block';
            } else {
                cancelBtn.style.display = 'none';
            }
            
            okBtn.onclick = function() {
                modal.style.display = 'none';
                if (callback) callback(true);
            };
            
            cancelBtn.onclick = function() {
                modal.style.display = 'none';
                if (callback) callback(false);
            };
        }

        const windowId = <?= $window['id'] ?>;
        const windowPrefix = '<?= $window['prefix'] ?? '' ?>';
        let currentQueueId = <?= $now_serving ? $now_serving['id'] : 'null' ?>;
        let selectedQueueId = null;
        let selectedTicketNumber = null;
        let currentServiceType = ''; // Store the service type of current serving customer
        let isSelectionRestored = false; // Track if selection is already restored
        // Load isServingCompleted from localStorage on page load
        let isServingCompleted = localStorage.getItem('isServingCompleted_' + windowId) === 'true';
        console.log('Initial load - isServingCompleted from localStorage:', isServingCompleted, 'key:', 'isServingCompleted_' + windowId);

        // Disable service field for BREQS window and auto-set value
        if (windowPrefix === 'BREQS') {
            const serviceSelect = document.getElementById('service');
            serviceSelect.value = 'BREQS';
            serviceSelect.disabled = true;
            serviceSelect.title = 'BREQS window only handles BREQS service';
            console.log('BREQS window: Service field disabled and set to BREQS');
        }

        // Queue item click handlers
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('queue-item')) {
                const queueId = e.target.dataset.id;
                const ticketNumber = e.target.dataset.ticket;
                const serviceType = e.target.dataset.serviceType;
                const isCompleted = e.target.classList.contains('completed');
                const isWaiting = e.target.classList.contains('waiting');
                const isSkipped = e.target.classList.contains('skipped');
                
                console.log('Queue item clicked:');
                console.log('- Queue ID:', queueId);
                console.log('- Ticket Number:', ticketNumber);
                console.log('- Service Type:', serviceType);
                console.log('- Is Completed:', isCompleted);
                console.log('- Is Waiting:', isWaiting);
                console.log('- Classes:', e.target.className);
                
                // Check if clicking the same item (unselect)
                if (selectedQueueId === queueId) {
                    // Unselect the item
                    e.target.classList.remove('selected');
                    selectedQueueId = null;
                    selectedTicketNumber = null;
                    
                    // Disable call button if no waiting customers
                    const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
                    document.getElementById('callBtn').disabled = waitingCount === 0;
                    
                    // ALWAYS clear form when unselecting any item (not just completed)
                    clearForm();
                    console.log('Cleared form after unselecting item:', ticketNumber);
                    
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
                    // Store service type from clicked item for auto-population
                    if (serviceType) {
                        currentServiceType = serviceType;
                    }
                    
                    // Enable call button when any waiting/skipped/completed item is selected
                    document.getElementById('callBtn').disabled = false;
                    
                    // ALWAYS clear form first when switching to a different item
                    clearForm();
                    
                    // Handle form population based on item status
                    if (isCompleted) {
                        console.log('Calling loadCustomerData for completed item');
                        // Load customer data for completed items
                        loadCustomerData(ticketNumber);
                    } else {
                        console.log('Calling autoPopulateService for non-completed item');
                        // Auto populate service for waiting/skipped items
                        autoPopulateService(selectedTicketNumber);
                        const transactionNumber = generateTransactionNumber(selectedTicketNumber);
                        document.getElementById('transactionNumber').value = transactionNumber;
                    }
                    
                    console.log('Selected queue item:', ticketNumber, 'ID:', queueId, 'Completed:', isCompleted);
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
            console.log('restoreSelection called, selectedQueueId:', selectedQueueId, 'selectedTicketNumber:', selectedTicketNumber);
            if (selectedQueueId) {
                const selectedItem = document.querySelector(`.queue-item[data-id="${selectedQueueId}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('selected');
                    console.log('Restored selection for:', selectedTicketNumber);
                } else {
                    // Item no longer exists (was called/served)
                    console.log('Selected item no longer in DOM, clearing selection');
                    clearSelection();
                }
            } else {
                console.log('No selection to restore (selectedQueueId is null)');
            }
        }

        function callNext() {
            // Must have a selection to call next
            let targetQueueId = selectedQueueId;
            let isFromCompleted = false;
            
            if (!targetQueueId) {
                console.log('No queue item selected to call');
                return;
            }
            
            // Check if selected item is from completed or skipped list
            const selectedItem = document.querySelector(`.queue-item[data-id="${targetQueueId}"]`);
            isFromCompleted = selectedItem && (selectedItem.classList.contains('completed') || selectedItem.classList.contains('skipped'));
            isServingCompleted = isFromCompleted;
            console.log('Calling specific queue item:', targetQueueId, 'Ticket:', selectedTicketNumber, 'From completed:', isFromCompleted);
            
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
                    // Set flag based on server response - indicates if called from completed list
                    isServingCompleted = data.is_from_completed || false;
                    // Persist to localStorage
                    localStorage.setItem('isServingCompleted_' + windowId, isServingCompleted.toString());
                    console.log('callNext success - isServingCompleted:', isServingCompleted);
                    
                    // Store service type from response for form auto-population
                    if (data.service_type) {
                        currentServiceType = data.service_type;
                        console.log('Service type from callNext:', currentServiceType);
                    }
                    
                    // Clear selection after successful call
                    clearSelection();
                    
                    // Immediately update Now Serving display
                    if (data.ticket_number) {
                        document.getElementById('nowServing').textContent = data.ticket_number;
                        console.log('Updated Now Serving to:', data.ticket_number);
                    }
                    
                    // Immediately update button states
                    const callBtn = document.getElementById('callBtn');
                    const completeBtn = document.getElementById('completeBtn');
                    const skipBtn = document.getElementById('skipBtn');
                    callBtn.disabled = true;
                    completeBtn.disabled = false;
                    skipBtn.disabled = data.is_from_completed || false;
                    
                    // Short delay to ensure DB transaction commits before full refresh
                    setTimeout(() => {
                        refreshData();
                    }, 300);
                    
                    // If called from completed list, load customer data using TRANSACTION NUMBER
                    if (data.is_from_completed && data.transaction_number) {
                        loadCustomerDataByTransaction(data.transaction_number);
                    } else if (data.ticket_number) {
                        // For new customers, just auto-populate service
                        autoPopulateService(data.ticket_number);
                        document.getElementById('transactionNumber').value = data.transaction_number || '';
                    }
                } else {
                    showModal('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                showModal('Network error: ' + error.message);
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
                    isServingCompleted = false; // Reset flag
                    // Clear from localStorage
                    localStorage.removeItem('isServingCompleted_' + windowId);
                    refreshData();
                }
            });
        }
        
        function completeCurrent() {
            if (!currentQueueId) return;
            
            console.log('Completing current transaction');
            
            // First check if form has required data or there's saved data in DB
            hasValidTransactionData().then(hasData => {
                if (!hasData) {
                    showModal('There is no transaction record can\'t save. Please fill in all required fields (Name of Customer, Name in Document, Service) before completing.');
                    return;
                }
                
                // If there are unsaved changes, save first
                const savePromise = hasUnsavedChanges ? saveCustomerData() : Promise.resolve({ success: true });
                
                savePromise.then(saveResult => {
                    if (!saveResult.success && hasUnsavedChanges) {
                        showModal('Failed to save customer data: ' + (saveResult.message || 'Unknown error') + '. Please try again.');
                        return;
                    }
                    
                    // Complete current transaction only - do NOT auto-call next
                    fetch('<?= base_url('window/complete/') ?>' + currentQueueId, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            isServingCompleted = false; // Reset flag
                            // Clear from localStorage
                            localStorage.removeItem('isServingCompleted_' + windowId);
                            
                            clearSelection();
                            clearForm();
                            hasUnsavedChanges = false;
                            
                            refreshData();
                        } else {
                            showModal('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Network error:', error);
                        showModal('Network error: ' + error.message);
                    });
                });
            });
        }
        
        function loadCustomerDataByTransaction(transactionNumber) {
            console.log('loadCustomerDataByTransaction called for transaction:', transactionNumber);
            
            // Fetch customer data by transaction number
            fetch('<?= base_url('window/getCustomerDataByTransaction/') ?>' + encodeURIComponent(transactionNumber), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                console.log('Response status:', r.status);
                return r.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                if (data.success && data.customer) {
                    console.log('Customer data found:', data.customer);
                    
                    // Populate form with customer data
                    document.getElementById('customerName').value = data.customer.customer_name || '';
                    document.getElementById('documentName').value = data.customer.document_name || '';
                    document.getElementById('service').value = data.customer.service || '';
                    document.getElementById('remarks').value = data.customer.remarks || '';
                    document.getElementById('transactionNumber').value = data.customer.transaction_number || transactionNumber;
                    
                    console.log('Form populated successfully from existing record');
                } else {
                    console.log('No customer data found for transaction:', transactionNumber);
                }
            })
            .catch(error => {
                console.error('Error loading customer data:', error);
            });
        }

        // Keep old function for backward compatibility
        function loadCustomerData(ticketNumber) {
            console.log('loadCustomerData called for ticket:', ticketNumber);
            
            // Fetch customer data for completed ticket
            fetch('<?= base_url('window/getCustomerData/') ?>' + ticketNumber, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                console.log('Response status:', r.status);
                return r.json();
            })
            .then(data => {
                console.log('Received data:', data);
                
                if (data.success && data.customer) {
                    console.log('Customer data found:', data.customer);
                    
                    // Populate form with customer data
                    document.getElementById('customerName').value = data.customer.customer_name || '';
                    document.getElementById('documentName').value = data.customer.document_name || '';
                    document.getElementById('service').value = data.customer.service || '';
                    document.getElementById('remarks').value = data.customer.remarks || '';
                    document.getElementById('transactionNumber').value = data.customer.transaction_number || '';
                    
                    console.log('Form populated successfully');
                    console.log('Customer Name set to:', data.customer.customer_name);
                    console.log('Document Name set to:', data.customer.document_name);
                } else {
                    console.log('No customer data found, falling back to service auto-population');
                    // Fallback to auto-populate service if no customer data found
                    autoPopulateService(ticketNumber);
                    const transactionNumber = generateTransactionNumber(ticketNumber);
                    document.getElementById('transactionNumber').value = transactionNumber;
                    console.log('No customer data found for completed ticket:', ticketNumber);
                }
            })
            .catch(error => {
                console.error('Error loading customer data:', error);
                // Fallback to auto-populate service on error
                autoPopulateService(ticketNumber);
                const transactionNumber = generateTransactionNumber(ticketNumber);
                document.getElementById('transactionNumber').value = transactionNumber;
            });
        }

        // Auto-serve first customer on page load if none is being served
        function autoServeFirstIfNeeded() {
            const nowServing = document.getElementById('nowServing').textContent;
            if (nowServing === 'None' || nowServing === '' || !currentQueueId) {
                // Check if there are waiting customers
                const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
                if (waitingCount > 0) {
                    console.log('Auto-serving first customer on page load...');
                    // Call the autoServeFirst endpoint
                    fetch('<?= base_url('window/autoServeFirst/') ?>' + windowId, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Auto-served first customer:', data.ticket_number);
                            refreshData();
                        }
                    })
                    .catch(error => {
                        console.error('Auto-serve error:', error);
                    });
                }
            }
        }

        // Connection state management
        let isOnline = navigator.onLine;
        let retryCount = 0;
        let maxRetries = 3;
        let refreshInterval;

        function refreshData() {
            // Check if we're offline
            if (!navigator.onLine) {
                console.warn('Device is offline, skipping data refresh');
                return;
            }

            // Prevent too many retries
            if (retryCount >= maxRetries) {
                console.warn('Max retries reached, stopping refresh');
                clearInterval(refreshInterval);
                showConnectionError();
                return;
            }

            fetch('<?= base_url('window/data/') ?>' + windowId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                timeout: 5000 // 5 second timeout
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Reset retry count on success
                    retryCount = 0;
                    hideConnectionError();
                    
                    // Update now serving
                    document.getElementById('nowServing').textContent = data.now_serving || 'None';
                    currentQueueId = data.current_queue_id || null;
                    
                    // Store service type for form auto-population
                    currentServiceType = data.now_serving_service_type || '';
                    console.log('Current service type:', currentServiceType);
                    
                    // Update waiting list
                    const waitingList = document.getElementById('waitingList');
                    if (data.waiting_list.length === 0) {
                        waitingList.innerHTML = '<div class="empty-state">No customers waiting</div>';
                    } else {
                        waitingList.innerHTML = data.waiting_list.map(w => 
                            `<div class="queue-item waiting" data-id="${w.id}" data-ticket="${w.ticket_number}" data-service-type="${w.service_type || ''}">${w.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Update skipped list
                    const skippedList = document.getElementById('skippedList');
                    if (data.skipped_list.length === 0) {
                        skippedList.innerHTML = '<div class="empty-state">No skipped customers</div>';
                    } else {
                        skippedList.innerHTML = data.skipped_list.map(s => 
                            `<div class="queue-item skipped" data-id="${s.id}" data-ticket="${s.ticket_number}" data-service-type="${s.service_type || ''}">${s.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Update completed list
                    const completedList = document.getElementById('completedList');
                    if (data.completed_list.length === 0) {
                        completedList.innerHTML = '<div class="empty-state">No completed customers</div>';
                    } else {
                        completedList.innerHTML = data.completed_list.map(c => 
                            `<div class="queue-item completed" data-id="${c.id}" data-ticket="${c.ticket_number}" data-service-type="${c.service_type || ''}">${c.ticket_number}</div>`
                        ).join('');
                    }
                    
                    // Restore selection immediately after updating lists (no delay to prevent blinking)
                    restoreSelection();
                    
                    // Enable/disable call button based on selection and current serving
                    const callBtn = document.getElementById('callBtn');
                    const completeBtn = document.getElementById('completeBtn');
                    const skipBtn = document.getElementById('skipBtn');
                    
                    // If there's a current serving customer, disable Call Next and enable Complete/Skip
                    if (data.now_serving && data.now_serving !== 'None') {
                        callBtn.disabled = true;
                        completeBtn.disabled = false;
                        // Disable Skip if serving from completed list
                        // Check server flag first, fallback to local flag
                        const shouldDisableSkip = data.is_serving_from_completed || isServingCompleted;
                        skipBtn.disabled = shouldDisableSkip;
                        console.log('Skip button disabled state:', shouldDisableSkip, 'server:', data.is_serving_from_completed, 'local:', isServingCompleted);
                    } else {
                        // No current serving, enable Call Next if there are waiting/skipped/completed customers AND a customer is selected
                        const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
                        const skippedCount = document.querySelectorAll('#skippedList .queue-item').length;
                        const completedCount = document.querySelectorAll('#completedList .queue-item').length;
                        callBtn.disabled = (waitingCount === 0 && skippedCount === 0 && completedCount === 0) || !selectedQueueId;
                        completeBtn.disabled = true;
                        skipBtn.disabled = true;
                        // Reset flag when no customer is being served
                        isServingCompleted = false;
                        // Clear from localStorage
                        localStorage.removeItem('isServingCompleted_' + windowId);
                    }
                    
                    // Update customer info if there's a current serving customer
                    if (data.now_serving && data.now_serving !== 'None') {
                        updateCustomerInfo();
                    }
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            })
            .catch(error => {
                retryCount++;
                console.error('Data refresh error:', error.message);
                
                // Show connection error after 2 failed attempts
                if (retryCount >= 2) {
                    showConnectionError();
                }
            });
        }

        function showConnectionError() {
            // Create or update connection error message
            let errorDiv = document.getElementById('connectionError');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'connectionError';
                errorDiv.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #e74c3c;
                    color: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    z-index: 9999;
                    font-weight: 600;
                `;
                document.body.appendChild(errorDiv);
            }
            errorDiv.innerHTML = `
                Connection Lost ⚠️<br>
                <small>Unable to connect to server. Please check your connection.</small>
                <button onclick="retryConnection()" style="margin-left: 10px; padding: 5px 10px; background: white; color: #e74c3c; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
            `;
        }

        function hideConnectionError() {
            const errorDiv = document.getElementById('connectionError');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function retryConnection() {
            retryCount = 0;
            hideConnectionError();
            refreshData();
            // Restart interval if it was stopped
            if (!refreshInterval) {
                refreshInterval = setInterval(refreshData, 2000);
            }
        }

        // Track if form has unsaved changes
        let hasUnsavedChanges = false;
        let autoSaveInterval;
        
        // Monitor form changes
        const formInputs = document.querySelectorAll('#customerForm input, #customerForm select, #customerForm textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
            input.addEventListener('change', () => {
                hasUnsavedChanges = true;
            });
        });
        
        // Check if current serving ticket has saved data in database
        async function hasSavedDataInDB() {
            const transactionNumber = document.getElementById('transactionNumber').value.trim();
            const nowServing = document.getElementById('nowServing').textContent;
            
            if (!transactionNumber && nowServing === 'None') {
                return false;
            }
            
            // Try to get transaction number from form or generate from ticket
            let txnToCheck = transactionNumber;
            if (!txnToCheck && nowServing !== 'None') {
                // Generate expected transaction number
                txnToCheck = generateTransactionNumber(nowServing);
            }
            
            if (!txnToCheck) {
                return false;
            }
            
            try {
                const response = await fetch('<?= base_url('window/getCustomerDataByTransaction/') ?>' + encodeURIComponent(txnToCheck), {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const data = await response.json();
                
                if (data.success && data.customer) {
                    // Check if all required fields are present in the record
                    const customer = data.customer;
                    const hasRequiredData = customer.customer_name && 
                                          customer.document_name && 
                                          customer.service && 
                                          customer.transaction_number;
                    
                    if (hasRequiredData) {
                        console.log('Found saved customer data in DB for:', txnToCheck);
                    }
                    return hasRequiredData;
                }
                
                return false;
            } catch (error) {
                console.error('Error checking saved data:', error);
                return false;
            }
        }
        
        // Check if form or database has valid transaction data
        async function hasValidTransactionData() {
            // First check form
            if (hasFormData()) {
                return true;
            }
            
            // If form is empty, check database
            return await hasSavedDataInDB();
        }
        
        // Check if form has required data filled
        function hasFormData() {
            const customerName = document.getElementById('customerName').value.trim();
            const documentName = document.getElementById('documentName').value.trim();
            const transactionNumber = document.getElementById('transactionNumber').value.trim();
            
            // For BREQS, service is always valid (auto-set to BREQS)
            // For other windows, check service field
            let serviceValid = false;
            if (windowPrefix === 'BREQS') {
                serviceValid = true; // BREQS always has service = 'BREQS'
            } else {
                serviceValid = !!document.getElementById('service').value;
            }
            
            return customerName && documentName && serviceValid && transactionNumber;
        }
        function getFormData() {
            const windowNameElement = document.querySelector('.header-info h1');
            let windowName = '';
            
            if (windowNameElement) {
                const fullWindowName = windowNameElement.textContent;
                windowName = fullWindowName.split(' - ')[1] || '';
            }
            
            // For BREQS, force service to 'BREQS' since disabled field doesn't submit
            const serviceValue = windowPrefix === 'BREQS' ? 'BREQS' : document.getElementById('service').value;
            
            return {
                customerName: document.getElementById('customerName').value.trim(),
                documentName: document.getElementById('documentName').value.trim(),
                service: serviceValue,
                remarks: document.getElementById('remarks').value.trim(),
                transactionNumber: document.getElementById('transactionNumber').value.trim(),
                window_id: windowId,
                window_name: windowName
            };
        }
        
        // Save customer data function
        async function saveCustomerData() {
            const data = getFormData();
            
            // Validate required fields
            if (!data.customerName || !data.documentName || !data.service || !data.transactionNumber) {
                return { success: false, message: 'Required fields missing' };
            }
            
            try {
                const response = await fetch('<?= base_url('window/saveCustomer') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    hasUnsavedChanges = false;
                }
                return result;
            } catch (error) {
                console.error('Auto-save error:', error);
                return { success: false, message: 'Network error' };
            }
        }
        
        // Auto-save every 30 seconds
        function startAutoSave() {
            autoSaveInterval = setInterval(() => {
                if (hasUnsavedChanges && hasFormData()) {
                    console.log('Auto-saving form data...');
                    saveCustomerData().then(result => {
                        if (result.success) {
                            console.log('Auto-save successful');
                        }
                    });
                }
            }, 30000); // 30 seconds
        }
        
        // Start auto-save on page load
        startAutoSave();
        
        // Warn about unsaved changes when leaving page
        window.addEventListener('beforeunload', (e) => {
            if (hasUnsavedChanges && hasFormData()) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
        window.addEventListener('online', () => {
            console.log('Connection restored');
            retryConnection();
        });

        window.addEventListener('offline', () => {
            console.log('Connection lost');
            showConnectionError();
        });

        // Auto refresh every 2 seconds with error handling
        refreshInterval = setInterval(refreshData, 2000);
        
        // Initial call to set correct button states immediately
        refreshData();

        // Form functionality
        function generateTransactionNumber(ticketNumber) {
            const today = new Date();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const year = today.getFullYear();
            const date = year + month + day; // YYYYMMDD format matching backend
            
            // Determine prefix from ticket number, NOT from form
            let prefix = 'BREQS';
            if (ticketNumber.startsWith('BIRTH-')) {
                prefix = 'BIRTH';
            } else if (ticketNumber.startsWith('DEATH-')) {
                prefix = 'DEATH';
            } else if (ticketNumber.startsWith('MARRIAGE-')) {
                prefix = 'MARRIAGE';
            } else if (ticketNumber.startsWith('BREQS-')) {
                prefix = 'BREQS';
            }
            
            // Extract ticket number from the end and pad to 3 digits
            const ticketNum = ticketNumber.split('-').pop();
            const paddedNumber = String(ticketNum).padStart(3, '0');
            
            // Format: PREFIX + YYYYMMDD + '-' + 3-digit number
            // e.g., BREQS20260331-001
            return `${prefix}${date}-${paddedNumber}`;
        }

        function autoPopulateService(ticketNumber) {
            const serviceSelect = document.getElementById('service');
            
            // If we have a stored service type from the API, use it
            if (currentServiceType) {
                console.log('Using stored service type:', currentServiceType);
                // Map the full service type to the select option value
                const serviceMap = {
                    'BREQS': 'BREQS',
                    'Birth - Regular': 'BIRTH-REGULAR',
                    'Birth - Delayed': 'BIRTH-DELAYED',
                    'Birth - Out-of-Town': 'BIRTH-OUT-OF-TOWN',
                    'Death - Regular': 'DEATH-REGULAR',
                    'Death - Delayed': 'DEATH-DELAYED',
                    'Marriage - Regular': 'MARRIAGE-REGULAR',
                    'Marriage - Delayed': 'MARRIAGE-DELAYED',
                    'Marriage - License Endorsement': 'MARRIAGE-LICENSE-ENDORSEMENT',
                    'Marriage - License Application': 'MARRIAGE-LICENSE-APPLICATION'
                };
                
                const selectValue = serviceMap[currentServiceType];
                if (selectValue) {
                    serviceSelect.value = selectValue;
                    console.log('Service auto-populated to:', selectValue);
                    return;
                }
            }
            
            // Fallback to ticket prefix detection if no service type available
            if (ticketNumber.startsWith('BREQS-')) {
                serviceSelect.value = 'BREQS';
            } else if (ticketNumber.startsWith('BIRTH-')) {
                serviceSelect.value = 'BIRTH-REGULAR';
            } else if (ticketNumber.startsWith('DEATH-')) {
                serviceSelect.value = 'DEATH-REGULAR';
            } else if (ticketNumber.startsWith('MARRIAGE-')) {
                serviceSelect.value = 'MARRIAGE-REGULAR';
            }
        }

        function clearForm() {
            document.getElementById('customerName').value = '';
            document.getElementById('documentName').value = '';
            document.getElementById('service').value = '';
            document.getElementById('remarks').value = '';
            document.getElementById('transactionNumber').value = '';
            hasUnsavedChanges = false;
            console.log('Form cleared');
        }

        // Customer Search Functionality
        let searchTimeout;
        const searchBar = document.getElementById('searchBar');
        const searchResults = document.getElementById('searchResults');

        // Debounced search function
        function debouncedSearch() {
            clearTimeout(searchTimeout);
            const query = searchBar.value.trim();
            
            if (query.length < 2) {
                hideSearchResults();
                return;
            }
            
            searchTimeout = setTimeout(() => performSearch(query), 300);
        }

        // Perform search via AJAX
        function performSearch(query) {
            console.log('Searching for:', query);
            const url = '<?= base_url('window/searchCustomers?q=') ?>' + encodeURIComponent(query);
            console.log('Search URL:', url);
            
            fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => {
                console.log('Search response status:', r.status);
                return r.json();
            })
            .then(data => {
                console.log('Search results:', data);
                if (data.success) {
                    displaySearchResults(data.customers);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
            });
        }

        // Display search results dropdown
        function displaySearchResults(customers) {
            if (!customers || customers.length === 0) {
                searchResults.innerHTML = '<div class="search-no-results">No customers found</div>';
                searchResults.style.display = 'block';
                return;
            }
            
            let html = '';
            customers.forEach(customer => {
                const transNum = customer.transaction_number || 'N/A';
                const docName = customer.document_name || 'N/A';
                const service = customer.service || 'N/A';
                
                html += `
                    <div class="search-result-item" data-customer='${JSON.stringify(customer).replace(/'/g, "&apos;")}'>
                        <div class="search-result-name">${escapeHtml(transNum)} | ${escapeHtml(docName)} | ${escapeHtml(service)}</div>
                    </div>
                `;
            });
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
            
            // Add click handlers to result items
            document.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', function() {
                    const customerData = JSON.parse(this.dataset.customer);
                    populateFormWithCustomer(customerData);
                    hideSearchResults();
                    searchBar.value = customerData.document_name || '';
                });
            });
        }

        // Hide search results
        function hideSearchResults() {
            searchResults.style.display = 'none';
        }

        // Populate form with customer data
        function populateFormWithCustomer(customer) {
            document.getElementById('customerName').value = customer.customer_name || '';
            document.getElementById('documentName').value = customer.document_name || '';
            document.getElementById('service').value = customer.service || '';
            document.getElementById('remarks').value = customer.remarks || '';
            document.getElementById('transactionNumber').value = customer.transaction_number || '';
            
            console.log('Form populated with customer:', customer.customer_name);
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchBar.contains(e.target) && !searchResults.contains(e.target)) {
                hideSearchResults();
            }
        });

        // Search input event listeners
        searchBar.addEventListener('input', debouncedSearch);
        searchBar.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                debouncedSearch();
            }
        });

        function searchCustomer() {
            debouncedSearch();
        }

        // Form submission
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            const customerName = document.getElementById('customerName').value.trim();
            const documentName = document.getElementById('documentName').value.trim();
            const transactionNumber = document.getElementById('transactionNumber').value;
            
            // For BREQS, service is auto-set; for other windows, validate service
            let serviceValid = false;
            if (windowPrefix === 'BREQS') {
                serviceValid = true;
            } else {
                serviceValid = !!document.getElementById('service').value;
            }
            
            if (!customerName || !documentName || !serviceValid || !transactionNumber) {
                showModal('Please fill in all required fields (marked with *).');
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
            
            // For BREQS, manually set service since disabled field won't submit
            if (windowPrefix === 'BREQS') {
                data.service = 'BREQS';
            }
            
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
                    hasUnsavedChanges = false; // Mark as saved
                    showModal('Customer information saved successfully!', 'alert', function() {
                        clearForm();
                    });
                } else {
                    showModal('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Network error: Failed to save customer information.');
            });
        });

        // Form submission
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
