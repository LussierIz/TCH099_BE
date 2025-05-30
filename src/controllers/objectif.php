<?php
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
class objectif
{
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

    public static function getObjectifs($userId) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        if (!is_numeric($userId) || $userId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID utilisateur invalide']);
            return;
        }
    
        $query = $pdo->prepare("SELECT * FROM Objectifs WHERE id_utilisateur = :id_utilisateur");
        $query->bindParam(':id_utilisateur', $userId);

        if ($query->execute()) {
            $objectifs = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'objectifs' => $objectifs]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération des objectifs"]);
        }
    }

    public static function getObjectif($id) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        // Authentification
        if (!Controller::authentifier()) {
            return;
        }
    
        // Vérification de l'ID utilisateur
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => "ID invalide"]);
            return;
        }
    
        try {
            $query = $pdo->prepare("SELECT * FROM Objectifs WHERE id_objectif = :id_objectif");
            $query->bindParam(':id_objectif', $id);
            
            if($query->execute()){
                $objectifs = $query->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'objectifs' => $objectifs]);
            }    
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération : " . $e->getMessage()]);
        }
    }    

    public static function updateObjectif($id) {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->titre) || !isset($data->description) ||
            !isset($data->date_debut) || !isset($data->date_fin) || !isset($data->statut)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        $query = $pdo->prepare("
            UPDATE Objectifs 
            SET titre = :titre,
                description = :description,
                date_debut = :date_debut,
                date_fin = :date_fin,
                statut = :statut
            WHERE id_objectif = :id_objectif
        ");
        $query->bindParam(':titre', $data->titre);
        $query->bindParam(':description', $data->description);
        $query->bindParam(':date_debut', $data->date_debut);
        $query->bindParam(':date_fin', $data->date_fin);
        $query->bindParam(':statut', $data->statut);
        $query->bindParam(':id_objectif', $id);

        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                echo json_encode(['success' => "Objectif mis à jour avec succès"]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => "Objectif non trouvé ou aucune modification apportée"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la mise à jour de l'objectif"]);
        }
    }

    public static function deleteObjectif($id) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => "ID invalide"]);
            return;
        }
    
        // Supprimer les tâches liées à cet objectif
        $deleteTaches = $pdo->prepare("DELETE FROM Taches WHERE id_objectif = :id_objectif");
        $deleteTaches->bindParam(':id_objectif', $id);
        $deleteTaches->execute();
    
        // Ensuite, supprimer l'objectif
        $query = $pdo->prepare("DELETE FROM Objectifs WHERE id_objectif = :id_objectif");
        $query->bindParam(':id_objectif', $id);
    
        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                echo json_encode(['success' => "Objectif et tâches supprimés avec succès"]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => "Objectif non trouvé"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la suppression de l'objectif"]);
        }
    }  
    
    public static function completerObjectif($id) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => "ID invalide"]);
            return;
        }
    
        $check = $pdo->prepare("SELECT COUNT(*) FROM Taches WHERE id_objectif = :id_objectif AND statut != 'complété'");
        $check->bindParam(':id_objectif', $id);
        $check->execute();
        $incompletes = $check->fetchColumn();
    
        if ($incompletes > 0) {
            http_response_code(400);
            echo json_encode(['error' => "Toutes les tâches doivent être complétées avant de valider l'objectif."]);
            return;
        }
    
        $query = $pdo->prepare("UPDATE Objectifs SET statut = 'complété' WHERE id_objectif = :id_objectif");
        $query->bindParam(':id_objectif', $id);
    
        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                $taskCheck = $pdo->prepare("SELECT COUNT(*) FROM Taches WHERE id_objectif = :id_objectif");
                $taskCheck->bindParam(':id_objectif', $id);
                $taskCheck->execute();
                $taskCount = $taskCheck->fetchColumn();
    
                if ($taskCount > 0) {
                    $userQuery = $pdo->prepare("SELECT id_utilisateur FROM Objectifs WHERE id_objectif = :id_objectif");
                    $userQuery->bindParam(':id_objectif', $id);
                    $userQuery->execute();
                    $userData = $userQuery->fetch(PDO::FETCH_ASSOC);
                    $userId = $userData['id_utilisateur'];
    
                    $stmtBanqueUpdate = $pdo->prepare("UPDATE Banque SET quantite_xp = quantite_xp + 1, quantite_coins = quantite_coins + 3 WHERE id_utilisateur = :id_utilisateur");
                    $stmtBanqueUpdate->bindParam(':id_utilisateur', $userId);
                    $stmtBanqueUpdate->execute();
    
                    echo json_encode(['success' => "Objectif marqué comme complété et coins ajoutés"]);
                } else {
                    echo json_encode(['success' => "Objectif marqué comme complété, mais aucune tâche liée."]);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => "Objectif non trouvé"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la mise à jour"]);
        }
    }           
}