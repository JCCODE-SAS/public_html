# CopFlow Dashboard – Documentación de Módulo “Usuarios”

Este documento describe la comunicación y el flujo de datos para la funcionalidad de **activar / desactivar usuarios** en el módulo de Usuarios.

---

## 1. Estructura de archivos clave

├─ dashboard/  
│ └─ paginas/  
│ └─ usuarios/  
│ ├─ comportamientos/  
│ │ ├─ usuarios.js ← Lógica general (toggleEstado, cargar lista, estadísticas)  
│ │ └─ nuevo_usuario.js ← Lógica de creación de usuarios (formulario, validación, submit)  
│ ├─ api/  
│ │ ├─ cambiar_estado_usuario.php ← Endpoint para update `status` en BD  
│ │ ├─ estadisticas_usuarios.php ← Endpoint para leer o regenerar cache de stats  
│ │ └─ limpiar_cache_stats.php ← Helper para borrar cache de estadísticas  
│ └─ usuarios.php ← Listado de usuarios (renderiza tabla, filtros, botón toggle)  
├─ modales/  
│ └─ crear_users_modal.php ← Fragmento HTML del modal de “Crear Usuario”  
└─ conexion/  
 └─ bd.php ← Conexión a MySQL (`$conexion`)  
 └─ logger.php ← Sistema de logging (`writeLog`)

---

## 2. Flujo de “Activar / Desactivar” usuario

1. **Listado de Usuarios** (`usuarios.php`)

   - Por cada fila de usuario se imprime un botón:
     ```html
     <button
       onclick="toggleEstado( ID, 'activo'|'inactivo', 'Nombre Apellido' )"
     >
       Activar / Desactivar
     </button>
     ```
   - Importa `usuarios.js` donde reside la función global `toggleEstado`.

2. **Front-end** (`usuarios.js`)

   - `toggleEstado(id, estadoActual, nombre)`:
     1. Calcula `nuevoEstado = (estadoActual==='activo'?'inactivo':'activo')`
     2. Llama a un modal de confirmación:
        ```js
        mostrarModalConfirmacion(
          `Confirmar ${accion}`,
          `¿Deseas ${accion} al usuario "${nombre}"?`,
          () => confirmarCambioEstado(id, nuevoEstado)
        );
        ```
     3. Si confirma, hace:
        ```js
        fetch(
          "/public_html/dashboard/paginas/usuarios/api/cambiar_estado_usuario.php",
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, nuevo_estado: nuevoEstado }),
          }
        )
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              mostrarModalExito("Éxito", data.message);
              // Actualiza la celda de estado en la tabla:
              fila.querySelector(".user-status").textContent =
                nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
              // Cambia el texto/handler del botón
              actualizarBotonToggle(fila, id, nuevoEstado);
              // Refresca contadores de usuarios
              actualizarEstadisticasUsuarios();
            } else {
              mostrarModalError("Error", data.message);
            }
          });
        ```

3. **Back-end** (`cambiar_estado_usuario.php`)

   - Incluye:
     ```php
     require_once __DIR__ . '/../../../configuracion/bd.php';
     require_once __DIR__ . '/../../../logs/logger.php';
     ```
   - Lee JSON de `php://input` (o `$_POST`), registra con `writeLog`.
   - Prepara y ejecuta:
     ```php
     $stmt = $conexion->prepare(
       "UPDATE users SET status = ? WHERE id = ?"
     );
     $stmt->bind_param("si", $nuevo_estado, $id);
     $stmt->execute();
     ```
   - Si `execute()` OK:
     1. Llama a `limpiar_cache_stats.php` para invalidar cache.
     2. Envía respuesta JSON:
        ```json
        { "success": true, "message": "Estado actualizado correctamente." }
        ```
   - En error:
     ```json
     { "success": false, "message": "No se pudo actualizar el estado." }
     ```

4. **Base de datos**

   - Tabla principal: `users`
     - Columnas relevantes:
       - `id` (INT, PK)
       - `status` (ENUM('activo','inactivo'))
   - El UPDATE modifica solo la columna `status`.

5. **Cache de Estadísticas**
   - Cada vez que cambia un estado, se elimina el archivo JSON en:
     ```
     dashboard/paginas/usuarios/api/cache/stats_usuarios.json
     ```
   - `estadisticas_usuarios.php` detecta ausencia de cache y regenera conteos:
     ```php
     SELECT COUNT(*) AS total,
            SUM(status='activo') AS active,
            SUM(status='inactivo') AS inactive,
            SUM(role='admin') AS admins
       FROM users;
     ```
   - Devuelve JSON con los valores, el front-end actualiza los widgets.

---

## 3. Resumen de dependencias

- Front-end (HTML + JS):
  - `usuarios.php` → importa `usuarios.js`
  - `usuarios.js` → llama a endpoints PHP y modales
- Modales:
  - `crear_users_modal.php` → fragmento HTML para creación
  - `nuevo_usuario.js` → lógica de formulario inyectado
- Back-end (PHP):
  - `bd.php`, `logger.php` → conexión y registro de logs
  - `cambiar_estado_usuario.php` → actualiza BD + limpia cache
  - `estadisticas_usuarios.php` → lee o genera cache de estadísticas

Con esta guía tienes el flujo completo: desde que el admin hace clic en “activar/desactivar” hasta que el cambio se refleja en la base de datos y en la interfaz de usuario.
