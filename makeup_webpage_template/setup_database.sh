#!/bin/bash
# Database Setup Script for BeautyVibe
# This script sets up the MySQL database for the shop

echo "========================================="
echo "BeautyVibe Database Setup"
echo "========================================="
echo ""

# Database configuration (from db.php)
DB_NAME="shop_db"
DB_USER="shop_user"
DB_PASS="ShopPass123!"

echo "This script will:"
echo "1. Create database: $DB_NAME"
echo "2. Create user: $DB_USER"
echo "3. Import schema from schema.sql"
echo ""
read -p "Do you want to continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Setup cancelled."
    exit 1
fi

echo ""
echo "Please enter your MySQL root password:"
read -s ROOT_PASS

echo ""
echo "Creating database..."
mysql -u root -p"$ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Error creating database. Please check your root password."
    exit 1
fi

echo "✅ Database created!"
echo ""

echo "Creating user..."
mysql -u root -p"$ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" 2>&1
mysql -u root -p"$ROOT_PASS" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" 2>&1
mysql -u root -p"$ROOT_PASS" -e "FLUSH PRIVILEGES;" 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Error creating user."
    exit 1
fi

echo "✅ User created!"
echo ""

echo "Importing schema..."
mysql -u root -p"$ROOT_PASS" $DB_NAME < schema.sql 2>&1

if [ $? -ne 0 ]; then
    echo "❌ Error importing schema."
    exit 1
fi

echo "✅ Schema imported!"
echo ""
echo "========================================="
echo "✅ Database setup complete!"
echo "========================================="
echo ""
echo "Database: $DB_NAME"
echo "User: $DB_USER"
echo "Password: $DB_PASS"
echo ""
echo "You can now use the application!"
