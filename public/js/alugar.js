document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('aluguel-form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);

        fetch('/locadora_de_carros/src/routes/api/alugar_carro.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(text => {
                    throw new Error('Resposta inesperada: ' + text);
                });
            }
        })
        .then(data => {
            if (data.success) {
                alert('Aluguel realizado com sucesso!');
                form.reset();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao realizar o aluguel:', error);
            alert('Erro ao realizar o aluguel. Por favor, tente novamente.');
        });
    });
});
