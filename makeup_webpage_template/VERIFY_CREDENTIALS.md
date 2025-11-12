# How to Verify Database Credentials - Step by Step

Follow these exact steps to check if your database credentials are set correctly.

## Method 1: Using the Test Script (Recommended - Easiest)

### Step 1: Open Terminal
- **macOS**: Press `Cmd + Space`, type "Terminal", press Enter
- **Windows**: Open Command Prompt or Git Bash
- **Linux**: Press `Ctrl + Alt + T`

### Step 2: Navigate to the Project Folder
Replace `/path/to/project` with your actual project path:

```bash
cd /path/to/IE4727/makeup_webpage_template
```

**Example (if project is on Desktop):**
```bash
cd ~/Desktop/project/IE4727/makeup_webpage_template
```

**Or if you're already in the project folder, just verify you're in the right place:**
```bash
ls
```
You should see files like: `db.php`, `index.html`, `cart.php`, etc.

### Step 3: Run the Test Script
```bash
php test_db_connection.php
```

### Step 4: Check the Output

**✅ If credentials are correct, you'll see:**
```
========================================
Database Connection Test
========================================

✅ db.php file found

Credentials from db.php:
  Host: 127.0.0.1:3306
  Username: shop_user
  Password: **********
  Database: shop_db

Testing connection...
✅ CONNECTION SUCCESSFUL!

Connected to:
  Database: shop_db
  User: shop_user@localhost

Available tables (5):
  - cart_items
  - customers
  - order_items
  - orders
  - products

✅ Products table has 11 products
✅ Orders table has X orders
✅ Cart items table has X items

========================================
✅ All tests passed! Database is ready.
========================================
```

**❌ If credentials are wrong, you'll see:**
```
❌ CONNECTION FAILED!

Error: [error message]

Troubleshooting:
1. Check if MySQL is running
2. Verify the credentials in db.php match your database
3. Make sure the database and user exist
```

---

## Method 2: Test via Web Browser

### Step 1: Make sure PHP server is running
```bash
cd /path/to/IE4727/makeup_webpage_template
php -S localhost:8000
```

### Step 2: Open browser
Go to: `http://localhost:8000/test_db_connection.php`

You'll see the same test results in your browser.

---

## Method 3: Quick MySQL Test (Alternative)

If you want to test the connection directly with MySQL:

```bash
mysql -u shop_user -pShopPass123! shop_db -e "SELECT 'Connection OK!' as status, DATABASE() as database_name, USER() as user_name;"
```

**Expected output:**
```
status          database_name  user_name
Connection OK!  shop_db        shop_user@localhost
```

---

## Method 4: View Credentials in File

To see what credentials are actually set in `db.php`:

```bash
cat db.php | grep -E "(host|user|pass|db)"
```

**Expected output:**
```
$host = '127.0.0.1:3306';
$user = 'shop_user';
$pass = 'ShopPass123!';
$db = 'shop_db';
```

---

## Troubleshooting

### If you get "php: command not found"
**macOS:**
```bash
brew install php
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install php
```

**Windows:** Download from https://www.php.net/downloads

### If you get "mysql: command not found"
**macOS:**
```bash
brew install mysql
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install mysql-client
```

### If connection fails:
1. **Check if MySQL is running:**
   ```bash
   # macOS
   brew services list
   # or
   mysql.server status
   
   # Linux
   sudo systemctl status mysql
   ```

2. **Start MySQL if not running:**
   ```bash
   # macOS
   brew services start mysql
   
   # Linux
   sudo systemctl start mysql
   ```

3. **Verify database and user exist:**
   ```bash
   mysql -u root -p -e "SHOW DATABASES LIKE 'shop_db';"
   mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User='shop_user';"
   ```

---

## Quick Copy-Paste Commands

**For quick verification, copy and paste these commands one by one:**

```bash
# 1. Navigate to project (adjust path as needed)
cd ~/Desktop/project/IE4727/makeup_webpage_template

# 2. Check if you're in the right folder
ls db.php test_db_connection.php

# 3. Run the test
php test_db_connection.php

# That's it! Check the output.
```

---

## What Your Friend Should See

If everything is set up correctly, the test script will show:
- ✅ Credentials are found in `db.php`
- ✅ Connection to database is successful
- ✅ All tables exist (products, orders, cart_items, etc.)
- ✅ Record counts for each table

This confirms that the database is properly configured and ready to use!
