<?php
    session_start();

    $host = parse_url($_SERVER["HTTP_HOST"], PHP_URL_HOST);
    if($host=="localhost"){
        //Code d'accès à la base de données locale
        $host = 'db';
        $db = 'mydatabase';
        $user = 'user';
        $pass = 'password';
    } else {
        //Codes d'accès à la base de données de production
        $host = 'localhost';
        $db = 'equipeXXX';
        $user = 'equipeXXX';
        $pass = 'VOTRE_MOT_DE_PASSE';
    }

    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
?>