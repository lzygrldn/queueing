<?php $this->extend('layouts/main') ?>

<?php $this->section('hide_navbar') ?>true<?php $this->endSection() ?>

<?php $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/landing_page.css') ?>">
<?php $this->endSection() ?>

<?php $this->section('content') ?>
<div class="login-container">
    <div class="login-card">
        <h2>Login</h2>
        <form id="loginForm" action="<?= base_url('login') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="error-message"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <div class="public-links">
        <a href="<?= base_url('queue') ?>">Get Ticket</a>
        <a href="<?= base_url('display') ?>">Display Monitor</a>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script src="<?= base_url('assets/js/landing_page.js') ?>"></script>
<?php $this->endSection() ?>
