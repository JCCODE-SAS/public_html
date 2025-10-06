#!/bin/bash
clear
echo "=================================================="
echo "Seleccione el desarrollador BRAHIAM CTG:"
echo "=================================================="
echo "1. Delia Milena Fuentes"
echo "2. Noe Diomedes Madrigal"
echo "3. VPS Servidor"
echo "=================================================="
read -p "Ingrese el número del desarrollador: " opcion

case $opcion in
    1)
        nombre="Delia Milena Fuentes"
        ;;
    2)
        nombre="Noe Diomedes Madrigal"
        ;;
    3)
        nombre="VPS Servidor"
        ;;
    *)
        echo "Opción inválida. Abortando..."
        exit 1
        ;;
esac

echo
echo "Agregando cambios y generando commit como: $nombre"
echo

git add .
git commit -m "Commit realizado por $nombre"
git push origin main

echo
echo "✅ Cambios enviados correctamente por $nombre"
