<?php
require_once __DIR__ . '/../autoload.php';

// Verificar se o usuário está logado
startSession();
if (!isAuthenticated()) {
    redirectTo('/login');
}

try {
    $db = Database::getInstance();
    
    // Buscar dados do usuário
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado.";
        redirectTo('/login');
        exit;
    }

    // Buscar estatísticas do dashboard
    $stats = [];
    
    // Total de carros
    $stmt = $db->query("SELECT COUNT(*) as total FROM carros WHERE disponivel = 1");
    $stats['carros_disponiveis'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de aluguéis ativos
    $stmt = $db->query("SELECT COUNT(*) as total FROM alugueis WHERE status = 'ativo'");
    $stats['alugueis_ativos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de usuários
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Aluguéis do usuário atual
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM alugueis WHERE usuario_id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id']);
    $stmt->execute();
    $stats['meus_alugueis'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DriveMax</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="dashboard">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-car"></i>
                <span>DriveMax</span>
            </div>
            <div class="nav-menu">
                <a href="/" class="nav-link">Dashboard</a>
                <a href="#fleet" class="nav-link">Frota</a>
                <a href="#profile" class="nav-link">Perfil</a>
            </div>
            <div class="nav-auth">
                <span class="user-welcome">Olá, <?php echo htmlspecialchars($usuario['nome']); ?></span>
                <button onclick="logout()" class="btn-auth login">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </button>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Gerencie sua frota e aluguéis de forma inteligente</p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content">
        <!-- Quick Actions -->
        <div class="dashboard-nav">
            <div class="nav-buttons">
                <button onclick="openModal('create-car-modal')" class="nav-button">
                    <i class="fas fa-plus-circle"></i>
                    <span>Criar Carro</span>
                </button>
                <button onclick="openModal('list-cars-modal')" class="nav-button">
                    <i class="fas fa-car"></i>
                    <span>Listar Carros</span>
                </button>
                <button onclick="openModal('rent-car-modal')" class="nav-button">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Alugar Carro</span>
                </button>
                <?php if ($usuario['role'] === 'admin'): ?>
                <button onclick="openModal('list-users-modal')" class="nav-button">
                    <i class="fas fa-users"></i>
                    <span>Gerenciar Usuários</span>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <h3 class="card-title">Carros Disponíveis</h3>
                </div>
                <div class="card-content">
                    <div class="stat-number"><?php echo $stats['carros_disponiveis']; ?></div>
                    <p>Veículos prontos para locação</p>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="card-title">Aluguéis Ativos</h3>
                </div>
                <div class="card-content">
                    <div class="stat-number"><?php echo $stats['alugueis_ativos']; ?></div>
                    <p>Locações em andamento</p>
                </div>
            </div>

            <?php if ($usuario['role'] === 'admin'): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Total de Usuários</h3>
                </div>
                <div class="card-content">
                    <div class="stat-number"><?php echo $stats['total_usuarios']; ?></div>
                    <p>Clientes cadastrados</p>
                </div>
            </div>
            <?php else: ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="card-title">Meus Aluguéis</h3>
                </div>
                <div class="card-content">
                    <div class="stat-number"><?php echo $stats['meus_alugueis']; ?></div>
                    <p>Histórico de locações</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Status da Conta</h3>
                </div>
                <div class="card-content">
                    <div class="stat-number">
                        <span style="color: var(--success); font-size: 1rem;">
                            <?php echo ucfirst($usuario['role']); ?>
                        </span>
                    </div>
                    <p>Perfil: <?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Create Car Modal -->
    <div id="create-car-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Criar Novo Carro</h3>
                <button class="modal-close" onclick="closeModal('create-car-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="create-car-form">
                <div class="form-group">
                    <label for="marca" class="form-label">
                        <i class="fas fa-car"></i>
                        Marca
                    </label>
                    <input type="text" id="marca" name="marca" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="modelo" class="form-label">
                        <i class="fas fa-tag"></i>
                        Modelo
                    </label>
                    <input type="text" id="modelo" name="modelo" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="ano" class="form-label">
                        <i class="fas fa-calendar"></i>
                        Ano
                    </label>
                    <input type="number" id="ano" name="ano" class="form-input" min="1990" max="2025" required>
                </div>
                <div class="form-group">
                    <label for="preco_diario" class="form-label">
                        <i class="fas fa-dollar-sign"></i>
                        Preço Diário
                    </label>
                    <input type="number" id="preco_diario" name="preco_diario" class="form-input" step="0.01" required>
                </div>
                <button type="submit" class="form-button">
                    <i class="fas fa-plus"></i>
                    Criar Carro
                </button>
            </form>
        </div>
    </div>

    <!-- List Cars Modal -->
    <div id="list-cars-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Lista de Carros</h3>
                <button class="modal-close" onclick="closeModal('list-cars-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="cars-list-content">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                    <p>Carregando carros...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rent Car Modal -->
    <div id="rent-car-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Alugar Carro</h3>
                <button class="modal-close" onclick="closeModal('rent-car-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rent-car-form">
                <div class="form-group">
                    <label for="carro_id" class="form-label">
                        <i class="fas fa-car"></i>
                        ID do Carro
                    </label>
                    <input type="number" id="carro_id" name="carro_id" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="data_inicio" class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        Data de Início
                    </label>
                    <input type="date" id="data_inicio" name="data_inicio" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="data_fim" class="form-label">
                        <i class="fas fa-calendar-check"></i>
                        Data de Fim
                    </label>
                    <input type="date" id="data_fim" name="data_fim" class="form-input" required>
                </div>
                <button type="submit" class="form-button">
                    <i class="fas fa-calendar-plus"></i>
                    Alugar Carro
                </button>
            </form>
        </div>
    </div>

    <?php if ($usuario['role'] === 'admin'): ?>
    <!-- List Users Modal -->
    <div id="list-users-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Gerenciar Usuários</h3>
                <button class="modal-close" onclick="closeModal('list-users-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="users-list-content">
                <div style="text-align: center; padding: 2rem;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
                    <p>Carregando usuários...</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('active');
            
            // Load content for list modals
            if (modalId === 'list-cars-modal') {
                loadCarsList();
            } else if (modalId === 'list-users-modal') {
                loadUsersList();
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('active');
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Form Handlers
        document.getElementById('create-car-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const button = this.querySelector('.form-button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando...';
            button.disabled = true;
            
            try {
                const response = await fetch('/api/criacao_carro', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    button.innerHTML = '<i class="fas fa-check"></i> Criado!';
                    button.style.background = 'var(--success)';
                    
                    setTimeout(() => {
                        closeModal('create-car-modal');
                        this.reset();
                        button.innerHTML = originalText;
                        button.style.background = 'var(--primary)';
                        button.disabled = false;
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Erro ao criar carro');
                }
            } catch (error) {
                button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro';
                button.style.background = 'var(--error)';
                alert(error.message);
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = 'var(--primary)';
                    button.disabled = false;
                }, 2000);
            }
        });

        document.getElementById('rent-car-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const button = this.querySelector('.form-button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            button.disabled = true;
            
            try {
                const response = await fetch('/api/alugar_carro', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    button.innerHTML = '<i class="fas fa-check"></i> Alugado!';
                    button.style.background = 'var(--success)';
                    
                    setTimeout(() => {
                        closeModal('rent-car-modal');
                        this.reset();
                        button.innerHTML = originalText;
                        button.style.background = 'var(--primary)';
                        button.disabled = false;
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.error || 'Erro ao alugar carro');
                }
            } catch (error) {
                button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro';
                button.style.background = 'var(--error)';
                alert(error.message);
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = 'var(--primary)';
                    button.disabled = false;
                }, 2000);
            }
        });

        // Load Cars List
        async function loadCarsList() {
            try {
                const response = await fetch('/api/listar_carro');
                const carros = await response.json();
                
                let html = '<div style="display: grid; gap: 1rem;">';
                
                if (carros && carros.length > 0) {
                    carros.forEach(carro => {
                        const disponivel = carro.disponivel ? 'Disponível' : 'Indisponível';
                        const statusColor = carro.disponivel ? 'var(--success)' : 'var(--error)';
                        
                        html += `
                            <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; border-left: 4px solid ${statusColor};">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h4 style="margin: 0; color: var(--gray-900);">ID: ${carro.id} - ${carro.marca} ${carro.modelo}</h4>
                                        <p style="margin: 0.5rem 0; color: var(--gray-600);">Ano: ${carro.ano} | Preço: R$ ${carro.preco_diario}/dia</p>
                                        <span style="color: ${statusColor}; font-weight: 600;">${disponivel}</span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="editCar(${carro.id})" style="padding: 0.5rem; background: var(--primary); color: white; border: none; border-radius: 4px; cursor: pointer;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteCar(${carro.id})" style="padding: 0.5rem; background: var(--error); color: white; border: none; border-radius: 4px; cursor: pointer;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += '<p style="text-align: center; color: var(--gray-600);">Nenhum carro encontrado.</p>';
                }
                
                html += '</div>';
                document.getElementById('cars-list-content').innerHTML = html;
            } catch (error) {
                document.getElementById('cars-list-content').innerHTML = 
                    '<p style="text-align: center; color: var(--error);">Erro ao carregar carros.</p>';
            }
        }

        // Load Users List (Admin only)
        async function loadUsersList() {
            try {
                const response = await fetch('/api/listar_usuarios');
                const usuarios = await response.json();
                
                let html = '<div style="display: grid; gap: 1rem;">';
                
                if (usuarios && usuarios.length > 0) {
                    usuarios.forEach(usuario => {
                        const roleColor = usuario.role === 'admin' ? 'var(--warning)' : 'var(--primary)';
                        
                        html += `
                            <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; border-left: 4px solid ${roleColor};">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h4 style="margin: 0; color: var(--gray-900);">ID: ${usuario.id} - ${usuario.nome}</h4>
                                        <p style="margin: 0.5rem 0; color: var(--gray-600);">Email: ${usuario.email}</p>
                                        <span style="color: ${roleColor}; font-weight: 600;">Role: ${usuario.role}</span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="deleteUser(${usuario.id})" style="padding: 0.5rem; background: var(--error); color: white; border: none; border-radius: 4px; cursor: pointer;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += '<p style="text-align: center; color: var(--gray-600);">Nenhum usuário encontrado.</p>';
                }
                
                html += '</div>';
                document.getElementById('users-list-content').innerHTML = html;
            } catch (error) {
                document.getElementById('users-list-content').innerHTML = 
                    '<p style="text-align: center; color: var(--error);">Erro ao carregar usuários.</p>';
            }
        }

        // Delete functions
        async function deleteCar(id) {
            if (confirm('Tem certeza que deseja deletar este carro?')) {
                try {
                    const response = await fetch('/api/deletar_carro', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `carro_id=${id}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        loadCarsList();
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao deletar carro');
                    }
                } catch (error) {
                    alert('Erro ao deletar carro');
                }
            }
        }

        async function deleteUser(id) {
            if (confirm('Tem certeza que deseja deletar este usuário?')) {
                try {
                    const response = await fetch('/api/deletar_usuario', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `usuario_id=${id}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        loadUsersList();
                        location.reload();
                    } else {
                        alert(data.error || 'Erro ao deletar usuário');
                    }
                } catch (error) {
                    alert('Erro ao deletar usuário');
                }
            }
        }

        async function logout() {
            try {
                const response = await fetch('/api/logout', {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    window.location.href = '/';
                } else {
                    alert('Erro ao fazer logout');
                }
            } catch (error) {
                console.error('Erro:', error);
                window.location.href = '/';
            }
        }

        // Set minimum date for rent form
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('data_inicio').min = today;
            document.getElementById('data_fim').min = today;
            
            // Update end date minimum when start date changes
            document.getElementById('data_inicio').addEventListener('change', function() {
                document.getElementById('data_fim').min = this.value;
            });
        });
    </script>
</body>
</html>
            const marca = prompt('Marca:');
            const ano = prompt('Ano:');
            const preco = prompt('Preço diário:');
            
            if (nome && marca && ano && preco) {
                fetch('/api/carros', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `nome=${nome}&marca=${marca}&ano=${ano}&preco_diario=${preco}`
                })
                .then(r => r.json())
                .then(data => alert(data.message || data.error))
                .catch(() => alert('Erro ao criar carro'));
            }
        }
        
        async function listarCarros() {
            try {
                const response = await fetch('/api/carros');
                const carros = await response.json();
                
                let lista = 'CARROS DISPONÍVEIS:\n\n';
                carros.forEach(carro => {
                    lista += `ID: ${carro.id} - ${carro.marca} ${carro.modelo} (${carro.ano}) - R$ ${carro.preco_diario}/dia\n`;
                });
                
                alert(lista);
            } catch (error) {
                alert('Erro ao listar carros');
            }
        }
        
        function alugarCarro() {
            const carroId = prompt('ID do carro para alugar:');
            const dataInicio = prompt('Data início (YYYY-MM-DD):');
            const dataFim = prompt('Data fim (YYYY-MM-DD):');
            
            if (carroId && dataInicio && dataFim) {
                fetch('/api/alugueis', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `carro_id=${carroId}&data_inicio=${dataInicio}&data_fim=${dataFim}`
                })
                .then(r => r.json())
                .then(data => alert(data.message || data.error))
                .catch(() => alert('Erro ao alugar carro'));
            }
        }
        
        async function listarUsuarios() {
            try {
                const response = await fetch('/api/usuarios');
                const usuarios = await response.json();
                
                let lista = 'USUÁRIOS CADASTRADOS:\n\n';
                usuarios.forEach(user => {
                    lista += `ID: ${user.id} - ${user.nome} (${user.email}) - Role: ${user.role}\n`;
                });
                
                alert(lista);
            } catch (error) {
                alert('Erro ao listar usuários');
            }
        }
    </script>
</body>

</html>