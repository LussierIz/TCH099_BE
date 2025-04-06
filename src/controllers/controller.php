<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class Controller
{
    public static function login() {
        global $pdo;
        global $API_SECRET;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        $query = $pdo->prepare('SELECT * FROM Utilisateur WHERE email = :email');
        $query->bindParam(':email', $data->courriel);
        $query->execute();
    
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($data->mot_passe, $user['mot_de_passe'])) {   
            $payload = [
                "iss" => "http://equipeF.tch099.ovh",
                "aud" => "http://equipeF.tch099.ovh", 
                "iat" => time(),
                "exp" => time() + 3600,
                "user_id" => $user['id_utilisateur']
                 ];
                $jwt = JWT::encode($payload, $API_SECRET, 'HS256');

            $response['message'] = "Authentification réussie";
            $response['token'] = $jwt;
            $response['id'] = $user['id_utilisateur'];

            http_response_code(200);
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Identifiants incorrects']);
        }
    }     
    
    public static function register() {
        global $pdo;
        global $API_SECRET;

    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        if (!isset($data->email) || !isset($data->mot_de_passe) || !isset($data->prenom) || !isset($data->nom) || !isset($data->statut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }
    
        try {
            // Vérification de l'email existant
            $query = "SELECT * FROM Utilisateur WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ce courriel est déjà utilisé']);
                return;
            }
    
            // Hachage du mot de passe
            $hashedPassword = password_hash($data->mot_de_passe, PASSWORD_DEFAULT);
    
            // Récupérer la date d'aujourd'hui
            $date = new DateTime();
            $dateParam = $date->format('Y-m-d');
    
            // Insertion de l'utilisateur dans la base de données
            $query = "INSERT INTO Utilisateur (email, mot_de_passe, prenom, nom, date_inscription, statut) 
                      VALUES (:email, :mot_de_passe, :prenom, :nom, :date_inscription, :statut)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':mot_de_passe', $hashedPassword);
            $stmt->bindParam(':prenom', $data->prenom);
            $stmt->bindParam(':nom', $data->nom);
            $stmt->bindParam(':date_inscription', $dateParam);
            $stmt->bindParam(':statut', $data->statut);
    
            // Exécution de l'insertion
            if ($stmt->execute()) {
                $userId = $pdo->lastInsertId();
    
                // Récupérer les informations de l'utilisateur après l'insertion
                $query = "SELECT id_utilisateur, statut FROM Utilisateur WHERE id_utilisateur = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    $payload = [
                        "iss" => "http://equipeF.tch099.ovh",
                        "aud" => "http://equipeF.tch099.ovh", 
                        "iat" => time(),
                        "exp" => time() + 3600,
                        "user_id" => $user['id_utilisateur']
                         ];
                        $jwt = JWT::encode($payload, $API_SECRET, 'HS256');
        
                    $response['message'] = "Authentification réussie";
                    $response['token'] = $jwt;
                    $response['id'] = $userId;
        
                    http_response_code(200);
                    echo json_encode($response);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Utilisateur créé, mais impossible de récupérer les informations.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
        }
    }   
    
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

    public static function authentifier() {
        global $API_SECRET;

        $headers = getallheaders();

        $jwt = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

        try {
            $decoded = JWT::decode($jwt, new Key($API_SECRET, 'HS256'));
            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token expiré!']);
            return false;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Erreur de token']);
            return false;
        }
    }

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

    public static function createObjectif() {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->id_utilisateur) || !isset($data->titre) || !isset($data->description) ||
            !isset($data->date_debut) || !isset($data->date_fin) || !isset($data->statut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        $query = $pdo->prepare("
            INSERT INTO Objectifs (id_utilisateur, titre, description, date_debut, date_fin, statut)
            VALUES (:id_utilisateur, :titre, :description, :date_debut, :date_fin, :statut)
        ");
        $query->bindParam(':id_utilisateur', $data->id_utilisateur);
        $query->bindParam(':titre', $data->titre);
        $query->bindParam(':description', $data->description);
        $query->bindParam(':date_debut', $data->date_debut);
        $query->bindParam(':date_fin', $data->date_fin);
        $query->bindParam(':statut', $data->statut);

        if ($query->execute()) {
            $id_objectif = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode([
                'success' => "Objectif créé avec succès",
                'id_objectif' => $id_objectif
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la création de l'objectif"]);
        }
    }

    
}