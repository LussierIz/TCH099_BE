<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class note
{
    public static function saveNote() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        $data = json_decode(file_get_contents("php://input"));
    
        if(!Controller::authentifier()){
            return;
        }
    
        if (!isset($data->id_utilisateur)|| !isset($data->titre) || !isset($data->contenu)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }
    
        try {
            $query = $pdo->prepare("INSERT INTO Notes (titre, contenu, date_creation, id_utilisateur) VALUES (:titre, :contenu, NOW(), :id_utilisateur)");
            $query->bindParam(':titre', $data->titre);
            $query->bindParam(':contenu', $data->contenu);
            $query->bindParam(':id_utilisateur', $data->id_utilisateur);
    
            if ($query->execute()) {
                $id_note = $pdo->lastInsertId(); // Récupérer l'ID généré
                http_response_code(201);
                echo json_encode([
                    'success' => 'Note enregistrée avec succès',
                    'id_note' => $id_note
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement de la note']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur interne du serveur', 'message' => $e->getMessage()]);
        }
    }
    
    public static function getNotes($id_utilisateur) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
            return;
        }
    
        try {
            $query = $pdo->prepare("SELECT * FROM Notes WHERE id_utilisateur = :id_utilisateur ORDER BY date_creation DESC");
            $query->bindParam(':id_utilisateur', $id_utilisateur);
            $query->execute();
    
            $notes = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'notes' => $notes]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur interne du serveur', 'message' => $e->getMessage()]);
        }
    }
    public static function deleteNote($id_note) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }
    
        try {
          
            $query = $pdo->prepare("DELETE FROM Notes WHERE id_note = :id_note");
            $query->bindParam(':id_note', $id_note, PDO::PARAM_INT);
    
            if ($query->execute()) {
                echo json_encode(['success' => true, 'message' => 'Note supprimée']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec suppression']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur', 'message' => $e->getMessage()]);
        }
    }
    public static function updateNote($id_note) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }
    
        $data = json_decode(file_get_contents("php://input"));
    
        try {
            $query = $pdo->prepare("UPDATE Notes SET titre = :titre, contenu = :contenu WHERE id_note = :id_note");
            $query->bindParam(':titre', $data->titre);
            $query->bindParam(':contenu', $data->contenu);
            $query->bindParam(':id_note', $id_note, PDO::PARAM_INT);
    
            if ($query->execute()) {
                echo json_encode(['success' => true, 'message' => 'Note mise à jour']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec mise à jour']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur', 'message' => $e->getMessage()]);
        }
    }
    

}