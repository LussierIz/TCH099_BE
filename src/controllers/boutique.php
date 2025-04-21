<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class Boutique
{
    public static function getBoutiqueItems($userId) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        // if (!Controller::authentifier()) {
        //     return;
        // }

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
}