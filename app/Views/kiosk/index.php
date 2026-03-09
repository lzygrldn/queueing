<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk - Queueing System</title>
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
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .header h1 {
            color: #667eea;
            font-size: 2rem;
        }
        
        .back-btn {
            padding: 12px 25px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #c0392b;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .instructions {
            text-align: center;
            color: white;
            margin-top: 50px;
            margin-bottom: 100px;
        }
        
        .instructions h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .instructions p {
            font-size: 1.5rem;
            opacity: 0.9;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .service-btn {
            background: white;
            border: none;
            border-radius: 10px;
            padding: 30px 20px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .service-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }
        
        .service-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .service-prefix {
            font-size: 1.2rem;
            color: #667eea;
        }
        
        /* Ticket Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .ticket-modal {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        .ticket {
            border: 3px dashed #667eea;
            padding: 30px;
            margin: 20px 0;
            border-radius: 10px;
        }
        
        .ticket-header {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .ticket-number {
            font-size: 3rem;
            font-weight: bold;
            color: #e74c3c;
            margin: 20px 0;
        }
        
        .ticket-date {
            font-size: 1rem;
            color: #7f8c8d;
        }
        
        .btn-primary {
            padding: 15px 40px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }

        /* Thermal Print Styles */
        .ticket-preview {
            background: white;
            border: 2px solid #333;
            padding: 12px;
            margin: 20px 0;
            text-align: center;
            width: 288px;
            min-height: max-content;
            min-width: 300px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .preview-header {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        
        .preview-city {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
        }
        
        .preview-number {
            font-size: 32px;
            font-weight: bold;
            color: #000;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
            line-height: 1;
        }
        
        .preview-datetime {
            font-size: 9px;
            color: #333;
            margin-top: 6px;
        }
        
        .btn-secondary {
            padding: 15px 40px;
            background: #95a5a6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="instructions">
            <h2>Welcome to Local Civil Registry</h2>
            <p>Please select a service to get your ticket number</p>
        </div>

        <div class="services-grid">
            <button class="service-btn" onclick="printTicket('psa')">
                <div class="service-name">PSA</div>
                <div class="service-prefix">Window 1</div>
            </button>
            
            <button class="service-btn" onclick="printTicket('birth')">
                <div class="service-name">Birth Registration</div>
                <div class="service-prefix">Window 2</div>
            </button>
            
            <button class="service-btn" onclick="printTicket('death')">
                <div class="service-name">Death Registration</div>
                <div class="service-prefix">Window 3</div>
            </button>
            
            <button class="service-btn" onclick="printTicket('marriage')">
                <div class="service-name">Marriage Registration</div>
                <div class="service-prefix">Window 4</div>
            </button>
        </div>
    </div>

    <!-- Hidden iframe for thermal printing -->
    <iframe id="printFrame" style="display: none;"></iframe>
    
    <!-- Ticket Modal -->
    <div class="modal-overlay" id="ticketModal">
        <div class="ticket-modal">
            <div class="success-icon">✓</div>
            <div class="ticket-preview" id="ticketPreview">
                <div class="preview-header">OFFICE OF THE LOCAL CIVIL REGISTRAR</div>
                <div class="preview-city">GENERAL SANTOS CITY</div>
                <div class="preview-number" id="previewNumber"></div>
                <div class="preview-datetime" id="previewDateTime"></div>
            </div>
            <button class="btn-primary" onclick="printAndClose()">Print Ticket</button>
            <button class="btn-secondary" onclick="closeTicket()">Cancel</button>
        </div>
    </div>

    <script>
        let currentTicketData = null;
        
        function printTicket(service) {
            fetch('<?= base_url('kiosk/print') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'service=' + service
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentTicketData = data.ticket;
                    document.getElementById('previewNumber').textContent = data.ticket.number;
                    document.getElementById('previewDateTime').textContent = data.ticket.datetime;
                    document.getElementById('ticketModal').classList.add('active');
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        function printAndClose() {
            if (!currentTicketData) return;
            
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        @page { size: 48mm 120mm; margin: 0; }
                        body { 
                            margin: 5mm; 
                            padding: 6px; 
                            font-family: Arial, sans-serif;
                            text-align: center;
                            width: 288px;
                        }
                        .header {
                            font-size: 10px;
                            font-weight: bold;
                            line-height: 1.2;
                            margin-bottom: 2px;
                        }
                        .city {
                            font-size: 10px;
                            font-weight: bold;
                            margin-bottom: 6px;
                        }
                        .number {
                            font-size: 30px;
                            font-weight: bold;
                            margin: 6px 0;
                            font-family: 'Courier New', monospace;
                            line-height: 1;
                        }
                        .datetime {
                            font-size: 9px;
                            margin-top: 4px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">OFFICE OF THE LOCAL CIVIL REGISTRAR</div>
                    <div class="city">GENERAL SANTOS CITY</div>
                    <div class="number">${currentTicketData.number}</div>
                    <div class="datetime">${currentTicketData.datetime}</div>
                </body>
                </html>
            `;
            
            const printFrame = document.getElementById('printFrame');
            printFrame.contentDocument.write(printContent);
            printFrame.contentDocument.close();
            
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
            
            closeTicket();
        }

        function closeTicket() {
            document.getElementById('ticketModal').classList.remove('active');
            currentTicketData = null;
        }

        // Close modal on outside click
        document.getElementById('ticketModal').addEventListener('click', function(e) {
            if (e.target === this) closeTicket();
        });
    </script>
</body>
</html>
