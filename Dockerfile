# ===================================================================
# Mầm Xanh LMS 2.0 - Production Turbo Dockerfile (PHP 8.3 + Moodle 4.5 LTS)
# ===================================================================
FROM php:8.3-apache

LABEL maintainer="Mam Xanh AgTech Architecture Team"
LABEL project="Mam Xanh LMS v2.0 - High-Tech Agriculture Training System"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Asia/Ho_Chi_Minh

# 1. Install OS packages, Git, MariaDB client, Locales, and PHP extension dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        locales \
        mariadb-client \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libicu-dev \
        libzip-dev \
        libxml2-dev \
        libsodium-dev \
    && sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen \
    && sed -i '/vi_VN.UTF-8/s/^# //g' /etc/locale.gen \
    && locale-gen \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        zip \
        soap \
        mysqli \
        pdo_mysql \
        opcache \
        exif \
    && (php -m | grep -qi '^sodium$' || docker-php-ext-install -j"$(nproc)" sodium) \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers expires deflate remoteip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

# 2. Download Moodle 4.5 LTS directly onto native Linux filesystem (Zero Windows NTFS I/O bottleneck)
RUN rm -rf /var/www/html/* \
    && curl -fsSL https://github.com/moodle/moodle/archive/refs/heads/MOODLE_405_STABLE.tar.gz \
       | tar -xz --strip-components=1 -C /var/www/html \
    && mkdir -p /opt/moodle-lang /var/moodledata \
    && (curl -fsSL -o /opt/moodle-lang/vi.zip https://download.moodle.org/download.php/direct/langpack/4.5/vi.zip || true) \
    && chown -R www-data:www-data /var/www/html /var/moodledata

# 3. Copy Turbo PHP OPcache/JIT & Apache configurations
COPY docker/php/zz-performance.ini /usr/local/etc/php/conf.d/zz-performance.ini
COPY docker/apache/moodle.conf /etc/apache2/conf-available/moodle.conf
COPY docker/entrypoint.sh /usr/local/bin/moodle-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/moodle-entrypoint.sh \
    && chmod +x /usr/local/bin/moodle-entrypoint.sh \
    && a2enconf moodle

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/moodle-entrypoint.sh"]
CMD ["apache2-foreground"]
