#!/bin/bash

# --- Colores ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

## --- Variables de configuración ---
NGINX_CONF_FILE="nginx/nginx.conf"
ENV_FILE=".env"
IP_ADDRESS=$(hostname -I | awk '{print $1}')

if [ -z "$IP_ADDRESS" ]; then
    echo "No se pudo obtener la dirección IP. Asegúrate de que ifconfig esté instalado."
    exit 1
fi

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

update_nginx_conf() {
    mkdir -p nginx/

    if [ ! -f "nginx/nginx.conf.template" ]; then
        echo -e "${RED} Error: No se encontró el archivo nginx/nginx.conf.template. Asegúrate de crearlo.${NC}"
        exit 1
    fi
    cp "nginx/nginx.conf.template" "$NGINX_CONF_FILE"

    sed -i "s/SERVER_IP/$IP_ADDRESS/" $NGINX_CONF_FILE
    echo -e "${GREEN} Configuración de Nginx actualizada con la IP del host.${NC}"
}

copy_env() {
    if [ ! -f ".env" ]; then
        echo -e "${RED} Error: El archivo .env no se encontró en la raíz del proyecto.${NC}"
        echo "Por favor, copia .env.example a .env y configúralo antes de ejecutar."
        exit 1
    fi

    sed -i "s/L5_SWAGGER_CONST_HOST=http:\/\/[0-9.]*/L5_SWAGGER_CONST_HOST=http:\/\/${IP_ADDRESS}/" $ENV_FILE
    sed -i "s/VITE_APP_URL_BASE=http:\/\/[0-9.]*/VITE_APP_URL_BASE=http:\/\/${IP_ADDRESS}/" $ENV_FILE
    sed -i "s/IP_ADDRESS=.*/IP_ADDRESS=${IP_ADDRESS}/" $ENV_FILE

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

    if echo "$OUTPUT" | grep -q "ERROR"; then
        echo -e "${RED}Error durante la migración:${NC}"
        echo -e "${RED}$OUTPUT${NC}"
        exit 1
    elif echo "$OUTPUT" | grep -q "Exception"; then
        echo -e "${RED}Error durante la migración:${NC}"
        echo -e "${RED}$OUTPUT${NC}"
        exit 1    
    else
        echo -e "${GREEN}Migraciones completadas exitosamente.${NC}"
        echo "$OUTPUT"
    fi

    echo -e "${YELLOW}Generando key de Laravel...${NC}"
    OUTPUT_KEY=$(docker compose exec backend php artisan key:generate 2>&1) || true
    if echo "$OUTPUT_KEY" | grep -q "Exception"; then
        echo -e "${RED}Error generando key de Laravel:${NC}"
        echo -e "${RED}$OUTPUT_KEY${NC}"
        exit 1
    fi
    echo -e "${GREEN}Key de Laravel generada correctamente.${NC}"

    echo -e "${YELLOW}Generando JWT secret...${NC}"
    OUTPUT_JWT=$(docker compose exec backend php artisan jwt:secret --force 2>&1) || true
    if echo "$OUTPUT_JWT" | grep -q "Exception"; then
        echo -e "${RED}Error generando JWT secret:${NC}"
        echo -e "${RED}$OUTPUT_JWT${NC}"
        exit 1
    fi
    echo -e "${GREEN}JWT secret generado correctamente.${NC}"

    echo -e "${YELLOW}Limpiando configuración y cache de Laravel...${NC}"
    OUTPUT_CONFIG=$(docker compose exec backend php artisan config:clear 2>&1) || true
    OUTPUT_CACHE=$(docker compose exec backend php artisan cache:clear 2>&1) || true

    if echo "$OUTPUT_CONFIG" | grep -q "Exception"; then
        echo -e "${RED}Error limpiando configuración:${NC}"
        echo -e "${RED}$OUTPUT_CONFIG${NC}"
        exit 1
    fi

    if echo "$OUTPUT_CACHE" | grep -q "Exception"; then
        echo -e "${RED}Error limpiando cache:${NC}"
        echo -e "${RED}$OUTPUT_CACHE${NC}"
        exit 1
    fi
    echo -e "${GREEN}Configuración y cache limpiados correctamente.${NC}"
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

# Acciones de Preparación (para cualquier 'up')
copy_env
update_nginx_conf
echo "Archivo .env copiado al backend."

# Acciones post seteo
UP_CMD="docker compose up -d"
STORAGE_LINK_CMD="docker compose exec backend php artisan storage:link"

if [ "$CLEAN_FLAG" = true ] && [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Limpiando entorno por completo (contenedores y volúmenes)...${NC}"
    docker compose down -v
    echo -e "${YELLOW} Reconstruyendo e iniciando servicios...${NC}"
    docker compose up -d --build $SERVICES
    run_migrations
    $STORAGE_LINK_CMD
    echo -e "${GREEN} Servidor disponible en segundo plano.${NC}"
    echo -e "${GREEN} Acceso por Nginx (Host IP): ${NC} ${lightblue}http://${IP_ADDRESS}/${NC}"
    exit 0
fi

if [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Reconstruyendo e iniciando servicios...${NC}"
    UP_CMD="docker compose up -d --build"
else
    echo -e "${YELLOW} Iniciando servicios...${NC}"
fi

$UP_CMD $SERVICES
$STORAGE_LINK_CMD

if [ "$MIGRATE_FLAG" = true ]; then
    run_migrations
fi

echo -e "${GREEN} Servidor disponible en segundo plano.${NC}"
echo -e "${GREEN} Acceso por Nginx (Host IP): ${NC} ${lightblue}http://${IP_ADDRESS}/${NC}"