<?php
class Controller
{
    public static function login() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Lire le JSON envoyé dans la requête POST
        $data = json_decode(file_get_contents("php://input"));
    
        // Requête pour vérifier si l'utilisateur existe dans la base de données
        $query = $pdo->prepare('SELECT * FROM Client WHERE courriel = :courriel');
        $query->bindParam(':courriel', $data->courriel);
        $query->execute();
    
        $user = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($user && password_verify($data->mot_passe, $user['mot_passe'])) {   
            // Réponse réussie
            echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
        } else {
            // Échec de la connexion
            echo json_encode(['error' => 'Identifiants incorrects']);
        }
    } 
    
    public static function register() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Récupérer les données envoyées dans la requête (en JSON)
        $data = json_decode(file_get_contents("php://input"));
    
        // Vérifier si les données nécessaires sont présentes
        if (!isset($data->courriel) || !isset($data->mot_passe) || !isset($data->username) || !isset($data->prenom) || !isset($data->nom) || !isset($data->type)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }
    
        try {
            // Vérifier si le courriel est déjà utilisé
            $query = "SELECT * FROM Client WHERE courriel = :courriel";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':courriel', $data->courriel);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ce courriel est déjà utilisé']);
                return;
            }
    
            // Vérifier si le nom d'utilisateur est déjà utilisé
            $query = "SELECT * FROM Client WHERE username = :username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $data->username);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ce nom d\'utilisateur est déjà utilisé']);
                return;
            }
    
            // Hacher le mot de passe
            $hashedPassword = password_hash($data->mot_passe, PASSWORD_DEFAULT);
    
            // Insérer l'utilisateur dans la base de données
            $query = "INSERT INTO Client (courriel, mot_passe, username, prenom, nom, type) 
                      VALUES (:courriel, :mot_passe, :username, :prenom, :nom, :type)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':courriel', $data->courriel);
            $stmt->bindParam(':mot_passe', $hashedPassword);
            $stmt->bindParam(':username', $data->username);
            $stmt->bindParam(':prenom', $data->prenom);
            $stmt->bindParam(':nom', $data->nom);
            $stmt->bindParam(':type', $data->type); // Assurez-vous que le type est valide ('étudiant', 'tuteur', 'admin')
    
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(['success' => 'Utilisateur enregistré avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement']);
            }
        } catch (PDOException $e) {
            // Gérer l'erreur PDO et retourner une réponse détaillée
            http_response_code(500);
            echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
        }
    }   
}