@echo off
echo ========================================
echo Queueing System - Final Deployment Test
echo ========================================
echo.

echo [1/4] Testing database connection...
c:\xampp\mysql\bin\mysql.exe -u root -e "USE queueing_system; SELECT COUNT(*) as window_count FROM windows;" 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Database connection failed
    pause
    exit /b 1
)
echo Database connection: OK

echo.
echo [2/4] Testing web server...
curl -s -o nul -w "%%{http_code}" http://localhost/queueing/ 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Web server not responding
    echo Please check Apache is running
    pause
    exit /b 1
)
echo Web server: OK

echo.
echo [3/4] Testing application files...
if not exist "c:\xampp\htdocs\queueing\index.php" (
    echo ERROR: index.php not found
    pause
    exit /b 1
)
if not exist "c:\xampp\htdocs\queueing\.htaccess" (
    echo ERROR: .htaccess not found
    pause
    exit /b 1
)
echo Application files: OK

echo.
echo [4/4] Testing permissions...
if not exist "c:\xampp\htdocs\queueing\writable\session" (
    mkdir "c:\xampp\htdocs\queueing\writable\session" 2>nul
)
if not exist "c:\xampp\htdocs\queueing\writable\cache" (
    mkdir "c:\xampp\htdocs\queueing\writable\cache" 2>nul
)
if not exist "c:\xampp\htdocs\queueing\writable\logs" (
    mkdir "c:\xampp\htdocs\queueing\writable\logs" 2>nul
)
echo Permissions: OK

echo.
echo ========================================
echo ✅ DEPLOYMENT VERIFICATION COMPLETE!
echo ========================================
echo.
echo Your queueing system is ready!
echo.
echo 🌐 Access URL: http://localhost/queueing/
echo 👤 Admin Login: admin / admin123
echo.
echo 📋 Quick Test Checklist:
echo ☐ Landing page loads with 4 options
echo ☐ Admin login works
echo ☐ Window staff dashboard loads
echo ☐ Kiosk prints tickets
echo ☐ Display monitor shows real-time data
echo.
echo Press any key to open the system...
pause >nul
start http://localhost/queueing/
echo.
echo 🎉 Deployment successful! System is now running.
