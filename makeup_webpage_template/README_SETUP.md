# BeautyVibe Shopping Cart - Setup Instructions

## Prerequisites

Before running the application, make sure you have:
1. **PHP** installed (version 7.4 or higher)
2. **MySQL/MariaDB** installed and running
3. **Web browser** (Chrome, Firefox, Safari, etc.)

### Check if PHP is installed:
```bash
php -v
```
If not installed, install it:
- **macOS**: `brew install php`
- **Windows**: Download from https://www.php.net/downloads
- **Linux**: `sudo apt-get install php` (Ubuntu/Debian)

### Check if MySQL is installed:
```bash
mysql --version
```
If not installed, install it:
- **macOS**: `brew install mysql`
- **Windows**: Download MySQL Installer
- **Linux**: `sudo apt-get install mysql-server`

## Step 1: Database Setup

### Option A: Using the Quick Setup Script (Recommended)

1. Open Terminal/Command Prompt and navigate to the project folder:
   ```bash
   cd /path/to/IE4727/makeup_webpage_template
   ```

2. Run the setup script:
   ```bash
   mysql -u root -p < quick_setup.sql
   ```
   - Enter your MySQL root password when prompted
   - This will create the database, user, and import all tables

### Option B: Manual Setup

1. Login to MySQL as root:
   ```bash
   mysql -u root -p
   ```

2. Run the commands from `quick_setup.sql` manually:
   - Create database: `CREATE DATABASE shop_db;`
   - Create user: `CREATE USER 'shop_user'@'localhost' IDENTIFIED BY 'ShopPass123!';`
   - Grant privileges: `GRANT ALL PRIVILEGES ON shop_db.* TO 'shop_user'@'localhost';`
   - Import schema: `USE shop_db; SOURCE schema.sql;`

3. Exit MySQL: `exit`

## Step 2: Verify Database Connection

The database credentials are set in `db.php`:
- **Host**: `127.0.0.1:3306`
- **Username**: `shop_user`
- **Password**: `ShopPass123!`
- **Database**: `shop_db`

If you need to change these, edit `db.php` with your credentials.

### How to Check if Credentials are Set Correctly

**Option 1: Using the Test Script (Easiest)**
```bash
php test_db_connection.php
```
Or visit: `http://localhost:8000/test_db_connection.php`

This will show you:
- ✅ If credentials are set in `db.php`
- ✅ If connection is successful
- ✅ Which tables exist
- ✅ Record counts in each table

**Option 2: Using MySQL Command Line**
```bash
mysql -u shop_user -pShopPass123! shop_db -e "SELECT DATABASE(), USER();"
```

**Option 3: Check db.php File Directly**
```bash
cat db.php | grep -E "(host|user|pass|db)"
```

**Expected Output:**
- If credentials are correct: You'll see connection successful message
- If credentials are wrong: You'll get an error message with troubleshooting tips

## Step 3: Start the PHP Server

1. Navigate to the project folder:
   ```bash
   cd /path/to/IE4727/makeup_webpage_template
   ```

2. Start the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

3. You should see:
   ```
   PHP 8.x.x Development Server (http://localhost:8000) started
   ```

## Step 4: Open in Browser

Open your web browser and go to:
```
http://localhost:8000/index.html
```

You should see the BeautyVibe products page!

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running: `mysql.server start` (macOS) or `sudo systemctl start mysql` (Linux)
- Check credentials in `db.php`
- Verify database exists: `mysql -u shop_user -pShopPass123! shop_db -e "SHOW TABLES;"`

### Port Already in Use
- If port 8000 is busy, use a different port:
  ```bash
  php -S localhost:8080
  ```
  Then open: `http://localhost:8080/index.html`

### Images Not Showing
- Make sure the `product pictures/` folder exists with all image files
- Check file names match exactly (case-sensitive on Linux/Mac)

### Cart Not Working
- Clear browser cookies/session
- Make sure JavaScript is enabled in your browser
- Check browser console for errors (F12)

## Features

✅ **Working Features:**
- Browse products with filtering and sorting
- Add items to cart (saved to database)
- View cart with aligned layout
- Update quantities and remove items
- Checkout and place orders
- Order history

## Quick Commands Reference

**Start server:**
```bash
php -S localhost:8000
```

**Check cart items in database:**
```bash
mysql -u shop_user -pShopPass123! shop_db -e "SELECT p.name, ci.quantity FROM cart_items ci JOIN products p ON p.id = ci.product_id;"
```

**Check orders:**
```bash
mysql -u shop_user -pShopPass123! shop_db -e "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5;"
```

**Clear all cart items (if needed):**
```bash
mysql -u shop_user -pShopPass123! shop_db -e "DELETE FROM cart_items;"
```

## Notes

- The server must be running for the application to work
- Cart items are stored per browser session
- Orders are saved to the `orders` table in the database
- Product images should be in the `product pictures/` folder

## Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Check PHP error logs
3. Check browser console for JavaScript errors
4. Verify database connection with the test commands above
