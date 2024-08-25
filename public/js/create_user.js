document.getElementById('create-user-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Impede o envio padrão do formulário

    const nome = document.getElementById('nome').value;
    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    fetch('/locadora_de_carros/src/routes/api/cadastro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            nome: nome,
            email: email,
            senha: senha
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message); // Exibir mensagem de sucesso
            window.location.href = '/locadora_de_carros/public/login.html'; // Redirecionar para a página de login
        } else {
            alert(data.message); // Exibir mensagem de erro
            if (data.message === 'Este email já está cadastrado.') {
                window.location.href = '/locadora_de_carros/public/login.html'; // Redirecionar para login se o email já estiver cadastrado
            }
        }
    })
    .catch(error => console.error('Erro ao criar usuário:', error));
});
