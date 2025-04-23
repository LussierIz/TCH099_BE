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
    
        if ($user) {
            if (password_verify($data->mot_passe, $user['mot_de_passe'])) {
                // Générer le token et envoyer la réponse
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
                $response['statut'] = $user['statut'];
        
                http_response_code(200);
                echo json_encode($response);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Mot de passe incorrect']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Utilisateur introuvable']);
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
    
        $mdp = $data->mot_de_passe;
    
        // Validation du mot de passe
        if (
            strlen($mdp) < 8 ||
            preg_match_all('/\d/', $mdp) < 4 ||
            !preg_match('/[\W_]/', $mdp)
        ) {
            $message = "Le mot de passe doit contenir au moins 8 caractères, 4 chiffres et 1 caractère spécial.";
            http_response_code(400);
            echo json_encode(['error' => $message]);
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
            $hashedPassword = password_hash($mdp, PASSWORD_DEFAULT);
    
            // Date actuelle
            $date = new DateTime();
            $dateParam = $date->format('Y-m-d');
    
            // Insertion de l'utilisateur
            $query = "INSERT INTO Utilisateur (email, mot_de_passe, prenom, nom, date_inscription, statut) 
                      VALUES (:email, :mot_de_passe, :prenom, :nom, :date_inscription, :statut)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':mot_de_passe', $hashedPassword);
            $stmt->bindParam(':prenom', $data->prenom);
            $stmt->bindParam(':nom', $data->nom);
            $stmt->bindParam(':date_inscription', $dateParam);
            $stmt->bindParam(':statut', $data->statut);
    
            if ($stmt->execute()) {
                $userId = $pdo->lastInsertId();
                $zeroValue = 0;
    
                $queryBanque = "INSERT INTO Banque (quantite_xp, quantite_coins, id_utilisateur) 
                                VALUES (:quantite_xp, :quantite_coins, :id_utilisateur)";
                $stmtBanque = $pdo->prepare($queryBanque);
                $stmtBanque->bindParam(':quantite_xp', $zeroValue);
                $stmtBanque->bindParam(':quantite_coins', $zeroValue);
                $stmtBanque->bindParam(':id_utilisateur', $userId);
    
                if (!$stmtBanque->execute()) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Erreur lors de l\'enregistrement (au niveau de la banque)']);
                    return;
                }
    
                $payload = [
                    "iss" => "http://equipeF.tch099.ovh",
                    "aud" => "http://equipeF.tch099.ovh", 
                    "iat" => time(),
                    "exp" => time() + 3600,
                    "user_id" => $userId
                ];
    
                $jwt = JWT::encode($payload, $API_SECRET, 'HS256');
    
                echo json_encode([
                    'message' => "Authentification réussie",
                    'token' => $jwt,
                    'id' => $userId,
                    'statut' => $data->statut
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
        }
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
}