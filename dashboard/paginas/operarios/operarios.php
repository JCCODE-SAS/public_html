<?php
$roles_permitidos = ['admin'];
include_once __DIR__ . '/../../../sesiones/control_acceso.php';
require_once __DIR__ . '/../../../configuracion/bd.php';
require_once __DIR__ . '/../../../logs/logger.php';

?>
<link rel="stylesheet" href="/public_html/dashboard/paginas/operarios/comportamientos/operarios.css">
<?php

//===============================================================
// PARÁMETROS DE FILTROS
//===============================================================
$search        = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$page          = isset($_GET['page'])   ? (int)$_GET['page'] : 1;
$page          = max(1, $page);

$allowed_page_sizes = [10, 25, 50, 100, 200];
$records_per_page   = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
if (!in_array($records_per_page, $allowed_page_sizes)) {
    $records_per_page = 10;
}

writeLog("operarios.php", "Filtros recibidos: search='$search', status='$status_filter', page=$page, per_page=$records_per_page");

//===============================================================
// CONSTRUCCIÓN DE CONSULTA CON FILTROS
//===============================================================
$where_conditions = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where_conditions[] = "(nombre LIKE ? OR usuario LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'sss';
}
if ($status_filter !== '') {
    $where_conditions[] = "disponible = ?";
    $params[] = $status_filter;
    $types   .= 'i';
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

//===============================================================
// TOTAL DE REGISTROS
//===============================================================
$count_sql = "SELECT COUNT(*) AS total FROM operadores $where_clause";
$count_stmt = $conexion->prepare($count_sql);
if ($types !== '') {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = (int)$count_stmt->get_result()->fetch_assoc()['total'];
$total_pages   = max(1, (int)ceil($total_records / $records_per_page));

// Ajuste de página si excede
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $records_per_page;

//===============================================================
// CONSULTA PRINCIPAL
//===============================================================
$sql = "SELECT id, nombre, usuario, email, disponible, creado, actualizado 
        FROM operadores 
        $where_clause 
        ORDER BY creado DESC 
        LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($sql);
$main_types = $types . 'ii';
$main_params = $params;
$main_params[] = $records_per_page;
$main_params[] = $offset;
$stmt->bind_param($main_types, ...$main_params);
$stmt->execute();
$result = $stmt->get_result();

//===============================================================
// ESTADÍSTICAS OPTIMIZADAS
//===============================================================
$stats_sql = "SELECT COUNT(*) as total, 
                     SUM(disponible=1) as available, 
                     SUM(disponible=0) as unavailable 
              FROM operadores";
$stats_stmt = $conexion->prepare($stats_sql);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

//===============================================================
// HELPER PARA LINKS DE PAGINACIÓN
//===============================================================
function base_query(array $extra = []): string
{
    $current = $_GET;
    unset($current['page']);
    $merged = array_merge($current, $extra);
    return '?' . http_build_query($merged);
}
?>

<div class="users-module min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-6"
    id="operarios-wrapper">

    <!-- HEADER -->
    <div class="glass-header rounded-3xl p-8 mb-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1
                    class="text-4xl font-bold mb-2 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    Gestión de Operarios
                </h1>
                <p class="text-blue-100 text-lg">Administra y controla los operarios del sistema de manera eficiente</p>
                <div class="flex flex-wrap items-center mt-3 text-blue-200 text-sm gap-x-2">
                    <i class="ri-user-line"></i>
                    <span><?= $total_records ?> operarios</span>
                    <span class="text-blue-300">•</span>
                    <span>Página <?= $page ?> / <?= $total_pages ?></span>
                    <span class="text-blue-300">•</span>
                    <span class="text-blue-300">Registros por página: <?= $records_per_page ?></span>
                </div>
            </div>
            <div class="mt-6 lg:mt-0">
                <button onclick="mostrarModalNuevoOperario()" class="action-btn btn-add group">
                    <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nuevo Operario
                </button>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="stats-card rounded-2xl p-6 hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Operarios</p>
                    <p class="text-3xl font-bold text-gray-800" id="stats-total"><?= $stats['total'] ?></p>
                    <p class="text-xs text-gray-500 mt-1">Operarios registrados</p>
                </div>
                <div
                    class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                    <i class="ri-group-line text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stats-card rounded-2xl p-6 hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Operarios disponibles</p>
                    <p class="text-3xl font-bold text-gray-800" id="stats-available"><?= $stats['available'] ?></p>
                    <p class="text-xs text-gray-500 mt-1">Activos y listos</p>
                </div>
                <div
                    class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-xl flex items-center justify-center">
                    <i class="ri-checkbox-circle-line text-white text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stats-card rounded-2xl p-6 hover:scale-105 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Operarios no disponibles</p>
                    <p class="text-3xl font-bold text-gray-800" id="stats-unavailable"><?= $stats['unavailable'] ?></p>
                    <p class="text-xs text-gray-500 mt-1">Actualmente inactivos</p>
                </div>
                <div
                    class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <i class="ri-close-circle-line text-white text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filter-glass rounded-2xl p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="ri-filter-3-line mr-2 text-blue-600"></i>
                Filtros de Búsqueda
            </h2>
            <div class="text-sm text-gray-600">
                <?= $total_records ?> resultado<?= $total_records !== 1 ? 's' : '' ?>
            </div>
        </div>
        <form id="filtroOperariosForm" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="ri-search-line mr-1"></i>Buscar Operario
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Nombre, usuario o email..."
                        class="search-input w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="ri-toggle-line mr-1"></i>Disponibilidad
                </label>
                <select name="status"
                    class="filter-select w-full px-4 py-3 rounded-xl border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Disponible</option>
                    <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>No disponible</option>
                </select>
            </div>
            <div class="md:col-span-1 flex justify-end">
                <button type="button" id="limpiarFiltrosBtn" class="action-btn btn-clear w-full lg:w-auto group">
                    <i class="ri-refresh-line mr-1"></i> Limpiar
                </button>
            </div>
        </form>
    </div>

    <!-- TABLA -->
    <div class="table-glass rounded-2xl overflow-hidden shadow-2xl">
        <?php if ($result->num_rows === 0): ?>
            <div class="empty-state p-12 text-center rounded-2xl">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-yellow-400/20 to-orange-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-alert-line text-yellow-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    <?= ($search || $status_filter) ? 'No se encontraron operarios con esos criterios' : 'No hay operarios registrados' ?>
                </h3>
                <p class="text-gray-500 mb-6">
                    <?= ($search || $status_filter)
                        ? 'Ajusta los filtros e inténtalo de nuevo.'
                        : 'Agrega el primer operario al sistema.' ?>
                </p>
                <div class="flex gap-3 justify-center flex-wrap">
                    <?php if ($search || $status_filter): ?>
                        <button type="button" onclick="limpiarTodosFiltros()" class="action-btn btn-edit">
                            <i class="ri-filter-off-line"></i> Limpiar filtros
                        </button>
                    <?php endif; ?>
                    <button onclick="mostrarModalNuevoOperario()" class="action-btn btn-add">
                        <i class="ri-user-add-line"></i> Agregar Operario
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Disponible</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Fecha creación</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Última edición</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php while ($o = $result->fetch_assoc()): ?>
                            <tr data-operario-id="<?= $o['id'] ?>"
                                class="hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group">
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                    #<?= str_pad($o['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-medium">
                                            <?= strtoupper(substr($o['nombre'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($o['nombre']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($o['usuario']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="ri-user-line mr-1"></i>
                                        <?= htmlspecialchars($o['usuario']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($o['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 user-status">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= $o['disponible'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <span
                                            class="w-1.5 h-1.5 mr-1.5 rounded-full <?= $o['disponible'] ? 'bg-green-600' : 'bg-red-600' ?>"></span>
                                        <?= $o['disponible'] ? 'Disponible' : 'No disponible' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="ri-calendar-2-line mr-2 text-gray-400"></i>
                                        <?= date('d/m/Y', strtotime($o['creado'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <?= date('H:i', strtotime($o['creado'])) ?> hrs
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php if (!empty($o['actualizado'])): ?>
                                        <span class="bg-blue-50 text-blue-800 px-2 py-1 rounded text-xs">
                                            <i class="ri-history-line mr-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($o['actualizado'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Sin edición</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="acciones-btn-group flex items-center justify-center space-x-2">
                                        <button onclick="abrirModalVerOperario(<?= $o['id'] ?>)"
                                            class="action-btn btn-view text-xs" title="Ver">
                                            <i class="ri-eye-line"></i> Ver
                                        </button>
                                        <button onclick="editarOperario(<?= $o['id'] ?>)" class="action-btn btn-edit text-xs"
                                            title="Editar">
                                            <i class="ri-edit-line"></i> Editar
                                        </button>
                                        <button type="button" class="action-btn btn-toggle text-xs"
                                            onclick="toggleDisponibilidad(<?= $o['id'] ?>, <?= $o['disponible'] ?>, '<?= htmlspecialchars($o['nombre']) ?>')"
                                            title="<?= $o['disponible'] ? 'Deshabilitar' : 'Habilitar' ?>">
                                            <i
                                                class="<?= $o['disponible'] ? 'ri-pause-circle-line' : 'ri-play-circle-line' ?>"></i>
                                            <?= $o['disponible'] ? 'Deshabilitar' : 'Habilitar' ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- PAGINACIÓN MEJORADA -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-10 space-y-4" id="paginacion-operarios">

            <!-- Barra superior: info + selector per_page -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-600 flex flex-wrap items-center gap-x-2">
                    <i class="ri-file-list-line text-gray-500"></i>
                    Mostrando
                    <span class="font-semibold">
                        <?= $total_records === 0 ? 0 : ($offset + 1) ?> -
                        <?= min($offset + $records_per_page, $total_records) ?>
                    </span>
                    de
                    <span class="font-semibold"><?= $total_records ?></span>
                    operarios
                </div>

                <form method="GET" class="flex items-center gap-2 text-sm" id="formPerPage">
                    <?php
                    foreach ($_GET as $k => $v) {
                        if (in_array($k, ['per_page', 'page'])) continue;
                        if (is_array($v)) {
                            foreach ($v as $vv) {
                                echo '<input type="hidden" name="' . htmlspecialchars($k) . '[]" value="' . htmlspecialchars($vv) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '">';
                        }
                    }
                    ?>
                    <label class="text-gray-600">Registros:</label>
                    <select name="per_page"
                        class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white per-page-select">
                        <?php foreach ($allowed_page_sizes as $size): ?>
                            <option value="<?= $size ?>" <?= $size === $records_per_page ? 'selected' : '' ?>><?= $size ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button class="px-3 py-2 bg-blue-600 text-white rounded-lg">Aplicar</button></noscript>
                </form>
            </div>

            <!-- Navegación -->
            <nav class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                aria-label="Paginación de operarios">

                <!-- Controles -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Primera -->
                    <a href="<?= base_query(['page' => 1]) ?>"
                        class="pagination-btn <?= $page === 1 ? 'opacity-40 pointer-events-none' : '' ?>"
                        aria-label="Primera página">«</a>

                    <!-- Anterior -->
                    <a href="<?= base_query(['page' => max(1, $page - 1)]) ?>"
                        class="pagination-btn <?= $page === 1 ? 'opacity-40 pointer-events-none' : '' ?>"
                        aria-label="Página anterior">‹</a>

                    <?php
                    $window = 2;
                    $start_page = max(1, $page - $window);
                    $end_page   = min($total_pages, $page + $window);

                    if ($start_page > 1) {
                        echo '<a href="' . base_query(['page' => 1]) . '" class="pagination-btn">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="px-2 text-gray-400">...</span>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i === $page ? 'pagination-active' : '';
                        echo '<a href="' . base_query(['page' => $i]) . '" class="pagination-btn ' . $active . '"' .
                            ($active ? ' aria-current="page"' : '') . '>' . $i . '</a>';
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="px-2 text-gray-400">...</span>';
                        }
                        echo '<a href="' . base_query(['page' => $total_pages]) . '" class="pagination-btn">' . $total_pages . '</a>';
                    }
                    ?>

                    <!-- Siguiente -->
                    <a href="<?= base_query(['page' => min($total_pages, $page + 1)]) ?>"
                        class="pagination-btn <?= $page === $total_pages ? 'opacity-40 pointer-events-none' : '' ?>"
                        aria-label="Página siguiente">›</a>

                    <!-- Última -->
                    <a href="<?= base_query(['page' => $total_pages]) ?>"
                        class="pagination-btn <?= $page === $total_pages ? 'opacity-40 pointer-events-none' : '' ?>"
                        aria-label="Última página">»</a>
                </div>

                <!-- Ir a página -->
                <form method="GET" class="flex items-center gap-2 text-sm" id="formGoTo"
                    onsubmit="if (this.querySelector('[name=page]').value > <?= $total_pages ?>) { this.querySelector('[name=page]').value = <?= $total_pages ?> }">
                    <?php
                    foreach ($_GET as $k => $v) {
                        if ($k === 'page') continue;
                        if (is_array($v)) {
                            foreach ($v as $vv) {
                                echo '<input type="hidden" name="' . htmlspecialchars($k) . '[]" value="' . htmlspecialchars($vv) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '">';
                        }
                    }
                    ?>
                    <label for="go_to_page" class="text-gray-600">Ir a:</label>
                    <input type="number" min="1" max="<?= $total_pages ?>" name="page" id="go_to_page"
                        class="w-20 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white pagination-go"
                        value="<?= $page ?>">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                        Ir
                    </button>
                </form>
            </nav>
        </div>
    <?php endif; ?>

</div>

<!-- SCRIPTS -->
<?php
$scripts_operarios = [
    'operarios.js',
    'ver_operarios.js',
    'crear_operarios.js',
    'editar_operarios.js'
];
$base_operarios = '/public_html/dashboard/paginas/operarios/comportamientos/';
foreach ($scripts_operarios as $fichero) {
    $ruta = $_SERVER['DOCUMENT_ROOT'] . $base_operarios . $fichero;
    if (file_exists($ruta)) {
        echo "<script src=\"$base_operarios$fichero\"></script>\n";
    }
}
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.inicializarOperarios === 'function') {
            window.inicializarOperarios();
        }
    });
</script>
<?php
$result->free();
$conexion->close();
writeLog("operarios.php", "Módulo operarios cargado. Total=$total_records, Página=$page, PerPage=$records_per_page");
?>