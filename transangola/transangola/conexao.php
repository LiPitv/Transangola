<?php
$host = 'localhost';
$dbname = 'TransAngola';
$username = 'root'; // Altere para seu usuário MySQL
$password = ''; // Altere para sua senha MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>