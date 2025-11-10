@echo off
cls
echo ===============================================
echo   COMPARIX DEVELOPMENT SERVER
echo ===============================================
echo.
echo Server URL: http://localhost:8080
echo.
echo TEST URLS:
echo   Demo (hardcoded): http://localhost:8080/compare/demo
echo   Real data:        http://localhost:8080/compare/versus?ids=323,324,325
echo.
echo ===============================================
echo Press Ctrl+C to stop the server
echo ===============================================
echo.
php -S localhost:8080 -t public


