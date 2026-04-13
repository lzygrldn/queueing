<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Queueing System' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <?php if (!$this->renderSection('hide_navbar')): ?>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <h1><?= $this->renderSection('navbar_title') ?: 'Queueing System' ?></h1>
        </div>
        <div class="navbar-actions">
            <?= $this->renderSection('navbar_actions') ?>
            <!-- Theme Toggle Button -->
            <button class="theme-toggle" title="Switch Theme"><i class="bi bi-moon-fill"></i></button>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Layout Container -->
    <div class="layout-container">
        <?= $this->renderSection('content') ?>
    </div>
    
    <!-- Theme Switcher Script -->
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
