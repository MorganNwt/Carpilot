# API CARPILOT

Bienvenue sur la documentation de l'API de notre plateforme de vente de véhicules CarPilot. Cette API RESTful, développée avec **Symfony 7.2**, est le cœur de notre service, permettant aux particuliers de vendre leurs véhicules directement à la marque.

## Table des matières

1.  [Prérequis](#prérequis)
2.  [Installation locale](#installation-locale)
3.  [Concepts Clés](#concepts-clés)
    -   [Architecture en Couches](#architecture-en-couches)
    -   [Authentification avec JWT](#authentification-avec-jwt-json-web-token)
4.  [Guide d'utilisation avec Postman](#guide-dutilisation-avec-postman)
    -   [CRUD d'un Compte Vendeur](#crud-dun-compte-vendeur)
    -   [CRUD d'un Véhicule](#crud-dun-véhicule)
    -   [Réinitialisation de Mot de Passe](#réinitialisation-de-mot-de-passe)
    -   [Gestion Admin](#gestion-admin)
5.  [Documentation des Endpoints](#documentation-des-endpoints)

---

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants sur votre machine :

-   **PHP 8.2** ou supérieur
-   **Composer** (gestionnaire de dépendances pour PHP)
-   **Symfony CLI**
-   Un serveur de base de données local (ex: MySQL, MariaDB)
-   **Git**

---

## Installation locale

Suivez ces étapes pour configurer le projet en local.

1.  **Clonez le dépôt Git**
    ```bash
    git clone http://github.com/jenam15/carpilot-api.git
    cd carpilot-api
    ```
2.  **Installez les dépendances PHP**
    ```bash
    composer install
    ```
3.  **Configurez les variables d'environnement**

    -   Copiez le fichier `.env` en `.env.local`. Ce dernier ne sera pas suivi par Git et contiendra vos configurations personnelles.
        ```bash
        cp .env .env.local
        ```
    -   Ouvrez le fichier `.env.local` et modifiez la variable `DATABASE_URL` pour qu'elle corresponde aux accès de votre base de données locale. Par exemple :
        ```env
        # Format: mysql://user:password@127.0.0.1:3306/database_name
        DATABASE_URL="mysql://root:password@127.0.0.1:3306/votre_db?serverVersion=8.0&charset=utf8mb4"
        ```

4.  **Créez la base de données et appliquez les migrations**

    -   Assurez-vous que votre serveur de base de données local est bien démarré.
        ```bash
        symfony console doctrine:database:create
        symfony console doctrine:migrations:migrate
        ```

5.  **Chargez des données de test**

    -   Une fixture d'utilisateur admin est fournie. Vous pouvez également ajouter des données de test pour les vendeurs et les véhicules.
    -   Pour charger les fixtures, exécutez la commande suivante :
        ```bash
        symfony console doctrine:fixtures:load
        ```

6.  **Générez les clés pour le JWT**

    -   Ces clés sont nécessaires pour signer les tokens d'authentification.
        ```bash
        php bin/console lexik:jwt:generate-keypair
        ```

7.  **Lancez le serveur local**
    `bash
    symfony server:start
    ```
    Votre API est maintenant accessible, généralement à l'adresse `http://127.0.0.1:8000`.
    ```

---

## Concepts Clés

### Architecture en Couches

Notre API est bâtie sur une architecture en couches pour garantir sa maintenabilité et son évolutivité.

-   **Contrôleur (Controller)** : C'est la porte d'entrée. Il reçoit les requêtes HTTP et délègue la logique métier aux services.
-   **Service** : C'est le cerveau de l'application. Il contient toute la **logique métier** et interagit avec la base de données.
-   **DTO (Data Transfer Object)** : Un objet simple qui **transporte des données** entre les couches pour valider les requêtes et maîtriser les données exposées.
-   **Mapper** : Un concept appliqué dans les **services** pour transformer les entités Doctrine en DTOs et vice-versa.

### Authentification avec JWT (JSON Web Token)

L'authentification de l'API repose sur les **JSON Web Tokens**. C'est une méthode d'authentification **stateless** (sans état), ce qui signifie que le serveur n'a pas besoin de stocker d'informations sur la session de l'utilisateur. Tout est contenu dans le token lui-même.

La signature du token se fait via une **paire de clés privée/publique** (RS256) stockée dans `/config/jwt`.

Le flux est le suivant : l'utilisateur s'identifie sur `/api/login_check`, reçoit un token, et doit présenter ce token dans l'en-tête `Authorization: Bearer <token>` pour toutes les requêtes sécurisées. Le serveur vérifie la validité du token avec sa clé publique.

---

## Guide d'utilisation avec Postman

Ce guide explique comment appeler chaque endpoint de l'API avec [Postman](http://www.postman.com/).

### CRUD d'un Compte Vendeur

#### 1.1. Créer le compte vendeur (Create)

Cette première étape est publique.

-   **Méthode** : `POST`
-   **URL** : `http://127.0.0.1:8000/api/sellers`
-   **Body** (`raw` / `JSON`):
    ```json
    {
        "firstName": "Jean",
        "lastName": "Dupont",
        "email": "jean.dupont@email.com",
        "password": "password123",
        "address": "10 Rue de la Paix",
        "city": "Paris",
        "postalCode": "75002",
        "country": "France",
        "phone": "0612345679"
    }
    ```
-   **Résultat attendu** : Un code `201 Created` avec les informations du profil créé.

#### 1.2. Se connecter et obtenir le Token

-   **Méthode** : `POST`
-   **URL** : `http://127.0.0.1:8000/api/login_check`
-   **Body** (`raw` / `JSON`) : Utilisez les identifiants que vous venez de créer.
    ```json
    {
        "email": "jean.dupont@email.com",
        "password": "password123"
    }
    ```
-   **Action** : Envoyez la requête. La réponse contiendra un `token`. Copiez cette valeur pour les étapes suivantes.

#### 1.3. Lire le profil (Read)

-   **Méthode** : `GET`
-   **URL** : `http://127.0.0.1:8000/api/sellers/profile`
-   **Authorization** : Dans l'onglet, sélectionnez `Bearer Token` et collez votre token.
-   **Résultat attendu** : Un code `200 OK` avec les détails du profil du vendeur connecté.

#### 1.4. Mettre à jour le profil (Update)

-   **Méthode** : `PUT`
-   **URL** : `http://127.0.0.1:8000/api/sellers/profile`
-   **Authorization** : Collez votre token.
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "phoneNumber": "0611223344"
    }
    ```
-   **Résultat attendu** : Un code `200 OK` avec le profil mis à jour.

#### 1.5. Changer le mot de passe (Update)

-   **Méthode** : `POST`
-   **URL** : `http://127.0.0.1:8000/api/sellers/profile/change-password`
-   **Authorization** : Collez votre token.
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "currentPassword": "password123",
        "newPassword": "newPassword1234!"
    }
    ```
-   **Résultat attendu** : Un code `200 OK`. Pour vérifier, vous pouvez refaire l'étape 1.2 avec le nouveau mot de passe.

#### 1.6. Supprimer le compte (Delete)

-   **Méthode** : `DELETE`
-   **URL** : `http://127.0.0.1:8000/api/sellers/profile`
-   **Authorization** : Collez votre token.
-   **Résultat attendu** : Un code `204 No Content`, indiquant que le compte a été supprimé.

---

### Réinitialisation de Mot de Passe

Simulation du processus si l'utilisateur oublie son mot de passe.

#### Configurer Mailtrap.io

Mailtrap est une plateforme qui simule une boîte de réception pour capturer les e-mails envoyés en environnement de développement.

1.  **Créez un compte** : Allez sur [Mailtrap.io](https://mailtrap.io) et connectez-vous.
2.  **Récupérez vos identifiants SMTP** :
    -   Une fois connecté, allez dans votre "Inbox" par défaut (ou créez-en une).
    -   Dans la section "SMTP Settings", vous trouverez les identifiants dont vous avez besoin avec Symfony.
3.  **Configurez Symfony** :
    -   Assurez-vous d'avoir supprimé ou commenté la ligne `MAILER_DSN=null://null` dans votre fichier `.env`.
    -   Ouvrez votre fichier `.env.local` et mettez à jour la variable `MAILER_DSN` avec les identifiants de Mailtrap :
        ```env
        # .env.local
        # Remplacez par VOS identifiants Mailtrap
        MAILER_DSN=smtp://VOTRE_USERNAME:VOTRE_PASSWORD@sandbox.smtp.mailtrap.io:2525
        ```

#### Lancer Symfony Messenger

Symfony Messenger est un composant qui permet à votre application de gérer des tâches de manière asynchrone.
Son but principal est de découpler les actions rapides (comme répondre à une requête web) des actions lentes (comme envoyer un e-mail).

-   Ouvrez un terminal et laissez-le tourner :
    ```bash
    symfony console messenger:consume async -vv
    ```

#### Demander la réinitialisation depuis Postman

-   **Méthode** : `POST`
-   **URL** : `https://127.0.0.1:8000/api/reset-password/request`
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "email": "jean.dupont@email.com"
    }
    ```
-   **Action** : Envoyez la requête. Symfony Messenger va traiter le message et l'envoyer à Mailtrap.

#### Récupérer le Token dans Mailtrap

1.  Retournez sur le site de **Mailtrap.io**, dans votre boîte de réception.
2.  Un nouvel e-mail de votre application devrait y être apparu.
3.  Cliquez dessus pour l'ouvrir.
4.  **Copiez le token** qui se trouve dans le lien fourni dans l'e-mail.

#### Définir le nouveau mot de passe

Retournez dans Postman pour finaliser le processus avec le token récupéré.

-   **Méthode** : `POST`
-   **URL** : `https://127.0.0.1:8000/api/reset-password/reset`
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "token": "LE_TOKEN_COPIÉ_DEPUIS_MAILTRAP",
        "newPassword": "UnNouveauMotDePasseTresSecurise123!",
        "confirmPassword": "UnNouveauMotDePasseTresSecurise123!"
    }
    ```
-   **Résultat attendu** : Un code `200 OK`. Le mot de passe de l'utilisateur est maintenant réinitialisé avec succès.

### CRUD d'un Véhicule

Cela suppose que vous êtes connecté en tant que vendeur (vous possédez un token de vendeur valide obtenu à l'étape 1.2).

#### 2.1. Créer un véhicule (Create)

-   **Méthode** : `POST`
-   **URL** : `http://127.0.0.1:8000/api/sellers/vehicles`
-   **Authorization** : Collez votre token de vendeur.
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "plate": "AA123ZZ",
        "vin": "VF15ABHG854895200",
        "brand": "RENAULT",
        "model": "CLIO",
        "version": "1.5 DCI",
        "energy": "DIESEL",
        "horsePower": 85,
        "fiscalPower": 4.0,
        "gearBox": "MANUELLE",
        "doors": 5,
        "seats": 5,
        "bodyType": "CITADINE",
        "weightKg": 1050,
        "color": "ROUGE",
        "registrationDate": "2020-02-23"
    }
    ```
-   -   **Résultat attendu** : Un code `201 Created` avec les informations du véhicule créé.

#### 2.2. Lister les véhicules (Read)

-   **Méthode** : `GET`
-   **URL** : `http://127.0.0.1:8000/api/sellers/vehicles`
-   **Authorization** : Collez votre token de vendeur.

#### 2.3. Mettre à jour le véhicule (Update)

-   **Méthode** : `PUT`
-   **URL** : `http://127.0.0.1:8000/api/sellers/vehicles/{id}` (remplacez `{id}` par l'ID noté précédemment).
-   **Authorization** : Collez votre token de vendeur.
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "horsePower": 90
    }
    ```
-   **Résultat attendu** : Un code `200 OK` avec le véhicule mis à jour.

#### 2.4. Supprimer le véhicule (Delete)

-   **Méthode** : `DELETE`
-   **URL** : `http://127.0.0.1:8000/api/sellers/vehicles/{id}` (remplacez `{id}` par l'ID du véhicule).
-   **Authorization** : Collez votre token de vendeur.
-   **Résultat attendu** : Un code `204 No Content`.

---

### Gestion Admin

Cela requiert des identifiants pour un compte avec le rôle `ROLE_ADMIN`.

#### 3.1. Se connecter en tant qu'Admin et obtenir le Token

-   **Méthode** : `POST`
-   **URL** : `http://127.0.0.1:8000/api/login_check`
-   **Body** (`raw` / `JSON`) :
    ```json
    {
        "email": "admin@test.com",
        "password": "admin_password"
    }
    ```
-   **Action** : Copiez le nouveau token qui appartient à l'administrateur.

#### 3.2. Lister tous les vendeurs (Read)

-   **Méthode** : `GET`
-   **URL** : `http://127.0.0.1:8000/api/admin/sellers`
-   **Authorization** : Collez votre token d'administrateur.
-   **Paramètres (optionnel)** : Pour tester la pagination, allez dans l'onglet `Params` et ajoutez :
    -   `page` : `1`
    -   `limit` : `5`
-   **Résultat attendu** : Un code `200 OK` avec la liste paginée de tous les utilisateurs vendeurs.

#### 3.2. Lister tous les agents (Read)

-   **Méthode** : `GET`
-   **URL** : `http://127.0.0.1:8000/api/admin/agents`
-   **Authorization** : Collez votre token d'administrateur.
-   **Paramètres (optionnel)** : Pour tester la pagination, allez dans l'onglet `Params` et ajoutez :
    -   `page` : `1`
    -   `limit` : `5`
-   **Résultat attendu** : Un code `200 OK` avec la liste paginée de tous les utilisateurs agents.

---

## Documentation des Endpoints

Toutes les routes sont préfixées par `/api`.

| Rôle          | Méthode  | Route                              | Description                                   |
| :------------ | :------- | :--------------------------------- | :-------------------------------------------- |
| Public        | `POST`   | `/sellers`                         | Crée un nouveau compte vendeur.               |
| Public        | `POST`   | `/reset-password/request`          | Demande une réinitialisation de mot de passe. |
| Public        | `POST`   | `/reset-password/reset`            | Définit un nouveau mot de passe après reset.  |
| `ROLE_SELLER` | `GET`    | `/sellers/profile`                 | Récupère le profil du vendeur connecté.       |
| `ROLE_SELLER` | `PUT`    | `/sellers/profile`                 | Met à jour le profil du vendeur connecté.     |
| `ROLE_SELLER` | `DELETE` | `/sellers/profile`                 | Supprime le compte du vendeur connecté.       |
| `ROLE_SELLER` | `POST`   | `/sellers/profile/change-password` | Change le mot de passe du vendeur.            |
| `ROLE_SELLER` | `POST`   | `/sellers/vehicles`                | Ajoute un nouveau véhicule.                   |
| `ROLE_SELLER` | `GET`    | `/sellers/vehicles`                | Liste les véhicules du vendeur.               |
| `ROLE_SELLER` | `GET`    | `/sellers/vehicles/{id}`           | Affiche un véhicule spécifique.               |
| `ROLE_SELLER` | `PUT`    | `/sellers/vehicles/{id}`           | Met à jour un véhicule.                       |
| `ROLE_SELLER` | `DELETE` | `/sellers/vehicles/{id}`           | Supprime un véhicule.                         |
| `ROLE_ADMIN`  | `GET`    | `/admin/sellers`                   | Liste tous les vendeurs de la plateforme.     |
| `ROLE_ADMIN`  | `GET`    | `/admin/agents`                    | Liste tous les agents de la plateforme.       |

---

<!-- ##  Lancer les Tests

Pour garantir le fonctionnement de l'API, vous pouvez lancer la suite de tests automatisés avec la commande suivante :

```bash
php bin/phpunit
``` -->


-------------------------------------------------------------------------------------

# Projet Symfony Carpilot

## Installation

### 1. Cloner et installer

```bash
# Cloner le projet
git clone https://github.com/jenam15/carpilot-api.git
cd carpilot-api

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install
```

### 2. Compiler les assets

```bash
# Compiler les assets
npm run watch
```

### 3. Démarrer le serveur

```bash
# Démarrer le serveur de développement
symfony server:start
```

### 4. Accéder au projet

Ouvrir le navigateur sur : **http://localhost:8000**

----------------------------------------------------------
CMD: utiles

php bin/console --version  
php -v

symfony server:start 
yarn encore dev --watch   
yarn watch   
npm run watch

git clone https://github.com/jenam15/carpilot-api.git
git remote add origin https://github.com/MorganNwt/carpilot-api.git

php bin/console debug:router   
php bin/console cache:clear

php bin/console make:entity User    

php bin/console doctrine:database:create   
php bin/console make:migration
php bin/console doctrine:migrations:migrate

--------------------------------------------------
** Fixtures sont des données de test ou d’exemple qu’on injecte dans la base de données. **
** Faker est une bibliothèque PHP qui génère de fausses données réalistes automatiquement. **

composer require --dev orm-fixtures
composer require fakerphp/faker

 php bin/console doctrine:migrations:diff
 php bin/console doctrine:migrations:migrate
 php bin/console doctrine:fixtures:load


** un composant très utilisé dans les applications Symfony pour gérer l'authentification par JWT (JSON Web Tokens). **

Il permet de :
 * Générer des tokens JWT (JSON Web Tokens) après une authentification (par exemple avec un email et un mot de passe).
* Protéger tes routes (API REST) en exigeant un token pour y accéder.
* Vérifier automatiquement la validité du token à chaque requête.
* Intégrer une authentification stateless, sans session.

composer require "lexik/jwt-authentication-bundle"
 php bin/console messenger:consume async // sert à lancer un "worker" Messenger dans Symfony pour traiter les messages asynchrones (en file d’attente), envoyés via le composant Messenger.


#   c a r p i l o t  
 #   c a r p i l o t  
 # carpilot
# carpilot
