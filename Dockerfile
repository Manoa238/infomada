# 1. Image de base : PHP avec Apache
FROM php:8.2-apache

# AJOUTEZ CES DEUX LIGNES AVANT l'installation de l'extension PHP
# Installation des dépendances nécessaires (libpq-dev) pour compiler l'extension PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# 2. Copier tous vos fichiers locaux dans le répertoire web par défaut d'Apache
COPY . /var/www/html/

# 3. Installer l'extension PHP (maintenant que les dépendances sont là)
RUN docker-php-ext-install pdo pdo_pgsql