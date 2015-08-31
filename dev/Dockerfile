FROM ubuntu:14.04

RUN apt-get update
RUN apt-get -y install apache2
RUN apt-get -y install php5 libapache2-mod-php5 php5-mcrypt php5-curl php5-gd php5-mysql

RUN sed -i -e 's/\/var\/www/\/var\/www\/htdocs/' /etc/apache2/apache2.conf
RUN sed -i -e 's/\/var\/www\/html/\/var\/www\/htdocs/' /etc/apache2/sites-available/000-default.conf

##### PHP/MYSQL

RUN echo 'mysql-server mysql-server/root_password password P4ssw0rd' | debconf-set-selections
RUN echo 'mysql-server mysql-server/root_password_again password P4ssw0rd' | debconf-set-selections

WORKDIR /var/www/htdocs

# packages/dependencies installation
RUN apt-get install -y mysql-client-5.5 mysql-server

COPY bin/php.ini /usr/local/etc/php/php.ini

RUN apt-get install -y libxml2-dev
RUN apt-get update && apt-get install -y libpng12-dev libjpeg-dev

RUN apt-get update && apt-get install -y vim emacs24-nox git-core wget curl zsh zip
RUN chsh -s /bin/zsh

RUN sh -c "$(curl -fsSL https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh)" || true

COPY bin/.zshrc /root/.zshrc

RUN rm -rf /var/www/html

##### WORDPRESS

RUN cd /tmp;\
	wget https://wordpress.org/wordpress-4.2.2.tar.gz; tar -xvzf wordpress-4.2.2.tar.gz; mv wordpress/* /var/www/htdocs/; \
	rm wordpress-4.2.2.tar.gz

RUN service mysql start && mysql -u root -pP4ssw0rd -e 'CREATE DATABASE wordpress' && service mysql stop;

COPY bin/wp-config.php /var/www/htdocs/wp-config.php

RUN chown -R www-data:www-data /var/www/htdocs

RUN cd /tmp; curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar; mv wp-cli.phar /usr/bin/wp; chmod +x /usr/bin/wp

RUN service mysql start && \
	wp core install --path=/var/www/htdocs --allow-root --url="http://mywordpress.com/" --title="Wordpress Algolia Demo" --admin_user="admin" --admin_password="admin" --admin_email="test@test.com" && \
	service mysql stop


##### WOOCOMMERCE

RUN cd /tmp; curl -O https://downloads.wordpress.org/plugin/woocommerce.zip; unzip woocommerce.zip; mv woocommerce/ /var/www/htdocs/wp-content/plugins/
RUN service mysql start && wp --allow-root plugin activate woocommerce && service mysql stop
RUN cd /tmp; curl -O http://demos.algolia.com/downloads/mystile-wordpress-theme.zip; unzip mystile-wordpress-theme.zip; mv mystile/ /var/www/htdocs/wp-content/themes/
RUN service mysql start && wp --allow-root theme activate mystile && service mysql stop

RUN cd /tmp; curl -O https://downloads.wordpress.org/plugin/wordpress-importer.0.6.1.zip; unzip wordpress-importer.0.6.1.zip; mv wordpress-importer/ /var/www/htdocs/wp-content/plugins/
RUN service mysql start && wp --allow-root plugin activate wordpress-importer && service mysql stop

RUN service mysql start && wp --allow-root import /var/www/htdocs/wp-content/plugins/woocommerce/dummy-data/dummy-data.xml --authors=create && service mysql stop

RUN service mysql start && wp --allow-root media --yes regenerate && service mysql stop

##### PHPMYADMIN

RUN cd /tmp && curl -O http://demos.algolia.com/downloads/phpMyAdmin-4.4.9-english.tar.gz && tar xvf phpMyAdmin-4.4.9-english.tar.gz > /dev/null && mv phpMyAdmin-4.4.9-english /var/www/htdocs/phpmyadmin
COPY bin/config.inc.php /var/www/htdocs/phpmyadmin/



##### PATCHING

RUN sed -i 's/esc_attr( \$user_login )/"admin"/g' /var/www/htdocs/wp-login.php && \
	sed -i 's/id="user_pass"<?php echo \$aria_describedby_error; ?> class="input" value=""/<?php echo $aria_describedby_error; ?> class="input" value="admin"/g' /var/www/htdocs/wp-login.php && \
	sed -i 's/<form name="loginform"/<script>document.addEventListener("DOMContentLoaded",function(event){document.forms["loginform"].submit();});<\/script><form name="loginform"/g' /var/www/htdocs/wp-login.php && \
	sed -i 's/error_reporting/\/\/error_reporting/g' /var/www/htdocs/wp-includes/load.php && \
	sed -i 's/mysql_real_escape_string(/mysqli_real_escape_string(mysqli_connect("localhost", "root", "P4ssw0rd", "wordpress"),/g' /var/www/htdocs/wp-content/themes/mystile/functions/admin-interface.php && \
	sed -i 's/\$_SERVER\['\''HTTPS'\''] =/\/\/$_SERVER['\''HTTPS'\''] =/g' /var/www/htdocs/wp-content/plugins/woocommerce/woocommerce.php

###### Algolia Extension

RUN cd /var/www/htdocs/wp-content/plugins/ && git clone https://github.com/algolia/algoliasearch-wordpress.git
RUN service mysql start && wp --allow-root plugin activate algoliasearch-wordpress && service mysql stop

COPY bin/algolia-config-generator.php /usr/local/bin/algolia-config-generator
RUN chmod +x /usr/local/bin/algolia-config-generator



##### MAKE IT RUN

# start script
COPY ./bin/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD start.sh


