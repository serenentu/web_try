# Install PHP on macOS - Quick Guide

## If you see "command not found: php"

PHP needs to be installed first. Here's how:

### Step 1: Check if Homebrew is installed

```bash
brew --version
```

If you see a version number, skip to Step 3.  
If you see "command not found: brew", continue to Step 2.

### Step 2: Install Homebrew (if not installed)

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Follow the on-screen instructions. This may take a few minutes.

### Step 3: Install PHP

```bash
brew install php
```

This will install PHP. Wait for it to complete.

### Step 4: Verify PHP is installed

```bash
php -v
```

You should see something like:
```
PHP 8.x.x (cli) ...
```

### Step 5: Now try the credential test again

```bash
cd ~/Desktop/project/IE4727/makeup_webpage_template
php test_db_connection.php
```

---

## Alternative: Quick Install (if Homebrew is already installed)

Just run:
```bash
brew install php
php -v
```

Then proceed with the credential test!
