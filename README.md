# apiSymfony

lien git: [https://github.com/MrScrupulus/apiSymfony.git](https://github.com/MrScrupulus/apiSymfony.git)

## Description

API Symfony pour la gestion de livres et d'auteurs avec authentification JWT, pagination et cache.

## Fonctionnalités

-   **Authentification JWT** avec rôles utilisateur et administrateur
-   **Gestion des livres** (CRUD complet)
-   **Gestion des auteurs** (CRUD complet)
-   **Pagination** des résultats
-   **Système de cache** pour optimiser les performances
-   **Validation des données** avec contraintes Symfony
-   **Sécurité** basée sur les rôles

## Installation

1. Cloner le dépôt :

```bash
git clone https://github.com/MrScrupulus/apiSymfony.git
cd apiSymfony
```

2. Installer les dépendances :

```bash
composer install
```

3. Configurer la base de données dans le fichier `.env`

4. Créer la base de données et exécuter les migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Charger les données de test :

```bash
php bin/console doctrine:fixtures:load
```

## Utilisation

Démarrer le serveur de développement :

```bash
symfony server:start
```

L'API sera accessible à l'adresse : `http://localhost:8000`

## Endpoints disponibles

-   `GET /api/book/` - Liste des livres (avec pagination)
-   `GET /api/book/{id}` - Détail d'un livre
-   `POST /api/books` - Créer un livre (ROLE_ADMIN requis)
-   `PUT /api/book/{id}` - Modifier un livre
-   `DELETE /api/book/{id}/delete` - Supprimer un livre

-   `GET /api/author/` - Liste des auteurs
-   `GET /api/author/{id}` - Détail d'un auteur
-   `POST /api/author` - Créer un auteur
-   `PUT /api/author/{id}` - Modifier un auteur
-   `DELETE /api/author/{id}` - Supprimer un auteur

## Utilisateurs de test

-   **Utilisateur normal** : `user@bookapi.com` / `password`
-   **Administrateur** : `admin@bookapi.com` / `password`
