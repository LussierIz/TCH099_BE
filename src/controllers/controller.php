<?php
class Controller
{
    public static function login() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        $query = $pdo->prepare('SELECT * FROM Utilisateur WHERE email = :email');
        $query->bindParam(':email', $data->courriel);
        $query->execute();
    
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($data->mot_passe, $user['mot_de_passe'])) {   
            echo json_encode([
                'success' => true,
                'id' => $user['id_utilisateur'],
                'type' => $user['statut']
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
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'id' => $user['id_utilisateur'],
                        'statut' => $user['statut']
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
}