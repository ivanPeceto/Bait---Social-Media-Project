#!/bin/bash

# --- Colores ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# --- -------- ---

# Menú de flahs y uso
show_help() {
    echo "----------------------------------------------------------------"
    echo "Uso: ./start_server.sh [FLAGS] [SERVICIO]"
    echo ""
    echo "FLAGS:"
    echo "  (sin flag)    Levanta los contenedores en segundo plano."
    echo "  --help        Muestra este menú de ayuda."
    echo "  --stop        Detiene y elimina los contenedores."
    echo "  --build       Reconstruye las imágenes de los contenedores antes de levantarlos."
    echo "  --clean       Detiene y elimina contenedores Y VOLÚMENES (borra las bases de datos)."
    echo "  --migrate     Ejecuta las migraciones de la base de datos de Laravel."
    echo ""
    echo "COMBINACIONES ESPECIALES:"
    echo "  --clean --build  Limpieza completa, reconstrucción y migraciones automáticas."
    echo ""
    echo "SERVICIOS (opcional, al final del comando):"
    echo "  --api         Aplica el comando únicamente al servicio del backend (y sus dependencias)."
    echo "  --front       Aplica el comando únicamente al servicio del frontend."
    echo "----------------------------------------------------------------"
}

copy_env() {
    if [ ! -f ".env" ]; then
        echo -e "${RED} Error: El archivo .env no se encontró en la raíz del proyecto.${NC}"
        echo "Por favor, copia .env.example a .env y configúralo antes de ejecutar."
        exit 1
    fi
    cp .env backend/bait-api/.env
}

wait_for_db() {
    SERVICES=("mysql" "mysql_test")
    for service in "${SERVICES[@]}"; do
        echo -e "${YELLOW} Esperando a que la base de datos '$service' esté lista...${NC}"
        # 30 intentos de 5 segundos)
        for i in {1..30}; do
            if docker compose logs $service | grep -q "/usr/sbin/mysqld: ready for connections."; then
                echo -e "${GREEN} La base de datos está lista.${NC}"
                return 0;
            fi
            echo "Base de datos '$service' no disponible, reintentando en 5 segundos..."
            sleep 5
        done

        echo -e "${RED}Error: La base de datos '$service' no estuvo lista después de 150 segundos.${NC}"
        echo "    Revisá los logs del contenedor '$service' con 'docker compose logs $service'."
    done
}

run_migrations() {
    wait_for_db
    sleep 15
    echo -e "${YELLOW}Ejecutando migraciones y seeders...${NC}"
    OUTPUT=$(docker compose exec backend php artisan migrate --seed 2>&1) || true

    # Verificamos si la salida contiene la palabra "ERROR".
    if echo "$OUTPUT" | grep -q "ERROR"; then
        echo -e "${RED} Error durante la migración:${NC}"
        echo -e "${RED}$OUTPUT${NC}"
        exit 1
    elif echo "$OUTPUT" | grep -q "Exception"; then
        echo -e "${RED} Error durante la migración:${NC}"
        echo -e "${RED}$OUTPUT${NC}"
        exit 1    
    else
        echo -e "${GREEN} Migraciones completadas exitosamente.${NC}"
        echo "$OUTPUT"
    fi
}

# -- Variables init ---.
BUILD_FLAG=false
CLEAN_FLAG=false
MIGRATE_FLAG=false
STOP_FLAG=false
SERVICES=""

for arg in "$@"
do
    case $arg in
        --help)
            show_help
            exit 0
            ;;
        --build)
            BUILD_FLAG=true
            shift 
            ;;
        --clean)
            CLEAN_FLAG=true
            shift
            ;;
        --migrate)
            MIGRATE_FLAG=true
            shift
            ;;
        --stop)
            STOP_FLAG=true
            shift
            ;;
        --api)
            SERVICES="backend mysql mysql_test"
            shift
            ;;
        --front)
            SERVICES="frontend mysql mysql_test"
            shift
            ;;
    esac
done

#---- ascii art ----#
blue='\e[34m'
cyan='\e[36m'     
lightblue='\e[94m'      
lightcyan='\e[96m'

echo -e "${blue}                            ####                                                  "
echo -e "                            ######                                  "
echo -e "                            #######                                 "
echo -e "                            #######                                 "
echo -e "                            #######                                 "
echo -e "                            #######                                 "
echo -e "                            #######                                 "
echo -e "                            ###${cyan}XXX${blue}#${NC}                                 "
echo -e "                            ${cyan}XXXXXX${blue}#${NC}       ${cyan}XXXXXXX${lightblue}x                  "
echo -e "                            ${cyan}XXXXXX${blue}#${NC}  ${cyan}XXXXXXXXXXX${lightblue}xxxxxx${lightcyan}+             "
echo -e "                            ${cyan}XXXXXXXXXXXXX${lightblue}xxxxxxxxxxxxxxxxx${lightcyan}+         "
echo -e "                            ${cyan}XXXXXXXXX${lightblue}xxxxx        xxxxxx${lightcyan}+++++       "
echo -e "                            ${cyan}XXXXX${lightblue}xxxx${cyan}x               ${lightblue}x${lightcyan}++++++++      "
echo -e "                            ${cyan}XX${lightblue}xxxxxx${cyan}X                  ${lightcyan}++++++++     "
echo -e "                            ${lightblue}xxxxxxxx                    ${lightcyan}+++++++     "
echo -e "                            ${lightblue}xxxxxxx                      ${lightcyan}+++++++    "
echo -e "                            ${lightblue}xxxxxx${lightcyan}+                      +++++++    "
echo -e "                            ${cyan}xxx${lightcyan}+++${cyan}x                      ${lightcyan}+++++++    "
echo -e "                                                        ${lightcyan}+++++++     "
echo -e "                                                      ${lightcyan}++++++++      "
echo -e "                             ${lightcyan}++++                   +++++++++       "
echo -e "                             ${lightcyan}++++    +++++++++++++++++++++++        "
echo -e "                            ${lightcyan}++++  ++++++++++++++++++++++++          "
echo -e "                            ${lightcyan}+++ ++++++++++++++++++++++++            "              
echo -e "                            ${lightcyan}+++++++++++ ++++++++++++                "
echo -e "                           ${lightcyan}++++++++                                 "
echo -e "                           ${lightcyan}+++++${NC}                                    "
                                                                            
#---------------------------#

if [ "$STOP_FLAG" = true ]; then
    echo -e "${YELLOW} Deteniendo contenedores...${NC}"
    docker compose down
    echo -e "${GREEN} Contenedores detenidos.${NC}"
    exit 0
fi

if [ "$CLEAN_FLAG" = true ] && [ "$BUILD_FLAG" = false ]; then
    echo -e "${YELLOW} Limpiando contenedores y volúmenes...${NC}"
    docker compose down -v
    echo -e "${GREEN} Entorno limpio.${NC}"
    exit 0
fi

# Copia el .env solo si vamos a levantar servicios.
copy_env
echo "Archivo .env copiado al backend."

if [ "$CLEAN_FLAG" = true ] && [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Limpiando entorno por completo (contenedores y volúmenes)...${NC}"
    docker compose down -v
    echo -e "${YELLOW} Reconstruyendo e iniciando servicios...${NC}"
    docker compose up -d --build $SERVICES
    run_migrations
    exit 0
fi

UP_CMD="docker compose up -d"
if [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Reconstruyendo e iniciando servicios...${NC}"
    UP_CMD="docker compose up -d --build"
else
    echo -e "${YELLOW} Iniciando servicios...${NC}"
fi

$UP_CMD $SERVICES

if [ "$MIGRATE_FLAG" = true ]; then
    run_migrations
fi

echo -e "${GREEN} Servidor disponible en segundo plano. Puertos: Backend (8000), Frontend (4200).${NC}"