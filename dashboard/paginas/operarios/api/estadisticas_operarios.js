function actualizarEstadisticasOperarios() {
    fetch("/public_html/dashboard/paginas/operarios/api/estadisticas_operarios.php", {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
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
        console.log("📊 Estadísticas operarios recibidas:", stats);

        if (!stats.success) {
            throw new Error(stats.message || 'Error en la respuesta del servidor');
        }

        // Los IDs deben coincidir con los que usas en el dashboard de operarios
        const elementos = {
            total: document.getElementById("stats-total"),
            available: document.getElementById("stats-available"),
            unavailable: document.getElementById("stats-unavailable")
        };

        Object.keys(elementos).forEach(key => {
            if (elementos[key] && stats[key] !== undefined) {
                elementos[key].textContent = stats[key];
            }
        });
    })
    .catch(error => {
        console.error("❌ Error al cargar estadísticas operarios:", error);
        const elementos = {
            total: document.getElementById("stats-total"),
            available: document.getElementById("stats-available"),
            unavailable: document.getElementById("stats-unavailable")
        };
        Object.values(elementos).forEach(elem => {
            if (elem) elem.textContent = '--';
        });
    });
}