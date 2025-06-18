<?php
require_once 'conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        if (!isset($_FILES['comprovativo']) || $_FILES['comprovativo']['error'] == UPLOAD_ERR_NO_FILE) {
            throw new Exception("Nenhum arquivo foi enviado.");
        }
        

        if ($_FILES['comprovativo']['error'] !== UPLOAD_ERR_OK) {
            switch ($_FILES['comprovativo']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception("O arquivo enviado excede o tamanho máximo permitido.");
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception("O upload do arquivo foi feito parcialmente.");
                default:
                    throw new Exception("Erro no upload do arquivo: " . $_FILES['comprovativo']['error']);
            }
        }
        

        $pdo->beginTransaction();
        

        $selectedSeats = json_decode($_POST['selectedSeats'], true);
        
        if (empty($selectedSeats)) {
            throw new Exception("Nenhuma poltrona selecionada.");
        }
        

        $_SESSION['reserva_info'] = [
            'nome' => $_POST['nomeCompleto'],
            'poltronas' => implode(', ', $selectedSeats),
            'email' => $_POST['email']
        ];
        
  
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Não foi possível criar o diretório de uploads.");
            }
        }
        
       
        if (!is_writable($uploadDir)) {
            throw new Exception("O diretório de uploads não tem permissão de escrita.");
        }
        
     
        $fileName = uniqid() . '_' . basename($_FILES['comprovativo']['name']);
        $targetPath = $uploadDir . $fileName;
        
    
        if (!move_uploaded_file($_FILES['comprovativo']['tmp_name'], $targetPath)) {
            throw new Exception("Falha ao mover o arquivo para o diretório de uploads.");
        }
        

        foreach ($selectedSeats as $seatNumber) {
          
            $stmt = $pdo->prepare("SELECT id FROM Poltronas WHERE numero_poltrona = ?");
            $stmt->execute([$seatNumber]);
            $poltrona = $stmt->fetch(PDO::FETCH_ASSOC);
            
          
            if (!$poltrona) {
                $stmt = $pdo->prepare("INSERT INTO Poltronas (numero_poltrona, status) VALUES (?, 'comprado')");
                $stmt->execute([$seatNumber]);
                
       
                $poltrona_id = $pdo->lastInsertId();
            } else {
     
                $stmt = $pdo->prepare("UPDATE Poltronas SET status = 'comprado' WHERE id = ?");
                $stmt->execute([$poltrona['id']]);
                $poltrona_id = $poltrona['id'];
            }
            
        
            $stmt = $pdo->prepare("INSERT INTO Passageiros 
                (nome_completo, tipo_documento, numero_documento, email, telefone, celular, data_nascimento, comprovativo_transferencia, poltrona_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_POST['nomeCompleto'],
                ($_POST['documento'] === 'BI' ? 'BI' : 'Passaporte'),
                $_POST['numeroDocumento'],
                $_POST['email'],
                '+244' . $_POST['telefone'],
                !empty($_POST['celular']) ? '+244' . $_POST['celular'] : null,
                $_POST['dataNascimento'],
                $targetPath,
                $poltrona_id
            ]);
        }
        

        $pdo->commit();
        
     
        echo '<script>localStorage.removeItem("selectedSeats");</script>';
        
 
        header('Location: sucesso.php');
        exit();
        
    } catch (Exception $e) {
   
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errorMessage = "Erro ao processar reserva: " . $e->getMessage();
    }
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações Pessoais - TransAngola</title>
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
        }

        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        h2 {
            color: #3498db;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        input[type="file"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .input-prefix {
            display: flex;
            align-items: center;
        }

        .prefix {
            background-color: #eee;
            padding: 10px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }

        .prefix-input {
            flex: 1;
            border-radius: 0 4px 4px 0;
        }

        .checkbox-group {
            margin-top: 10px;
        }

        .checkbox-item {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .checkbox-item input {
            margin-right: 10px;
        }

        .file-upload {
            display: flex;
            flex-direction: column;
        }

        .file-upload-label {
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px dashed #ddd;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-label:hover {
            background-color: #e9ecef;
            border-color: #3498db;
        }

        .file-name {
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }

        hr {
            border: 0;
            height: 1px;
            background-color: #ddd;
            margin: 30px 0;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-previous {
            background-color: #95a5a6;
            color: white;
        }

        .btn-previous:hover {
            background-color: #7f8c8d;
        }

        .btn-next {
            background-color: #3498db;
            color: white;
        }

        .btn-next:hover {
            background-color: #2980b9;
        }

        .iban-format {
            font-size: 24px;
            color: #2980b9;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }

        .message {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 10px;
            min-height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <h1>Informações pessoais</h1>
        
        <form action="Informações pessoais.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="selectedSeats" name="selectedSeats">
            
            <!-- Seus campos de formulário existentes aqui -->
            <div class="form-group">
                <h2>Nome Completo</h2>
                <input type="text" id="nomeCompleto" name="nomeCompleto" placeholder="Digite seu nome completo" required>
            </div>

            <div class="form-group">
                <h2>Documento</h2>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="radio" id="documentoBI" name="documento" value="BI" required>
                        <label for="documentoBI">Documento BI</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="radio" id="passaporte" name="documento" value="Passaporte">
                        <label for="passaporte">Passaporte</label>
                    </div>
                </div>
                <input type="text" id="numeroDocumento" name="numeroDocumento" placeholder="Número do documento" required>
            </div>

            <hr>

        <div class="form-group">
            <h2>E-mail</h2>
            <input type="email" id="email" name="email" placeholder="Digite seu e-mail">
            <input type="email" id="confirmaEmail" name="confirmaEmail" placeholder="Confirme seu e-mail" style="margin-top: 10px;">
        </div>

        <hr>

        <div class="form-group">
            <h2>Telefone</h2>
            <div class="input-prefix">
                <span class="prefix">+244</span>
                <input type="tel" id="telefone" name="telefone" placeholder="Telefone" class="prefix-input">
            </div>
        </div>

        <div class="form-group">
            <h2>Celular</h2>
            <div class="input-prefix">
                <span class="prefix">+244</span>
                <input type="tel" id="celular" name="celular" placeholder="Celular" class="prefix-input">
            </div>
        </div>

        <div class="form-group">
            <h2>Data Nascimento</h2>
            <input type="date" id="dataNascimento" name="dataNascimento">
        </div>

        <div class="form-group">
            <h2>Transferência Express</h2>
            <div class="iban-format">Nº: 930728358</div>
        </div>

        <div class="form-group">
            <h2>Comprovativo de Transferência</h2>
            <div class="file-upload">
                <label for="comprovativo" class="file-upload-label">
                    Clique para enviar o comprovativo ou arraste o arquivo aqui
                </label>
                <input type="file" id="comprovativo" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                <div id="fileName" class="file-name">Nenhum arquivo selecionado</div>
            </div>
        </div>

        <div class="message" id="message"></div>
           

            <div class="buttons">
                <button type="button" class="btn btn-previous" id="btnAnterior">Anterior</button>
                <button type="submit" class="btn btn-next" id="btnProximo">Próximo</button>
            </div>
        </form>
    </div>

    <script>
      
        document.addEventListener('DOMContentLoaded', function() {
            const selectedSeats = localStorage.getItem('selectedSeats');
            if (selectedSeats) {
                document.getElementById('selectedSeats').value = selectedSeats;
            }
        });

    
        

        document.getElementById('comprovativo').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Nenhum arquivo selecionado';
            document.getElementById('fileName').textContent = fileName;
        });

        document.getElementById('btnAnterior').addEventListener('click', () => {
            window.location.href = 'Lugar.html';
        });

     
        document.querySelector('form').addEventListener('submit', function(e) {
            const nome = document.getElementById('nomeCompleto').value;
            const documentoSelecionado = document.querySelector('input[name="documento"]:checked');
            const numeroDocumento = document.getElementById('numeroDocumento').value;
            const email = document.getElementById('email').value;
            const confirmaEmail = document.getElementById('confirmaEmail').value;
            const telefone = document.getElementById('telefone').value;
            const dataNascimento = document.getElementById('dataNascimento').value;
            const comprovativo = document.getElementById('comprovativo').files[0];
            const message = document.getElementById('message');

            if (!nome || !documentoSelecionado || !numeroDocumento || !email || !confirmaEmail || !telefone || !dataNascimento || !comprovativo) {
                e.preventDefault();
                message.textContent = 'Por favor, preencha todos os campos obrigatórios.';
                return;
            }

            if (email !== confirmaEmail) {
                e.preventDefault();
                message.textContent = 'Os e-mails digitados não coincidem.';
                return;
            }

            const allowedExtensions = /(\.pdf|\.jpg|\.jpeg|\.png)$/i;
            if (!allowedExtensions.exec(comprovativo.name)) {
                e.preventDefault();
                message.textContent = 'Por favor, envie um arquivo nos formatos: PDF, JPG, JPEG ou PNG.';
                return;
            }
        });
    </script>
</body>
</html>