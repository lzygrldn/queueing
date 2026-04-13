<?php $this->extend('layouts/main') ?>

<?php $this->section('styles') ?>
<!-- Styles included in main.css -->
<?php $this->endSection() ?>

<?php $this->section('navbar_title') ?>Window <?= $window['window_number'] ?> - <?= $window['window_name'] ?><?php $this->endSection() ?>

<?php $this->section('navbar_actions') ?>
<!-- Debug: from_admin = <?= var_export($from_admin ?? 'NOT SET', true) ?> -->
<a href="<?= !empty($from_admin) ? base_url('admin') : base_url() ?>" class="btn btn-secondary">
    <?= !empty($from_admin) ? 'Back to Admin' : 'Back' ?>
</a>
<?php $this->endSection() ?>

<?php $this->section('content') ?>

    <div class="window-main-grid">
        <!-- Left Section -->
        <div class="window-left-section">
            <!-- Now Serving -->
            <div class="now-serving-card">
                <h2>Now Serving</h2>
                <div class="ticket-number" id="nowServing">
                    <?= $now_serving ? $now_serving['ticket_number'] : 'None' ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-card">
                <h3>Actions</h3>
                <div class="actions-inline">
                    <button class="btn btn-call" id="callBtn" disabled onclick="callNext()">CALL NEXT</button>
                    <button class="btn btn-skip" id="skipBtn" disabled onclick="skipCurrent()">SKIP</button>
                    <button class="btn btn-complete" id="completeBtn" disabled onclick="completeCurrent()">COMPLETE</button>
                </div>
            </div>

            <!-- Three Column Lists -->
            <div class="three-column-layout">
                <div class="list-section">
                    <h3>Waiting Queue</h3>
                    <div class="queue-list" id="waitingList">
                        <?php if (empty($waiting_list)): ?>
                            <div class="empty-state">No customers waiting</div>
                        <?php else: ?>
                            <?php foreach ($waiting_list as $waiting): ?>
                                <div class="queue-item waiting" data-id="<?= $waiting['id'] ?>" data-ticket="<?= $waiting['ticket_number'] ?>" data-service-type="<?= $waiting['service_type'] ?? '' ?>"><?= $waiting['ticket_number'] ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="list-section">
                    <h3>Skipped</h3>
                    <div class="queue-list" id="skippedList">
                        <?php if (empty($skipped_list)): ?>
                            <div class="empty-state">No skipped customers</div>
                        <?php else: ?>
                            <?php foreach ($skipped_list as $skipped): ?>
                                <div class="queue-item skipped" data-id="<?= $skipped['id'] ?>" data-ticket="<?= $skipped['ticket_number'] ?>" data-service-type="<?= $skipped['service_type'] ?? '' ?>"><?= $skipped['ticket_number'] ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="list-section">
                    <h3>Completed</h3>
                    <div class="queue-list" id="completedList">
                        <?php if (empty($completed_list)): ?>
                            <div class="empty-state">No completed customers</div>
                        <?php else: ?>
                            <?php foreach ($completed_list as $completed): ?>
                                <div class="queue-item completed" data-id="<?= $completed['id'] ?>" data-ticket="<?= $completed['ticket_number'] ?>" data-service-type="<?= $completed['service_type'] ?? '' ?>"><?= $completed['ticket_number'] ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="window-right-column">
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search by transaction number or name..." id="searchBar" autocomplete="off">
                <div id="searchResults" class="search-results" style="display: none;"></div>
            </div>

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
                        <textarea id="remarks" name="remarks"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="transactionNumber">Transaction Number</label>
                        <input type="text" id="transactionNumber" name="transactionNumber" readonly>
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-call">Save Customer</button>
                        <button type="button" class="btn btn-skip" onclick="clearForm()">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="customModal">
        <div>
            <div id="modalMessage"></div>
            <div id="modalButtons">
                <button id="modalOkBtn">OK</button>
                <button id="modalCancelBtn" style="display: none;">Cancel</button>
            </div>
        </div>
    </div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
    <script>
        // Global WindowData object - PHP values passed to JavaScript
        window.WindowData = {
            windowId: <?= $window['id'] ?>,
            windowPrefix: '<?= $window['prefix'] ?? '' ?>',
            currentQueueId: <?= $now_serving ? $now_serving['id'] : 'null' ?>,
            baseUrl: '<?= base_url() ?>'
        };
    </script>
    <script><?= file_get_contents(APPPATH . 'Views/window/window.js') ?></script>
<?php $this->endSection() ?>

