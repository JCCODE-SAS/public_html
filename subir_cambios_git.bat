@echo off
echo ==================================================
echo Seleccione el desarrollador BRAHIAM CTG:
echo ==================================================
echo 1. Delia Milena Fuentes
echo 2. Noe Diomedes Madrigal
echo ==================================================

set /p nombre=Ingrese el numero del desarollador:

if "%opcion%"=="1" ser nomber="Delia Milena Fuentes"
if "%opcion%"=="2" ser nomber="Noe Diomedes Madrigal"

echo.
echo Agregando cambios y generando commit como: %nombre%
echo.

git add .
git commit -m "Commit realizado por %nombre%"
git push origin main

pause
