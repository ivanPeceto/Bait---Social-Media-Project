#!/bin/bash
set -e

if [ ! -f ".env" ]; then
    echo "Error: El archivo .env no se encontró en la raíz del proyecto."
    echo "Hace una copia de .env.example y renombrala a .env antes de empezar."
    exit 1
fi

cp .env backend/bait-api/.env

cleanup() {
  echo -e "\nLimpiando... Eliminando el .env temporal del backend."
  rm backend/bait-api/.env
}
trap cleanup EXIT


DOCKER_CMD="docker compose up"

if [ "$1" == "--build" ]; then
  DOCKER_CMD="docker compose up --build"
fi

echo "Iniciando contenedores..."
echo "/n⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣤⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⣀⣀⠀⠀⠀⠀⠀⠀⢹⣛⣷⣶⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠀⠀⠀⠀⢀⣠⠴⣒⡮⠭⠉⠉⢐⠊⢋⡉⠉⠉⢒⣒⠬⢄⡉⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠀⠀⢀⡴⣫⠤⠖⠒⠈⠉⠉⠀⠀⠀⠀⠈⠒⠂⠀⣠⠉⣑⠚⠵⣦⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠀⡰⡻⠋⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠉⠘⠳⠀⠒⠙⢦⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢴⣼⣿⡄⠀⠀"
echo "/n⠀⠀⣰⠟⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⢦⠀⠀⠀⠀⠀⠀⠀⣤⣄⠀⠀⠁⠉⠀⠀⠀"
echo "/n⠀⢠⠏⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠳⡀⠀⠀⠀⠀⢰⢷⣿⣷⡄⠀⢀⣀⣀⣀"
echo "/n⠀⣸⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠙⣄⠀⠀⠀⢸⣻⠚⠙⣧⢾⢿⣯⣿⡿"
echo "/n⠀⡏⠀⢀⡀⠀⣀⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⠣⡀⠀⠀⠳⢤⡽⢏⠋⠀⠈⣽⠇"
echo "/n⢀⣇⣀⣈⣓⣋⣁⣀⣙⣒⣚⡁⠀⠀⢀⣀⣀⠀⠀⢿⣿⣿⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⣓⢦⣤⣾⣧⡟⠦⠶⠞⠋⠀"
echo "/n⠈⡏⠇⠀⡇⠀⡇⠀⡇⠀⡇⠉⡏⠉⡟⠛⡫⠤⣀⣀⣉⡀⠀⠀⠀⢀⣴⣦⣄⠀⠀⠀⠀⠀⠀⠀⠀⠈⠀⢹⣿⡿⠀⠀⠀⠀⠀⠀"
echo "/n⠀⣇⢸⠀⡇⠀⡇⠀⡇⠀⡁⠀⡇⠀⡇⠀⡇⠀⢸⢹⠀⡧⡀⠀⠀⢸⣿⣿⣿⣷⣄⡀⠀⠀⠀⠀⠀⠀⣶⣾⡟⠁⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠘⢾⣆⢸⠀⢣⠀⡇⠀⡇⠀⡇⠀⡇⠀⢱⠀⢸⠈⠚⡇⡌⢦⡀⠈⢿⣿⣿⣿⣿⣿⣦⡀⠀⢠⣶⣾⣿⠋⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠈⠙⣦⣣⡘⣆⠸⡀⢳⡀⢱⠀⢱⠀⠸⡀⠘⡄⠀⣷⠗⢰⠈⠓⡾⢿⣿⣿⣿⣿⣿⣿⣦⣤⡿⠋⢁⠀⠀⠀⠀⠀⠀⠀⠀⠀"
echo "/n⠀⠀⠀⠀⠹⠛⠛⠛⠓⠿⠦⠷⠦⢷⣤⣷⣄⣳⣄⣱⣄⣘⣦⣀⣳⣤⡽⠦⠴⠾⠟⠛⣿⢿⣿⠿⠿⠿⠿⠄⠀⠀⠀⠀⠀⠀⠀⠀"
$DOCKER_CMD