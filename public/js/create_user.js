document.getElementById('create-user-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevents the default form submission

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
            alert(data.message); // Display success message
            window.location.href = '/locadora_de_carros/src/views/login.html'; // Redirect to login page
        } else {
            alert(data.message); // Display error message
            if (data.message === 'Este email já está cadastrado.') {
                window.location.href = '/locadora_de_carros/src/views/login.html'; // Redirect to login page if email is already registered
            }
        }
    })
    .catch(error => console.error('Erro ao criar usuário:', error));
});
