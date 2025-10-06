function actualizarEstadisticasUsuarios() {
fetch("/public_html/dashboard/paginas/usuarios/api/estadisticas_usuarios.php", {
method: 'GET',
headers: {
'Accept': 'application/json',
'Content-Type': 'application/json'
}
})
.then(response => {
// Verificar que la respuesta sea JSON
if (!response.ok) {
throw new Error(`HTTP ${response.status}: ${response.statusText}`);
}

const contentType = response.headers.get('Content-Type');
if (!contentType || !contentType.includes('application/json')) {
throw new Error('La respuesta no es JSON válido');
}

return response.json();
})
.then(stats => {
console.log("📊 Estadísticas recibidas:", stats);

if (!stats.success) {
throw new Error(stats.message || 'Error en la respuesta del servidor');
}

const elementos = {
total: document.getElementById("stats-total"),
active: document.getElementById("stats-active"),
inactive: document.getElementById("stats-inactive"),
admins: document.getElementById("stats-admins")
};

Object.keys(elementos).forEach(key => {
if (elementos[key] && stats[key] !== undefined) {
elementos[key].textContent = stats[key];
}
});
})
.catch(error => {
console.error("❌ Error al cargar estadísticas:", error);
// Mostrar valores por defecto en caso de error
const elementos = {
total: document.getElementById("stats-total"),
active: document.getElementById("stats-active"),
inactive: document.getElementById("stats-inactive"),
admins: document.getElementById("stats-admins")
};

Object.values(elementos).forEach(elem => {
if (elem) elem.textContent = '--';
});
});
}