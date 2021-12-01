# db-dropbox
Small php script to backup mysql databases and upload them (gz compressed) to dropbox

Works well as a cron job for a nightly offsite backup. Ofcourse you'd never use this on a larger project but for small apps/wordpress site's it works well where more enterprise-level database setup's are not justified.

## Installation & operation:
```
checkout the repo
composer install
cp schemas-example.yaml schemas.yaml
add your mysql database(s) and dropbox key
php index.php
```

## Optional: setup crontab (debian/ubuntu)
```
sudo crontab -e
add:
15 9 * * * cd <repo/path> && php <repo/path>/index.php >> <repo/path>/cron.log 2>&1
```
