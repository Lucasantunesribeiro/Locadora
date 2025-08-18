<?php
// Verificar se está autenticado
startSession();
if (!isAuthenticated()) {
    redirectTo('/login');
}

$db = Database::getInstance();

// Buscar estatísticas do usuário
$stmt = $db->prepare("SELECT COUNT(*) as total_alugueis FROM alugueis WHERE usuario_id = :usuario_id");
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$stats_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar carros disponíveis
$stmt = $db->query("SELECT COUNT(*) as carros_disponiveis FROM carros WHERE disponivel = 1");
$stats_carros = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar total de aluguéis ativos
$stmt = $db->prepare("SELECT COUNT(*) as alugueis_ativos FROM alugueis WHERE usuario_id = :usuario_id AND status = 'ativo'");
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt->execute();
$stats_ativos = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Painel - AutoLux Premium</title>
    <link rel="stylesheet" href="/css/shadcn.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/lucide.min.css">
    <style>
        /* Custom Variables */
        :root {
            --header-height: 4rem;
            --sidebar-width: 16rem;
        }

        /* Layout */
        .dashboard-layout {
            min-height: 100vh;
            background: hsl(var(--background));
        }

        .top-nav {
            height: var(--header-height);
            background: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: hsl(var(--foreground));
        }

        .brand-icon {
            width: 2rem;
            height: 2rem;
            background: hsl(var(--primary));
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary-foreground));
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid hsl(var(--border));
            background: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .user-avatar {
            width: 2rem;
            height: 2rem;
            background: hsl(var(--primary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--primary-foreground));
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: hsl(var(--muted-foreground));
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.primary {
            background: hsl(var(--primary) / 0.1);
            color: hsl(var(--primary));
        }

        .stat-icon.success {
            background: hsl(142 76% 36% / 0.1);
            color: hsl(142 76% 36%);
        }

        .stat-icon.info {
            background: hsl(217 91% 60% / 0.1);
            color: hsl(217 91% 60%);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            line-height: 1;
        }

        .stat-label {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: hsl(var(--card));
            border: 2px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: hsl(var(--foreground));
        }

        .action-card:hover {
            border-color: hsl(var(--primary));
            background: hsl(var(--primary) / 0.05);
            transform: translateY(-2px);
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
            font-size: 1.5rem;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-description {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        /* Sections */
        .dashboard-section {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
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

        /* Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .car-card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .car-card:hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .car-image {
            height: 200px;
            background: linear-gradient(135deg, hsl(var(--muted)) 0%, hsl(var(--accent)) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--muted-foreground));
            font-size: 3rem;
        }

        .car-content {
            padding: 1.5rem;
        }

        .car-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            margin-bottom: 0.75rem;
        }

        .car-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .car-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        .car-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: hsl(var(--primary));
            margin-bottom: 1rem;
        }

        /* Table */
        .table-container {
            border: 1px solid hsl(var(--border));
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: hsl(var(--muted));
            color: hsl(var(--muted-foreground));
            font-weight: 600;
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid hsl(var(--border));
        }

        .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid hsl(var(--border));
            color: hsl(var(--foreground));
        }

        .data-table tbody tr:hover {
            background: hsl(var(--muted) / 0.5);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            font-size: 0.875rem;
            text-decoration: none;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
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

        .btn-destructive {
            background: hsl(var(--destructive));
            color: hsl(var(--destructive-foreground));
            padding: 0.375rem 0.75rem;
        }

        .btn-destructive:hover {
            background: hsl(var(--destructive) / 0.9);
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: hsl(var(--muted-foreground));
            padding: 0.25rem;
            border-radius: 0.375rem;
        }

        .modal-close:hover {
            background: hsl(var(--muted));
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: hsl(var(--foreground));
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: 0.375rem;
            background: hsl(var(--background));
            color: hsl(var(--foreground));
            font-size: 0.875rem;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: hsl(var(--ring));
            box-shadow: 0 0 0 3px hsl(var(--ring) / 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1.5rem;
            border-top: 1px solid hsl(var(--border));
        }

        /* Status Badge */
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-ativo {
            background: hsl(142 76% 36% / 0.1);
            color: hsl(142 76% 36%);
        }

        .status-concluido {
            background: hsl(210 40% 96%);
            color: hsl(215.4 16.3% 46.9%);
        }

        .status-cancelado {
            background: hsl(var(--destructive) / 0.1);
            color: hsl(var(--destructive));
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: hsl(var(--muted-foreground));
        }

        .empty-icon {
            width: 4rem;
            height: 4rem;
            background: hsl(var(--muted));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: hsl(var(--muted-foreground));
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
            }
            
            .top-nav {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body class="dashboard-layout">
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-brand">
            <div class="brand-icon">
                <i data-lucide="car" class="w-5 h-5"></i>
            </div>
            <span>AutoLux</span>
        </div>
        
        <div class="nav-user">
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['usuario_nome'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            </div>
            <button onclick="logout()" class="btn btn-outline">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Sair
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Meu Painel</h1>
            <p class="page-subtitle">Gerencie seus aluguéis e encontre o carro perfeito para sua próxima viagem</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats_user['total_alugueis']; ?></div>
                        <div class="stat-label">Total de Aluguéis</div>
                    </div>
                    <div class="stat-icon primary">
                        <i data-lucide="calendar-check" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats_ativos['alugueis_ativos']; ?></div>
                        <div class="stat-label">Aluguéis Ativos</div>
                    </div>
                    <div class="stat-icon success">
                        <i data-lucide="car-front" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-number"><?php echo $stats_carros['carros_disponiveis']; ?></div>
                        <div class="stat-label">Carros Disponíveis</div>
                    </div>
                    <div class="stat-icon info">
                        <i data-lucide="car" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="action-grid">
            <div class="action-card" onclick="showSection('carros')">
                <div class="action-icon">
                    <i data-lucide="search" class="w-6 h-6"></i>
                </div>
                <div class="action-title">Explorar Frota</div>
                <div class="action-description">Descubra nossa seleção premium de veículos</div>
            </div>

            <div class="action-card" onclick="showSection('meus-alugueis')">
                <div class="action-icon">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <div class="action-title">Meus Aluguéis</div>
                <div class="action-description">Acompanhe seus aluguéis atuais e histórico</div>
            </div>

            <div class="action-card" onclick="openModal('aluguelModal')">
                <div class="action-icon">
                    <i data-lucide="plus-circle" class="w-6 h-6"></i>
                </div>
                <div class="action-title">Novo Aluguel</div>
                <div class="action-description">Reserve seu próximo veículo agora</div>
            </div>

            <div class="action-card" onclick="openModal('perfilModal')">
                <div class="action-icon">
                    <i data-lucide="user" class="w-6 h-6"></i>
                </div>
                <div class="action-title">Meu Perfil</div>
                <div class="action-description">Gerencie suas informações pessoais</div>
            </div>
        </div>

        <!-- Seção: Carros Disponíveis -->
        <div id="carros-section" class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i data-lucide="car" class="w-5 h-5"></i>
                    Frota Premium Disponível
                </h2>
                <button onclick="loadCarros()" class="btn btn-secondary">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Atualizar
                </button>
            </div>
            <div id="carros-list" class="cars-grid"></div>
        </div>

        <!-- Seção: Meus Aluguéis -->
        <div id="meus-alugueis-section" class="dashboard-section" style="display: none;">
            <div class="section-header">
                <h2 class="section-title">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    Histórico de Aluguéis
                </h2>
                <button onclick="loadMeusAlugueis()" class="btn btn-secondary">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Atualizar
                </button>
            </div>
            <div id="meus-alugueis-list"></div>
        </div>
    </main>

    <!-- Modal: Novo Aluguel -->
    <div id="aluguelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Novo Aluguel
                </h3>
                <button class="modal-close" onclick="closeModal('aluguelModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="aluguelForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Veículo:</label>
                        <select name="carro_id" id="carroSelect" class="form-select" required>
                            <option value="">Selecione um veículo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Início:</label>
                        <input type="date" name="data_inicio" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Fim:</label>
                        <input type="date" name="data_fim" class="form-input" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('aluguelModal')" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Confirmar Aluguel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Editar Aluguel -->
    <div id="editarAluguelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i data-lucide="edit" class="w-5 h-5"></i>
                    Editar Aluguel
                </h3>
                <button class="modal-close" onclick="closeModal('editarAluguelModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="editarAluguelForm">
                <input type="hidden" id="editAluguelId" name="aluguel_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Veículo:</label>
                        <input type="text" id="editCarroInfo" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Início:</label>
                        <input type="date" name="data_inicio" id="editDataInicio" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Data de Fim:</label>
                        <input type="date" name="data_fim" id="editDataFim" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status:</label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="ativo">Ativo</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('editarAluguelModal')" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Meu Perfil -->
    <div id="perfilModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    Meu Perfil
                </h3>
                <button class="modal-close" onclick="closeModal('perfilModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="perfilForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nome Completo:</label>
                        <input type="text" name="nome" id="perfilNome" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" id="perfilEmail" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha Atual (para alteração):</label>
                        <input type="password" name="senha_atual" id="perfilSenhaAtual" class="form-input">
                        <small style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;">Deixe em branco se não quiser alterar a senha</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nova Senha:</label>
                        <input type="password" name="nova_senha" id="perfilNovaSenha" class="form-input">
                        <small style="color: hsl(var(--muted-foreground)); font-size: 0.75rem;">Deixe em branco se não quiser alterar a senha</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Conta:</label>
                        <input type="text" id="perfilRole" class="form-input" value="Usuário" readonly>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModal('perfilModal')" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Salvar Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Estado da aplicação
        let currentSection = 'carros';

        // Mostrar seção específica
        function showSection(section) {
            // Esconder todas as seções
            document.querySelectorAll('.dashboard-section').forEach(s => s.style.display = 'none');
            
            // Mostrar seção selecionada
            document.getElementById(section + '-section').style.display = 'block';
            currentSection = section;
            
            // Carregar dados da seção
            if (section === 'carros') {
                loadCarros();
            } else if (section === 'meus-alugueis') {
                loadMeusAlugueis();
            }
        }

        // Carregar carros disponíveis
        async function loadCarros() {
            try {
                const response = await fetch('/api/carros');
                const carros = await response.json();
                
                let html = '';
                const carrosDisponiveis = carros.filter(carro => carro.disponivel);
                
                if (carrosDisponiveis.length === 0) {
                    html = `
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i data-lucide="car" class="w-8 h-8"></i>
                            </div>
                            <h3>Nenhum veículo disponível</h3>
                            <p>Não há veículos disponíveis no momento. Tente novamente mais tarde.</p>
                        </div>
                    `;
                } else {
                    carrosDisponiveis.forEach(carro => {
                        html += `
                            <div class="car-card">
                                <div class="car-image">
                                    <i data-lucide="car" class="w-16 h-16"></i>
                                </div>
                                <div class="car-content">
                                    <h3 class="car-title">${carro.marca} ${carro.modelo}</h3>
                                    <div class="car-details">
                                        <div class="car-detail">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                            <span>${carro.ano}</span>
                                        </div>
                                        <div class="car-detail">
                                            <i data-lucide="palette" class="w-4 h-4"></i>
                                            <span>${carro.cor}</span>
                                        </div>
                                        <div class="car-detail">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            <span>Disponível</span>
                                        </div>
                                    </div>
                                    <div class="car-price">R$ ${parseFloat(carro.preco_diario).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}/dia</div>
                                    <button onclick="selecionarCarro(${carro.id}, '${carro.marca} ${carro.modelo}')" class="btn btn-primary w-full">
                                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                                        Alugar Este Veículo
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                
                document.getElementById('carros-list').innerHTML = html;
                lucide.createIcons();
            } catch (error) {
                console.error('Erro ao carregar carros:', error);
                document.getElementById('carros-list').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="alert-circle" class="w-8 h-8"></i>
                        </div>
                        <h3>Erro ao carregar veículos</h3>
                        <p>Não foi possível carregar a lista de veículos. Tente novamente.</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Carregar meus aluguéis
        async function loadMeusAlugueis() {
            try {
                const response = await fetch('/api/meus-alugueis');
                const alugueis = await response.json();
                
                let html = '';
                
                if (alugueis.length === 0) {
                    html = `
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i data-lucide="calendar-x" class="w-8 h-8"></i>
                            </div>
                            <h3>Nenhum aluguel encontrado</h3>
                            <p>Você ainda não possui aluguéis. Que tal reservar seu primeiro veículo?</p>
                        </div>
                    `;
                } else {
                    html = `
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Veículo</th>
                                        <th>Data Início</th>
                                        <th>Data Fim</th>
                                        <th>Valor Total</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    alugueis.forEach(aluguel => {
                        html += `
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="car" class="w-4 h-4"></i>
                                        <span>${aluguel.marca} ${aluguel.modelo}</span>
                                    </div>
                                </td>
                                <td>${formatDate(aluguel.data_inicio)}</td>
                                <td>${formatDate(aluguel.data_fim)}</td>
                                <td>R$ ${parseFloat(aluguel.valor_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                                <td><span class="status-badge status-${aluguel.status}">${aluguel.status}</span></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="editarAluguel(${aluguel.id})" class="btn btn-sm btn-secondary">
                                            <i data-lucide="edit" class="w-3 h-3"></i>
                                            Editar
                                        </button>
                                        ${aluguel.status === 'ativo' ? 
                                            `<button onclick="cancelarAluguel(${aluguel.id})" class="btn btn-sm btn-destructive">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                                Cancelar
                                            </button>` : 
                                            ''
                                        }
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table></div>';
                }
                
                document.getElementById('meus-alugueis-list').innerHTML = html;
                lucide.createIcons();
            } catch (error) {
                console.error('Erro ao carregar aluguéis:', error);
                document.getElementById('meus-alugueis-list').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="alert-circle" class="w-8 h-8"></i>
                        </div>
                        <h3>Erro ao carregar aluguéis</h3>
                        <p>Não foi possível carregar seu histórico de aluguéis. Tente novamente.</p>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Selecionar carro para aluguel
        function selecionarCarro(id, nome) {
            document.getElementById('carroSelect').value = id;
            openModal('aluguelModal');
            loadCarrosSelect();
        }

        // Carregar carros no select
        async function loadCarrosSelect() {
            try {
                const response = await fetch('/api/carros');
                const carros = await response.json();
                
                let html = '<option value="">Selecione um veículo</option>';
                carros.forEach(carro => {
                    if (carro.disponivel) {
                        html += `<option value="${carro.id}">${carro.marca} ${carro.modelo} - R$ ${parseFloat(carro.preco_diario).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}/dia</option>`;
                    }
                });
                
                document.getElementById('carroSelect').innerHTML = html;
            } catch (error) {
                console.error('Erro ao carregar carros:', error);
            }
        }

        // Cancelar aluguel
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
                    loadMeusAlugueis();
                    loadCarros();
                } else {
                    showNotification(result.error || 'Erro ao cancelar aluguel', 'error');
                }
            } catch (error) {
                showNotification('Erro ao cancelar aluguel', 'error');
            }
        }

        // Editar aluguel
        async function editarAluguel(id) {
            try {
                const response = await fetch(`/api/alugueis/${id}`);
                const aluguel = await response.json();
                
                if (aluguel.error) {
                    showNotification(aluguel.error, 'error');
                    return;
                }
                
                // Preencher form de edição
                document.getElementById('editAluguelId').value = aluguel.id;
                document.getElementById('editCarroInfo').value = `${aluguel.marca} ${aluguel.modelo} (${aluguel.ano})`;
                document.getElementById('editDataInicio').value = aluguel.data_inicio;
                document.getElementById('editDataFim').value = aluguel.data_fim;
                document.getElementById('editStatus').value = aluguel.status;
                
                openModal('editarAluguelModal');
            } catch (error) {
                showNotification('Erro ao carregar dados do aluguel', 'error');
            }
        }

        // Carregar dados do perfil
        async function carregarPerfil() {
            try {
                const response = await fetch('/api/perfil');
                const perfil = await response.json();
                
                if (perfil.error) {
                    showNotification(perfil.error, 'error');
                    return;
                }
                
                document.getElementById('perfilNome').value = perfil.nome;
                document.getElementById('perfilEmail').value = perfil.email;
                document.getElementById('perfilRole').value = perfil.role === 'admin' ? 'Administrador' : 'Usuário';
                
                // Limpar campos de senha
                document.getElementById('perfilSenhaAtual').value = '';
                document.getElementById('perfilNovaSenha').value = '';
            } catch (error) {
                showNotification('Erro ao carregar perfil', 'error');
            }
        }

        // Submit do formulário de aluguel
        document.getElementById('aluguelForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/alugueis', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Aluguel criado com sucesso!', 'success');
                    closeModal('aluguelModal');
                    e.target.reset();
                    loadMeusAlugueis();
                    loadCarros();
                } else {
                    showNotification(result.error || 'Erro ao criar aluguel', 'error');
                }
            } catch (error) {
                showNotification('Erro ao criar aluguel', 'error');
            }
        });

        // Submit do formulário de edição de aluguel
        document.getElementById('editarAluguelForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/atualizar_aluguel', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Aluguel atualizado com sucesso!', 'success');
                    closeModal('editarAluguelModal');
                    loadMeusAlugueis();
                    loadCarros();
                } else {
                    showNotification(result.error || 'Erro ao atualizar aluguel', 'error');
                }
            } catch (error) {
                showNotification('Erro ao atualizar aluguel', 'error');
            }
        });

        // Submit do formulário de perfil
        document.getElementById('perfilForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/perfil', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showNotification('Perfil atualizado com sucesso!', 'success');
                    closeModal('perfilModal');
                    // Atualizar nome na interface se mudou
                    location.reload();
                } else {
                    showNotification(result.error || 'Erro ao atualizar perfil', 'error');
                }
            } catch (error) {
                showNotification('Erro ao atualizar perfil', 'error');
            }
        });

        // Funções auxiliares
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('pt-BR');
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            
            // Carregar dados do perfil quando abrir o modal
            if (modalId === 'perfilModal') {
                carregarPerfil();
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showNotification(message, type) {
            // Criar notificação moderna
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5"></i>
                    <span>${message}</span>
                </div>
            `;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 1000;
                background: ${type === 'success' ? 'hsl(142 76% 36%)' : 'hsl(var(--destructive))'};
                color: white;
                padding: 1rem 1.5rem; border-radius: 0.5rem; 
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                font-weight: 500;
            `;
            
            document.body.appendChild(notification);
            lucide.createIcons();
            
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

        // Inicializar página
        window.addEventListener('load', () => {
            showSection('carros');
            loadCarrosSelect();
        });
    </script>
</body>
</html>