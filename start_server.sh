#!/bin/bash

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# --- Help Menu ---
show_help() {
    echo "----------------------------------------------------------------"
    echo "Usage: ./start_server.sh [FLAGS] [SERVICE]"
    echo ""
    echo "FLAGS:"
    echo "  (no flag)      Starts containers in detached mode."
    echo "  --help         Displays this help menu."
    echo "  --stop         Stops and removes containers."
    echo "  --build        Rebuilds images before starting."
    echo "  --clean        Stops and removes containers AND volumes (destroys databases)."
    echo "  --migrate      Runs Laravel database migrations."
    echo "  --init-api     Inicializa Laravel solo en servicios API (requiere --api)."
    echo ""
    echo "SPECIAL COMBINATIONS:"
    echo "  --clean --build  Full reset, rebuild, and automatic migrations."
    echo ""
    echo "SERVICES (optional, at the end):"
    echo "  --api          Apply command to backend only (and dependencies)."
    echo "  --front        Apply command to frontend only."
    echo "----------------------------------------------------------------"
}

# --- Copy .env to backend ---
copy_env() {
    if [ ! -f ".env" ]; then
        echo -e "${RED} Error: .env file not found at the project root.${NC}"
        echo "Please copy .env.example to .env and configure it first."
        exit 1
    fi
    cp .env backend/bait-api/.env
}

# --- Wait for DB to be ready ---
wait_for_db() {
    SERVICES=("mysql" "mysql_test")
    for service in "${SERVICES[@]}"; do
        echo -e "${YELLOW} Waiting for database '$service' to be ready...${NC}"
        for i in {1..30}; do
            if docker compose logs $service | grep -q "/usr/sbin/mysqld: ready for connections."; then
                echo -e "${GREEN} Database '$service' is ready.${NC}"
                return 0
            fi
            echo "Database '$service' not ready yet, retrying in 5 seconds..."
            sleep 5
        done
        echo -e "${RED}Error: Database '$service' not ready after 150 seconds.${NC}"
        echo "Check logs with: docker compose logs $service"
    done
}

# --- Run Laravel Migrations ---
run_migrations() {
    wait_for_db
    sleep 15
    echo -e "${YELLOW}Ejecutando migraciones y seeders...${NC}"
    OUTPUT=$(docker compose exec backend php artisan migrate --seed 2>&1) || true

    if echo "$OUTPUT" | grep -q "ERROR\|Exception"; then
        echo -e "${RED} Migration error:${NC}"
        echo -e "${RED}$OUTPUT${NC}"
        exit 1
    else
        echo -e "${GREEN} Migrations completed successfully.${NC}"
        echo "$OUTPUT"
    fi
}

# --- Initialize Laravel: key, jwt, migrate, seed ---
initialize_laravel_backend() {
    echo -e "${YELLOW} Initializing Laravel backend...${NC}"

    # Generate app key if missing
    docker compose exec backend grep -q "APP_KEY=" .env
    if [ $? -ne 0 ]; then
        echo -e "${YELLOW} Generating APP_KEY...${NC}"
        docker compose exec backend php artisan key:generate
    else
        echo -e "${GREEN} APP_KEY already exists. Skipping key:generate.${NC}"
    fi

    echo -e "${YELLOW} Generating JWT secret...${NC}"
    docker compose exec backend php artisan jwt:secret --force

    echo -e "${YELLOW} Running migrations...${NC}"
    docker compose exec backend php artisan migrate --force

    echo -e "${YELLOW} Running seeders...${NC}"
    docker compose exec backend php artisan db:seed --force

    echo -e "${GREEN} Laravel backend initialized successfully.${NC}"
}

# --- Flags ---
BUILD_FLAG=false
CLEAN_FLAG=false
MIGRATE_FLAG=false
STOP_FLAG=false
INIT_API=false
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
        --init-api)
            INIT_API=true
            shift
            ;;
        --api)
            SERVICES="backend mysql mysql_test phpmyadmin"
            shift
            ;;
        --front)
            SERVICES="frontend mysql mysql_test phpmyadmin"
            shift
            ;;
    esac
done

# --- ASCII Art ---
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

# --- Execution Logic ---
if [ "$STOP_FLAG" = true ]; then
    echo -e "${YELLOW} Stopping containers...${NC}"
    docker compose down
    echo -e "${GREEN} Containers stopped.${NC}"
    exit 0
fi

if [ "$CLEAN_FLAG" = true ] && [ "$BUILD_FLAG" = false ]; then
    echo -e "${YELLOW} Removing containers and volumes...${NC}"
    docker compose down -v
    echo -e "${GREEN} Environment cleaned.${NC}"
    exit 0
fi

copy_env
echo "Copied .env file to backend."

if [ "$CLEAN_FLAG" = true ] && [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Full clean: removing containers and volumes...${NC}"
    docker compose down -v
    echo -e "${YELLOW} Rebuilding and starting services...${NC}"
    docker compose up -d --build $SERVICES
    run_migrations
    exit 0
fi

UP_CMD="docker compose up -d"
if [ "$BUILD_FLAG" = true ]; then
    echo -e "${YELLOW} Rebuilding and starting services...${NC}"
    UP_CMD="docker compose up -d --build"
else
    echo -e "${YELLOW} Starting services...${NC}"
fi

$UP_CMD $SERVICES

if [ "$MIGRATE_FLAG" = true ]; then
    run_migrations
fi

# --- Init Laravel Backend if flag is set and service is API ---
if [ "$INIT_API" = true ] && [ "$SERVICES" = "backend mysql mysql_test phpmyadmin" ]; then
    initialize_laravel_backend
fi

echo -e "${GREEN} Server is running in background. Ports: Backend (8000), Front"
