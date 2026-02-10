@echo off
echo ========================================
echo   Church Management System - Windows Fix
echo ========================================
echo.

echo 1. Stopping all servers...
taskkill /F /IM php.exe 2>nul
taskkill /F /IM node.exe 2>nul

echo 2. Clearing Laravel caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo 3. Fixing corrupted routes file...
del routes\web.php 2>nul
(
echo ^<?php
echo.
echo use Illuminate\Support\Facades\Route;
echo use App\Http\Controllers\Auth\LoginController;
echo use App\Http\Controllers\Auth\ChurchRegistrationController;
echo.
echo // Public routes ^(no authentication required^)
echo Route::middleware^("guest"^)-^>group^(function ^(^) {
echo     // Welcome page ^(public landing^)
echo     Route::get^("/", function ^(^) {
echo         return view^("welcome"^);
echo     ^}^);
echo.
echo     // Login
echo     Route::get^("/login", [LoginController::class, "create"]^)-^>name^("login"^);
echo     Route::post^("/login", [LoginController::class, "store"]^);
echo.
echo     // Registration
echo     Route::get^("/register", [ChurchRegistrationController::class, "create"]^)-^>name^("register"^);
echo     Route::post^("/register", [ChurchRegistrationController::class, "store"]^);
echo ^}^);
echo.
echo // Protected routes ^(require authentication^)
echo Route::middleware^("auth"^)-^>group^(function ^(^) {
echo     // Vue.js Application - Main SPA
echo     Route::get^("/app", function ^(^) {
echo         return view^("app"^);
echo     ^}^);
echo.
echo     // Dashboard redirect to Vue app
echo     Route::get^("/dashboard", function ^(^) {
echo         return redirect^("/app"^);
echo     ^}^);
echo.
echo     // Logout
echo     Route::post^("/logout", [LoginController::class, "destroy"]^)-^>name^("logout"^);
echo ^}^);
) > routes\web.php

echo 4. Checking view files...
if not exist "resources\views\app.blade.php" (
    echo Creating app.blade.php...
    copy nul "resources\views\app.blade.php"
)

echo 5. Verifying routes...
php artisan route:list

echo.
echo ========================================
echo   START THESE IN SEPARATE TERMINALS:
echo ========================================
echo.
echo   TERMINAL 1: php artisan serve
echo   TERMINAL 2: npm run dev
echo.
echo ========================================
echo   TEST IN THIS ORDER:
echo ========================================
echo   1. http://localhost:8000/
echo   2. http://localhost:8000/login
echo   3. Login with: admin@church.com / password123
echo   4. Should redirect to: http://localhost:8000/app
echo.
pause
