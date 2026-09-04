FROM php:8.4-fpm

# System-Abhängigkeiten installieren
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    mariadb-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# GD-Erweiterung konfigurieren
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# PHP-Erweiterungen installieren
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# Redis via PECL installieren
RUN pecl install redis && docker-php-ext-enable redis

# Composer kopieren
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP_CodeSniffer & PHP Code Beautifier globally installieren
RUN curl -L https://phars.phpcodesniffer.com/phpcs.phar -o /usr/local/bin/phpcs \
    && chmod +x /usr/local/bin/phpcs \
    && curl -L https://phars.phpcodesniffer.com/phpcbf.phar -o /usr/local/bin/phpcbf \
    && chmod +x /usr/local/bin/phpcbf

# Node.js und NPM installieren (LTS Version 22)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Benutzer erstellen (passend zur Host-UID/GID um Rechte-Konflikte zu vermeiden)
ARG UID=1000
ARG GID=1000
ARG USER=laravel

RUN if getent group ${GID} >/dev/null; then \
        EXISTING_GROUP=$(getent group ${GID} | cut -d: -f1); \
        useradd -u ${UID} -g "$EXISTING_GROUP" -m -s /bin/bash ${USER}; \
    else \
        groupadd -g ${GID} ${USER} && \
        useradd -u ${UID} -g ${USER} -m -s /bin/bash ${USER}; \
    fi

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

USER ${USER}
