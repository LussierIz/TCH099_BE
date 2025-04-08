<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class stats
{
    public static function getStatistics($id) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($id) || empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun ID fourni']);
            return;
        }
    
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'ID invalide']);
            return;
        }
    
        $query = $pdo->prepare('SELECT * FROM SessionEtude WHERE id_utilisateur = :id_utilisateur');
        $query->bindParam(':id_utilisateur', $id);

        if (!$query->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l’ajout du participant']);
            return;
        }
        
        $stats = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($stats);
    } 
}