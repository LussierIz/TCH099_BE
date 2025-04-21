CREATE TABLE Utilisateur (
    id_utilisateur INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(20),
    prenom VARCHAR(20),
    email VARCHAR(50) UNIQUE,
    mot_de_passe VARCHAR(255),
    date_inscription DATE,
    statut VARCHAR(20)
);
CREATE TABLE Recompense (
    id_recompense INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    nom_recompense VARCHAR(20),
    description VARCHAR(255),
    points_necessaires INTEGER(100)
);
CREATE TABLE Defi (
    id_defi INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    nom_defi VARCHAR(20),
    description VARCHAR(255),
    date_debut DATE,
    date_fin DATE,
    recompenses VARCHAR(255),
    id_utilisateur INTEGER(10),
    id_recompenses INTEGER(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_recompenses) REFERENCES Recompense(id_recompense)
);
CREATE TABLE ParticipationDefi (
    id_participation INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    statut VARCHAR(20),
    points_gagnes INTEGER(100),
    id_utilisateur INTEGER(10),
    id_defi INTEGER(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_defi) REFERENCES Defi(id_defi)
);
CREATE TABLE Notes (
    id_note INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(100),
    contenu VARCHAR(255),
    date_creation DATE,
    id_utilisateur INTEGER(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Objectifs (
    id_objectif INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INTEGER(10),
    titre VARCHAR(100),
    description VARCHAR(255),
    date_debut DATE,
    date_fin DATE,
    statut VARCHAR(20),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Taches (
    id_tache INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    id_objectif INTEGER(10),
    titre VARCHAR(100),
    description VARCHAR(255),
    statut VARCHAR(20) DEFAULT 'En cours',
    date_fin DATE,
    FOREIGN KEY (id_objectif) REFERENCES Objectifs(id_objectif)
);
CREATE TABLE HappyHour (
    id_happy INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    date_debut DATE,
    date_fin DATE,
    multiple_points FLOAT(10,2)
);
CREATE TABLE SessionEtude (
    id_session INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    date_debut DATE,
    date_fin DATE,
    Duree TIME,
    points_gagnes INTEGER(100),
    id_utilisateur INTEGER(10),
    id_happy INTEGER(10) DEFAULT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_happy) REFERENCES HappyHour(id_happy)
);
CREATE TABLE Nudge (
    id_nudge INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    date_envoi DATE,
    message VARCHAR(255),
    id_utilisateur_envoyeur INTEGER(10),
    FOREIGN KEY (id_utilisateur_envoyeur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Amitie (
    id_amitie INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    date_debut_amitie DATE,
    statut VARCHAR(50),
    id_utilisateur1 INTEGER(10),
    id_utilisateur2 INTEGER(10),
    FOREIGN KEY (id_utilisateur1) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_utilisateur2) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Inventaire (
    id_inventaire INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INTEGER(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Banque (
    id_banque INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    quantite_xp BIGINT(19),
    quantite_coins BIGINT(19),
    id_utilisateur INTEGER(10),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Boutique (
    id_boutique INTEGER(10) PRIMARY KEY AUTO_INCREMENT
);
CREATE TABLE Produit (
    id_produit INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    prix INTEGER(10),
    titre VARCHAR(100),
    description VARCHAR(255),
    image VARCHAR(255),
    id_boutique INTEGER(10),
    FOREIGN KEY (id_boutique) REFERENCES Boutique(id_boutique)
);
CREATE TABLE Inventaire_Produit (
    id_inventaire INTEGER(10),
    id_produit INTEGER(10),
    PRIMARY KEY (id_inventaire, id_produit),
    FOREIGN KEY (id_inventaire) REFERENCES Inventaire(id_inventaire),
    FOREIGN KEY (id_produit) REFERENCES Produit(id_produit)
);
CREATE TABLE Chat (
    id_chat INTEGER PRIMARY KEY AUTO_INCREMENT,
    date DATE,
    chat_name VARCHAR(255)
);
CREATE TABLE Participant (
    id_participant INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    id_chat INTEGER(10),
    id_utilisateur INTEGER(10),
    FOREIGN KEY (id_chat) REFERENCES Chat(id_chat),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Messages (
    id_message INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    texte VARCHAR(255),
    date_envoi DATE,
    id_chat INTEGER(10),
    id_utilisateur INTEGER(10),
    FOREIGN KEY (id_chat) REFERENCES Chat(id_chat),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);
CREATE TABLE Devoir (
    id_devoir INTEGER(10) PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_limite DATETIME,
    id_utilisateur INTEGER(10),
    id_destinataire INTEGER(10),
    statut VARCHAR(20) NOT NULL DEFAULT 'à faire',
    FOREIGN KEY (id_destinataire) REFERENCES Utilisateur(id_utilisateur),
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur)
);