<?php
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

// Registros por página configurable
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
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}
if ($status_filter !== '') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
    $types   .= 's';
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

//===============================================================
// TOTAL DE REGISTROS
//===============================================================
$count_sql  = "SELECT COUNT(*) AS total FROM operadores $where_clause";
$count_stmt = $conexion->prepare($count_sql);
if ($types !== '') {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = (int)$count_stmt->get_result()->fetch_assoc()['total'];
$total_pages   = max(1, (int)ceil($total_records / $records_per_page));

// Ajustar página si excede
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $records_per_page;

//===============================================================
// CONSULTA PRINCIPAL: tabla operadores y columnas renombradas
//===============================================================
$sql = "
  SELECT
    id,
    nombre     AS nombre,
    usuario    AS usuario,
    email,
    disponible AS disponible,
    creado     AS creado
  FROM operadores
  $where_clause
  ORDER BY creado DESC
  LIMIT ? OFFSET ?
";

// -------------------------------------------------------
// Paginación: calcular offset y agregar a tipos/params
// -------------------------------------------------------
$offset = ($page - 1) * $records_per_page;
$types  .= 'ii';
$params[] = $records_per_page;
$params[] = $offset;

// -------------------------------------------------------
// Preparar, bindear y ejecutar
// -------------------------------------------------------
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

//===============================================================
// ROLES DISPONIBLES
//===============================================================
$roles = [];
$roles_sql = "SELECT DISTINCT role FROM users ORDER BY role";
if ($rr = $conexion->query($roles_sql)) {
    while ($r = $rr->fetch_assoc()) {
        $roles[] = $r['role'];
    }
}

//===============================================================
// ESTADÍSTICAS
//===============================================================
$stats_queries = [
    'total'       => "SELECT COUNT(*) as count FROM operadores",
    'available'   => "SELECT COUNT(*) as count FROM operadores WHERE disponible=1",
    'unavailable' => "SELECT COUNT(*) as count FROM operadores WHERE disponible=0"
];

$stats = [];
foreach ($stats_queries as $k => $q) {
    $st = $conexion->prepare($q);
    $st->execute();
    $stats[$k] = $st->get_result()->fetch_assoc()['count'];
}

//===============================================================
// HELPER PARA LINKS DE PAGINACIÓN
//===============================================================
function base_query(array $extra = []): string
{
    $current = $_GET;
    unset($current['page']); // forzar reemplazo
    $merged = array_merge($current, $extra);
    return '?' . http_build_query($merged);
}

?>
<div class="users-module min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 p-6"
    id="usuarios-wrapper">

    <!-- HEADER -->
    <div class="glass-header rounded-3xl p-8 mb-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1
                    class="text-4xl font-bold mb-2 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    Gestión de Operarios
                </h1>
                <p class="text-blue-100 text-lg">Administra y controla los usuarios del sistema de manera eficiente</p>
                <div class="flex flex-wrap items-center mt-3 text-blue-200 text-sm gap-x-2">
                    <i class="ri-user-line"></i>
                    <span><?= $total_records ?> usuarios</span>
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

    <!-- FILTROS Y NUEVO OPERARIO -->
    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
        <form method="get" class="flex flex-wrap items-center space-x-2">
            <input type="text" name="search" placeholder="Buscar operarios..." value="<?= htmlspecialchars($search) ?>"
                class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <select name="status"
                class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Disponible</option>
                <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>No disponible</option>
            </select>
            <select name="per_page"
                class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <?php foreach ([10, 25, 50, 100] as $n): ?>
                <option value="<?= $n ?>" <?= $records_per_page === $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                Filtrar
            </button>
        </form>
        <button onclick="mostrarModalNuevoOperario()"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm mt-2 md:mt-0">
            + Nuevo Operario
        </button>
    </div>

    <!-- TABLA DE OPERARIOS -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Disponible</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap"><?= $row['id'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['nombre']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['usuario']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= $row['disponible'] ? 'Sí' : 'No' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($row['creado'])) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                        <button onclick="verOperario(<?= $row['id'] ?>)"
                            class="text-blue-600 hover:text-blue-900">Ver</button>
                        <button onclick="editarOperario(<?= $row['id'] ?>)"
                            class="text-green-600 hover:text-green-900">Editar</button>
                        <button onclick="toggleDisponibilidad(<?= $row['id'] ?>, <?= $row['disponible'] ?>)"
                            class="text-red-600 hover:text-red-900">
                            <?= $row['disponible'] ? 'Deshabilitar' : 'Habilitar' ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <nav class="mt-6 flex justify-center">
        <ul class="inline-flex -space-x-px">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li>
                <a href="<?= base_query(['page' => $i]) ?>"
                    class="px-3 py-2 border border-gray-300 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-blue-600' ?> hover:bg-blue-100">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <?php
    $result->free();
    $conexion->close();
    ?>
</div>