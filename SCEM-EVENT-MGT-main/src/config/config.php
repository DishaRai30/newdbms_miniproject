<?php
// config.php - Database configuration file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "SCEM_EVENT";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>