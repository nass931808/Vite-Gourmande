# README - Vite&Gourmande (STUDI)

## 🍽️ Présentation du site
Vite&Gourmande est un site web de restaurant/traiteur.

Le site permet de:
- 📋 Consulter les menus
- 🔐 Créer un compte et se connecter
- 🛒 Passer une commande
- 📦 Suivre une commande

## 🎓 Contexte du projet
Ce projet a été réalisé dans le cadre d'un **ECF pour Studi**.


---

Guide simple pour deployer l'application en local.

## Prerequis
- XAMPP installe
- Apache et MySQL demarres
- Projet dans `C:\xampp\htdocs\Vite&Gourmande`

## Etapes
1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Creer une base de donnees (ex: `vite_gourmande`)
3. Importer `config/schema.sql`
4. Verifier la connexion dans `config/database.php`
5. Ouvrir l'application: `http://localhost/Vite&Gourmande/pages/index.html`

## Verification rapide
- Accueil et menus accessibles
- Inscription / connexion fonctionnelles
- Commande et suivi de commande fonctionnels

## En cas de probleme
- Verifier que MySQL est demarre
- Verifier les identifiants de BDD dans `config/database.php`
