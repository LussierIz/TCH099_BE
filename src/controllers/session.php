<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    require_once './src/controllers/controller.php';
class session
{
    public static function addSession() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"));

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($data->user_id) || empty($data->user_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun ID d\'utilisateur']);
            return;
        }

        if (!isset($data->duree) || empty($data->duree)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune durée']);
            return;
        }

        if (!isset($data->date_debut) || empty($data->date_debut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune date de début']);
            return;
        }

        if (!isset($data->date_fin) || empty($data->date_fin)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucune date de fin']);
            return;
        }

        $duree = $data->duree;

        $pointGagner = floor($duree / 100);
        $pointGagner = intval($pointGagner);

        $querySession = $pdo->prepare('INSERT INTO SessionEtude (date_debut, date_fin, Duree, points_gagnes, id_utilisateur) 
                    VALUES (:date_debut, :date_fin, :Duree, :points_gagnes, :id_utilisateur)');
        $querySession->bindParam(':date_debut', $data->date_debut);
        $querySession->bindParam(':date_fin', $data->date_fin);
        $querySession->bindParam(':Duree', $data->duree);
        $querySession->bindParam(':points_gagnes', $pointGagner);
        $querySession->bindParam(':id_utilisateur', $data->user_id);

        if (!$querySession->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l’ajout de la SessionEtude']);
            return;
        }

        $queryBanque = $pdo->prepare('UPDATE Banque SET quantite_xp = quantite_xp + :quantite_xp 
                WHERE id_utilisateur = :id_utilisateur');

        $queryBanque->bindParam(':quantite_xp', $pointGagner);
        $queryBanque->bindParam(':id_utilisateur', $data->user_id);

        if (!$queryBanque->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l’update de la banque']);
            return;
        }

        echo json_encode([
            'success' => true,
            'points_gagnes' => $pointGagner,
            'message' => '+'.$pointGagner.' XP ajoutés avec succès !'
        ]);
    }

    public static function getNombreSession($id){
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
    
        $query = $pdo->prepare('SELECT COUNT(*) AS nombre_sessions FROM SessionEtude WHERE id_utilisateur = :id_utilisateur');
        $query->bindParam(':id_utilisateur', $id);
    
        if (!$query->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération du nombre de sessions']);
            return;
        }
    
        $result = $query->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['nombre_sessions' => $result['nombre_sessions']]);
    } 
}