<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../services/CarroService.php';
require_once __DIR__ . '/../../../core/Database.php';

$carroService = new CarroService();
$db = Database::getInstance();

$useFilter = isset($_GET['use_filter']) && $_GET['use_filter'] == '1';
$usePagination = isset($_GET['use_pagination']) && $_GET['use_pagination'] == '1';

$query = "SELECT * FROM carros";
$params = [];

$marcas = [
    'Chevrolet' => ['Chevrolet', 'Chevy'],
    'Fiat' => ['Fiat'],
    'Ford' => ['Ford'],
    'Volkswagen' => ['Volkswagen', 'VW'],
    'Renault' => ['Renault'],
    'Toyota' => ['Toyota'],
    'Hyundai' => ['Hyundai'],
    'Honda' => ['Honda'],
    'Jeep' => ['Jeep'],
    'Nissan' => ['Nissan'],
    'Peugeot' => ['Peugeot'],
    'Citroën' => ['Citroën', 'Citroen'],
    'Mitsubishi' => ['Mitsubishi'],
    'Mercedes-Benz' => ['Mercedes-Benz', 'Mercedes', 'Benz'],
    'BMW' => ['BMW'],
    'Audi' => ['Audi'],
    'Volvo' => ['Volvo'],
    'Kia' => ['Kia'],
    'Land Rover' => ['Land Rover', 'LandRover'],
    'Suzuki' => ['Suzuki'],
    'Subaru' => ['Subaru'],
    'Chery' => ['Chery'],
    'JAC' => ['JAC'],
    'Lifan' => ['Lifan'],
    'Troller' => ['Troller'],
    'Agrale' => ['Agrale'],
    'Aston Martin' => ['Aston Martin', 'Aston', 'Martin'],
    'Bentley' => ['Bentley'],
    'Bugatti' => ['Bugatti'],
    'Chrysler' => ['Chrysler'],
    'Dodge' => ['Dodge'],
    'Ferrari' => ['Ferrari'],
    'Lamborghini' => ['Lamborghini', 'Lambo'],
    'Maserati' => ['Maserati'],
    'McLaren' => ['McLaren'],
    'Porsche' => ['Porsche', 'Porche', 'Porshe'], 
    'Rolls-Royce' => ['Rolls-Royce', 'Rolls', 'Royce'],
    'Tesla' => ['Tesla'],
    'Alfa Romeo' => ['Alfa Romeo', 'Alfa', 'Romeo']
];

if ($useFilter) {
    $filtroMarca = isset($_GET['filtro_marca']) ? trim($_GET['filtro_marca']) : '';
    
    if ($filtroMarca) {
        // Normalizar a entrada do usuário
        $filtroMarcaNormalizado = strtolower(trim($filtroMarca));
        
        $marcaEncontrada = false;
        foreach ($marcas as $marca => $sinonimos) {
            foreach ($sinonimos as $sinonimo) {
                if (strtolower($sinonimo) === $filtroMarcaNormalizado) {
                    $marcaEncontrada = true;
                    break 2;
                }
            }
        }

        if ($marcaEncontrada) {
            $params[':filtroMarca'] = "%$filtroMarca%";
            // Adicionar a condição ao SQL query
            if (strpos($query, 'WHERE') === false) {
                $query .= " WHERE marca LIKE :filtroMarca";
            } else {
                $query .= " AND marca LIKE :filtroMarca";
            }
        } else {
            // Se não encontrar a marca, retorna uma lista vazia
            echo json_encode([
                'carros' => [],
                'totalPaginas' => 0
            ]);
            exit;
        }
    }
}

$orderBy = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'ID';
$orderBy = in_array($orderBy, ['alfabetica', 'disponibilidade', 'indisponibilidade', 'preco', 'preco_desc', 'ID']) ? $orderBy : 'ID';

switch ($orderBy) {
    case 'alfabetica':
        $query .= " ORDER BY modelo";
        break;
    case 'disponibilidade':
        $query .= " ORDER BY disponibilidade";
        break;
    case 'indisponibilidade':
        $query .= " ORDER BY disponibilidade DESC";
        break;
    case 'preco':
        $query .= " ORDER BY diaria";
        break;
    case 'preco_desc':
        $query .= " ORDER BY diaria DESC";
        break;
    case 'ID':
    default:
        $query .= " ORDER BY id";
        break;
}

if ($usePagination) {
    $itensPorPagina = isset($_GET['itens_por_pagina']) ? (int) $_GET['itens_por_pagina'] : 10;
    $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
    $offset = ($pagina - 1) * $itensPorPagina;
    $query .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $itensPorPagina;
    $params[':offset'] = $offset;
}

try {
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $carros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se não houver carros, retorna uma lista vazia
    if (empty($carros)) {
        echo json_encode([
            'carros' => [],
            'totalPaginas' => 0
        ]);
        exit;
    }

    $totalCarrosQuery = "SELECT COUNT(*) FROM carros";
    $totalCarrosParams = [];

    if ($useFilter && $filtroMarca) {
        $totalCarrosQuery .= " WHERE marca LIKE :filtroMarca";
        $totalCarrosParams[':filtroMarca'] = "%$filtroMarca%";
    }

    $totalCarrosStmt = $db->prepare($totalCarrosQuery);
    foreach ($totalCarrosParams as $key => $value) {
        $totalCarrosStmt->bindValue($key, $value);
    }
    $totalCarrosStmt->execute();
    $totalCarros = $totalCarrosStmt->fetchColumn();
    $totalPaginas = $usePagination ? ceil($totalCarros / $itensPorPagina) : 1;

    echo json_encode([
        'carros' => $carros,
        'totalPaginas' => $totalPaginas
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
