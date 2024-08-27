document.addEventListener('DOMContentLoaded', () => {
    const detalharCarroBtn = document.getElementById('detalharCarroBtn');

    detalharCarroBtn.addEventListener('click', () => {
        const carroId = document.getElementById('carro_id').value;

        fetch(`/locadora_de_carros/src/routes/api/detalhar_carro.php?id=${carroId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                const { carro, aluguéis } = data;
                exibirDetalhesDoCarro(carro, aluguéis);
            }
        })
        .catch(error => console.error('Erro ao buscar detalhes do carro:', error));
    });

    function formatarMoeda(valor) {
        return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function exibirDetalhesDoCarro(carro, aluguéis) {
        const container = document.getElementById('carroDetalhes');
        container.innerHTML = ''; // Limpar conteúdo anterior

        const detalhesHTML = `
            <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f2f2f2; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                <h2 style="text-align: center; color: #333;">Informações do Carro</h2>
                <p><strong>Modelo:</strong> ${carro.modelo}</p>
                <p><strong>Marca:</strong> ${carro.marca}</p>
                <p><strong>Ano:</strong> ${carro.ano}</p>
                <p><strong>Cor:</strong> ${carro.cor}</p>
                <p><strong>Placa:</strong> ${carro.placa}</p>
                <p><strong>Diária:</strong> ${formatarMoeda(carro.diaria)}</p>
                <p><strong>Disponibilidade:</strong> ${carro.disponibilidade}</p>
                ${aluguéis.length > 0 ? `
                    <h3>Histórico de Aluguéis</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Data de Início</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Data de Fim</th>
                                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${aluguéis.map(aluguel => `
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${aluguel.data_inicio}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${aluguel.data_fim}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${formatarMoeda(aluguel.valor_total)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                ` : '<p style="margin-top: 20px;">Este carro ainda não foi alugado.</p>'}
                <a href="/locadora_de_carros/src/views/home.php" style="display: block; text-align: center; margin-top: 20px; background-color: #333; color: #fff; padding: 10px; text-decoration: none; border-radius: 5px;">Voltar</a>
            </div>
        `;
        container.innerHTML = detalhesHTML;
    }
});
