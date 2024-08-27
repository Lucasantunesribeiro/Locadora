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
    'Chevrolet' => ['Chevrolet', 'Chevy', 'Chevy Truck', 'Chevy Car'],
    'Fiat' => ['Fiat', 'Fiat Car'],
    'Ford' => ['Ford', 'Ford Truck', 'Ford Car', 'Ford Mustang'],
    'Volkswagen' => ['Volkswagen', 'VW', 'Volkswagen Car'],
    'Renault' => ['Renault', 'Renault Car'],
    'Toyota' => ['Toyota', 'Toyota Car', 'Toyota Truck'],
    'Hyundai' => ['Hyundai', 'Hyundai Car'],
    'Honda' => ['Honda', 'Honda Car', 'Honda Civic', 'Honda Accord'],
    'Jeep' => ['Jeep', 'Jeep SUV', 'Jeep Wrangler'],
    'Nissan' => ['Nissan', 'Nissan Car', 'Nissan Truck'],
    'Peugeot' => ['Peugeot', 'Peugeot Car'],
    'Citroën' => ['Citroën', 'Citroen', 'Citroen Car'],
    'Mitsubishi' => ['Mitsubishi', 'Mitsubishi Car'],
    'Mercedes-Benz' => ['Mercedes-Benz', 'Mercedes', 'Benz', 'Mercedes Car'],
    'BMW' => ['BMW', 'Bimmer', 'BM', 'BMW Car', 'BMW SUV'],
    'Audi' => ['Audi', 'Audi Car'],
    'Volvo' => ['Volvo', 'Volvo Car', 'Volvo SUV'],
    'Kia' => ['Kia', 'Kia Car'],
    'Land Rover' => ['Land Rover', 'LandRover', 'Land Rover SUV'],
    'Suzuki' => ['Suzuki', 'Suzuki Car'],
    'Subaru' => ['Subaru', 'Subaru Car', 'Subaru SUV'],
    'Chery' => ['Chery', 'Chery Car'],
    'JAC' => ['JAC', 'JAC Car'],
    'Lifan' => ['Lifan', 'Lifan Car'],
    'Troller' => ['Troller', 'Troller SUV'],
    'Agrale' => ['Agrale', 'Agrale Truck'],
    'Aston Martin' => ['Aston Martin', 'Aston', 'Martin', 'Aston Martin Car'],
    'Bentley' => ['Bentley', 'Bentley Car'],
    'Bugatti' => ['Bugatti', 'Bugatti Car'],
    'Chrysler' => ['Chrysler', 'Chrysler Car'],
    'Dodge' => ['Dodge', 'Dodge Car', 'Dodge Truck'],
    'Ferrari' => ['Ferrari', 'Ferrari Car'],
    'Lamborghini' => ['Lamborghini', 'Lambo', 'Lambo Car'],
    'Maserati' => ['Maserati', 'Maserati Car'],
    'McLaren' => ['McLaren', 'McLaren Car'],
    'Porsche' => ['Porsche', 'Porche', 'Porshe', 'Porsche Car'],
    'Rolls-Royce' => ['Rolls-Royce', 'Rolls', 'Royce', 'Rolls-Royce Car'],
    'Tesla' => ['Tesla', 'Tesla Car'],
    'Alfa Romeo' => ['Alfa Romeo', 'Alfa', 'Romeo', 'Alfa Romeo Car'],
    'Jaguar' => ['Jaguar', 'Jaguar Car', 'Jaguar SUV'],
    'Buick' => ['Buick', 'Buick Car'],
    'Changan' => ['Changan', 'Changan Car'],
    'Geely' => ['Geely', 'Geely Car'],
    'Great Wall' => ['Great Wall', 'Great Wall Car'],
    'Lexus' => ['Lexus', 'Lexus Car'],
    'Mini' => ['Mini', 'Mini Car'],
    'Rover' => ['Rover', 'Rover Car'],
    'Scion' => ['Scion', 'Scion Car'],
    'Saab' => ['Saab', 'Saab Car'],
    'Smart' => ['Smart', 'Smart Car'],
    'Alpina' => ['Alpina', 'Alpina Car'],
    'Pagani' => ['Pagani', 'Pagani Car'],
    'Koenigsegg' => ['Koenigsegg', 'Koenigsegg Car'],
    'Daihatsu' => ['Daihatsu', 'Daihatsu Car']
];

if ($useFilter) {
    $filtroMarca = isset($_GET['filtro_marca']) ? trim($_GET['filtro_marca']) : '';
    
    if ($filtroMarca) {
        $filtroMarcaNormalizado = strtolower(trim($filtroMarca));
        
        $marcaEncontrada = false;
        $marcaFiltro = '';
        foreach ($marcas as $marca => $sinonimos) {
            foreach ($sinonimos as $sinonimo) {
                if (strtolower($sinonimo) === $filtroMarcaNormalizado) {
                    $marcaEncontrada = true;
                    $marcaFiltro = $marca; // Armazena a marca correta
                    break 2;
                }
            }
        }

        if ($marcaEncontrada) {
            $params[':filtroMarca'] = "%$marcaFiltro%";
            if (strpos($query, 'WHERE') === false) {
                $query .= " WHERE marca LIKE :filtroMarca";
            } else {
                $query .= " AND marca LIKE :filtroMarca";
            }
        } else {
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

    if (empty($carros)) {
        echo json_encode([
            'carros' => [],
            'totalPaginas' => 0
        ]);
        exit;
    }

    // Consulta para contar o total de carros
    $totalCarrosQuery = "SELECT COUNT(*) FROM carros";
    $totalCarrosParams = [];

    if ($useFilter && $filtroMarca) {
        $totalCarrosQuery .= " WHERE marca LIKE :filtroMarca";
        $totalCarrosParams[':filtroMarca'] = "%$marcaFiltro%";
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
