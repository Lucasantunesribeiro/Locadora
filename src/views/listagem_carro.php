<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Carros</title>
    <link rel="stylesheet" href="/locadora_de_carros/public/css/style.css">
    <!-- Assumindo que a estilização foi movida para um arquivo CSS separado -->
</head>

<body>
    <div class="container">
        <header>
            <h1 class="container_titulo">Listagem de Carros</h1>
        </header>


        <!-- Filtro -->
        <form id="carros-form" method="get" action="" class="container_listar-carro">
            <fieldset class="container-fieldset">
                <legend>Filtros e Paginação</legend>
                <label for="filtro_marca">Marca:</label>
                <input type="text" name="filtro_marca" id="filtro_marca"
                    value="<?php echo isset($_GET['filtro_marca']) ? htmlspecialchars($_GET['filtro_marca']) : ''; ?>"><br>
                    <div class="checkbox-container">
    <input type="checkbox" name="use_filter" value="1" id="use_filter" <?php echo isset($_GET['use_filter']) ? 'checked' : ''; ?>>
    <label for="use_filter">Ativar Filtro</label>
</div>
<div class="checkbox-container">
    <input type="checkbox" name="use_pagination" id="use_pagination" value="1" <?php echo isset($_GET['use_pagination']) ? 'checked' : ''; ?>>
    <label for="use_pagination">Ativar Paginação</label>
</div><br>
                <label for="ordenar_por">Ordenar por:</label>
                <select name="ordenar_por" id="ordenar_por">
                    <option value="alfabetica" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'alfabetica') ? 'selected' : ''; ?>>Alfabética</option>
                    <option value="disponibilidade" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'disponibilidade') ? 'selected' : ''; ?>>Disponibilidade</option>
                    <option value="indisponibilidade" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'indisponibilidade') ? 'selected' : ''; ?>>Indisponibilidade</option>
                    <option value="preco" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'preco') ? 'selected' : ''; ?>>Preço (Ascendente)</option>
                    <option value="preco_desc" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'preco_desc') ? 'selected' : ''; ?>>Preço (Descendente)</option>
                    <option value="ID" <?php echo (isset($_GET['ordenar_por']) && $_GET['ordenar_por'] === 'ID') ? 'selected' : ''; ?>>ID</option>
                </select><br>

                <!-- Itens por página - visível somente se a paginação estiver ativada -->
                <div id="pagination-options" style="display: none;">
                    <label for="itens_por_pagina">Itens por página:</label>
                    <select name="itens_por_pagina" id="itens_por_pagina">
                        <option value="5" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '5' ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '10' ? 'selected' : ''; ?>>10</option>
                        <option value="15" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '15' ? 'selected' : ''; ?>>15</option>
                        <option value="20" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '20' ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '50' ? 'selected' : ''; ?>>50</option>
                        <option value="75" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '75' ? 'selected' : ''; ?>>75</option>
                        <option value="100" <?php echo isset($_GET['itens_por_pagina']) && $_GET['itens_por_pagina'] == '100' ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>

                <input type="hidden" name="pagina"
                    value="<?php echo isset($_GET['pagina']) ? htmlspecialchars($_GET['pagina']) : '1'; ?>">
                <button type="submit" class="btn-criar">Aplicar</button>
                <div>
            <a href="home.php" class="btn-voltar">Voltar</a>
        </div>
            </fieldset>
            
        </form>

        <!-- Exibição dos Carros -->
        <table id="carros-table" class="carros-table">
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Marca</th>
                    <th>Ano</th>
                    <th>Cor</th>
                    <th>Placa</th>
                    <th>Diária</th>
                    <th>Disponibilidade</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody>
                <!-- As linhas da tabela serão inseridas dinamicamente pelo JavaScript -->
            </tbody>
            
        </table>
        
        

        <!-- Paginação -->
        <div id="pagination" class="pagination">
            <!-- Links de paginação serão inseridos dinamicamente pelo JavaScript -->
        </div>

        <script src="/locadora_de_carros/public/js/listar_carro.js"></script>
    </div>
</body>

</html>