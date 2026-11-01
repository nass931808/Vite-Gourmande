-- MySQL/XAMPP schema (utiliser en local avec phpMyAdmin)

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS commande_suivis;
DROP TABLE IF EXISTS commandes;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS plat_allergenes;
DROP TABLE IF EXISTS allergenes;
DROP TABLE IF EXISTS plats;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS menus;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS menus (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    description TEXT NULL,
    nb_personnes_min INT UNSIGNED NOT NULL DEFAULT 1,
    prix_minimum DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_menus_actif (actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id INT UNSIGNED NOT NULL,
    nom VARCHAR(160) NOT NULL,
    type ENUM('entree', 'plat', 'dessert', 'boisson') NOT NULL,
    allergenes VARCHAR(255) NOT NULL DEFAULT 'Non renseignés',
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plats_menu_id (menu_id),
    INDEX idx_plats_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS allergenes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL UNIQUE,
    composition TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS plat_allergenes (
    plat_id INT UNSIGNED NOT NULL,
    allergene_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    INDEX idx_plat_allergenes_allergene (allergene_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    prenom VARCHAR(120) NOT NULL,
    gsm VARCHAR(30) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    adresse_postale VARCHAR(255) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'employe', 'administrateur') NOT NULL DEFAULT 'utilisateur',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commandes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    nom_client VARCHAR(120) NOT NULL,
    prenom_client VARCHAR(120) NOT NULL,
    email_client VARCHAR(190) NOT NULL,
    gsm_client VARCHAR(30) NOT NULL,
    adresse_client VARCHAR(255) NOT NULL,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    lieu_livraison VARCHAR(255) NOT NULL,
    ville_livraison VARCHAR(120) NOT NULL,
    distance_km DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    nb_personnes INT UNSIGNED NOT NULL,
    prix_menu DECIMAL(10,2) NOT NULL,
    remise DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frais_livraison DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    prix_total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'accepte', 'en_preparation', 'en_cours_de_livraison', 'livre', 'terminee', 'annulee') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_commandes_utilisateur (utilisateur_id),
    INDEX idx_commandes_menu (menu_id),
    INDEX idx_commandes_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commande_suivis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id INT UNSIGNED NOT NULL,
    statut VARCHAR(60) NOT NULL,
    commentaire VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_suivis_commande_id (commande_id),
    INDEX idx_suivis_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS avis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    note TINYINT UNSIGNED NOT NULL,
    commentaire TEXT NOT NULL,
    statut ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_avis_commande (commande_id),
    INDEX idx_avis_statut (statut),
    INDEX idx_avis_utilisateur (utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    titre VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contacts_email (email),
    INDEX idx_contacts_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO menus (id, nom, description, nb_personnes_min, prix_minimum, actif)
VALUES
    (1, 'Menu de Noel', 'Menu festif pour les repas de Noel', 6, 180.00, 1),
    (2, 'Menu de Paques', 'Menu special pour Paques', 6, 170.00, 1),
    (3, 'Menu Evenement', 'Menu pour les evenements prives et pros', 10, 320.00, 1),
    (4, 'Menu Classique', 'Menu classique disponible toute l annee', 4, 95.00, 1)
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    description = VALUES(description),
    nb_personnes_min = VALUES(nb_personnes_min),
    prix_minimum = VALUES(prix_minimum),
    actif = VALUES(actif);

INSERT INTO plats (id, menu_id, nom, type, allergenes, stock)
VALUES
    (1,  1, 'Foie gras maison', 'entree', 'Aucun allergène majeur identifié', 12),
    (2,  1, 'Veloute de chataignes', 'entree', 'Lait', 15),
    (3,  1, 'Chapon farci aux marrons', 'plat', 'Gluten, fruits à coque, lait', 10),
    (4,  1, 'Gratin dauphinois', 'plat', 'Lait', 12),
    (5,  1, 'Buche de Noel chocolat', 'dessert', 'Lait, oeufs, gluten', 8),
    (6,  2, 'Asperges sauce mousseline', 'entree', 'Oeufs, lait', 14),
    (7,  2, 'Oeuf cocotte truffe', 'entree', 'Oeufs, lait', 12),
    (8,  2, 'Gigot d agneau aux herbes', 'plat', 'Aucun', 10),
    (9,  2, 'Gratin de pommes de terre', 'plat', 'Lait', 12),
    (10, 2, 'Charlotte aux fraises', 'dessert', 'Gluten, oeufs, lait', 8),
    (11, 3, 'Verrines de saumon', 'entree', 'Poisson, lait', 20),
    (12, 3, 'Planches de charcuterie', 'entree', 'Sulfites (selon charcuterie)', 20),
    (13, 3, 'Buffet chaud au choix', 'plat', 'Selon les préparations : gluten, lait, œufs ou céleri', 15),
    (14, 3, 'Riz pilaf et legumes', 'plat', 'Aucun', 15),
    (15, 3, 'Assortiment de desserts', 'dessert', 'Gluten, oeufs, lait', 15),
    (16, 4, 'Salade nicoise', 'entree', 'Poisson, oeufs', 16),
    (17, 4, 'Soupe a l oignon gratinee', 'entree', 'Gluten, lait', 16),
    (18, 4, 'Boeuf bourguignon', 'plat', 'Sulfites (vin)', 12),
    (19, 4, 'Pommes de terre vapeur', 'plat', 'Aucun', 16),
    (20, 4, 'Tarte Tatin maison', 'dessert', 'Gluten, lait', 10),
    (21, 1, 'Jus de pomme artisanal', 'boisson', 'Aucun allergène majeur identifié', 20),
    (22, 2, 'Eau minérale', 'boisson', 'Aucun allergène majeur identifié', 20),
    (23, 3, 'Jus d orange', 'boisson', 'Aucun allergène majeur identifié', 20),
    (24, 4, 'Eau pétillante', 'boisson', 'Aucun allergène majeur identifié', 20)
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    type = VALUES(type),
    allergenes = VALUES(allergenes),
    stock = VALUES(stock);

INSERT INTO allergenes (id, nom, composition)
VALUES
    (1, 'Gluten', 'Blé, seigle, orge, avoine et produits dérivés'),
    (2, 'Lait', 'Lait et produits à base de lait, y compris le lactose'),
    (3, 'Œufs', 'Œufs et produits à base d’œufs'),
    (4, 'Fruits à coque', 'Amandes, noisettes, noix, noix de cajou et produits dérivés'),
    (5, 'Poisson', 'Poissons et produits à base de poissons'),
    (6, 'Céleri', 'Céleri et produits à base de céleri'),
    (7, 'Sulfites', 'Anhydride sulfureux et sulfites à concentration réglementée')
ON DUPLICATE KEY UPDATE
    composition = VALUES(composition);

INSERT INTO plat_allergenes (plat_id, allergene_id)
VALUES
    (3, 1), (3, 2), (3, 4),
    (4, 2),
    (5, 1), (5, 2), (5, 3),
    (6, 2), (6, 3),
    (7, 2), (7, 3),
    (9, 2),
    (10, 1), (10, 2), (10, 3),
    (11, 2), (11, 5),
    (12, 7),
    (13, 1), (13, 2), (13, 3), (13, 6),
    (15, 1), (15, 2), (15, 3),
    (16, 3), (16, 5),
    (17, 1), (17, 2),
    (18, 7),
    (20, 1), (20, 2)
ON DUPLICATE KEY UPDATE
    allergene_id = VALUES(allergene_id);
