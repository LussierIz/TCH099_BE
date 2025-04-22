INSERT INTO Boutique () VALUES ();
SET @shopId = LAST_INSERT_ID();

INSERT INTO Produit (prix, titre, description, image, id_boutique)
VALUES
  (300, 'Thème Galaxie', 'Un habillage exclusif pour votre interface.', '/images/theme-galaxie.png', @shopId),
  (150, 'Companion',   'Un petit companion qui veille sur vous lors de votre étude',     '/images/cadre-dore.png',     @shopId),
  (100, 'Police spéciale','Change la police', '/images/police.png',    @shopId);

INSERT INTO Inventaire (id_utilisateur)
SELECT u.id_utilisateur
FROM Utilisateur u
LEFT JOIN Inventaire i ON i.id_utilisateur = u.id_utilisateur
WHERE i.id_utilisateur IS NULL;

INSERT INTO Banque (quantite_xp, quantite_coins, id_utilisateur)
SELECT 0            AS quantite_xp,
       0            AS quantite_coins,
       u.id_utilisateur
FROM Utilisateur u
LEFT JOIN Banque b ON b.id_utilisateur = u.id_utilisateur
WHERE b.id_utilisateur IS NULL;