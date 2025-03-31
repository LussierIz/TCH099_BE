<?php
class Controller
{
    public static function login() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        $query = $pdo->prepare('SELECT * FROM Client WHERE courriel = :courriel');
        $query->bindParam(':courriel', $data->courriel);
        $query->execute();
    
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($data->mot_passe, $user['mot_passe'])) {   
            echo json_encode([
                'success' => true,
                'id' => $user['id_client'],
                'username' => $user['username'],
                'type' => $user['type']
            ]);
        } else {
            echo json_encode(['error' => 'Identifiants incorrects']);
        }
    } 
    
    public static function register() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        if (!isset($data->courriel) || !isset($data->mot_passe) || !isset($data->username) || !isset($data->prenom) || !isset($data->nom) || !isset($data->type)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }
    
        try {
            $query = "SELECT * FROM Client WHERE courriel = :courriel";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':courriel', $data->courriel);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ce courriel est déjà utilisé']);
                return;
            }
    
            $query = "SELECT * FROM Client WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $data->username);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ce nom d\'utilisateur est déjà utilisé']);
                return;
            }
    
            $hashedPassword = password_hash($data->mot_passe, PASSWORD_DEFAULT);
    
            $query = "INSERT INTO Client (courriel, mot_passe, username, prenom, nom, type) 
                      VALUES (:courriel, :mot_passe, :username, :prenom, :nom, :type)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':courriel', $data->courriel);
            $stmt->bindParam(':mot_passe', $hashedPassword);
            $stmt->bindParam(':username', $data->username);
            $stmt->bindParam(':prenom', $data->prenom);
            $stmt->bindParam(':nom', $data->nom);
            $stmt->bindParam(':type', $data->type);
    
            if ($stmt->execute()) {
                $userId = $pdo->lastInsertId();
    
                $query = "SELECT id_client, username, type FROM Client WHERE id_client = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'id' => $user['id_client'],
                        'username' => $user['username'],
                        'type' => $user['type']
                    ]);
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
    
        $query = $pdo->prepare('SELECT id_chat FROM Participant WHERE id_client = :id_client');
        $query->bindParam(':id_client', $id);
        $query->execute();
    
        $convoIds = $query->fetchAll(PDO::FETCH_COLUMN);
    
        if (empty($convoIds)) {
            http_response_code(404);
            echo json_encode(['error' => 'Aucune conversation trouvée pour cet utilisateur']);
            return;
        }
    
        $userChats = [];
    
        foreach ($convoIds as $convoId) {
            $queryChat = $pdo->prepare("SELECT * FROM Chat WHERE id_convo = :id_convo");
            $queryChat->bindParam(':id_convo', $convoId);
            $queryChat->execute();
    
            $chats = $queryChat->fetchAll(PDO::FETCH_ASSOC);
    
            if ($chats) {
                $userChats[] = $chats;
            }
        }
    
        if (!empty($userChats)) {
            echo json_encode([
                'success' => true,
                'conversations' => $userChats
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de récupérer les conversations.']);
        }
    }             
}