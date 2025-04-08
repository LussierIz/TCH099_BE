<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class taches
{
    public static function addTache() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        
        if (!Controller::authentifier()) {
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->titre) || empty($data->description) || empty($data->date) || empty($data->titre_objectif) || empty($data->id_utilisateur)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }        
    
        $queryGetObjectif = $pdo->prepare('SELECT id_objectif FROM Objectifs WHERE titre = :titre AND id_utilisateur = :id_utilisateur LIMIT 1');
        $queryGetObjectif->bindParam(':titre', $data->titre_objectif);
        $queryGetObjectif->bindParam(':id_utilisateur', $data->id_utilisateur);
        $queryGetObjectif->execute();
        $idObjectif = $queryGetObjectif->fetch(PDO::FETCH_ASSOC);
    
        if (!$idObjectif) {
            http_response_code(404);
            echo json_encode(['error' => 'Objectif non trouvé']);
            return;
        }
    
        $idObjectif = $idObjectif['id_objectif'];
    
        $query = $pdo->prepare('INSERT INTO Taches (id_objectif, titre, description, date_fin) 
        VALUES (:id_objectif, :titre, :description, :date_fin)');
        $query->bindParam(':id_objectif', $idObjectif);
        $query->bindParam(':titre', $data->titre);
        $query->bindParam(':description', $data->description);
        $query->bindParam(':date_fin', $data->date);
        
        if ($query->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tâche créée avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création de la tâche']);
        }
    }
    
    public static function getTaches($id_utilisateur) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Vérification de l'authentification de l'utilisateur
        if (!Controller::authentifier()) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
            return;
        }
    
        try {
            $queryTaches = $pdo->prepare('SELECT * FROM Taches WHERE id_objectif IN 
            (SELECT id_objectif FROM Objectifs WHERE id_utilisateur = :id_utilisateur)');
            $queryTaches->bindParam(':id_utilisateur', $id_utilisateur);
            $queryTaches->execute();
            $taches = $queryTaches->fetchAll(PDO::FETCH_ASSOC);

            if (empty($taches)) {
                echo json_encode(['success' => false, 'message' => 'Aucune tâche trouvée pour cet utilisateur']);
                return;
            }

            echo json_encode(['success' => true, 'taches' => $taches]);
    
        } catch (Exception $e) {
            // Gestion des erreurs
            http_response_code(500);
            echo json_encode(['error' => 'Erreur interne du serveur', 'message' => $e->getMessage()]);
        }
    }
}