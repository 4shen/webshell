FROM debian:9

ARG DEBIAN_FRONTEND=noninteractive

# install: php / mysql / postgres / sqlite / tools / mssql deps
RUN apt-get update && apt-get -y install \
php-cli php-xml php-mbstring \
mariadb-server mariadb-client php-mysql \
postgresql php-pgsql \
postgresql-9.6-postgis-2.3 \
sqlite3 php-sqlite3 \
git wget

# install run script
ADD run.sh /usr/sbin/docker-run
CMD docker-run
