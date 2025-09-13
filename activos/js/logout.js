"use strict";

/**
 * Handler de cierre de sesión para el botón #logoutBtn
 * - Usa rutas.logout y rutas.login de ubicacion_paginas.js si existen.
 * - Fallback de rutas si ubicacion_paginas.js no está cargado.
 * - Notifica con modales (si existen) o alert/Toast fallback.
 */

(function () {
  // Evita doble inicialización si se carga dos veces
  if (window.__COPFLOW_LOGOUT_INIT__) return;
  window.__COPFLOW_LOGOUT_INIT__ = true;

  // Rutas fallback si no existe window.rutas
  (function ensureRoutes() {
    if (typeof window.rutas === "undefined") {
      const isLocalhost = ["localhost", "127.0.0.1"].includes(window.location.hostname);
      const baseURL = isLocalhost
        ? `${window.location.origin}/public_html/`
        : `${window.location.origin}/`;
      window.rutas = {
        login: baseURL + "index.php",
        logout: baseURL + "sesiones/destruir_sesion.php",
      };
    }
  })();

  function toast(text, type = "info") {
    const colors = { info: "#3b82f6", success: "#16a34a", error: "#dc2626", warn: "#f59e0b" };
    const wrapId = "copflow-toast-wrap";
    let wrap = document.getElementById(wrapId);
    if (!wrap) {
      wrap = document.createElement("div");
      wrap.id = wrapId;
      wrap.style.position = "fixed";
      wrap.style.top = "20px";
      wrap.style.right = "20px";
      wrap.style.display = "flex";
      wrap.style.flexDirection = "column";
      wrap.style.gap = "10px";
      wrap.style.zIndex = "10000";
      document.body.appendChild(wrap);
    }
    const el = document.createElement("div");
    el.textContent = text;
    el.style.background = "#fff";
    el.style.borderLeft = `4px solid ${colors[type] || colors.info}`;
    el.style.padding = "10px 12px";
    el.style.borderRadius = "10px";
    el.style.boxShadow = "0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)";
    el.style.color = "#111827";
    el.style.transform = "translateX(120%)";
    el.style.transition = "transform 300ms ease, opacity 300ms ease";
    el.style.opacity = "0.95";
    wrap.appendChild(el);
    requestAnimationFrame(() => (el.style.transform = "translateX(0)"));
    setTimeout(() => {
      el.style.opacity = "0";
      el.style.transform = "translateX(120%)";
      setTimeout(() => el.remove(), 300);
    }, 2200);
  }

  function notifySuccess(msg) {
    if (typeof window.mostrarNotificacionExito === "function") {
      try {
        window.mostrarNotificacionExito(msg);
        return;
      } catch {}
    }
    toast(msg, "success");
  }
  function notifyError(msg) {
    if (typeof window.mostrarNotificacionError === "function") {
      try {
        window.mostrarNotificacionError(msg);
        return;
      } catch {}
    }
    toast(msg, "error");
  }

  async function doLogout(btn) {
    // Si tienes modal de confirmación propio, puedes invocarlo aquí.
    const confirmar = confirm("¿Estás seguro de que deseas cerrar sesión?");
    if (!confirmar) return;

    try {
      if (btn) btn.disabled = true;

      const res = await fetch(window.rutas.logout, {
        method: "GET", // Compatible con tu destruir_sesion.php actual
        headers: { Accept: "application/json" },
        cache: "no-store",
        credentials: "same-origin",
      });

      const contentType = (res.headers.get("content-type") || "").toLowerCase();
      let data = {};
      if (contentType.includes("application/json")) {
        data = await res.json().catch(() => ({}));
      }

      const ok =
        res.ok &&
        (data.success === true ||
          data.cerrada === true ||
          data.logout === true ||
          data.estado === "ok" ||
          // Si no devuelve JSON pero responde 200, lo consideramos correcto
          !contentType.includes("application/json"));

      if (ok) {
        notifySuccess(data.message || "Sesión cerrada correctamente.");
        // Usa replace para evitar volver con el botón Atrás
        setTimeout(() => location.replace(window.rutas.login), 500);
      } else {
        notifyError(data.message || "No se pudo cerrar sesión.");
      }
    } catch (err) {
      console.error("Error logout:", err);
      notifyError("Error al cerrar sesión.");
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    const logoutBtn = document.getElementById("logoutBtn");
    if (!logoutBtn) return;
    logoutBtn.addEventListener("click", () => doLogout(logoutBtn));
  });
})();