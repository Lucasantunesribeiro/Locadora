FROM php:8.1-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar módulos necessários do Apache
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires

# Configurar document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar código da aplicação
COPY . /var/www/html/

# Criar diretório do banco e definir permissões
RUN mkdir -p /var/www/html/database && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 777 /var/www/html/database

# Inicializar banco de dados
RUN cd /var/www/html && php database/init.php

# Configurar PHP para produção
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configurar variáveis de ambiente para produção
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 80

CMD ["apache2-foreground"]