-- WH Solutions Database Schema
CREATE DATABASE IF NOT EXISTS whsolutions CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE whsolutions;

-- Admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('super_admin','admin') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin Activity Log
CREATE TABLE admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-box',
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    reference VARCHAR(100) DEFAULT NULL,
    description TEXT,
    short_description VARCHAR(500),
    image VARCHAR(255),
    price DECIMAL(10,2) DEFAULT NULL,
    old_price DECIMAL(10,2) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    views INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Contact Messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(30),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- WhatsApp Orders/Inquiries
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(30),
    customer_email VARCHAR(100),
    quantity INT DEFAULT 1,
    message TEXT,
    status ENUM('new','contacted','completed','cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Site Settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Packages / Propositions de Valeur
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    subtitle VARCHAR(300),
    description TEXT,
    price DECIMAL(10,2) DEFAULT NULL,
    price_label VARCHAR(100) DEFAULT NULL,
    duration_months INT DEFAULT 12,
    icon VARCHAR(50) DEFAULT 'fas fa-box',
    color VARCHAR(30) DEFAULT '#4ECDC4',
    badge VARCHAR(100) DEFAULT NULL,
    is_popular TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Package Features
CREATE TABLE package_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    feature_text VARCHAR(500) NOT NULL,
    is_included TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Customer Packages / Subscriptions
CREATE TABLE customer_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150),
    customer_phone VARCHAR(30),
    customer_company VARCHAR(200),
    customer_city VARCHAR(100),
    package_id INT,
    status ENUM('pending','active','expired','cancelled') DEFAULT 'pending',
    notes TEXT,
    activated_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Visitors Analytics
CREATE TABLE visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    page_visited VARCHAR(255),
    user_agent VARCHAR(500),
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default Super Admin (username: admin, password: set via setup.php)
-- After importing this SQL, visit http://localhost/whsolutions/setup.php to set password
INSERT INTO admins (username, password, name, email, role) VALUES
('admin', 'NEEDS_SETUP', 'Super Administrateur', 'info.whsolution@gmail.com', 'super_admin');

-- Categories
INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
('Nettoyage et Désinfection', 'nettoyage-desinfection', 'Nettoyage et désinfection des lignes de production, équipements, sols, surfaces et zones sensibles', 'fas fa-spray-can', 1),
('Traitement des Eaux', 'traitement-eaux', 'Station d''épuration industrielle et communale', 'fas fa-water', 2),
('Hygiène du Personnel', 'hygiene-personnel', 'Solutions pour postes de lavage, savons industriels, distributeurs automatiques, séchage', 'fas fa-hands-wash', 3),
('Matériel de Nettoyage', 'materiel-nettoyage', 'Aspirateurs, balais, raclettes et accessoires professionnels HACCP', 'fas fa-broom', 4),
('Consommables Jetables', 'consommables-jetables', 'Gants, charlottes, blouses, combinaisons jetables', 'fas fa-box-open', 5),
('Poubelles et Conteneurs', 'poubelles-conteneurs', 'Poubelles à pédale, conteneurs extérieur, bacs de tri', 'fas fa-trash-alt', 6),
('Papeterie Hygiénique', 'papeterie-hygienique', 'Papier hygiénique, essuie-mains, bobines industrielles', 'fas fa-toilet-paper', 7),
('Lutte Anti-Nuisibles', 'lutte-anti-nuisibles', 'Désinsectiseurs, pièges, solutions anti-nuisibles', 'fas fa-bug', 8);

-- Products: Nettoyage et Désinfection (cat 1)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(1, 'Détergent alcalin à haut pouvoir dégraissant', 'detergent-alcalin-degraissant', 'Puissant dégraissant alcalin pour l''industrie agroalimentaire', 'Détergent alcalin concentré à haut pouvoir dégraissant, spécialement formulé pour le nettoyage en profondeur des équipements industriels, lignes de production et surfaces grasses. Conforme aux normes HACCP.', 1),
(1, 'Détergent alcalin concentré', 'detergent-alcalin-concentre', 'Nettoyant alcalin multi-usage industriel', 'Détergent alcalin concentré pour le nettoyage intensif des surfaces, sols et équipements. Haute efficacité contre les salissures organiques.', 1),
(1, 'Détergent acide détartrant et antirouille', 'detergent-acide-detartrant', 'Détartrant acide professionnel', 'Détergent acide puissant pour éliminer le tartre, la rouille et les dépôts minéraux sur les équipements et surfaces industrielles.', 0),
(1, 'Détergent acide et détartrant peu moussant', 'detergent-acide-peu-moussant', 'Détartrant basse mousse pour CIP', 'Formulation acide peu moussante idéale pour les systèmes de nettoyage en place (CIP). Élimine efficacement le tartre et les résidus minéraux.', 0),
(1, 'Détergent parfumé neutre pour sols', 'detergent-parfume-sols', 'Nettoyant neutre parfumé pour sols', 'Détergent neutre parfumé pour le nettoyage quotidien des sols. Laisse une agréable odeur de fraîcheur. Adapté à tous types de revêtements.', 1),
(1, 'Hypochlorite de sodium 50°', 'hypochlorite-sodium-50', 'Eau de Javel concentrée 50°', 'Hypochlorite de sodium à 50° chlorométriques. Désinfectant puissant pour surfaces, eaux et équipements industriels.', 0),
(1, 'Soude caustique liquide 40-42%', 'soude-caustique-liquide', 'Soude caustique en solution concentrée', 'Soude caustique liquide à 40-42% de concentration. Utilisée pour le nettoyage en profondeur, le dégraissage et le traitement des eaux.', 0);

-- Products: Traitement des Eaux (cat 2)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(2, 'Chlorure ferrique', 'chlorure-ferrique', 'Coagulant pour traitement des eaux', 'Chlorure ferrique utilisé comme coagulant dans le traitement des eaux usées industrielles et communales. Haute performance de floculation.', 1),
(2, 'Polymères cationiques', 'polymeres-cationiques', 'Floculant cationique pour station d''épuration', 'Polymères cationiques pour la floculation et le traitement des boues en station d''épuration. Améliore la décantation et la clarification.', 0),
(2, 'Polymères anioniques', 'polymeres-anioniques', 'Floculant anionique pour eaux usées', 'Polymères anioniques pour le traitement avancé des eaux usées. Optimise la séparation solide-liquide.', 0);

-- Products: Hygiène du Personnel (cat 3)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(3, 'Savon liquide bactéricide', 'savon-liquide-bactericide', 'Savon antibactérien professionnel', 'Savon liquide bactéricide pour le lavage hygiénique des mains. Adapté aux postes de lavage en industrie agroalimentaire. Conforme HACCP.', 1),
(3, 'Gel hydroalcoolique', 'gel-hydroalcoolique', 'Désinfectant mains sans rinçage', 'Gel hydroalcoolique désinfectant pour les mains. Formule à séchage rapide. Élimine 99,9% des bactéries.', 1),
(3, 'Distributeur savon automatique', 'distributeur-savon-auto', 'Distributeur sans contact', 'Distributeur de savon automatique sans contact. Design professionnel adapté aux environnements industriels et agroalimentaires.', 0);

-- Products: Matériel de Nettoyage (cat 4)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(4, 'Aspirateurs eau et poussière - 2 Moteurs', 'aspirateur-2-moteurs', 'Aspirateur industriel bi-moteur', 'Aspirateur eau et poussière professionnel à 2 moteurs. Puissance et capacité adaptées aux besoins industriels.', 1),
(4, 'Aspirateurs eau et poussière - 3 Moteurs', 'aspirateur-3-moteurs', 'Aspirateur industriel tri-moteur', 'Aspirateur eau et poussière haute performance à 3 moteurs. Idéal pour les grandes surfaces industrielles.', 0),
(4, 'Balais alimentaires HACCP', 'balais-haccp', 'Balais certifiés HACCP manche aluminium', 'Balais alimentaires HACCP avec manche aluminium ou monobloc. Code couleur pour une gestion hygiénique optimale.', 1),
(4, 'Raclettes alimentaires HACCP', 'raclettes-haccp', 'Raclettes certifiées HACCP', 'Raclettes alimentaires HACCP avec manche aluminium ou monobloc. Disponibles en plusieurs couleurs et tailles.', 0),
(4, 'Carrés microfibres code couleur HACCP', 'carres-microfibres-haccp', 'Microfibres code couleur HACCP', 'Carrés microfibres professionnels avec code couleur HACCP. Nettoyage optimal des surfaces sensibles.', 0);

-- Products: Consommables Jetables (cat 5)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(5, 'Gants latex non poudrés', 'gants-latex-non-poudres', 'Gants d''examen en latex', 'Gants en latex non poudrés à usage unique. Haute sensibilité tactile. Boîte de 100 pièces.', 1),
(5, 'Gants nitrile non poudrés', 'gants-nitrile-non-poudres', 'Gants d''examen en nitrile', 'Gants en nitrile non poudrés, sans latex. Résistance chimique supérieure. Boîte de 100 pièces.', 1),
(5, 'Gants anti-froid alimentaire', 'gants-anti-froid', 'Gants isolants pour chambres froides', 'Gants anti-froid spécialement conçus pour le travail en chambre froide et la manipulation de produits surgelés.', 0),
(5, 'Gants de nettoyage - Latex bleu alimentaire', 'gants-nettoyage-latex-bleu', 'Gants de ménage bleus alimentaires', 'Gants de nettoyage en latex bleu, qualité alimentaire. Résistants aux produits chimiques courants.', 0),
(5, 'Charlottes jetables', 'charlottes-jetables', 'Coiffes jetables non tissé', 'Charlottes jetables en non-tissé. Indispensables en milieu agroalimentaire. Paquet de 100 pièces.', 0),
(5, 'Blouses jetables en non-tissé', 'blouses-jetables', 'Blouses visiteur jetables', 'Blouses jetables en non-tissé pour visiteurs et personnel temporaire. Taille unique.', 0),
(5, 'Combinaisons jetables en non-tissé', 'combinaisons-jetables', 'Combinaisons de protection jetables', 'Combinaisons jetables intégrales en non-tissé avec capuche. Protection complète.', 0),
(5, 'Surchaussures jetables', 'surchaussures-jetables', 'Couvre-chaussures jetables', 'Surchaussures jetables en polyéthylène. Protection hygiénique des chaussures. Paquet de 100 pièces.', 0);

-- Products: Poubelles et Conteneurs (cat 6)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(6, 'Poubelle à pédale - 25L', 'poubelle-pedale-25l', 'Poubelle hygiénique 25 litres', 'Poubelle à pédale 25 litres. Ouverture sans contact. Idéale pour les espaces de travail.', 0),
(6, 'Poubelle à pédale - 50L', 'poubelle-pedale-50l', 'Poubelle hygiénique 50 litres', 'Poubelle à pédale 50 litres. Construction robuste, fermeture silencieuse.', 0),
(6, 'Poubelle à pédale - 80L', 'poubelle-pedale-80l', 'Poubelle hygiénique 80 litres', 'Poubelle à pédale 80 litres. Parfaite pour les zones à fort passage.', 0),
(6, 'Conteneur poubelle d''extérieur 660L', 'conteneur-660l', 'Conteneur roulant 660 litres', 'Conteneur poubelle d''extérieur de 660 litres. Robuste, avec roues et couvercle. Conformité environnementale.', 1);

-- Products: Papeterie Hygiénique (cat 7)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(7, 'Papier hygiénique compact 320-450g', 'papier-hygienique-compact', 'Rouleaux compacts 320g à 450g', 'Papier hygiénique compact disponible en 320g, 350g, 400g et 450g. Haute qualité, doux et résistant.', 1),
(7, 'Essuie-mains pliés ZIGZAG', 'essuie-mains-zigzag', 'Essuie-mains pliés en V', 'Essuie-mains pliés zigzag (pliage en V). Distribution feuille à feuille. Idéal pour sanitaires professionnels.', 0),
(7, 'Papier essuie-mains', 'papier-essuie-mains', 'Bobine essuie-mains industriel', 'Papier essuie-mains en bobine. Forte absorption, résistant à l''humidité. Usage industriel et professionnel.', 0);

-- Products: Lutte Anti-Nuisibles (cat 8)
INSERT INTO products (category_id, name, slug, short_description, description, is_featured) VALUES
(8, 'Désinsectiseur à feuille engluée', 'desinsectiseur-englue', 'Tue-mouche professionnel à glu', 'Désinsectiseur à feuille engluée (tue-mouche). Discret et efficace. Adapté aux zones de production alimentaire.', 1),
(8, 'Désinsectiseur électrique', 'desinsectiseur-electrique', 'Tue-insectes UV électrique', 'Désinsectiseur électrique à UV. Attraction et élimination des insectes volants. Conforme HACCP.', 0);

-- Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'WH Solutions'),
('site_tagline', 'Expert en Hygiène Professionnelle'),
('site_phone', '+212 (0) 6 52 020702'),
('site_email', 'info.whsolution@gmail.com'),
('site_address', 'N°31, Rue Martir ABD Eslam Ben Mohammed - Residence Riad Zayton, Bureau N°2 Val fleury - Kenitra Maroc'),
('whatsapp_number', '212652020702'),
('whatsapp_welcome_message', 'Bonjour, je souhaite avoir des informations sur vos produits d''hygiène professionnelle.'),
('whatsapp_order_message', 'Bonjour, je souhaite commander le produit : *{product}* (Quantité: {quantity})\nMerci de me contacter pour finaliser la commande.'),
('whatsapp_catalogue_message', 'Bonjour, j''ai consulté votre catalogue et je souhaite avoir plus d''informations sur vos produits.'),
('catalogue_url', 'https://drive.google.com/file/d/149LV19xcNzt5apoNIbIQvo7vI5KIB4v6/view');
