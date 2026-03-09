# Queueing System - Quick Test Guide

## ✅ System Status: READY

### Access URLs (with index.php):
- **Landing Page**: `http://localhost/queueing/public/index.php/`
- **Admin**: `http://localhost/queueing/public/index.php/admin`
- **Window Staff**: `http://localhost/queueing/public/index.php/window`
- **Kiosk**: `http://localhost/queueing/public/index.php/kiosk`
- **Display**: `http://localhost/queueing/public/index.php/display`

### Quick Test Steps:

1. **Start XAMPP** - Make sure Apache & MySQL are running

2. **Test Landing Page**:
   - Go to: `http://localhost/queueing/public/index.php/`
   - Should see 4 options horizontally aligned

3. **Test Admin**:
   - Click Admin → Login popup appears
   - Username: `admin`, Password: `admin123`
   - Should see dashboard with 4 window widgets

4. **Test Window Staff**:
   - Click Window Staff → Select window 1-4
   - Should see window dashboard with serving area

5. **Test Kiosk**:
   - Click Kiosk → Should see 4 service buttons
   - Click any button → Should print ticket

6. **Test Display**:
   - Click Display → Should show 4 windows with real-time clock

### Database Status:
- Database: `queueing_system` ✅ Created
- Tables: `windows`, `queues`, `service_records` ✅ Created
- Default windows: PSA, Birth, Death, Marriage ✅ Inserted

### Features Working:
- ✅ Real-time auto-refresh (2-3 seconds)
- ✅ Admin login/logout
- ✅ Complete/Skip functionality
- ✅ Ticket printing with correct format
- ✅ Reset buttons with confirmation
- ✅ Real-time clock on display
- ✅ Queue management across all windows

### If Issues Occur:
1. **404 Errors**: Check Apache is running
2. **Database Errors**: Verify MySQL is running and database exists
3. **Login Issues**: Clear browser cookies
4. **URL Issues**: Make sure to use `/index.php/` in all URLs

The system is now fully functional with proper URL routing!
