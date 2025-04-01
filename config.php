<?php
    session_start();

    require_once __DIR__."/src/vendor/JWTExceptionWithPayloadInterface.php";
    require_once __DIR__."/src/vendor/BeforeValidException.php";
    require_once __DIR__."/src/vendor/CachedKeySet.php";
    require_once __DIR__."/src/vendor/ExpiredException.php";
    require_once __DIR__."/src/vendor/JWK.php";
    require_once __DIR__."/src/vendor/JWT.php";
    require_once __DIR__."/src/vendor/Key.php";
    require_once __DIR__."/src/vendor/SignatureInvalidException.php";
    
    global $API_SECRET;
    $API_SECRET = "ma_cle_super_secrete";

    // Configuration et connexion à la base de données
    $host = 'db';
    $db = 'mydatabase';
    $user = 'user';
    $pass = 'password';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        // Création d'une nouvelle instance de PDO pour la connexion à la base de données
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        // En cas d'erreur de connexion, afficher un message d'erreur et arrêter l'exécution du script
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
?>