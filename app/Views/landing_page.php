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
            background: url('https://rpnradio.com/wp-content/uploads/2022/11/Gensan-city-hall.jpg') center center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            text-align: center;
            padding: 20px;
            width: 100%;
            max-width: 500px;
        }
        
        .login-card {
            background: rgba(0, 0, 0, 0.52);
            backdrop-filter: blur(3px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .login-card h2 {
            color: white;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #5568d3;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 4px;
        }
        
        .public-links {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        
        .public-links a {
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            background: rgba(0, 0, 0, 0.52);
            backdrop-filter: blur(3px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            font-weight: 500;
        }
        
        .public-links a:hover {
            background: rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2>Login</h2>
            <form id="loginForm" action="<?= base_url('login') ?>" method="POST">
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

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    </script>
</body>
</html>
