<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class message
{
    public static function getMessage($convoID){
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($convoID) || empty($convoID) || !is_numeric($convoID) || $convoID <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de conversation invalide']);
            return;
        }

        $query = $pdo->prepare('SELECT * FROM Messages WHERE id_chat = :convoID');
        $query->bindParam(':convoID', $convoID);
        $query->execute();

        $messages = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public static function newMessage() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"));

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($data->texte) || empty($data->texte)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le texte du message est requis']);
            return;
        }
    
        if (!isset($data->id_chat) || !is_numeric($data->id_chat) || $data->id_chat <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID du chat invalide']);
            return;
        }
    
        if (!isset($data->id_utilisateur) || !is_numeric($data->id_utilisateur) || $data->id_utilisateur <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de l\'utilisateur invalide']);
            return;
        }

        $date = new DateTime();
        $dateParam = $date->format('Y-m-d');

        $queryMessage = "INSERT INTO Messages (texte, date_envoi, id_chat, id_utilisateur) 
                        VALUES (:texte, :date_envoi, :id_chat, :id_utilisateur)";
        $stmtMessages = $pdo->prepare($queryMessage);
        $stmtMessages->bindParam(':texte', $data->texte);
        $stmtMessages->bindParam(':date_envoi', $dateParam);
        $stmtMessages->bindParam(':id_chat', $data->id_chat);
        $stmtMessages->bindParam(':id_utilisateur', $data->id_utilisateur);
    
        if (!$stmtMessages->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l\'ajout du message']);
            return;
        }
    
        echo json_encode(['success' => 'Message créé avec succès']);
    }
}