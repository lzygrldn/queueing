<?php $this->extend('layouts/main') ?>

<?php $this->section('styles') ?>
<!-- Styles included in main.css -->
<?php $this->endSection() ?>

<?php $this->section('navbar_title') ?><?= $title ?><?php $this->endSection() ?>

<?php $this->section('navbar_actions') ?>
<button class="btn btn-primary" onclick="exportData()">Export CSV</button>
<a href="<?= base_url('admin') ?>" class="btn btn-secondary">Back</a>
<?php $this->endSection() ?>

<?php $this->section('content') ?>
<meta name="base-url" content="<?= base_url() ?>">
<div class="records-container">
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
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script><?= file_get_contents(APPPATH . 'Views/admin/admin.js') ?></script>
<?php $this->endSection() ?>
