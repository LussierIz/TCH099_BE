<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class User
{
    public static function getUser($id) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => "ID invalide"]);
            return;
        }
    
        $query = $pdo->prepare("SELECT id_utilisateur, prenom, nom, email, statut FROM Utilisateur WHERE id_utilisateur = :id");
        $query->bindParam(':id', $id);
    
        if ($query->execute()) {
            $user = $query->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => "Utilisateur non trouvé"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération de l'utilisateur"]);
        }
    }
}