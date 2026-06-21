[Русский](README.md)

# ChatAdditions Gag List

Web panel for viewing and managing **gag** (chat mute) punishments from the [ChatAdditions_AMXX](https://github.com/ChatAdditions/ChatAdditions_AMXX) plugin for Counter-Strike 1.6 servers.

## Features

- View all gag punishments sorted by date (newest first)
- Punishment status: **active** / **expired** / **permanent**
- Human-readable duration format (`7 days (19.06.2026 09:54 — 26.06.2026 09:54)`)
- Filtering: all / active only
- Search by nickname, Steam ID, or IP (including Cyrillic)
- Edit and delete punishments (authentication required)
- Gag flag management (text chat, team chat, voice chat) via checkboxes
- Dark and light theme with auto-save
- Responsive design for mobile devices
- CSRF protection on forms

## Requirements

- PHP 7.4+ with `mysqli` and `mbstring` extensions
- MySQL/MariaDB (same server used by the ChatAdditions plugin)
- Nginx or any other web server with PHP support

## Installation

### 1. Clone the repository

```bash
cd /var/www/html
git clone https://github.com/Nord1cWarr1or/ChatAdditions_Gaglist.git gaglist
```

### 2. Configure database connection

Edit `config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');        // MySQL user
define('DB_PASS', 'password');    // MySQL password
define('DB_NAME', 'db_name');     // database name from the plugin (default: players_gags)

define('ADMIN_LOGIN', 'admin');   // panel login
define('ADMIN_PASSWORD', 'changeme'); // panel password
define('GAGS_TABLE', 'chatadditions_gags'); // table name (do not change unless necessary)
```

### 3. Configure Nginx

Add to your nginx config (e.g., `/etc/nginx/sites-available/default`):

```nginx
location /gaglist/ {
    root /var/www/html;
    index index.php;
    try_files $uri $uri/ /gaglist/index.php?$args;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 4. Set file permissions

```bash
sudo chown -R www-data:www-data /var/www/html/gaglist/
sudo chmod -R 755 /var/www/html/gaglist/
```

### 5. Reload nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Open the panel

Navigate to `http://your-domain/gaglist/`

## File Structure

```
├── index.php      # Main page — gag list (no auth required)
├── login.php      # Login page
├── logout.php     # Logout
├── edit.php       # Edit gag (auth required)
├── delete.php     # Delete gag (auth required, POST)
├── config.php     # DB settings, auth, helper functions
└── style.css      # Styles (light and dark theme)
```

## Database Structure

The panel uses the `chatadditions_gags` table from the ChatAdditions plugin:

| Column | Type | Description |
|--------|------|-------------|
| `id` | INTEGER PK | Record ID |
| `name` | VARCHAR(32) | Player nickname |
| `authid` | VARCHAR(64) | Steam ID |
| `ip` | VARCHAR(22) | IP address |
| `reason` | VARCHAR(256) | Punishment reason |
| `admin_name` | VARCHAR(32) | Admin nickname |
| `admin_authid` | VARCHAR(64) | Admin Steam ID |
| `admin_ip` | VARCHAR(22) | Admin IP address |
| `created_at` | DATETIME | Gag creation date |
| `expire_at` | DATETIME | Gag expiration date |
| `flags` | INTEGER | Bitwise flag sum |

### Flags

| Bit | Value | Description |
|-----|-------|-------------|
| a | 1 | Text chat |
| b | 2 | Team text chat |
| c | 4 | Voice chat |

Example: `flags = 5` → text chat (1) and voice chat (4) are disabled.

## Technologies

- **Backend**: PHP 7.4+ (vanilla PHP, no frameworks)
- **Database**: MySQL/MariaDB (prepared statements)
- **Frontend**: HTML + CSS + Vanilla JavaScript

## Security

- Prepared statements to prevent SQL injection
- `htmlspecialchars()` to prevent XSS
- CSRF tokens on edit and delete forms
- Session-based authentication

## License

[GPL-3.0](LICENSE.txt)

---

> Web panel created with the help of **MiMo-2.5** AI model by Xiaomi.
