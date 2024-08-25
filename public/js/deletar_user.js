document.addEventListener('DOMContentLoaded', () => {
    // Seleciona o formulário pelo seu ID
    const form = document.getElementById('delete-form');
    
    // Verifica se o formulário existe
    if (!form) {
        console.error('Formulário não encontrado!');
        return;
    }

    // Adiciona um ouvinte de evento para o envio do formulário
    form.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evita o envio padrão do formulário

        try {
            // Envia uma requisição POST com os dados do formulário
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            // Verifica se a resposta é válida e trata como JSON
            const result = await response.json();

            if (result.status === 'error') {
                alert(result.message); // Mostra mensagem de erro
            } else {
                window.location.href = '../public/login.html'; // Redireciona para a página de login
            }
        } catch (error) {
            console.error('Erro ao excluir conta:', error); // Mostra erro no console
        }
    });
});
