 // Validação para permitir apenas um checkbox selecionado
        document.querySelectorAll('input[name="documento"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelectorAll('input[name="documento"]').forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // Mostrar nome do arquivo selecionado
        document.getElementById('comprovativo').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Nenhum arquivo selecionado';
            document.getElementById('fileName').textContent = fileName;
        });

        // Redirecionar para Lugar.html quando clicar em Anterior
        document.getElementById('btnAnterior').addEventListener('click', () => {
            window.location.href = 'Lugar.html';
        });

        // Validação para o botão Próximo
        document.getElementById('btnProximo').addEventListener('click', () => {
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
                message.textContent = 'Por favor, preencha todos os campos obrigatórios.';
                return;
            }

            if (email !== confirmaEmail) {
                message.textContent = 'Os e-mails digitados não coincidem.';
                return;
            }

            // Verificar tipo de arquivo
            const allowedExtensions = /(\.pdf|\.jpg|\.jpeg|\.png)$/i;
            if (!allowedExtensions.exec(comprovativo.name)) {
                message.textContent = 'Por favor, envie um arquivo nos formatos: PDF, JPG, JPEG ou PNG.';
                return;
            }

            // Se tudo estiver válido, prossegue
            message.textContent = '';
            alert('Formulário válido. Prosseguindo para o pagamento...');
            // window.location.href = 'pagamento.html';
        });