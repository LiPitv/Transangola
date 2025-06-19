<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Concluída - TransAngola</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            color: #2ecc71;
            font-size: 64px;
            margin-bottom: 20px;
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .details {
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
            text-align: left;
        }

        .btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Reserva Concluída com Sucesso!</h1>
        
        <p>Sua reserva foi processada com sucesso. Os detalhes foram enviados para o e-mail cadastrado.</p>
        
        <div class="details">
            <h2>Detalhes da Reserva:</h2>
            <?php if(isset($_SESSION['reserva_info'])): ?>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION['reserva_info']['nome']); ?></p>
                <p><strong>Poltronas:</strong> <?php echo htmlspecialchars($_SESSION['reserva_info']['poltronas']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['reserva_info']['email']); ?></p>
            <?php else: ?>
                <p>Informações da reserva não disponíveis.</p>
            <?php endif; ?>
        </div>
        
        <a href="Lugar.php" class="btn">Voltar para a Página Inicial</a>
    </div>
</body>
</html>