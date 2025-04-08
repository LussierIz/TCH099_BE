<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class convo
{
    public static function getConvo($id) {
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
    
        $query = $pdo->prepare('SELECT id_chat FROM Participant WHERE id_utilisateur = :id_utilisateur');
        $query->bindParam(':id_utilisateur', $id);
        $query->execute();
    
        $convoIds = $query->fetchAll(PDO::FETCH_COLUMN);
    
        $userChats = [];
    
        foreach ($convoIds as $convoId) {
            $queryChat = $pdo->prepare("SELECT * FROM Chat WHERE id_chat = :id_chat");
            $queryChat->bindParam(':id_chat', $convoId);
            $queryChat->execute();
    
            $chats = $queryChat->fetchAll(PDO::FETCH_ASSOC);
    
            if ($chats) {
                $userChats[] = $chats;
            }
        }
    
        echo json_encode([
            'success' => true,
            'conversations' => $userChats
        ]);
    }
    
    public static function newConvo() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));

        if(!Controller::authentifier()){
            return;
        }
    
        if (!isset($data->id_utilisateur) || empty($data->id_utilisateur) || !is_numeric($data->id_utilisateur) || $data->id_utilisateur <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID utilisateur invalide']);
            return;
        }
    
        if (!isset($data->id_invite) || empty($data->id_invite) || !is_numeric($data->id_invite) || $data->id_invite <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invité invalide']);
            return;
        }
    
        $queryCheck = $pdo->prepare('SELECT * FROM Utilisateur WHERE id_utilisateur = :id_utilisateur');
        $queryCheck->bindParam(':id_utilisateur', $data->id_invite);
        $queryCheck->execute();
        
        if (!$queryCheck->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(422);
            echo json_encode(['error' => 'ID invité non valide']);
            return;
        }

        $date = new DateTime();
        $dateParam = $date->format('Y-m-d');
    
        $queryChat = "INSERT INTO Chat (date, chat_name) VALUES (:date, :chat_name)";
        $stmtChat = $pdo->prepare($queryChat);
        $stmtChat->bindParam(':date', $dateParam);
        $stmtChat->bindParam(':chat_name', $data->chat_name);
    
        if ($stmtChat->execute()) {
            $lastInsertIdChat = $pdo->lastInsertId();
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création du chat']);
            return;
        }
    
        $queryParticipant = "INSERT INTO Participant (id_chat, id_utilisateur) VALUES (:id_chat, :id_utilisateur)";
        $stmtParticipant = $pdo->prepare($queryParticipant);
        $stmtParticipant->bindParam(':id_chat', $lastInsertIdChat);
        $stmtParticipant->bindParam(':id_utilisateur', $data->id_utilisateur);
    
        if (!$stmtParticipant->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l’ajout du participant']);
            return;
        }
    
        $queryInviter = "INSERT INTO Participant (id_chat, id_utilisateur) VALUES (:id_chat, :id_invite)";
        $stmtInviter = $pdo->prepare($queryInviter);
        $stmtInviter->bindParam(':id_chat', $lastInsertIdChat);
        $stmtInviter->bindParam(':id_invite', $data->id_invite);
    
        if (!$stmtInviter->execute()) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de l’ajout de l’invité']);
            return;
        }
    
        echo json_encode(['success' => 'Conversation créée avec succès', 'chat_id' => $lastInsertIdChat]);
    } 
}