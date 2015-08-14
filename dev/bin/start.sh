#! /usr/bin/env bash

# start services
service mysql start

service apache2 start

# initialize

wp --allow-root option update siteurl $BASE_URL
wp --allow-root option update home $BASE_URL

curl -c cookiejar --data "log=admin&pwd=admin" -L $BASE_URL"wp-login.php"
curl -b cookiejar -L $BASE_URL"wp-admin/admin.php?page=wc-settings&install_woocommerce_pages=true"

mysql -u root -pP4ssw0rd wordpress -e "INSERT INTO wordpress.wp_options (option_id, option_name, option_value, autoload) VALUES (NULL, 'algolia 1.0', '`algolia-config-generator $APPLICATION_ID $SEARCH_ONLY_API_KEY $API_KEY $INDEX_PREFIX`', 'yes');"

curl -b cookiejar --data "action=reindex&subaction=handle_index_creation" -L $BASE_URL"wp-admin/admin-post.php"
curl -b cookiejar --data "action=reindex&subaction=type__product__0" -L $BASE_URL"wp-admin/admin-post.php"
curl -b cookiejar --data "action=reindex&subaction=type__post__0" -L $BASE_URL"wp-admin/admin-post.php"
curl -b cookiejar --data "action=reindex&subaction=type__page__0" -L $BASE_URL"wp-admin/admin-post.php"
curl -b cookiejar --data "action=reindex&subaction=index_taxonomies" -L $BASE_URL"wp-admin/admin-post.php"
curl -b cookiejar --data "action=reindex&subaction=move_indexes" -L $BASE_URL"wp-admin/admin-post.php"

service apache2 stop

exec /usr/sbin/apache2ctl -D FOREGROUND
#exec /bin/bash