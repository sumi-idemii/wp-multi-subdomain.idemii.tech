#!/bin/bash

# WordPress Multisite Setup Script
# This script helps set up WordPress multisite with database configuration

set -e

echo "=========================================="
echo "WordPress Multisite Setup Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating .env file from template...${NC}"
    if [ -f env.example ]; then
        cp env.example .env
        echo -e "${GREEN}.env file created. Please edit it with your database credentials.${NC}"
    else
        echo -e "${RED}Error: env.example file not found!${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}.env file already exists.${NC}"
fi

# Check if database setup SQL file exists
if [ -f database-setup.sql ]; then
    echo ""
    echo -e "${YELLOW}Database setup SQL file found.${NC}"
    echo "To set up the database, run:"
    echo "  mysql -u root -p < database-setup.sql"
    echo ""
    echo "Or edit database-setup.sql with your preferred database name and user, then run:"
    echo "  mysql -u root -p < database-setup.sql"
else
    echo -e "${YELLOW}Note: database-setup.sql not found.${NC}"
fi

# Check if wp-config.php exists
if [ ! -f wp-config.php ]; then
    echo -e "${RED}Error: wp-config.php not found!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}Setup script completed!${NC}"
echo ""
echo "Next steps:"
echo "1. Edit .env file with your database credentials"
echo "2. Run database-setup.sql to create database and user"
echo "3. Start your web server (PHP built-in server, Apache, Nginx, etc.)"
echo "4. Open your browser and navigate to your WordPress installation"
echo "5. Complete the WordPress installation wizard"
echo "6. After installation, go to Tools > Network Setup to enable multisite"
echo ""

