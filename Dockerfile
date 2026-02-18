# 1. Image de base : PHP avec Apache
FROM php:8.2-apache

# 2. Copier tous vos fichiers locaux dans le répertoire web par défaut d'Apache
# (Adaptez /var/www/html/ si vous utilisez un autre dossier dans votre Dockerfile)
COPY . /var/www/html/

# 3. (Facultatif mais recommandé pour la connexion DB)
# Si votre code PHP utilise des extensions PostgreSQL, vous devez les installer ici.
# Si vous n'avez pas de dépendances, vous pouvez ignorer cette partie.
RUN docker-php-ext-install pdo pdo_pgsql