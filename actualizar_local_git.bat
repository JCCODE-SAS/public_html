@echo off
cls
echo ===================================================
echo  ACTUALIZANDO DESDE GITHUB REPOSITORIO BRAHIAM CTG
echo ===================================================

:: Verifica si es un repositorio Git
IF NOT EXIST ".git" (
    echo Este directorio no es un repositorio Git.
    pause
    exit /b
)

:: Obtener rama actual
FOR /F "tokens=*" %%i IN ('git rev-parse --abbrev-ref HEAD') DO SET BRANCH=%%i

:: Actualizar desde el origen
echo.
echo Obteniendo cambios desde origin/%BRANCH% ...
git pull origin %BRANCH%

echo.
echo ✅ Proyecto BRAHIAM CTG actualizado con éxito desde GitHub.
pause
