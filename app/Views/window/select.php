<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Window</title>
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
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .window-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 600px;
        }
        
        .window-btn {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 40px 30px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .window-btn:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        
        .window-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .window-name {
            font-size: 1.2rem;
            color: #2c3e50;
        }
        
        .back-btn {
            margin-top: 30px;
            padding: 15px 40px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #f0f0f0;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Your Window</h1>
        
        <div class="window-grid">
            <button class="window-btn" onclick="selectWindow(1)">
                <div class="window-number">1</div>
                <div class="window-name">BREQS</div>
            </button>
            <button class="window-btn" onclick="selectWindow(2)">
                <div class="window-number">2</div>
                <div class="window-name">Birth Registration</div>
            </button>
            <button class="window-btn" onclick="selectWindow(3)">
                <div class="window-number">3</div>
                <div class="window-name">Death Registration</div>
            </button>
            <button class="window-btn" onclick="selectWindow(4)">
                <div class="window-number">4</div>
                <div class="window-name">Marriage Registration</div>
            </button>
        </div>
        
        <a href="<?= base_url() ?>" class="back-btn">Back to Home</a>
    </div>

    <script>
        function selectWindow(number) {
            window.location.href = '<?= base_url('window/') ?>' + number;
        }
    </script>
</body>
</html>
