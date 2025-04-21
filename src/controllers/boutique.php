<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class Boutique
{
    public static function getBoutiqueItems($userId) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        // 1) recuperation de l'id inventaire lie a l'utilisateur
        $stmt = $pdo->prepare("SELECT id_inventaire FROM Inventaire WHERE id_utilisateur = :uid");
        $stmt->execute([':uid'=>$userId]);
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
        $stmt->execute([':inv'=>$invId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success'=>true, 'items'=>$items]); 
    }

    public static function buyItem() {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        // 1) recuperation les donnees du front
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id_utilisateur) || !isset($data->id_produit)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes (id_utilisateur, id_produit)']);
            return;
        }
        $userId  = (int)$data->id_utilisateur;
        $prodId  = (int)$data->id_produit;

        // 2) verifier le solde de l'utilisateur
        $stmt = $pdo->prepare("SELECT quantite_coins FROM Banque WHERE id_utilisateur = :uid");
        $stmt->execute([':uid' => $userId]);
        $coins = (int)$stmt->fetchColumn();
        if ($coins === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Compte bancaire introuvable']);
            return;
        }

        // 3) Recuperer le prix du produit
        $stmt = $pdo->prepare("SELECT prix FROM Produit WHERE id_produit = :pid");
        $stmt->execute([':pid' => $prodId]);
        $prix = $stmt->fetchColumn();
        if ($prix === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Produit introuvable']);
            return;
        }

        // 4) verifier que l'utilisateur a assez de coins
        if ($coins < $prix) {
            http_response_code(400);
            echo json_encode(['error' => 'Solde insuffisant']);
            return;
        }

        // 5) debiter de la banque
        $stmt = $pdo->prepare("
        UPDATE Banque 
           SET quantite_coins = quantite_coins - :prix 
         WHERE id_utilisateur    = :uid
        ");
        $stmt->execute([
        ':prix' => $prix,
        ':uid'  => $userId
        ]);

        // 6) s'assurer que l'inventaire existe
        $stmt = $pdo->prepare("SELECT id_inventaire FROM Inventaire WHERE id_utilisateur = :uid");
        $stmt->execute([':uid' => $userId]);
        $invId = $stmt->fetchColumn();
        if (!$invId) {
            $stmt = $pdo->prepare("INSERT INTO Inventaire (id_utilisateur) VALUES (:uid)");
            $stmt->execute([':uid' => $userId]);
            $invId = $pdo->lastInsertId();
        }

        // 7) enregistrer l'achat
        $stmt = $pdo->prepare("
            INSERT INTO Inventaire_Produit (id_inventaire, id_produit)
            VALUES (:inv, :pid)
        ");
        $stmt->execute([
            ':inv' => $invId,
            ':pid' => $prodId
        ]);

        // 8) Répondre
        $newBalance = $coins - $prix;
        echo json_encode([
            'success'    => 'Achat réussi',
            'newBalance' => $newBalance
        ]);
    }
}