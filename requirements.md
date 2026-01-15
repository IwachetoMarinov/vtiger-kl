# vTiger CRM v8.40 – Server Requirements

This document describes the recommended server requirements for installing and running **vTiger CRM v8.40** in a production environment.

---

## 1. Operating System

vTiger CRM can run on most modern Linux distributions.

**Recommended:**
- Ubuntu Server 20.04 / 22.04 LTS
- CentOS 7 / AlmaLinux 8 / Rocky Linux 8

---

## 2. Web Server

One of the following web servers is required:

- Apache HTTP Server 2.4+
  - Required modules:
    - mod_rewrite
    - mod_ssl
    - mod_headers
- OR Nginx 1.18+ (with PHP-FPM)

---

## 3. PHP Requirements

**Supported PHP Versions:**
- PHP 8.0 or 8.1 (recommended)

**Required PHP Extensions:**
- mysqli
- pdo
- pdo_mysql
- curl
- gd
- imap
- mbstring
- openssl
- zip
- soap
- xml
- intl
- fileinfo

**Recommended PHP Settings:**
```ini
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 50M
upload_max_filesize = 50M
display_errors = Off
log_errors = On
```

---

## 4. Database Server

**Supported Databases:**
- MySQL 5.7 / 8.0
- MariaDB 10.3+

**Recommended Settings:**
- InnoDB storage engine
- UTF-8 (utf8mb4) character set

---

## 5. Hardware Requirements

### Minimum (Testing / Small Teams)
- CPU: 2 vCPU
- RAM: 4 GB
- Storage: 40 GB SSD

### Recommended (Production)
- CPU: 4+ vCPU
- RAM: 8–16 GB
- Storage: 100 GB SSD or higher

---

## 6. File System & Permissions

- Web server user must have read/write permissions on:
  - `storage/`
  - `cache/`
  - `test/`
  - `config.inc.php`

- Recommended ownership:
```bash
chown -R www-data:www-data vtigercrm/
```

---

## 7. Mail Server (Optional but Recommended)

For email features:
- SMTP server (Gmail, Office365, or custom SMTP)
- IMAP support for mailbox integration

---

## 8. SSL & Security

- SSL certificate (Let’s Encrypt or commercial)
- HTTPS enabled (mandatory for production)
- Firewall allowing ports:
  - 80 (HTTP)
  - 443 (HTTPS)

---

## 9. Browser Support (Client Side)

- Google Chrome (latest)
- Mozilla Firefox (latest)
- Microsoft Edge (Chromium-based)

---

## 10. Optional Tools

- Cron service enabled (for scheduled workflows) - Mandatory
- Redis / Memcached (for performance optimization) - Optional
- Backup solution for database and application files  - Mandatory

---

## 11. Notes

- Ensure time zone and locale are correctly configured on the server
- Keep PHP and database versions consistent across environments
- Regularly apply security updates and patches

---

**Document Version:** 1.0  
**Applies To:** vTiger CRM v8.40

