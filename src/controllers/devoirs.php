<?php
   use Firebase\JWT\JWT;
   use Firebase\JWT\Key;
class devoirs
{
    public static function createDevoir() {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        $data = json_decode(file_get_contents("php://input"));
    
        if (!isset($data->id_utilisateur) || !isset($data->titre) || !isset($data->id_destinataire)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }
    
        try {
            $query = $pdo->prepare("
                INSERT INTO Devoir (id_utilisateur, titre, description, date_limite, id_destinataire)
                VALUES (:id_utilisateur, :titre, :description, :date_limite, :id_destinataire)
            ");
            $query->bindParam(':id_utilisateur', $data->id_utilisateur);
            $query->bindParam(':titre', $data->titre);
            $query->bindParam(':description', $data->description);
            $query->bindParam(':date_limite', $data->date_limite);
            $query->bindParam(':id_destinataire', $data->id_destinataire);
    
            if ($query->execute()) {
                echo json_encode(['success' => true, 'id_devoir' => $pdo->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => "Erreur lors de la création du devoir"]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => "Erreur serveur : " . $e->getMessage()]);
        }
    }
    public static function getDevoirs($userId) {
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
    
        $query = $pdo->prepare("SELECT * FROM Devoir WHERE id_utilisateur = :id_utilisateur");
        $query->bindParam(':id_utilisateur', $userId);

        if ($query->execute()) {
            $devoirs = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'devoirs' => $devoirs]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération des devoirs"]);
        }
    }

    public static function getDevoir($id){
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID invalide']);
            return;
        }

        try{
        $query = $pdo->prepare("SELECT * FROM Devoir WHERE id_devoir = :id_devoir");
        $query->bindParam(':id_devoir', $id);

        if ($query->execute()) {
            $devoirs = $query->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'devoirs' => $devoirs]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération du devoir"]);
        }
    }   catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération : " . $e->getMessage()]);
    }
}
public static function updateDevoir($id) {
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    if (!Controller::authentifier()) {
        return;
    }

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->titre) || !isset($data->description) || !isset($data->date_creation) ||
        !isset($data->date_limite) || !isset($data->statut)) {
        http_response_code(400);
        echo json_encode(['error' => 'Données manquantes']);
            return;
        }
        $query = $pdo->prepare("
            UPDATE Devoir
            SET titre = :titre,
                description = :description,
                date_creation = :date_creation,
                date_limite = :date_limite,
                statut = :statut
            WHERE id_devoir = :id_devoir
        ");
        $query->bindParam(':titre', $data->titre);
        $query->bindParam(':description', $data->description);
        $query->bindParam(':date_creation', $data->date_creation);
        $query->bindParam(':date_limite', $data->date_limite);
        $query->bindParam(':statut', $data->statut);
        $query->bindParam(':id_devoir', $id);

        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                echo json_encode(['success' => "Devoir mis à jour avec succès"]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => "Devoir non trouvé ou aucune modification apportée"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la mise à jour du devoir"]);
        }
    }

    public static function deleteDevoir($id) {
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

        $query = $pdo->prepare("DELETE FROM Devoir WHERE id_devoir = :id_devoir");
        $query->bindParam(':id_devoir', $id);

        if ($query->execute()) {
            if ($query->rowCount() > 0) {
                echo json_encode(['success' => "devoir supprimé avec succès"]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => "devoir non trouvé"]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la suppression du devoir"]);
        }
    }
    public static function getDevoirsEnvoyes($tuteurId) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        if (!is_numeric($tuteurId) || $tuteurId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID tuteur invalide']);
            return;
        }
    
        $query = $pdo->prepare("
            SELECT * FROM Devoir 
            WHERE id_utilisateur = :id_utilisateur
        ");
        $query->bindParam(':id_utilisateur', $tuteurId);
    
        if ($query->execute()) {
            $devoirs = $query->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'devoirs' => $devoirs]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => "Erreur lors de la récupération des devoirs"]);
        }
    }

    public static function shareDevoir($idDevoir) {
        global $pdo;
    
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
    
        if (!Controller::authentifier()) {
            return;
        }
    
        try {
            // Récupérer le devoir avec les infos du destinataire
            $query = $pdo->prepare("
                SELECT d.*, u.nom as nom_destinataire 
                FROM Devoir d
                JOIN Utilisateur u ON d.id_destinataire = u.id_utilisateur
                WHERE d.id_devoir = :id
            ");
            $query->bindParam(':id', $idDevoir);
            $query->execute();
            $devoir = $query->fetch(PDO::FETCH_ASSOC);
    
            if (!$devoir) {
                http_response_code(404);
                echo json_encode(['error' => 'Devoir non trouvé']);
                return;
            }
    
            // Vérifier que le destinataire existe
            $query = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE id_utilisateur = :destinataire");
            $query->bindParam(':destinataire', $devoir['id_destinataire']);
            $query->execute();
            
            if ($query->rowCount() === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Destinataire invalide']);
                return;
            }
    
            // Mettre à jour le statut du devoir
            $query = $pdo->prepare("UPDATE Devoir SET statut = 'envoyé' WHERE id_devoir = :id");
            $query->bindParam(':id', $idDevoir);
            $query->execute();
    
            echo json_encode(['success' => true, 'message' => 'Devoir envoyé avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => "Erreur serveur : " . $e->getMessage()]);
        }
    }

public static function getDevoirsRecus($eleveId) {
    global $pdo;

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=utf-8');

    if (!Controller::authentifier()) {
        return;
    }

    try {
        $query = $pdo->prepare("
            SELECT d.*, u.nom as nom_tuteur 
            FROM Devoir d
            JOIN Utilisateur u ON d.id_utilisateur = u.id_utilisateur
            WHERE d.id_destinataire = :eleveId
            ORDER BY d.date_limite ASC
        ");
        $query->bindParam(':eleveId', $eleveId);
        $query->execute();

        $devoirs = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'devoirs' => $devoirs]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => "Erreur serveur : " . $e->getMessage()]);
    }
}
}