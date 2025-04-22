<?php
   use Firebase\JWT\JWT;
   use Firebase\JWT\Key;

class citations
{
    public static function getRandomQuote() {
        global $pdo;
        
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Requête pour une citation aléatoire
            $query = $pdo->query("SELECT id_citation as id, texte as text FROM Citation ORDER BY RAND() LIMIT 1");
            $quote = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($quote) {
                echo json_encode([
                    'success' => true,
                    'quote' => $quote
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Aucune citation disponible'
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur de base de données'
            ]);
        }
    }
}