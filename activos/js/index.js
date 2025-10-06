document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        try {
            const formData = new FormData(form);

            const response = await fetch(rutas.validarLogin, {
                method: "POST",
                body: formData
            });

            const text = await response.text();
            console.log("🔎 Respuesta cruda del servidor:", text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("❌ Error parseando JSON:", err);
                mostrarModalError("El servidor no devolvió un JSON válido. Revisa logs.");
                return;
            }

            if (data.success) {
                console.log("✅ Login correcto:", data);
                mostrarModalExito(data.message || "Inicio de sesión exitoso");
                // Redirige al dashboard tras mostrar el modal de éxito
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        } catch (err) {
            console.error("❌ Error en login:", err);
            mostrarModalError("Error de conexión con el servidor");
        }
    });
});
