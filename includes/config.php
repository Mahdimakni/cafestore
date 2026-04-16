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
       $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);
        if ($conn->connect_error) {
            die("Erreur de connexion: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}
?>
