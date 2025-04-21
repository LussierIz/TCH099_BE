<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Boutique
{
    public static function getBoutiqueItems($userId)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        // 1) recuperation de l'id inventaire lie a l'utilisateur
        $stmt = $pdo->prepare("SELECT id_inventaire FROM Inventaire WHERE id_utilisateur = :uid");
        $stmt->execute([':uid' => $userId]);
        $invId = $stmt->fetchColumn();

        // 2) recuperation de tous les produits et on joint avec Inventaire_Produit
        $sql = "SELECT p.id_produit, p.titre, p.description, p.prix, p.image,
                CASE WHEN ip.id_produit IS NOT NULL THEN 1 ELSE 0 END AS owned
                FROM Produit p
                LEFT JOIN Inventaire_Produit ip
                ON p.id_produit = ip.id_produit
                AND ip.id_inventaire = :inv
                WHERE p.id_boutique = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':inv' => $invId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'items' => $items]);
    }

    public static function buyItem($userId, $prodId)
    {
        global $pdo, $API_SECRET;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }
        try {
            $pdo->beginTransaction();

            // 1) verrouillage et recuperation du solde
            $stmt = $pdo->prepare("
                SELECT quantite_coins, quantite_xp 
                FROM Banque 
                WHERE id_utilisateur = :uid 
                FOR UPDATE");
            $stmt->execute([':uid' => $userId]);
            $bank = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$bank) {
                throw new \Exception("Compte bancaire introuvable");
            }

            // 2) Recuperer le prix du produit
            $stmt = $pdo->prepare("
                SELECT prix 
                FROM Produit 
                WHERE id_produit = :pid 
                AND id_boutique = 1");

            $stmt->execute([':pid' => $prodId]);
            $price = (int) $stmt->fetchColumn();
            if ($stmt->rowCount() === 0) {
                throw new \Exception("Produit introuvable");
            }

            // 3) verifier si le solde est suffisant
            if ($bank['quantite_coins'] < $price) {
                throw new \Exception("Solde insuffisant");
            }

            // 4) Débit
            $stmt = $pdo->prepare("
                UPDATE Banque 
                SET quantite_coins = quantite_coins - :pr 
                WHERE id_utilisateur   = :uid");
            $stmt->execute([
                ':pr' => $price,
                ':uid' => $userId
            ]);

            // 5) Inventaire (création si nécessaire)
            $stmt = $pdo->prepare("
            SELECT id_inventaire 
                FROM Inventaire 
                WHERE id_utilisateur = :uid");
            $stmt->execute([':uid' => $userId]);
            $invId = $stmt->fetchColumn();
            if (!$invId) {
                $stmt = $pdo->prepare("
                INSERT INTO Inventaire (id_utilisateur) 
                VALUES (:uid)");
            $stmt->execute([':uid' => $userId]);
            $invId = $pdo->lastInsertId();
            }

            // 6) Ajout en Inventaire_Produit (IGNORE pour éviter duplication)
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO Inventaire_Produit 
                (id_inventaire, id_produit)
                VALUES (:inv, :pid)");
            $stmt->execute([
                ':inv' => $invId,
                ':pid' => $prodId
            ]);

            // 7) Effet spécial 
            if ($prodId == 3) {
                $stmt = $pdo->prepare("
                    UPDATE Banque 
                    SET quantite_xp = quantite_xp + 50 
                    WHERE id_utilisateur = :uid");
                $stmt->execute([':uid' => $userId]);
            }

            // 10) Commit
            $pdo->commit();

            // 11) Recharger banque + inventaire complet
            $stmt = $pdo->prepare("
                SELECT quantite_coins, quantite_xp 
                FROM Banque 
                WHERE id_utilisateur = :uid");
            $stmt->execute([':uid' => $userId]);
            $newBank = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = "
            SELECT p.id_produit,
                   p.titre,
                   p.description,
                   p.prix,
                   p.image,
                   CASE WHEN ip.id_produit IS NOT NULL THEN 1 ELSE 0 END AS owned
              FROM Produit p
         LEFT JOIN Inventaire_Produit ip
                ON p.id_produit    = ip.id_produit
               AND ip.id_inventaire = :inv
             WHERE p.id_boutique = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':inv' => $invId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 12) Réponse
            echo json_encode([
                'success' => true,
                'banque' => [
                    'quantite_coins' => (int) $newBank['quantite_coins'],
                    'quantite_xp' => (int) $newBank['quantite_xp']
                ],
                'items' => $items
            ]);
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}