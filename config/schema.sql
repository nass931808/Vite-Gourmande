CREATE DATABASE IF NOT EXISTS `vite&gourmande`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `vite&gourmande`;

CREATE TABLE IF NOT EXISTS menus (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    description TEXT NULL,
    nb_personnes_min INT UNSIGNED NOT NULL DEFAULT 1,
    prix_minimum DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id INT UNSIGNED NOT NULL,
    nom VARCHAR(160) NOT NULL,
    type ENUM('entree', 'plat', 'dessert', 'boisson') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_plats_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_plats_menu_id (menu_id),
    INDEX idx_plats_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    CONSTRAINT fk_commandes_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_commandes_menu
        FOREIGN KEY (menu_id) REFERENCES menus(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    INDEX idx_commandes_utilisateur (utilisateur_id),
    INDEX idx_commandes_menu (menu_id),
    INDEX idx_commandes_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commande_suivis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id INT UNSIGNED NOT NULL,
    statut VARCHAR(60) NOT NULL,
    commentaire VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_suivis_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_suivis_commande_id (commande_id),
    INDEX idx_suivis_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    note TINYINT UNSIGNED NOT NULL,
    commentaire TEXT NOT NULL,
    statut ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_avis_commande (commande_id),
    CONSTRAINT fk_avis_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_avis_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    INDEX idx_avis_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    titre VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contacts_email (email),
    INDEX idx_contacts_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO menus (id, nom, description, nb_personnes_min, prix_minimum)
VALUES
    (1, 'Menu de Noel', 'Menu festif pour les repas de Noel', 6, 180.00),
    (2, 'Menu de Paques', 'Menu special pour Paques', 6, 170.00),
    (3, 'Menu Evenement', 'Menu pour les evenements prives et pros', 10, 320.00),
    (4, 'Menu Classique', 'Menu classique disponible toute l annee', 4, 95.00)
ON DUPLICATE KEY UPDATE
    nom = VALUES(nom),
    description = VALUES(description),
    nb_personnes_min = VALUES(nb_personnes_min),
    prix_minimum = VALUES(prix_minimum);
