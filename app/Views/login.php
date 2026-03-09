<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queueing System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(10px) rotate(240deg); }
        }

        .auth-container {
            position: relative;
            z-index: 10;
            max-width: 450px;
            width: 90%;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            transform: translateY(-2px);
        }

        .form-label {
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            transition: color 0.3s ease;
            z-index: 5;
        }

        .form-control.with-icon {
            padding-left: 45px;
        }

        .input-group:focus-within .input-icon {
            color: #667eea;
        }

        .access-info {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .info-card {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateX(5px);
        }

        .info-card h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-card p {
            margin: 0;
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 25px;
                margin: 20px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated background elements -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container mt-5 auth-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-users-cog fa-3x mb-3" style="color: #667eea;"></i>
                <h2>Queueing System</h2>
                <p>Please login to access the system</p>
            </div>

            <!-- Flash success message -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <!-- Flash error message -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Validation errors -->
            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('login') ?>">
                <?= csrf_field() ?>

                <div class="input-group">
                    <label class="form-label">Username</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           name="username" 
                           class="form-control with-icon" 
                           placeholder="Enter your username"
                           value="<?= set_value('username') ?>"
                           required>
                </div>

                <div class="input-group">
                    <label class="form-label">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           name="password" 
                           class="form-control with-icon" 
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Login to System
                </button>
            </form>

            <div class="access-info">
                <div class="info-card">
                    <h6><i class="fas fa-user-shield me-2"></i>Admin Access</h6>
                    <p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-window-maximize me-2"></i>Window Staff</h6>
                    <p><strong>Username:</strong> window1, window2, window3, window4<br><strong>Password:</strong> windowstaff1</p>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-user me-2"></i>User Access</h6>
                    <p><strong>Username:</strong> user<br><strong>Password:</strong> user123</p>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-desktop me-2"></i>Display Monitor</h6>
                    <p><strong>Username:</strong> display<br><strong>Password:</strong> displaymonitor</p>
                </div>
            </div>
        </div>

        <div class="footer-text">
            <p>&copy; 2026 Queueing System. All rights reserved.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>