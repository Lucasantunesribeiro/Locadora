document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('carros-form');
    const table = document.getElementById('carros-table').getElementsByTagName('tbody')[0];
    const paginationDiv = document.getElementById('pagination');
    const paginationOptionsDiv = document.getElementById('pagination-options');
    const paginationCheckbox = document.getElementById('use_pagination');

    function fetchCarros() {
        const formData = new FormData(form);
        const queryString = new URLSearchParams(formData).toString();

        fetch(`../src/routes/api/listar_carro.php?${queryString}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Alterado para text() para debug
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    renderTable(data.carros);
                    renderPagination(data.totalPaginas);
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    console.error('Resposta do servidor:', text);
                }
            })
            .catch(error => console.error('Erro ao buscar carros:', error));
    }

    function renderTable(carros) {
        table.innerHTML = ''; // Limpa a tabela existente
        carros.forEach(carro => {
            const row = table.insertRow();
            row.innerHTML = `
                <td>${carro.modelo}</td>
                <td>${carro.marca}</td>
                <td>${carro.ano}</td>
                <td>${carro.cor}</td>
                <td>${carro.placa}</td>
                <td>R$ ${parseFloat(carro.diaria).toFixed(2).replace('.', ',')}</td>
                <td>${carro.disponibilidade}</td>
                <td>${carro.id}</td>
            `;
        });
    }

    function renderPagination(totalPaginas) {
        paginationDiv.innerHTML = '';
        for (let i = 1; i <= totalPaginas; i++) {
            const link = document.createElement('a');
            link.href = `?pagina=${i}&${new URLSearchParams(new FormData(form)).toString()}`;
            link.textContent = i;
            link.className = (parseInt(new URLSearchParams(new FormData(form)).get('pagina')) === i) ? 'active' : '';
            paginationDiv.appendChild(link);
        }
    }

    function togglePaginationOptions() {
        paginationOptionsDiv.style.display = paginationCheckbox.checked ? 'block' : 'none';
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        fetchCarros();
    });

    paginationCheckbox.addEventListener('change', function() {
        togglePaginationOptions();
        fetchCarros(); // Recarrega os carros para aplicar as alterações
    });

    togglePaginationOptions(); // Configura a exibição inicial com base no estado do checkbox
    fetchCarros(); // Carrega os carros ao iniciar a página
});
