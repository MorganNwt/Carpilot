# -------------------------------------------------------
# Déclaration des commandes "phony"
# Cela indique à make que ces noms sont des commandes
# et non des fichiers présents dans le projet
# -------------------------------------------------------
.PHONY: help start up build stop down restart ps logs logs-app logs-web logs-db \
        bash websh composer install update about cache-clear cc migrate db-create \
        fixtures validate jwt-check nginx-config mysql reset-db reset-app clean \
        orphans phpunit test

# -------------------------------------------------------
# Variables utilisées pour éviter de répéter les commandes
# -------------------------------------------------------

# Commande Docker Compose utilisée dans tout le projet
DC = docker compose -f docker-compose.yml

# Exécuter une commande dans le conteneur PHP (Symfony)
APP = $(DC) exec app

# Exécuter une commande dans le conteneur Nginx
WEB = $(DC) exec web

# Exécuter une commande dans le conteneur MySQL
DB = $(DC) exec db


# -------------------------------------------------------
# Commande help : affiche toutes les commandes disponibles
# -------------------------------------------------------
help:
	@echo ""
	@echo "Commandes disponibles pour CarPilot :"
	@echo "  make start        -> démarre les conteneurs"
	@echo "  make build        -> build + démarre les conteneurs"
	@echo "  make stop         -> arrête les conteneurs"
	@echo "  make restart      -> redémarre les conteneurs"
	@echo "  make ps           -> affiche les conteneurs"
	@echo "  make logs         -> logs de tous les services"
	@echo "  make logs-app     -> logs du conteneur app"
	@echo "  make logs-web     -> logs du conteneur web"
	@echo "  make logs-db      -> logs du conteneur db"
	@echo "  make bash         -> ouvre un bash dans app"
	@echo "  make websh        -> ouvre un shell dans web"
	@echo "  make composer     -> composer install"
	@echo "  make update       -> composer update"
	@echo "  make about        -> infos Symfony"
	@echo "  make cc           -> vide le cache Symfony"
	@echo "  make migrate      -> lance les migrations"
	@echo "  make db-create    -> crée la base si besoin"
	@echo "  make fixtures     -> charge les fixtures"
	@echo "  make validate     -> vérifie la config Symfony"
	@echo "  make jwt-check    -> vérifie la présence des clés JWT"
	@echo "  make nginx-config -> affiche la config nginx chargée"
	@echo "  make mysql        -> ouvre MySQL dans le conteneur db"
	@echo "  make reset-db     -> supprime et recrée la base"
	@echo "  make reset-app    -> reinstall + cache + migrations"
	@echo "  make test         -> lance les tests"
	@echo "  make clean        -> down -v"
	@echo "  make orphans      -> supprime les conteneurs orphelins"
	@echo ""


# -------------------------------------------------------
# Commandes Docker principales
# -------------------------------------------------------

# Démarre les conteneurs en arrière-plan
start:
	$(DC) up -d

# Alias de start
up:
	$(DC) up -d

# Build les images Docker puis démarre les conteneurs
build:
	$(DC) up -d --build

# Arrête et supprime les conteneurs
stop:
	$(DC) down

# Alias de stop
down:
	$(DC) down

# Redémarre complètement l’environnement Docker
restart:
	$(DC) down
	$(DC) up -d

# Affiche les conteneurs actifs du projet
ps:
	$(DC) ps


# -------------------------------------------------------
# Commandes de logs
# -------------------------------------------------------

# Affiche les logs de tous les services
logs:
	$(DC) logs -f

# Logs du conteneur PHP (Symfony)
logs-app:
	$(DC) logs -f app

# Logs du conteneur Nginx
logs-web:
	$(DC) logs -f web

# Logs du conteneur MySQL
logs-db:
	$(DC) logs -f db


# -------------------------------------------------------
# Accès aux conteneurs
# -------------------------------------------------------

# Ouvre un terminal bash dans le conteneur PHP
bash:
	$(APP) bash

# Ouvre un shell dans le conteneur Nginx
websh:
	$(WEB) sh


# -------------------------------------------------------
# Commandes Composer
# -------------------------------------------------------

# Installe les dépendances PHP
composer:
	$(APP) composer install

# Alias de composer install
install:
	$(APP) composer install

# Met à jour les dépendances PHP
update:
	$(APP) composer update


# -------------------------------------------------------
# Commandes Symfony
# -------------------------------------------------------

# Affiche les informations sur l’installation Symfony
about:
	$(APP) php bin/console about

# Vide le cache Symfony
cache-clear:
	$(APP) php bin/console cache:clear

# Alias pour vider le cache
cc:
	$(APP) php bin/console cache:clear

# Lance les migrations Doctrine
migrate:
	$(APP) php bin/console doctrine:migrations:migrate --no-interaction

# Crée la base de données si elle n’existe pas
db-create:
	$(APP) php bin/console doctrine:database:create --if-not-exists

# Charge les fixtures (données de test)
fixtures:
	$(APP) php bin/console doctrine:fixtures:load --no-interaction


# -------------------------------------------------------
# Vérifications de configuration
# -------------------------------------------------------

# Vérifie la validité des fichiers YAML, Twig et du schéma Doctrine
validate:
	$(APP) php bin/console lint:yaml config
	$(APP) php bin/console lint:twig templates
	$(APP) php bin/console doctrine:schema:validate

# Vérifie la présence des clés JWT
jwt-check:
	$(APP) ls -la config/jwt || true


# -------------------------------------------------------
# Commandes d’administration des conteneurs
# -------------------------------------------------------

# Affiche la configuration Nginx réellement chargée
nginx-config:
	$(WEB) nginx -T

# Ouvre une console MySQL dans le conteneur base de données
mysql:
	$(DB) mysql -u root -p


# -------------------------------------------------------
# Réinitialisation de la base de données
# -------------------------------------------------------

# Supprime puis recrée la base de données et relance les migrations
reset-db:
	$(APP) php bin/console doctrine:database:drop --force --if-exists
	$(APP) php bin/console doctrine:database:create --if-not-exists
	$(APP) php bin/console doctrine:migrations:migrate --no-interaction


# -------------------------------------------------------
# Réinitialisation complète de l’application
# -------------------------------------------------------

# Réinstalle les dépendances, vide le cache et applique les migrations
reset-app:
	$(APP) composer install
	$(APP) php bin/console cache:clear
	$(APP) php bin/console doctrine:migrations:migrate --no-interaction


# -------------------------------------------------------
# Tests
# -------------------------------------------------------

# Lance PHPUnit
phpunit:
	$(APP) php bin/phpunit

# Alias de phpunit
test:
	$(APP) php bin/phpunit


# -------------------------------------------------------
# Nettoyage Docker
# -------------------------------------------------------

# Supprime les conteneurs orphelins
orphans:
	$(DC) up -d --remove-orphans

# Arrête les conteneurs et supprime les volumes (base de données)
clean:
	$(DC) down -v