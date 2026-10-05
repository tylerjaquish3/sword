# Deploying a Laravel app to the Lightsail box

Written from the Pockets migration. Follow this for Sword and any later app on the same instance.

**Server facts**

| Thing | Value |
|---|---|
| Lightsail static IP | `44.231.46.56` |
| OS | Debian 12, SSH user `admin` |
| Web server | nginx (nginx.org package). Runs as `www-data`. Site configs go in `/etc/nginx/conf.d/` |
| PHP | 8.4 (php-fpm socket `/run/php/php8.4-fpm.sock`, owned by `www-data`) |
| App folders | `/var/www/<app>/` (Pockets is `/var/www/pockets`, this app is `/var/www/sword`) |
| Routing | One nginx server block per app, one domain or subdomain per app |
| Database | SQLite file inside each app at `database/database.sqlite` |

Sword needs PHP `^8.4`, Laravel 12 and SQLite, the same as Pockets. It also runs `npm ci && npm run build` (Vite), so Node must be on the server.

---

## Part A. One-time server setup (already done for Pockets, skip unless noted)

Already in place: PHP 8.4 with extensions, Composer, nginx, certbot, `/var/www` ownership, FPM socket owner `www-data`, ports 80/443/22 open in the Lightsail firewall.

**Sword needs one new thing, Node (for Vite):**
```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v && npm -v
```

If you ever rebuild the box, the PHP setup is: add the Sury repo (`packages.sury.org/php`), install `php8.4-{fpm,cli,sqlite3,mbstring,xml,curl,zip,bcmath,intl,gd}`, run `sudo update-alternatives --set php /usr/bin/php8.4`, install Composer from getcomposer.org into `/usr/local/bin`, and set `listen.owner = www-data` and `listen.group = www-data` in `/etc/php/8.4/fpm/pool.d/www.conf`.

---

## Part B. Per-app checklist (Sword)

Prod domain for Sword is `mysword.app`.

### 1. DNS
In Route53 (or wherever your DNS lives), add an **A record** for `mysword.app` pointing to `44.231.46.56`. If you also want `www`, add a CNAME `www` pointing to `mysword.app`. You can add this record now. The old site keeps working until you switch it, so use a temporary name for testing, or wait until step 8.

Check it from your Mac: `dig +short mysword.app`

### 2. App folder
```bash
sudo mkdir -p /var/www/sword
sudo chown -R admin:www-data /var/www/sword
sudo chmod g+s /var/www/sword
```

### 3. nginx server block
`/etc/nginx/conf.d/sword.conf`:
```nginx
server {
    listen 80;
    server_name mysword.app;
    root /var/www/sword/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```
```bash
sudo nginx -t && sudo systemctl reload nginx
```
`root` must point at the app's `public/` folder. Add `www.mysword.app` to `server_name` only if that DNS record exists.

### 4. `.env` and writable folders (done once, because deploys exclude these)
```bash
cd /var/www/sword
# create .env: copy from the old server's .env, or from .env.example, then edit it
nano .env
```
In `.env` set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://mysword.app`, and `DB_CONNECTION=sqlite` (leave `DB_DATABASE` unset so it uses `database/database.sqlite`). Reuse the old server's `APP_KEY` so existing sessions and encrypted data keep working.

Create the folders Laravel needs. The deploy skips `storage/`, so nothing creates them for you:
```bash
mkdir -p storage/app/public storage/framework/{cache/data,sessions,views,testing} storage/logs bootstrap/cache database
sudo chown -R admin:www-data storage bootstrap/cache database
sudo chmod -R 775 storage bootstrap/cache database
```

### 5. Deploy key for GitHub Actions
Make a key just for this repo, on the server:
```bash
ssh-keygen -t ed25519 -f ~/.ssh/sword_deploy -N "" -C "github-sword"
cat ~/.ssh/sword_deploy.pub >> ~/.ssh/authorized_keys
cat ~/.ssh/sword_deploy
```
In the GitHub repo (Settings > Secrets and variables > Actions) set:

| Secret | Value |
|---|---|
| `EC2_SSH_HOST` | `44.231.46.56` |
| `EC2_SSH_USER` | `admin` |
| `EC2_SSH_KEY` | the full private key output, from the BEGIN line through the END line. **Press Enter after the END line** so the key ends with a newline, or the deploy fails with `error in libcrypto` |

After saving the secret, delete the private key from the server: `shred -u ~/.ssh/sword_deploy`

### 6. Update `.github/workflows/main.yml`
The branch is `main`. The changes from the current file are the target path, more excludes, and `composer install` instead of `composer update` (so the lock file is respected):

```yaml
      - name: Deploy to Lightsail
        uses: easingthemes/ssh-deploy@v2.1.5
        env:
          SSH_PRIVATE_KEY: ${{ secrets.EC2_SSH_KEY }}
          SOURCE: ""
          REMOTE_HOST: ${{ secrets.EC2_SSH_HOST }}
          REMOTE_USER: ${{ secrets.EC2_SSH_USER }}
          TARGET: "/var/www/sword"
          EXCLUDE: ".env, .git, storage, node_modules, database/database.sqlite"
          ARGS: "-rlzvc --no-perms --no-owner --no-group"

      - name: Run post-deploy steps
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.EC2_SSH_HOST }}
          username: ${{ secrets.EC2_SSH_USER }}
          key: ${{ secrets.EC2_SSH_KEY }}
          script: |
            cd /var/www/sword
            npm ci
            npm run build
            composer install --no-dev --optimize-autoloader --no-interaction
            php artisan migrate --force
            php artisan route:clear
            php artisan view:clear
            php artisan config:clear
```
Excluding `storage` and the SQLite file stops a deploy from overwriting production data, and excluding `node_modules` and `.git` keeps the upload small.

### 7. Copy the database from the old EC2 server
The old app lives in `/home/tyler_admin/sword`. Its database is `/home/tyler_admin/sword/database/database.sqlite`. Confirm that with `grep DB_ /home/tyler_admin/sword/.env` (no `DB_DATABASE` means the default path above).

1. **On the Lightsail server**, make a throwaway key and authorize it. Do the same steps from the EC2 side in the next block:
   ```bash
   # on EC2
   ssh-keygen -t ed25519 -f ~/.ssh/to_lightsail -N ""
   cat ~/.ssh/to_lightsail.pub
   ```
   ```bash
   # on Lightsail: paste the line printed above
   echo "<ssh-ed25519 line>" >> ~/.ssh/authorized_keys
   ```
2. **On EC2**, copy and send the file:
   ```bash
   cp /home/tyler_admin/sword/database/database.sqlite /tmp/sword.sqlite
   chmod 644 /tmp/sword.sqlite
   scp -i ~/.ssh/to_lightsail /tmp/sword.sqlite admin@44.231.46.56:/tmp/sword.sqlite
   ```
   If EC2's public IP has changed, get the current one from the AWS console. Also check that your IP is allowed on the security group (it blocks SSH from other addresses).
3. **On Lightsail**, put it in place:
   ```bash
   sudo mv /tmp/sword.sqlite /var/www/sword/database/database.sqlite
   sudo chown admin:www-data /var/www/sword/database/database.sqlite
   sudo chmod 664 /var/www/sword/database/database.sqlite
   ```
4. **Clean up:** on EC2 run `shred -u ~/.ssh/to_lightsail; rm -f ~/.ssh/to_lightsail.pub /tmp/sword.sqlite`. On Lightsail, delete the extra key line from `~/.ssh/authorized_keys`.

This is a snapshot. Repeat the copy right before you switch DNS (step 8), so nothing entered in between is lost. To block writes during that copy, run `php artisan down` on the old server first.

### 8. First deploy and DNS cutover
1. Push to `main` and watch the run in the Actions tab. Re-run failed jobs after fixing the cause.
2. Test before touching DNS from your Mac:
   ```bash
   curl -I --resolve mysword.app:80:44.231.46.56 http://mysword.app
   ```
   Expect a `200` or `302`, not a 502 or 500.
3. Do the final database copy (step 7).
4. Point the A record for `mysword.app` at `44.231.46.56` (if it isn't already) and confirm with `dig +short mysword.app`.
5. HTTPS, on the server, **after** DNS resolves to this box:
   ```bash
   sudo certbot --nginx -d mysword.app
   ```
   Choose to redirect HTTP to HTTPS. Only list names that already resolve (`www` fails with `NXDOMAIN` if its record is missing). Don't retry a failing request over and over, since Let's Encrypt rate-limits failures. Then confirm `APP_URL=https://mysword.app` in `.env`, and run `php artisan config:clear`.

### 9. Retire the old copy
Keep the old app on EC2 for a few days without writes, then stop it. Once you're sure nothing is missing, remove the old Sword folder, release anything you no longer need, and delete the unused GitHub secrets.

---

## Troubleshooting (symptom to fix)

| Symptom | Cause and fix |
|---|---|
| Actions: `Load key ... error in libcrypto` | The `EC2_SSH_KEY` secret is missing the trailing newline or part of the key. Re-paste the key and press Enter after the END line |
| Actions: `Permission denied (publickey)` | Wrong user or key. The public half must be in `~admin/.ssh/authorized_keys`, and the user must be `admin` |
| Composer: `requires ext-dom ... missing` | `php -v` isn't 8.4, or extensions are missing. Run `sudo update-alternatives --set php /usr/bin/php8.4` and install `php8.4-xml` |
| `Please provide a valid cache path` | `storage/framework/views` doesn't exist. Run the `mkdir -p` block in step 4 |
| `npm: command not found` in Actions | Node isn't installed on the server (Part A) |
| 502 Bad Gateway | nginx can't reach PHP-FPM. Check `sudo tail /var/log/nginx/error.log`. `Permission denied` on the socket means `listen.owner` and `listen.group` in `/etc/php/8.4/fpm/pool.d/www.conf` don't match nginx's user (`grep '^user' /etc/nginx/nginx.conf`, which is `www-data`). Restart `php8.4-fpm` after changing them |
| 500 error | Check `/var/www/sword/storage/logs/laravel.log`. Common causes are a missing `APP_KEY`, or `storage/`, `bootstrap/cache` and `database/` not writable by `www-data` |
| Certbot: `NXDOMAIN` | The domain has no DNS record yet. Add it, wait, then retry |
| Wrong site appears | `server_name` doesn't match the domain you requested, so nginx falls back to the first server block. Check `grep server_name /etc/nginx/conf.d/*.conf` |
| Static IP changed after a restart | The instance wasn't using a Lightsail static IP. Attach one under Networking |
