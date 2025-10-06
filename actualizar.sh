#!/bin/bash
clear
echo -e "\e[1;32m=========================================================="
echo -e "        🔄  ACTUALIZANDO DESDE GITHUB"
echo -e "             REPOSITORIO: BRAHIAM CTG"
echo -e "==========================================================\e[0m"
echo

# Verifica si es un repositorio Git
if [ ! -d ".git" ]; then
    echo -e "\e[1;31m❌  Este directorio no es un repositorio Git.\e[0m"
    echo
    read -p "Presiona [Enter] para salir..."
    exit 1
fi

# Obtener la rama actual
BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)

echo -e "\e[1;36m📦 Rama actual: $BRANCH\e[0m"
echo

# Obtener y aplicar los cambios del origen
echo -e "\e[1;33m🌐 Obteniendo cambios desde origin/$BRANCH ...\e[0m"
git pull origin "$BRANCH"
STATUS=$?

echo
if [ $STATUS -eq 0 ]; then
    echo -e "\e[1;32m✅ Proyecto BRAHIAM CTG actualizado con éxito desde GitHub.\e[0m"
else
    echo -e "\e[1;31m⚠️  Ocurrió un error al actualizar el repositorio.\e[0m"
fi

echo
read -p "Presiona [Enter] para cerrar..."
