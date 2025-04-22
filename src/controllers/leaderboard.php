<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Leaderboard
{
    public static function getWeeklyHours($userId)
    {
        global $pdo;

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        if (!Controller::authentifier()) {
            return;
        }

        // trouver tous les amis
        $sql = "
            (
                SELECT u.id_utilisateur, u.prenom, u.nom,
                       COALESCE(SUM(TIME_TO_SEC(s.Duree)) / 3600, 0) AS hours
                  FROM Amitie a
                  JOIN Utilisateur u ON (u.id_utilisateur = 
                       CASE 
                            WHEN a.id_utilisateur1 = :uid1 THEN a.id_utilisateur2 
                            ELSE a.id_utilisateur1 
                       END)
             LEFT JOIN SessionEtude s ON s.id_utilisateur = u.id_utilisateur 
                  AND s.date_debut >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                 WHERE (a.id_utilisateur1 = :uid2 OR a.id_utilisateur2 = :uid3)
                   AND a.statut = 'accepted'
                 GROUP BY u.id_utilisateur
            )
            UNION
            (
                SELECT u.id_utilisateur, u.prenom, u.nom,
                       COALESCE(SUM(TIME_TO_SEC(s.Duree)) / 3600, 0) AS hours
                  FROM Utilisateur u
             LEFT JOIN SessionEtude s ON s.id_utilisateur = u.id_utilisateur 
                  AND s.date_debut >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
                 WHERE u.id_utilisateur = :uid4
                 GROUP BY u.id_utilisateur
            )
            ORDER BY hours DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid1' => $userId,
            ':uid2' => $userId,
            ':uid3' => $userId,
            ':uid4' => $userId
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


        echo json_encode([
            'success' => true,
            'friends' => array_map(function ($f) use ($userId) {
                $f['hours'] = round($f['hours'], 1);
                $f['self'] = ($f['id_utilisateur'] == $userId);
                return $f;
            }, $results)
        ]);
    }

}