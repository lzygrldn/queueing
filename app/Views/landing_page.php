<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queueing System</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            text-align: center;
            padding: 20px;
        }
        
        h1 {
            color: white;
            font-size: 3rem;
            margin-bottom: 3rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .menu-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
        }
        
        .menu-item {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            width: 250px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        
        .menu-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        
        .menu-item .icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .menu-item.admin .icon { color: #e74c3c; }
        .menu-item.window .icon { color: #3498db; }
        .menu-item.kiosk .icon { color: #2ecc71; }
        .menu-item.display .icon { color: #f39c12; }
        
        .menu-item h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .menu-item p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        
        .modal h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #555;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 10px;
            text-align: center;
        }

        /* Window Selection Modal */
        .window-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .window-btn {
            padding: 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .window-btn:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Local Civil Registry Queueing System</h1>
        
        <div class="menu-grid">
            <div class="menu-item admin" onclick="openAdminModal()">
                <div class="icon">👨‍💼</div>
                <h2>Admin</h2>
                <p>Manage windows, queues, and view reports</p>
            </div>
            
            <div class="menu-item window" onclick="openWindowModal()">
                <div class="icon">🏢</div>
                <h2>Window Staff</h2>
                <p>Serve customers and manage queue</p>
            </div>
            
            <a href="<?= base_url('kiosk') ?>" class="menu-item kiosk">
                <div class="icon">🎫</div>
                <h2>Kiosk</h2>
                <p>Print tickets for customers</p>
            </a>
            
            <a href="<?= base_url('display') ?>" class="menu-item display">
                <div class="icon">📺</div>
                <h2>Display Monitor</h2>
                <p>View current queue status</p>
            </a>
        </div>
    </div>

    <!-- Admin Login Modal -->
    <div class="modal-overlay" id="adminModal">
        <div class="modal">
            <h2>Admin Login</h2>
            <form id="adminLoginForm" action="<?= base_url('admin/login') ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="error-message"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeAdminModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Window Selection Modal -->
    <div class="modal-overlay" id="windowModal">
        <div class="modal">
            <h2>Select Window</h2>
            <div class="window-grid">
                <button class="window-btn" onclick="selectWindow(1)">Window 1<br><small>PSA</small></button>
                <button class="window-btn" onclick="selectWindow(2)">Window 2<br><small>Birth</small></button>
                <button class="window-btn" onclick="selectWindow(3)">Window 3<br><small>Death</small></button>
                <button class="window-btn" onclick="selectWindow(4)">Window 4<br><small>Marriage</small></button>
            </div>
            <div class="btn-group" style="margin-top: 20px;">
                <button class="btn btn-secondary" onclick="closeWindowModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        function openAdminModal() {
            document.getElementById('adminModal').classList.add('active');
            document.getElementById('username').focus();
        }
        
        function closeAdminModal() {
            document.getElementById('adminModal').classList.remove('active');
        }
        
        function openWindowModal() {
            document.getElementById('windowModal').classList.add('active');
        }
        
        function closeWindowModal() {
            document.getElementById('windowModal').classList.remove('active');
        }
        
        function selectWindow(number) {
            window.location.href = '<?= base_url('window/') ?>' + number;
        }
        
        // Close modals when clicking outside
        document.getElementById('adminModal').addEventListener('click', function(e) {
            if (e.target === this) closeAdminModal();
        });
        
        document.getElementById('windowModal').addEventListener('click', function(e) {
            if (e.target === this) closeWindowModal();
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAdminModal();
                closeWindowModal();
            }
        });
    </script>
</body>
</html>
