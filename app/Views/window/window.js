// Window Dashboard JavaScript - Pure JS (no PHP)
// PHP variables passed via data attributes or global window.WindowData object

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

// Alternative: use global WindowData object if set
console.log('window.js loading, window.WindowData exists:', !!window.WindowData);
const WindowData = window.WindowData || {};
console.log('WindowData assigned:', WindowData);

let currentQueueId = WindowData.currentQueueId || null;
let selectedQueueId = null;
let selectedTicketNumber = null;
let currentServiceType = '';
let isSelectionRestored = false;
let isServingCompleted = localStorage.getItem('isServingCompleted_' + WindowData.windowId) === 'true';

console.log('Initial load - isServingCompleted from localStorage:', isServingCompleted, 'key:', 'isServingCompleted_' + WindowData.windowId);

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired - JS is running');
    const windowPrefix = WindowData.windowPrefix || '';
    
    // Disable service field for BREQS window and auto-set value
    if (windowPrefix === 'BREQS') {
        const serviceSelect = document.getElementById('service');
        if (serviceSelect) {
            serviceSelect.value = 'BREQS';
            serviceSelect.disabled = true;
            serviceSelect.title = 'BREQS window only handles BREQS service';
            console.log('BREQS window: Service field disabled and set to BREQS');
        }
    }
    
    // Initialize search functionality
    initSearch();
    
    // Initialize form submission
    initFormSubmission();
    
    // Initialize queue item clicks
    initQueueItemClicks();
    
    // Start auto-save
    startAutoSave();
    
    // Start refresh interval
    startRefreshInterval();
    
    // Initial data load
    refreshData();
    updateCustomerInfo();
});

// Queue item click handlers
function initQueueItemClicks() {
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
                
                // Disable call button if no waiting customers OR if someone is being served
                const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
                const nowServing = document.getElementById('nowServing').textContent;
                const isServing = nowServing && nowServing !== 'None';
                document.getElementById('callBtn').disabled = waitingCount === 0 || isServing;
                
                // ALWAYS clear form when unselecting any item
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
                
                // Enable call button only if no one is currently being served
                const nowServing = document.getElementById('nowServing').textContent;
                const isServing = nowServing && nowServing !== 'None';
                document.getElementById('callBtn').disabled = isServing;
                
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
}

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
    const windowId = WindowData.windowId;
    
    // Must have a selection to call next
    let targetQueueId = selectedQueueId;
    let isFromCompleted = false;
    
    if (!targetQueueId) {
        console.log('No queue item selected to call');
        return;
    }
    
    // Check if selected item is from completed or skipped list
    const selectedItem = document.querySelector(`.queue-item[data-id="${targetQueueId}"]`);
    const isFromSkipped = selectedItem && selectedItem.classList.contains('skipped');
    isFromCompleted = selectedItem && selectedItem.classList.contains('completed');
    isServingCompleted = isFromCompleted || isFromSkipped;
    console.log('Calling specific queue item:', targetQueueId, 'Ticket:', selectedTicketNumber, 'From completed:', isFromCompleted);
    
    fetch(WindowData.baseUrl + 'window/callNext/' + windowId, {
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
            // Only disable skip if from completed list (allow skip from skipped list)
            skipBtn.disabled = isFromCompleted;
            
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

function skipCurrent() {
    const windowId = WindowData.windowId;
    
    if (!currentQueueId) return;
    
    fetch(WindowData.baseUrl + 'window/skip/' + currentQueueId, {
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
    const windowId = WindowData.windowId;
    
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
            fetch(WindowData.baseUrl + 'window/complete/' + currentQueueId, {
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
    fetch(WindowData.baseUrl + 'window/getCustomerDataByTransaction/' + encodeURIComponent(transactionNumber), {
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
    fetch(WindowData.baseUrl + 'window/getCustomerData/' + ticketNumber, {
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

// Connection state management
let isOnline = navigator.onLine;
let retryCount = 0;
let maxRetries = 3;
let refreshInterval;
let hasUnsavedChanges = false;
let autoSaveInterval;

function startRefreshInterval() {
    // Auto refresh every 2 seconds with error handling
    refreshInterval = setInterval(refreshData, 2000);
    
    // Online/offline event listeners
    window.addEventListener('online', () => {
        console.log('Connection restored');
        retryConnection();
    });

    window.addEventListener('offline', () => {
        console.log('Connection lost');
        showConnectionError();
    });
}

function refreshData() {
    const windowId = WindowData.windowId;
    
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

    fetch(WindowData.baseUrl + 'window/data/' + windowId, {
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
            console.log('Server says now_serving:', data.now_serving, 'current_queue_id:', data.current_queue_id);
            document.getElementById('nowServing').textContent = data.now_serving || 'None';
            console.log('Now Serving element text is now:', document.getElementById('nowServing').textContent);
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
            
            // Restore selection immediately after updating lists
            restoreSelection();
            
            // Enable/disable buttons
            updateButtonStates(data);
            
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

function updateButtonStates(data) {
    const callBtn = document.getElementById('callBtn');
    const completeBtn = document.getElementById('completeBtn');
    const skipBtn = document.getElementById('skipBtn');
    
    // Check if there's a current serving customer
    console.log('updateButtonStates - data.now_serving:', data.now_serving, 'current_queue_id:', data.current_queue_id);
    const isServing = data.now_serving && data.now_serving !== 'None';
    console.log('isServing calculated as:', isServing);
    
    if (isServing) {
        // SERVING STATE: Call Next disabled, Complete/Skip enabled
        callBtn.disabled = true;
        completeBtn.disabled = false;
        // Disable Skip only if serving from completed list (not from skipped list)
        const shouldDisableSkip = data.is_serving_from_completed;
        skipBtn.disabled = shouldDisableSkip;
        console.log('State: SERVING - Call Next: disabled, Complete: enabled, Skip:', shouldDisableSkip ? 'disabled' : 'enabled');
    } else {
        // NOT SERVING STATE: Complete and Skip ALWAYS disabled
        completeBtn.disabled = true;
        skipBtn.disabled = true;
        
        // Call Next enabled only if there's a selection and there are customers in queue
        const waitingCount = document.querySelectorAll('#waitingList .queue-item').length;
        const skippedCount = document.querySelectorAll('#skippedList .queue-item').length;
        const completedCount = document.querySelectorAll('#completedList .queue-item').length;
        const hasQueueItems = waitingCount > 0 || skippedCount > 0 || completedCount > 0;
        
        // Call Next: enabled only when selection exists AND there are queue items
        callBtn.disabled = !hasQueueItems || !selectedQueueId;
        
        console.log('State: NOT SERVING - Call Next:', callBtn.disabled ? 'disabled' : 'enabled', '(selection:', selectedQueueId ? 'yes' : 'none', ', queue items:', hasQueueItems ? 'yes' : 'no', '), Complete: disabled, Skip: disabled');
        
        // Reset flag when no customer is being served
        isServingCompleted = false;
        localStorage.removeItem('isServingCompleted_' + WindowData.windowId);
    }
}

function showConnectionError() {
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
        Connection Lost <i class="bi bi-exclamation-triangle-fill"></i><br>
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
        const response = await fetch(WindowData.baseUrl + 'window/getCustomerDataByTransaction/' + encodeURIComponent(txnToCheck), {
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
    if (WindowData.windowPrefix === 'BREQS') {
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
    const serviceValue = WindowData.windowPrefix === 'BREQS' ? 'BREQS' : document.getElementById('service').value;
    
    return {
        customerName: document.getElementById('customerName').value.trim(),
        documentName: document.getElementById('documentName').value.trim(),
        service: serviceValue,
        remarks: document.getElementById('remarks').value.trim(),
        transactionNumber: document.getElementById('transactionNumber').value.trim(),
        window_id: WindowData.windowId,
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
        const response = await fetch(WindowData.baseUrl + 'window/saveCustomer', {
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
    
    // Warn about unsaved changes when leaving page
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges && hasFormData()) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
    
    // Auto-save interval
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
    
    // Don't clear service for BREQS windows
    if (WindowData.windowPrefix !== 'BREQS') {
        document.getElementById('service').value = '';
    }
    
    document.getElementById('remarks').value = '';
    document.getElementById('transactionNumber').value = '';
    hasUnsavedChanges = false;
    console.log('Form cleared');
}

// Customer Search Functionality
let searchTimeout;

function initSearch() {
    const searchBar = document.getElementById('searchBar');
    
    if (!searchBar) return;
    
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
    
    // Search input event listeners
    searchBar.addEventListener('input', debouncedSearch);
    searchBar.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            debouncedSearch();
        }
    });
    
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        const searchResults = document.getElementById('searchResults');
        if (!searchBar.contains(e.target) && !searchResults.contains(e.target)) {
            hideSearchResults();
        }
    });
}

// Perform search via AJAX
function performSearch(query) {
    console.log('Searching for:', query);
    const url = WindowData.baseUrl + 'window/searchCustomers?q=' + encodeURIComponent(query);
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
    const searchResults = document.getElementById('searchResults');
    
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
            document.getElementById('searchBar').value = customerData.document_name || '';
        });
    });
}

// Hide search results
function hideSearchResults() {
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        searchResults.style.display = 'none';
    }
}

// Populate form with customer data
function populateFormWithCustomer(customer) {
    document.getElementById('customerName').value = customer.customer_name || '';
    document.getElementById('documentName').value = customer.document_name || '';
    
    // Don't override service for BREQS windows
    if (WindowData.windowPrefix !== 'BREQS') {
        document.getElementById('service').value = customer.service || '';
    }
    
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

function searchCustomer() {
    const searchBar = document.getElementById('searchBar');
    if (searchBar && searchBar.value.trim().length >= 2) {
        performSearch(searchBar.value.trim());
    }
}

// Form submission
function initFormSubmission() {
    const customerForm = document.getElementById('customerForm');
    if (!customerForm) return;
    
    customerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const customerName = document.getElementById('customerName').value.trim();
        const documentName = document.getElementById('documentName').value.trim();
        const transactionNumber = document.getElementById('transactionNumber').value;
        
        // For BREQS, service is auto-set; for other windows, validate service
        let serviceValid = false;
        if (WindowData.windowPrefix === 'BREQS') {
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
        let currentWindowId = WindowData.windowId;
        
        if (windowNameElement) {
            const fullWindowName = windowNameElement.textContent;
            windowName = fullWindowName.split(' - ')[1] || '';
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // For BREQS, manually set service since disabled field won't submit
        if (WindowData.windowPrefix === 'BREQS') {
            data.service = 'BREQS';
        }
        
        // Add window information
        data.window_id = currentWindowId;
        data.window_name = windowName;
        
        // Send data to server
        fetch(WindowData.baseUrl + 'window/saveCustomer', {
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
}

// Update customer info
function updateCustomerInfo() {
    const nowServing = document.getElementById('nowServing').textContent;
    if (nowServing !== 'None') {
        autoPopulateService(nowServing);
        const transactionNumber = generateTransactionNumber(nowServing);
        document.getElementById('transactionNumber').value = transactionNumber;
    }
}
