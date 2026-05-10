<?php
// ============================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Modifier selon votre config
define('DB_PASS', '');             // Modifier selon votre config
define('DB_NAME', 'cafestore');

define('SITE_NAME', 'CaféStore');
define('SITE_URL', 'http://localhost/cafestore');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME . ";charset=utf8mb4";
            $conn = new PDO($dsn, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion: " . $e->getMessage());
        }
    }
    return $conn;
}
?>
