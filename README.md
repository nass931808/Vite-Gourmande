# README - Vite&Gourmande (STUDI)

## 🍽️ Présentation du site
J'ai créé Vite&Gourmande, un site web de restaurant/traiteur.

Le site permet de:
- 📋 Consulter les menus
- 🔐 Créer un compte et se connecter
- 🛒 Passer une commande
- 📦 Suivre une commande

## 🎓 Contexte du projet
J'ai réalisé ce projet dans le cadre d'un **ECF pour Studi**.


---

Dans ce guide, j'ai expliqué simplement comment déployer l'application en local.

## Choix du schéma selon l'environnement
- En local avec XAMPP/MySQL, j'ai utilisé `config/schema.sql`
- Avec Supabase/PostgreSQL, j'ai utilisé `config/schema_supabase.sql`
- Pour la connexion PHP, j'ai utilisé `config/database.php` avec la variable d'environnement `DB_DRIVER`
	- `DB_DRIVER=mysql` pour MySQL
	- `DB_DRIVER=pgsql` pour Supabase

## Prérequis
- J'avais XAMPP installé
- J'ai démarré Apache et MySQL
- J'ai placé le projet dans `C:\xampp\htdocs\Vite&Gourmande`

## Étapes
1. J'ai ouvert phpMyAdmin: `http://localhost/phpmyadmin`
2. J'ai créé une base de données (ex: `vite_gourmande`)
3. J'ai importé `config/schema.sql`
4. J'ai vérifié la connexion dans `config/database.php`
5. J'ai ouvert l'application: `http://localhost/Vite&Gourmande/pages/index.html`

## Vérification rapide
- J'ai vérifié que l'accueil et les menus étaient accessibles
- J'ai vérifié que l'inscription et la connexion fonctionnaient
- J'ai vérifié que la commande et le suivi de commande fonctionnaient




