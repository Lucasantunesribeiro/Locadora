document.addEventListener('DOMContentLoaded', function() {
  fetch('../src/routes/api/listar_usuarios.php')
      .then(response => response.json())
      .then(data => {
          if (Array.isArray(data)) {
              const tbody = document.getElementById('user-table-body');
              tbody.innerHTML = data.map(user => `
                  <tr>
                      <td>${user.id}</td>
                      <td>${user.nome}</td>
                      <td>${user.email}</td>
                  </tr>
              `).join('');
          } else {
              console.error('Erro ao carregar dados dos usuários.');
          }
      })
      .catch(error => console.error('Erro ao buscar dados dos usuários:', error));
});
