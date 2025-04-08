<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class friend
{
    public static function sendFriendRequest() {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"));

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($data->senderId) || !isset($data->receiverId)) {
            http_response_code(400);
            echo json_encode(['error' => 'Pas de ID du recevoir ou de lenvoyeur']);
            return;
        }

        if ($data->senderId == $data->receiverId) {
            http_response_code(400);
            echo json_encode(['error' => 'Ne pas envoyer une requete a soi-meme']);
            return;
        }

        $query = $pdo->prepare("SELECT * FROM Amitie 
        WHERE ((id_utilisateur1 = :senderId1 AND id_utilisateur2 = :receiverId1)
        OR (id_utilisateur1 = :receiverId2 AND id_utilisateur2 = :senderId2))
        AND statut IN ('pending', 'accepted')");

        $query->bindParam(':senderId1', $data->senderId);
        $query->bindParam(':receiverId1', $data->receiverId);
        $query->bindParam(':receiverId2', $data->receiverId);
        $query->bindParam(':senderId2', $data->senderId);
        $query->execute(); 

        $existing = $query->fetchAll(PDO::FETCH_ASSOC);
        if (count($existing) > 0) {
        foreach ($existing as $relation) {
            if ($relation['statut'] == 'accepted') {
                http_response_code(400);
                echo json_encode(['error' => 'Vous êtes déjà amis']);
                return;
            }
        }
        
        http_response_code(400);
        echo json_encode(['error' => 'Demande d\'amis déjà en attente']);
        return;
        }

        $stmt = $pdo->prepare("INSERT INTO Amitie (date_debut_amitie, statut, id_utilisateur1, id_utilisateur2) VALUES (NULL, 'pending', :senderId, :receiverId)");
        $stmt->bindParam(':senderId', $data->senderId);
        $stmt->bindParam(':receiverId', $data->receiverId);
    
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['success' => 'Demande damis envoyé']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Echec de lenvoi de la demande damis']);
        }
    }

    public static function getFriendRequests($userId) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if(!Controller::authentifier()){
            return;
        }

        if (!is_numeric($userId) || $userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid user ID']);
            return;
        }
    
        // Get all requetes damis
        $query = $pdo->prepare("SELECT * FROM Amitie WHERE id_utilisateur2 = :userId AND statut = 'pending'");
        $query->bindParam(':userId', $userId);
        $query->execute();
        $requests = $query->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'requests' => $requests]);
    }

    public static function updateFriendRequest($requestId) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));

        if(!Controller::authentifier()){
            return;
        }
    
        if (!isset($data->action)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing action (accept/decline)']);
            return;
        }
    
        $action = $data->action;
        if ($action !== 'accept' && $action !== 'decline') {
            http_response_code(400);
            echo json_encode(['error' => 'Action invalide. Use "accept" or "decline"']);
            return;
        }
    
        if ($action === 'accept') {
            // If accepte, update statut to "accepted" and set the start date
            $query = $pdo->prepare("UPDATE Amitie SET statut = 'accepted', date_debut_amitie = CURDATE() WHERE id_amitie = :requestId");
        } else {
            // sinon declined
            $query = $pdo->prepare("UPDATE Amitie SET statut = 'declined' WHERE id_amitie = :requestId");
        }
    
        $query->bindParam(':requestId', $requestId);
    
        if ($query->execute()) {
            echo json_encode(['success' => "Friend request $action" . "ed"]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update friend request']);
        }
    }

    public static function getFriendList($userId) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if(!Controller::authentifier()){
            return;
        }

        if (!isset($userId) || !is_numeric($userId) || $userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid user ID']);
            return;
        }

        $query = $pdo->prepare("
        SELECT 
            u.id_utilisateur, 
            u.email, 
            u.prenom, 
            u.nom, 
            u.date_inscription, 
            u.statut
        FROM Amitie a
        JOIN Utilisateur u 
            ON (
                (a.id_utilisateur1 = :userId1 AND a.id_utilisateur2 = u.id_utilisateur)
                OR 
                (a.id_utilisateur2 = :userId2 AND a.id_utilisateur1 = u.id_utilisateur)
            )
        WHERE a.statut = 'accepted'
        ");
        $query->bindParam(':userId1', $userId);
        $query->bindParam(':userId2', $userId);
        $query->execute();
        $friends = $query->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['success' => true, 'friends' => $friends]);
    } 
}