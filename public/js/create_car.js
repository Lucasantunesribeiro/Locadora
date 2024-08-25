document.getElementById('create-car-form').addEventListener('submit', function (event) {
    event.preventDefault();

    const modelo = document.getElementById('modelo').value;
    const marca = document.getElementById('marca').value;
    const ano = document.getElementById('ano').value;
    const cor = document.getElementById('cor').value;
    const placa = document.getElementById('placa').value;
    const diaria = document.getElementById('diaria').value;


    fetch('../src/routes/api/criacao_carro.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `modelo=${encodeURIComponent(modelo)}&marca=${encodeURIComponent(marca)}&ano=${encodeURIComponent(ano)}&cor=${encodeURIComponent(cor)}&placa=${encodeURIComponent(placa)}&diaria=${encodeURIComponent(diaria)}`,
    })
        .then(response => response.text())
        .then(data => {
            alert(data);
            window.location.href = 'index.php';
        })
        .catch(error => console.error('Erro ao criar carro:', error));
});
