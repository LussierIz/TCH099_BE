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
        
        if (empty($data->titre) || empty($data->description) || empty($data->date) || empty($data->id_objectif) || empty($data->id_utilisateur)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }        
    
        $queryGetObjectif = $pdo->prepare('SELECT id_objectif, statut FROM Objectifs WHERE id_objectif = :id_objectif AND id_utilisateur = :id_utilisateur');
        $queryGetObjectif->bindParam(':id_objectif', $data->id_objectif);
        $queryGetObjectif->bindParam(':id_utilisateur', $data->id_utilisateur);
        $queryGetObjectif->execute();
        $objectif = $queryGetObjectif->fetch(PDO::FETCH_ASSOC);
        
        if (!$objectif) {
            http_response_code(404);
            echo json_encode(['error' => 'Objectif non trouvé']);
            return;
        }
        
        if ($objectif['statut'] === 'complété') {
            http_response_code(400);
            echo json_encode(['error' => 'Impossible d\'ajouter une tâche à un objectif complet']);
            return;
        }        

        $query = $pdo->prepare('INSERT INTO Taches (id_objectif, titre, description, date_fin) 
        VALUES (:id_objectif, :titre, :description, :date_fin)');
        $query->bindParam(':id_objectif', $data->id_objectif);
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
        
        if (!Controller::authentifier()) {
            return;
        }
    
        try {
            // Récupérer tous les objectifs de l'utilisateur
            $queryObjectifs = $pdo->prepare('
                SELECT id_objectif AS id, titre 
                FROM Objectifs 
                WHERE id_utilisateur = :id_utilisateur
            ');
            $queryObjectifs->bindParam(':id_utilisateur', $id_utilisateur);
            $queryObjectifs->execute();
            $objectifs = $queryObjectifs->fetchAll(PDO::FETCH_ASSOC);
    
            // Récupérer les tâches associées à ces objectifs
            $queryTaches = $pdo->prepare('
                SELECT t.*, o.titre AS objectif_titre 
                FROM Taches t
                LEFT JOIN Objectifs o ON t.id_objectif = o.id_objectif
                WHERE o.id_utilisateur = :id_utilisateur
            ');
            $queryTaches->bindParam(':id_utilisateur', $id_utilisateur);
            $queryTaches->execute();
            $taches = $queryTaches->fetchAll(PDO::FETCH_ASSOC);
    
            echo json_encode([
                'success' => true,
                'objectifs' => $objectifs,
                'taches' => $taches
            ]);
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur interne du serveur', 'message' => $e->getMessage()]);
        }
    } 

    public static function setStatut() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Lire les données JSON reçues
        $data = json_decode(file_get_contents("php://input"));
       
        if (!Controller::authentifier()) {
            return;
        }
    
        if (!isset($data->tache_id)) {
            http_response_code(400);
            echo json_encode(["error" => "ID de la tâche manquant."]);
            return;
        }
    
        $tacheId = $data->tache_id;
        $newStatut = 'complété';
    
        try {
            // Vérification si la tâche existe
            $stmtCheck = $pdo->prepare("SELECT 1 FROM Taches WHERE id_tache = :tache_id");
            $stmtCheck->bindParam(':tache_id', $tacheId);
            $stmtCheck->execute();
        
            if ($stmtCheck->rowCount() == 0) {
                echo json_encode(["error" => "La tâche avec l'ID spécifié n'existe pas."]);
                return;
            }
        
            // Mise à jour du statut
            $stmt = $pdo->prepare("UPDATE Taches SET statut = :statut WHERE id_tache = :tache_id");
            $stmt->bindParam(':tache_id', $tacheId);
            $stmt->bindParam(':statut', $newStatut);
            $stmt->execute();

            $stmtBanqueUpdate = $pdo->prepare("UPDATE Banque SET quantite_xp = quantite_xp + 1, quantite_coins = quantite_coins + 1 WHERE id_utilisateur = :id_utilisateur");
            $stmtBanqueUpdate->bindParam(':id_utilisateur', $data->id_utilisateur);
            $stmtBanqueUpdate->execute();
        
            echo json_encode(["success" => true, "message" => "Statut mis à jour en 'complété'."]);
        
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Erreur lors de la mise à jour du statut : " . $e->getMessage()]);
        }        
    }     
}