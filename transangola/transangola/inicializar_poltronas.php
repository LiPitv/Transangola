<?php
require_once 'conexao.php';

try {
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM Poltronas");
    $count = $stmt->fetchColumn();
    

    if ($count == 0) {
        $pdo->beginTransaction();
        
        for ($i = 1; $i <= 28; $i++) {
            $numero = str_pad($i, 2, '0', STR_PAD_LEFT); // Formata para 01, 02, etc.
            $stmt = $pdo->prepare("INSERT INTO Poltronas (numero_poltrona, status) VALUES (?, 'disponível')");
            $stmt->execute([$numero]);
        }
        
        $pdo->commit();
        echo "28 poltronas foram inicializadas com sucesso!";
    } else {
        echo "As poltronas já foram inicializadas. Total: $count";
    }
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "Erro ao inicializar poltronas: " . $e->getMessage();
}
?>