<?php
// Verificar se está autenticado e é admin
startSession();
if (!isAuthenticated() || $_SESSION['usuario_role'] !== 'admin') {
    redirectTo('/login');
}

$db = Database::getInstance();

// Buscar estatísticas
$stmt = $db->query("SELECT COUNT(*) as total_carros FROM carros");
$stats_carros = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT COUNT(*) as carros_disponiveis FROM carros WHERE disponivel = 1");
$stats_disponiveis = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT COUNT(*) as total_usuarios FROM usuarios WHERE role != 'admin'");
$stats_usuarios = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT COUNT(*) as total_alugueis FROM alugueis");
$stats_alugueis = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT COUNT(*) as alugueis_ativos FROM alugueis WHERE status = 'ativo'");
$stats_ativos = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT SUM(valor_total) as receita_total FROM alugueis WHERE status = 'finalizado'");
$stats_receita = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - AutoLux</title>
    <link rel="stylesheet" href="/css/shadcn.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/lucide.min.css">
    <style>
        /* Custom Dashboard Styles */
        .dashboard-header {
            background: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary) / 0.9) 100%);
            color: white;
        }
        
        .stat-card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            color: hsl(var(--primary));
        }
        
        .stat-label {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .action-card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: hsl(var(--primary));
        }
        
        .action-icon {
            width: 3rem;
            height: 3rem;
            background: hsl(var(--primary) / 0.1);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: hsl(var(--primary));
        }
        
        .content-section {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
            padding: 0.5rem 1rem;
        }
        
        .btn-primary:hover {
            background: hsl(var(--primary) / 0.9);
        }
        
        .btn-secondary {
            background: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
            padding: 0.5rem 1rem;
        }
        
        .btn-secondary:hover {
            background: hsl(var(--secondary) / 0.8);
        }
        
        .btn-outline {
            background: transparent;
            color: hsl(var(--foreground));
            border: 1px solid hsl(var(--border));
            padding: 0.5rem 1rem;
        }
        
        .btn-outline:hover {
            background: hsl(var(--accent));
            color: hsl(var(--accent-foreground));
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid hsl(var(--border));
        }
        
        .data-table th {
            background: hsl(var(--muted));
            color: hsl(var(--muted-foreground));
            font-weight: 600;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.875rem;
        }
        
        .data-table td {
            padding: 0.75rem 1rem;
            border-top: 1px solid hsl(var(--border));
        }
        
        .data-table tr:hover {
            background: hsl(var(--muted) / 0.5);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-ativo {
            background: rgba(34, 197, 94, 0.1);
            color: rgb(34, 197, 94);
        }
        
        .status-inativo {
            background: rgba(239, 68, 68, 0.1);
            color: rgb(239, 68, 68);
        }
        
        .status-disponivel {
            background: rgba(34, 197, 94, 0.1);
            color: rgb(34, 197, 94);
        }
        
        .status-indisponivel {
            background: rgba(239, 68, 68, 0.1);
            color: rgb(239, 68, 68);
        }
        
        .navbar {
            background: hsl(var(--background));
            border-bottom: 1px solid hsl(var(--border));
            padding: 1rem 0;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 50;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: hsl(var(--background));
            border-radius: 0.75rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid hsl(var(--border));
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid hsl(var(--border));
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(var(--foreground));
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid hsl(var(--border));
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: hsl(var(--foreground));
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: 0.375rem;
            background: hsl(var(--background));
            color: hsl(var(--foreground));
            font-size: 0.875rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: hsl(var(--primary));
            box-shadow: 0 0 0 3px hsl(var(--primary) / 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: 0.375rem;
            background: hsl(var(--background));
            color: hsl(var(--foreground));
            font-size: 0.875rem;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: hsl(var(--muted-foreground));
            padding: 0.25rem;
            border-radius: 0.25rem;
        }
        
        .close-btn:hover {
            color: hsl(var(--foreground));
            background: hsl(var(--muted));
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: hsl(var(--muted-foreground));
        }
        
        .grid {
            display: grid;
        }
        
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-8 { gap: 2rem; }
        
        @media (min-width: 768px) {
            .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        
        @media (min-width: 1024px) {
            .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>
</head>
<body class="bg-background text-foreground">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                        <i data-lucide="car" class="w-6 h-6 text-primary-foreground"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-primary">AutoLux</span>
                        <span class="block text-sm text-muted-foreground">Dashboard Admin</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="text-sm text-muted-foreground">
                        Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>
                    </span>
                    <button onclick="logout()" class="btn btn-outline btn-sm">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Sair
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <section class="dashboard-header py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Dashboard Administrativo</h1>
                <p class="text-lg opacity-90">Gerencie sua locadora com total controle e eficiência</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="mx-auto max-w-7xl px-6 py-8">
        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-number"><?php echo $stats_carros['total_carros']; ?></div>
                        <div class="stat-label">Total de Carros</div>
                    </div>
                    <div class="action-icon">
                        <i data-lucide="car" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-number"><?php echo $stats_disponiveis['carros_disponiveis']; ?></div>
                        <div class="stat-label">Carros Disponíveis</div>
                    </div>
                    <div class="action-icon">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-number"><?php echo $stats_usuarios['total_usuarios']; ?></div>
                        <div class="stat-label">Usuários Ativos</div>
                    </div>
                    <div class="action-icon">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="stat-number"><?php echo $stats_alugueis['total_alugueis']; ?></div>
                        <div class="stat-label">Total Aluguéis</div>
                    </div>
                    <div class="action-icon">
                        <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-section">
            <h2 class="section-title mb-6">
                <i data-lucide="zap" class="w-5 h-5"></i>
                Ações Rápidas
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="action-card" onclick="showSection('carros'); openModal('carroModal')">
                    <div class="action-icon">
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-foreground mb-2">Adicionar Carro</h3>
                    <p class="text-sm text-muted-foreground">Cadastre um novo veículo na frota</p>
                </div>
                
                <div class="action-card" onclick="showSection('usuarios'); openModal('usuarioModal')">
                    <div class="action-icon">
                        <i data-lucide="user-plus" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-foreground mb-2">Novo Usuário</h3>
                    <p class="text-sm text-muted-foreground">Adicione um novo usuário ao sistema</p>
                </div>
                
                <div class="action-card" onclick="showSection('alugueis')">
                    <div class="action-icon">
                        <i data-lucide="eye" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-foreground mb-2">Ver Aluguéis</h3>
                    <p class="text-sm text-muted-foreground">Monitore todos os aluguéis ativos</p>
                </div>
            </div>
        </div>

        <!-- Section Tabs -->
        <div class="content-section">
            <div class="flex border-b border-border mb-6">
                <button class="section-tab active" data-section="carros">
                    <i data-lucide="car" class="w-4 h-4"></i>
                    Carros
                </button>
                <button class="section-tab" data-section="usuarios">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Usuários
                </button>
                <button class="section-tab" data-section="alugueis">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    Aluguéis
                </button>
            </div>

            <!-- Carros Section -->
            <div id="carros-section" class="section-content">
                <div class="section-header">
                    <h2 class="section-title">
                        <i data-lucide="car" class="w-5 h-5"></i>
                        Gerenciar Carros
                    </h2>
                    <button onclick="openModal('carroModal')" class="btn btn-primary">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Adicionar Carro
                    </button>
                </div>
                <div id="carros-list">
                    <div class="empty-state">
                        <i data-lucide="car" class="w-12 h-12 mx-auto mb-4 text-muted-foreground"></i>
                        <p>Carregando carros...</p>
                    </div>
                </div>
            </div>

            <!-- Usuários Section -->
            <div id="usuarios-section" class="section-content" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        Gerenciar Usuários
                    </h2>
                    <button onclick="openModal('usuarioModal')" class="btn btn-primary">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Adicionar Usuário
                    </button>
                </div>
                <div id="usuarios-list">
                    <div class="empty-state">
                        <i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-muted-foreground"></i>
                        <p>Carregando usuários...</p>
                    </div>
                </div>
            </div>

            <!-- Aluguéis Section -->
            <div id="alugueis-section" class="section-content" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        Gerenciar Aluguéis
                    </h2>
                    <button onclick="loadAlugueis()" class="btn btn-secondary">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Atualizar
                    </button>
                </div>
                <div id="alugueis-list">
                    <div class="empty-state">
                        <i data-lucide="calendar" class="w-12 h-12 mx-auto mb-4 text-muted-foreground"></i>
                        <p>Carregando aluguéis...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Carro -->
    <div id="carroModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Adicionar Carro</h3>
                <button class="close-btn" onclick="closeModal('carroModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="carroForm" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="text" name="marca" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ano</label>
                    <input type="number" name="ano" class="form-input" required min="1990" max="2025">
                </div>
                <div class="form-group">
                    <label class="form-label">Cor</label>
                    <input type="text" name="cor" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Preço Diário (R$)</label>
                    <input type="number" name="preco_diario" class="form-input" required step="0.01" min="0">
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('carroModal')" class="btn btn-outline">Cancelar</button>
                <button type="submit" form="carroForm" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Usuário -->
    <div id="usuarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Adicionar Usuário</h3>
                <button class="close-btn" onclick="closeModal('usuarioModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="usuarioForm" class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="nome" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="role" class="form-select" required>
                        <option value="user">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('usuarioModal')" class="btn btn-outline">Cancelar</button>
                <button type="submit" form="usuarioForm" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Usuário -->
    <div id="editarUsuarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Usuário</h3>
                <button class="close-btn" onclick="closeModal('editarUsuarioModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="editarUsuarioForm" class="modal-body">
                <input type="hidden" id="editUsuarioId" name="usuario_id">
                <div class="form-group">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" name="nome" id="editUsuarioNome" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="editUsuarioEmail" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="role" id="editUsuarioRole" class="form-select" required>
                        <option value="user">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <small style="color: hsl(var(--muted-foreground));">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        A senha não será alterada. Para alterar a senha, o usuário deve fazer isso em seu perfil.
                    </small>
                </div>
            </form>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editarUsuarioModal')" class="btn btn-outline">Cancelar</button>
                <button type="submit" form="editarUsuarioForm" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        let currentSection = 'carros';

        // Section management
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(s => s.style.display = 'none');
            document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
            
            // Show selected section
            document.getElementById(section + '-section').style.display = 'block';
            document.querySelector(`[data-section="${section}"]`).classList.add('active');
            currentSection = section;
            
            // Load data for section
            if (section === 'carros') loadCarros();
            else if (section === 'usuarios') loadUsuarios();
            else if (section === 'alugueis') loadAlugueis();
        }

        // Tab navigation
        document.querySelectorAll('.section-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const section = tab.dataset.section;
                showSection(section);
            });
        });

        // Load Carros
        async function loadCarros() {
            try {
                const response = await fetch('/api/carros');
                const carros = await response.json();
                
                let html = `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Marca/Modelo</th>
                                <th>Ano</th>
                                <th>Cor</th>
                                <th>Preço/Dia</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                carros.forEach(carro => {
                    html += `
                        <tr>
                            <td>#${carro.id}</td>
                            <td><strong>${carro.marca}</strong> ${carro.modelo}</td>
                            <td>${carro.ano}</td>
                            <td>${carro.cor}</td>
                            <td>R$ ${parseFloat(carro.preco_diario).toFixed(2)}</td>
                            <td>
                                <span class="status-badge ${carro.disponivel ? 'status-disponivel' : 'status-indisponivel'}">
                                    ${carro.disponivel ? 'Disponível' : 'Indisponível'}
                                </span>
                            </td>
                            <td>
                                <button onclick="editarCarro(${carro.id})" class="btn btn-outline btn-sm">
                                    <i data-lucide="edit" class="w-3 h-3"></i>
                                </button>
                                <button onclick="deletarCarro(${carro.id})" class="btn btn-outline btn-sm" style="color: rgb(239, 68, 68);">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
                document.getElementById('carros-list').innerHTML = html;
                lucide.createIcons();
            } catch (error) {
                document.getElementById('carros-list').innerHTML = '<div class="empty-state">Erro ao carregar carros</div>';
            }
        }

        // Load Usuários
        async function loadUsuarios() {
            try {
                const response = await fetch('/api/usuarios');
                const usuarios = await response.json();
                
                let html = `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                usuarios.forEach(usuario => {
                    html += `
                        <tr>
                            <td>#${usuario.id}</td>
                            <td><strong>${usuario.nome}</strong></td>
                            <td>${usuario.email}</td>
                            <td>
                                <span class="status-badge ${usuario.role === 'admin' ? 'status-ativo' : 'status-disponivel'}">
                                    ${usuario.role === 'admin' ? 'Admin' : 'Usuário'}
                                </span>
                            </td>
                            <td>${formatDate(usuario.created_at)}</td>
                            <td>
                                <button onclick="editarUsuario(${usuario.id})" class="btn btn-outline btn-sm">
                                    <i data-lucide="edit" class="w-3 h-3"></i>
                                </button>
                                ${usuario.role !== 'admin' ? `
                                    <button onclick="deletarUsuario(${usuario.id})" class="btn btn-outline btn-sm" style="color: rgb(239, 68, 68);">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
                document.getElementById('usuarios-list').innerHTML = html;
                lucide.createIcons();
            } catch (error) {
                document.getElementById('usuarios-list').innerHTML = '<div class="empty-state">Erro ao carregar usuários</div>';
            }
        }

        // Load Aluguéis
        async function loadAlugueis() {
            try {
                const response = await fetch('/api/alugueis');
                const alugueis = await response.json();
                
                let html = `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Veículo</th>
                                <th>Período</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                alugueis.forEach(aluguel => {
                    html += `
                        <tr>
                            <td>#${aluguel.id}</td>
                            <td><strong>${aluguel.usuario_nome}</strong><br><small>${aluguel.usuario_email}</small></td>
                            <td><strong>${aluguel.marca}</strong> ${aluguel.modelo} (${aluguel.ano})</td>
                            <td>
                                ${formatDate(aluguel.data_inicio)} até<br>
                                ${formatDate(aluguel.data_fim)}
                            </td>
                            <td>R$ ${parseFloat(aluguel.valor_total).toFixed(2)}</td>
                            <td>
                                <span class="status-badge status-${aluguel.status}">
                                    ${aluguel.status}
                                </span>
                            </td>
                            <td>
                                <button onclick="editarAluguel(${aluguel.id})" class="btn btn-outline btn-sm">
                                    <i data-lucide="edit" class="w-3 h-3"></i>
                                </button>
                                ${aluguel.status === 'ativo' ? `
                                    <button onclick="cancelarAluguel(${aluguel.id})" class="btn btn-outline btn-sm" style="color: rgb(239, 68, 68);">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
                document.getElementById('alugueis-list').innerHTML = html;
                lucide.createIcons();
            } catch (error) {
                document.getElementById('alugueis-list').innerHTML = '<div class="empty-state">Erro ao carregar aluguéis</div>';
            }
        }

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            // Reset form
            const form = document.querySelector(`#${modalId} form`);
            if (form) form.reset();
        }

        // Form submissions
        document.getElementById('carroForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/carros', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Carro adicionado com sucesso!', 'success');
                    closeModal('carroModal');
                    loadCarros();
                } else {
                    showNotification(result.error || 'Erro ao adicionar carro', 'error');
                }
            } catch (error) {
                showNotification('Erro ao adicionar carro', 'error');
            }
        });

        document.getElementById('usuarioForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/usuarios', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Usuário adicionado com sucesso!', 'success');
                    closeModal('usuarioModal');
                    loadUsuarios();
                } else {
                    showNotification(result.error || 'Erro ao adicionar usuário', 'error');
                }
            } catch (error) {
                showNotification('Erro ao adicionar usuário', 'error');
            }
        });

        // Form de edição de usuário
        document.getElementById('editarUsuarioForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/atualizar_usuario', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Usuário atualizado com sucesso!', 'success');
                    closeModal('editarUsuarioModal');
                    loadUsuarios();
                } else {
                    showNotification(result.error || 'Erro ao atualizar usuário', 'error');
                }
            } catch (error) {
                showNotification('Erro ao atualizar usuário', 'error');
            }
        });

        // Delete functions
        async function deletarCarro(id) {
            if (!confirm('Tem certeza que deseja deletar este carro?')) return;
            
            try {
                const formData = new FormData();
                formData.append('carro_id', id);
                
                const response = await fetch('/api/deletar_carro', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Carro deletado com sucesso!', 'success');
                    loadCarros();
                } else {
                    showNotification(result.error || 'Erro ao deletar carro', 'error');
                }
            } catch (error) {
                showNotification('Erro ao deletar carro', 'error');
            }
        }

        async function editarUsuario(id) {
            try {
                const response = await fetch(`/api/usuarios/${id}`);
                const usuario = await response.json();
                
                if (usuario.error) {
                    showNotification(usuario.error, 'error');
                    return;
                }
                
                // Preencher form de edição
                document.getElementById('editUsuarioId').value = usuario.id;
                document.getElementById('editUsuarioNome').value = usuario.nome;
                document.getElementById('editUsuarioEmail').value = usuario.email;
                document.getElementById('editUsuarioRole').value = usuario.role;
                
                openModal('editarUsuarioModal');
            } catch (error) {
                showNotification('Erro ao carregar dados do usuário', 'error');
            }
        }

        async function deletarUsuario(id) {
            if (!confirm('Tem certeza que deseja deletar este usuário?')) return;
            
            try {
                const formData = new FormData();
                formData.append('usuario_id', id);
                
                const response = await fetch('/api/deletar_usuario', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Usuário deletado com sucesso!', 'success');
                    loadUsuarios();
                } else {
                    showNotification(result.error || 'Erro ao deletar usuário', 'error');
                }
            } catch (error) {
                showNotification('Erro ao deletar usuário', 'error');
            }
        }

        async function cancelarAluguel(id) {
            if (!confirm('Tem certeza que deseja cancelar este aluguel?')) return;
            
            try {
                const formData = new FormData();
                formData.append('aluguel_id', id);
                
                const response = await fetch('/api/deletar_aluguel', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Aluguel cancelado com sucesso!', 'success');
                    loadAlugueis();
                } else {
                    showNotification(result.error || 'Erro ao cancelar aluguel', 'error');
                }
            } catch (error) {
                showNotification('Erro ao cancelar aluguel', 'error');
            }
        }

        // Utility functions
        function formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('pt-BR');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 1000;
                background: ${type === 'success' ? 'hsl(var(--primary))' : 'rgb(239, 68, 68)'};
                color: white; padding: 1rem 1.5rem; border-radius: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                font-weight: 500; max-width: 400px;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 4000);
        }

        async function logout() {
            try {
                const response = await fetch('/api/logout', { method: 'POST' });
                const result = await response.json();
                
                if (result.status === 'success') {
                    window.location.href = '/login';
                }
            } catch (error) {
                window.location.href = '/login';
            }
        }

        // Add section tab styles
        const style = document.createElement('style');
        style.textContent = `
            .section-tab {
                padding: 0.75rem 1.5rem;
                border: none;
                background: none;
                color: hsl(var(--muted-foreground));
                font-weight: 500;
                cursor: pointer;
                border-bottom: 2px solid transparent;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.2s ease;
            }
            
            .section-tab:hover {
                color: hsl(var(--foreground));
            }
            
            .section-tab.active {
                color: hsl(var(--primary));
                border-bottom-color: hsl(var(--primary));
            }
        `;
        document.head.appendChild(style);

        // Initialize
        window.addEventListener('load', () => {
            showSection('carros');
        });

        // Close modals on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                const modalId = e.target.id;
                closeModal(modalId);
            }
        });
    </script>
</body>
</html>