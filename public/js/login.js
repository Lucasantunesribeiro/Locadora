document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    fetch('../src/routes/api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `nome=${encodeURIComponent(nome)}&email=${encodeURIComponent(email)}&senha=${encodeURIComponent(senha)}`
    })
    .then(response => response.text()) // Obtém a resposta como texto
    .then(text => {
        console.log('Resposta do servidor:', text); // Log da resposta para depuração
        try {
            const data = JSON.parse(text); // Tenta parsear a resposta como JSON
            if (data.status === 'success') {
                window.location.href = data.redirect || '/locadora_de_carros/public/index.php';
            } else {
                alert(data.message); // Exibe a mensagem de erro
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }
        } catch (jsonError) {
            console.error('Erro ao parsear JSON:', jsonError);
        }
    })
    .catch(error => console.error('Erro ao fazer login:', error));
});
