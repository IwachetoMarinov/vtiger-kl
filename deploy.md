# deploy.md

## Goal
- Provision a fresh VM
- Install all prerequisites
- Clone the project
- Configure environment variables
- Set up database
- Run and validate the app
- (Optional) Set up systemd + reverse proxy + SSL for production

---

## VM prerequisites
- Create a new VM (Ubuntu recommended)
- Ensure you have SSH access (key-based preferred)
- Ensure inbound rules allow:
  - SSH (22)
  - HTTP (80) / HTTPS (443) if you deploy behind a domain
  - Any app-specific port you need for direct access (only if required)

---

## OS update and base tools
- SSH into the VM
- Update packages
  - `sudo apt update && sudo apt -y upgrade`
- Install base utilities
  - `sudo apt -y install git curl wget unzip ca-certificates gnupg lsb-release build-essential`

---

## Create project user and folders
- Create a dedicated user (recommended)
  - `sudo adduser appuser`
  - `sudo usermod -aG sudo appuser`
- Switch to the user
  - `su - appuser`
- Create a working directory
  - `mkdir -p ~/apps`
  - `cd ~/apps`

---

## Install runtime dependencies (choose what your project needs)

### Node.js (if the project is Node/Next/React)
- Install Node via NodeSource (example for LTS)
  - `curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -`
  - `sudo apt -y install nodejs`
- Verify
  - `node -v`
  - `npm -v`

### Docker (if you run DB/services via docker)
- Install Docker
  - `curl -fsSL https://get.docker.com | sudo sh`
- Enable and start
  - `sudo systemctl enable docker`
  - `sudo systemctl start docker`
- Add your user to docker group
  - `sudo usermod -aG docker appuser`
- Re-login to apply group changes
- Verify
  - `docker --version`

### MySQL client (if needed for local dumps/restore)
- Install mysql client tools
  - `sudo apt -y install mysql-client`

---

## Clone the repository
- Clone the repo into the VM
  - `cd ~/apps`
  - `git clone <YOUR_REPO_URL> <PROJECT_DIR>`
- Enter the project folder
  - `cd <PROJECT_DIR>`

---

## Secrets and environment variables
- Create your environment file
  - `cp .env.example .env` (if you have one)
  - If you do not have `.env.example`, create `.env`
- Fill required variables (example placeholders)
  - `DB_HOST=`
  - `DB_PORT=`
  - `DB_NAME=`
  - `DB_USER=`
  - `DB_PASS=`
  - `APP_URL=`
  - Any third-party keys the project requires
- Ensure the `.env` file is not committed
  - confirm `.gitignore` includes `.env`

---

## Database setup (MySQL)

### If database runs on the same VM (native MySQL)
- Install MySQL server
  - `sudo apt -y install mysql-server`
- Secure MySQL (recommended)
  - `sudo mysql_secure_installation`
- Create DB and user
  - `sudo mysql -e "CREATE DATABASE vtiger_gpm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
  - `sudo mysql -e "CREATE USER 'vtiger'@'localhost' IDENTIFIED BY '<PASSWORD>';"`
  - `sudo mysql -e "GRANT ALL PRIVILEGES ON vtiger_gpm.* TO 'vtiger'@'localhost'; FLUSH PRIVILEGES;"`

### If database runs in Docker (example)
- Start MySQL container (adjust versions and passwords)
  - `docker run -d --name mysql-vtiger -p 3306:3306 -e MYSQL_ROOT_PASSWORD=<ROOT_PASS> -e MYSQL_DATABASE=vtiger_gpm mysql:8`
- Verify container is running
  - `docker ps`
- Create app user if needed
  - `docker exec -it mysql-vtiger mysql -uroot -p`
  - `CREATE USER 'vtiger'@'%' IDENTIFIED BY '<PASSWORD>';`
  - `GRANT ALL PRIVILEGES ON vtiger_gpm.* TO 'vtiger'@'%';`
  - `FLUSH PRIVILEGES;`

---

## Import schema-only dump (no INSERTs)
- Copy your schema dump file onto the VM (scp or download)
- If the dump is gzipped, unzip it
  - `gunzip <file>.sql.gz`
- Import schema into the DB
  - `mysql -h <DB_HOST> -P <DB_PORT> -u <DB_USER> -p <DB_NAME> < <file>.sql`
- Validate tables exist
  - `mysql -h <DB_HOST> -P <DB_PORT> -u <DB_USER> -p -e "USE <DB_NAME>; SHOW TABLES;"`

---

## Install project dependencies
- Install npm packages
  - `npm ci` (preferred for CI/lockfile)
  - If no lockfile, use `npm install`

---

## Build the project (production-like)
- Build
  - `npm run build`
- Verify build output is created successfully

---

## Run the project (development)
- Start dev server
  - `npm run dev`
- Confirm the port and access from browser
  - If VM is remote, you may need to bind host `0.0.0.0`
  - Example: `npm run dev -- --hostname 0.0.0.0 --port 3000`
- Validate main flows
  - App loads
  - API routes respond
  - DB connection works (if applicable)

---

## Run the project (production)
- Start production server
  - `npm run start`
- Confirm the port and that it stays up

---

## Process management with systemd (recommended for production)
- Create a systemd service file
  - `sudo nano /etc/systemd/system/<project>.service`
- Use a template like:
  - `WorkingDirectory=/home/appuser/apps/<PROJECT_DIR>`
  - `ExecStart=/usr/bin/npm run start`
  - `Restart=always`
  - `EnvironmentFile=/home/appuser/apps/<PROJECT_DIR>/.env`
- Reload systemd and start
  - `sudo systemctl daemon-reload`
  - `sudo systemctl enable <project>`
  - `sudo systemctl start <project>`
- Check logs
  - `sudo journalctl -u <project> -f`

---

## Reverse proxy (Nginx) + SSL (optional but typical)
- Install nginx
  - `sudo apt -y install nginx`
- Configure a site
  - create a config in `/etc/nginx/sites-available/<project>`
  - proxy to your app port (e.g. 3000)
- Enable config
  - `sudo ln -s /etc/nginx/sites-available/<project> /etc/nginx/sites-enabled/`
- Test and reload
  - `sudo nginx -t`
  - `sudo systemctl reload nginx`
- SSL with certbot (if domain is ready)
  - `sudo apt -y install certbot python3-certbot-nginx`
  - `sudo certbot --nginx -d <DOMAIN>`

---

## Backups (schema-only)
- Place the schema-only dump script in the repo (example: `scripts/schema_backup.sh`)
- Make it executable
  - `chmod +x scripts/schema_backup.sh`
- Run it
  - `./scripts/schema_backup.sh`
- Confirm output exists in `./db_backups`

---

## Deployment checklist
- Confirm `.env` exists and contains correct values
- Confirm DB connection works
- Confirm app builds successfully
- Confirm app starts successfully
- Confirm reverse proxy routes correctly (if used)
- Confirm SSL works (if used)
- Confirm logs are accessible
- Confirm backups are generated and stored safely

---

## Notes / Troubleshooting
- If a port is blocked, check firewall/security group rules
- If the app can’t bind, ensure it listens on `0.0.0.0` (not only `localhost`)
- If DB auth fails, confirm host permissions (`'user'@'%'` vs `'user'@'localhost'`)
- If permissions fail, verify ownership of `~/apps/<PROJECT_DIR>` belongs to `appuser`
