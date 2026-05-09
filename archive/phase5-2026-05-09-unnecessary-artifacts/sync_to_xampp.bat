@echo off
echo ===================================
echo   SportFuel - Synchronisation auto
echo ===================================
echo.
echo Copie vers C:\xampp\htdocs\SportFuel-Module1 ...
xcopy /E /I /Y "C:\Users\LENOVO\Documents\sport fuel\SportFuel-Module1\*" "C:\xampp\htdocs\SportFuel-Module1\"
echo.
echo ===================================
echo   SYNC TERMINE !
echo   Ouvrez : http://localhost/SportFuel-Module1/view/index.html
echo ===================================
pause
