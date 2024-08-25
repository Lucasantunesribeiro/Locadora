document.addEventListener('DOMContentLoaded', () => {
    fetch('../src/routes/api/listar_carro.php') // Verifique o caminho e nome do arquivo PHP
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#carros-table tbody');
            tableBody.innerHTML = ''; // Limpa o corpo da tabela

            if (data.carros) {
                data.carros.forEach(carro => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${carro.id}</td>
                        <td>${carro.modelo}</td>
                        <td>${carro.marca}</td>
                        <td>${carro.ano}</td>
                        <td>${carro.cor}</td>
                        <td>${carro.placa}</td>
                        <td><button class="btn-excluir" onclick="deletarCarro(${carro.id})">Excluir</button></td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                console.error('Erro ao carregar os carros:', data);
            }
        })
        .catch(error => console.error('Erro ao carregar os carros:', error));
});

function deletarCarro(id) {
    if (confirm('Tem certeza de que deseja excluir este carro?')) {
        fetch(`../src/routes/api/deletar_carro.php?id=${id}`, { // Verifique o caminho e nome do arquivo PHP
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'sucesso') {
                alert('Carro deletado com sucesso!');
                window.location.reload(); // Recarrega a página para atualizar a lista de carros
            } else {
                alert('Erro: ' + data.mensagem);
            }
        })
        .catch(error => console.error('Erro ao deletar o carro:', error));
    }
}
