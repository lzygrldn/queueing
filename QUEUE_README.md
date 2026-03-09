# Queueing System - Setup & Usage Guide

## 🚀 Quick Start

1. **Start XAMPP** - Open XAMPP Control Panel and start Apache & MySQL
2. **Create Database** - Import `database_setup.sql` into phpMyAdmin
3. **Access System** - Open `http://localhost/queueing/public/`

## 📋 System Components

### Landing Page (`/`)
4 horizontally aligned options:
- **👨‍💼 Admin** - Login: admin/admin123
- **🪟 Window Staff** - Select window 1-4
- **🎫 Kiosk** - Print tickets
- **📺 Display** - Monitor queue status

### Admin Dashboard
- 4 window widgets with real-time status
- Complete/Skip buttons for each window
- Data table for queue history
- Daily & monthly reports
- Reset functions with confirmation
- Navigate to Kiosk/Display with back button

### Window Staff (4 Windows)
| Window | Service | Ticket Format |
|--------|---------|---------------|
| 1 | PSA | PSA-001 |
| 2 | Birth | BIRTH-001 |
| 3 | Death | DEATH-001 |
| 4 | Marriage | MARRIAGE-001 |

Each window shows:
- Now Serving ticket number
- Complete/Skip buttons
- Waiting queue count
- Waiting list (no buttons)

### Kiosk
- 4 service buttons
- Prints tickets in format:
  ```
  Local Civil Registry General Santos City
  PSA-001
  Jan. 18, 2026
  ```
- Numbers reset to 001 when admin resets

### Display Monitor
- 4 widgets showing current serving numbers
- Real-time clock (updates every 100ms)
- Current date display
- Auto-refreshes every 2 seconds

## 🔄 Auto-Refresh Rates
- Admin: 3 seconds
- Window Staff: 2 seconds  
- Display: 2 seconds
- Clock: 100ms

## 🛡️ Security
- Admin login required for dashboard
- Session-based authentication
- Window staff isolated to their window only

## 🗄️ Database
**Database Name:** `queueing_system`

Run this SQL in phpMyAdmin or MySQL:
```sql
-- Import database_setup.sql file
```

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection error | Start MySQL in XAMPP |
| 404 errors | Check Apache is running |
| Login not working | Clear browser cookies |
| Pages not loading | Verify URL: `http://localhost/queueing/public/` |

## 📂 File Locations
- Controllers: `app/Controllers/`
- Models: `app/Models/`
- Views: `app/Views/`
- Config: `app/Config/`
- Database SQL: `database_setup.sql`

## ⚙️ Configuration
- Base URL: `http://localhost/queueing/public/`
- Database: `queueing_system`
- DB User: `root` (no password)

---
**Built with CodeIgniter 4** | Local Civil Registry - General Santos City
