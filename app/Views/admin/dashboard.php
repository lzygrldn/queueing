<?php $this->extend('layouts/main') ?>

<?php $this->section('styles') ?>
<!-- Styles included in main.css -->
<?php $this->endSection() ?>

<?php $this->section('navbar_title') ?>Admin Dashboard<?php $this->endSection() ?>

<?php $this->section('navbar_actions') ?>
<button class="btn btn-primary" onclick="toggleSystemControls(event)">System Controls</button>
<a href="<?= base_url('admin/customer-records') ?>" class="btn btn-primary">Customer Records</a>
<a href="<?= base_url('queue') ?>?from_admin=true" class="btn btn-primary">Queue</a>
<a href="<?= base_url('admin/display') ?>" class="btn btn-primary">Display</a>
<form action="<?= base_url('admin/logout') ?>" method="POST" style="display: inline;">
    <button type="submit" class="btn btn-danger">Logout</button>
</form>
<?php $this->endSection() ?>

<?php $this->section('content') ?>
<?php 
// Ensure base_url is never empty
$baseUrl = base_url();
if (empty($baseUrl)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . $host . '/queueing/';
}
?>
<meta name="base-url" content="<?= $baseUrl ?>">

<!-- System Controls Drawer -->
<div class="container" id="system-controls-container">
    <div class="reset-buttons">
        <button class="btn-reset btn-reset-windows" onclick="confirmResetWindows()">Reset Windows & Queues</button>
        <button class="btn-reset btn-reset-numbers" onclick="confirmResetNumbers()">Reset Released Numbers</button>
        <button class="btn-reset btn-reset-daily" onclick="confirmResetDailyStats()">Reset Daily Statistics</button>
        <button class="btn-reset btn-reset-monthly" onclick="confirmResetMonthlyStats()">Reset Monthly Statistics</button>
    </div>
</div>

<!-- Window Status -->
<div class="container" id="window-status-container">
    <h2 class="section-title" id="window-status">Window Status</h2>
    <div class="windows-grid">
        <?php foreach ($windows as $window): ?>
        <div class="window-widget">
            <div class="window-header">
                <h3>Window <?= $window['window_number'] ?></h3>
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
            <a href="<?= base_url('window/' . $window['window_number']) ?>?from_admin=true" class="btn-go-window btn-small">Go to Window <?= $window['window_number'] ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Statistics -->
<div class="container" id="statistics-container">
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
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script><?= file_get_contents(APPPATH . 'Views/admin/admin.js') ?></script>
<?php $this->endSection() ?>
