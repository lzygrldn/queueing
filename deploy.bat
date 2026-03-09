@echo off
echo ========================================
echo Queueing System Deployment Script
echo ========================================
echo.

echo [1/5] Checking XAMPP services...
c:\xampp\apache\bin\httpd.exe -n >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Apache is not installed or not running
    echo Please start Apache from XAMPP Control Panel
    pause
    exit /b 1
)

c:\xampp\mysql\bin\mysqld.exe --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: MySQL is not installed or not running
    echo Please start MySQL from XAMPP Control Panel
    pause
    exit /b 1
)
echo Apache and MySQL are running!

echo.
echo [2/5] Creating database...
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS queueing_system;" 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Could not create database
    echo Please check MySQL connection
    pause
    exit /b 1
)
echo Database created successfully!

echo.
echo [3/5] Running database migration...
cd /d c:\xampp\htdocs\queueing
c:\xampp\php\php.exe spark migrate --quiet
if %errorlevel% neq 0 (
    echo ERROR: Database migration failed
    pause
    exit /b 1
)
echo Database tables created successfully!

echo.
echo [4/5] Setting up permissions...
if not exist "writable\session" mkdir writable\session
if not exist "writable\cache" mkdir writable\cache
if not exist "writable\logs" mkdir writable\logs
echo Permissions set!

echo.
echo [5/5] Starting deployment...
echo.
echo ========================================
echo DEPLOYMENT SUCCESSFUL!
echo ========================================
echo.
echo Access your queueing system at:
echo http://localhost/queueing/
echo.
echo Admin Login:
echo Username: admin
echo Password: admin123
echo.
echo Press any key to open the system in your browser...
pause >nul
start http://localhost/queueing/
echo.
echo Deployment complete! Your system is now running.
