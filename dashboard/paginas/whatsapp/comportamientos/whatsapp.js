(function () {
    "use strict";
    if (window.__WA_MODULE_LOADED__) return;
    window.__WA_MODULE_LOADED__ = true;

    const CONFIG = { DEBUG_MODE: true };
    let chatSeleccionado = null;
    let chatsCache = [];
    let _wa_lastSignalTimestamp = null;
    let _wa_signalPollId = null;

    function log(msg, type = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        const prefix = `[${new Date().toLocaleTimeString()}] [WHATSAPP.JS]`;
        const icons = { error: '❌', warn: '⚠️', success: '✅', info: '🔄' };
        console[type === 'error' ? 'error' : type === 'warn' ? 'warn' : 'log'](`${prefix} ${icons[type] || icons.info}`, msg);
    }

    function cargarChats() {
        fetch('/public_html/dashboard/paginas/whatsapp/api/obtener_chats.php', { credentials: 'include' })
            .then(resp => resp.json())
            .then(data => {
                const lista = document.getElementById('wa-chatlist');
                if (!lista) return;
                lista.innerHTML = '';

                if (!data.ok) {
                    lista.innerHTML = `<li style=\"color:#d33;padding:20px;\">${data.error || 'Error cargando chats'}</li>`;
                    log(data.error || 'Error cargando chats', 'error');
                    return;
                }

                if (!Array.isArray(data.chats) || data.chats.length === 0) {
                    lista.innerHTML = `<li style=\"color:#aaa;padding:20px;\">No hay chats activos</li>`;
                    return;
                }

                chatsCache = data.chats;

                data.chats.forEach(chat => {
                    const li = document.createElement('li');
                    li.className = 'wa-chat';
                    li.dataset.chatId = chat.id;
                    // Badge de no leídos
                    let badge = '';
                    if (chat.no_leidos && chat.no_leidos > 0) {
                        badge = `<span class=\"wa-badge\">${chat.no_leidos}</span>`;
                    }
                    li.innerHTML = `<div><strong>${chat.cliente}</strong> ${badge}</div><div>${chat.estado}</div>`;
                    li.addEventListener('click', () => {
                        if (chatSeleccionado === chat.id) return;
                        document.querySelectorAll('.wa-chat').forEach(c => c.classList.remove('selected'));
                        li.classList.add('selected');
                        chatSeleccionado = chat.id;
                        // Marcar como leídos los mensajes de este chat
                        fetch('/public_html/dashboard/paginas/whatsapp/api/marcar_leido.php', {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id_chat=' + encodeURIComponent(chat.id)
                        }).then(() => {
                            // Refrescar chats y badge global después de marcar como leídos
                            cargarChats();
                        });
                        cargarMensajes(chat.id, true);
                    });
                    lista.appendChild(li);
                });

                // Actualizar badge global del menú
                actualizarBadgeGlobal();

                log(`Chats activos cargados (${data.chats.length})`, 'success');
            })
            .catch(err => {
                const lista = document.getElementById('wa-chatlist');
                if (lista) lista.innerHTML = `<li style=\"color:#d33;padding:20px;\">Error de red al cargar chats</li>`;
                log(`Error de red al cargar chats: ${err.message}`, 'error');
            });
    }

    // Consulta el total global de mensajes no leídos y lo muestra en el menú
    function actualizarBadgeGlobal() {
        fetch('/public_html/dashboard/paginas/whatsapp/api/contar_no_leidos.php', { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('wa-menu-badge');
                if (!badge) return;
                if (data.ok && data.total_no_leidos > 0) {
                    badge.textContent = data.total_no_leidos;
                    badge.style.display = '';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
    }

    // Cache de IDs de mensajes renderizados por chat
    let mensajesRenderizados = {};

    function cargarMensajes(chatId, scrollToEnd) {
        const cont = document.getElementById('wa-messages');
        if (!cont) return;

        // Si cambiamos de chat, limpiar cache y recargar todo
        if (!mensajesRenderizados[chatId]) mensajesRenderizados[chatId] = [];
        const idsActuales = new Set(mensajesRenderizados[chatId]);

        // Solo mostrar 'Cargando...' si no hay mensajes en pantalla
        if ((chatSeleccionado !== chatId || cont.childElementCount === 0) && mensajesRenderizados[chatId].length === 0) {
            cont.innerHTML = '<div style="color:#bbb;text-align:center;margin-top:60px;">Cargando mensajes...</div>';
        }

        fetch(`/public_html/dashboard/paginas/whatsapp/api/obtener_mensajes.php?id_chat=${encodeURIComponent(chatId)}`, { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    cont.innerHTML = `<div style=\"color:#d33;text-align:center;margin-top:60px;\">${data.error || 'Error cargando mensajes'}</div>`;
                    log(data.error || 'Error cargando mensajes', 'error');
                    return;
                }

                if (!Array.isArray(data.mensajes) || data.mensajes.length === 0) {
                    cont.innerHTML = '<div style=\"color:#bbb;text-align:center;margin-top:60px;\">No hay mensajes en este chat</div>';
                    mensajesRenderizados[chatId] = [];
                } else {
                    // Si es la primera carga o cambio de chat, renderizar todo
                    if (chatSeleccionado !== chatId || cont.childElementCount === 0) {
                        cont.innerHTML = '';
                        data.mensajes.forEach(msg => {
                            renderMensaje(msg, cont);
                        });
                        mensajesRenderizados[chatId] = data.mensajes.map(m => m.id);
                        if (scrollToEnd !== false) cont.scrollTop = cont.scrollHeight;
                    } else {
                        // Solo agregar los mensajes nuevos (sin limpiar el DOM)
                        let nuevos = 0;
                        data.mensajes.forEach(msg => {
                            if (!idsActuales.has(msg.id)) {
                                renderMensaje(msg, cont);
                                mensajesRenderizados[chatId].push(msg.id);
                                nuevos++;
                            }
                        });
                        if (nuevos > 0 && scrollToEnd !== false) cont.scrollTop = cont.scrollHeight;
                    }
                }

                mostrarInput(chatId);
                log(`Mensajes cargados para chat ${chatId} (${data.mensajes.length || 0})`, 'success');
            })
            .catch(err => {
                cont.innerHTML = '<div style=\"color:#d33;text-align:center;margin-top:60px;\">Error de red al cargar mensajes</div>';
                log(`Error de red al cargar mensajes: ${err.message}`, 'error');
            });
    }

    function renderMensaje(msg, cont) {
        const div = document.createElement('div');
        const tipo = msg.enviado_por === 'cliente' ? 'cliente' :
                     msg.enviado_por === 'operador' ? 'operador' : 'mia';
        div.className = 'wa-bubble ' + tipo;
        div.innerHTML = `<div>${msg.texto}</div><div class=\"wa-msg-meta\">${msg.fecha} ${msg.hora}</div>`;
        cont.appendChild(div);
    }

    function mostrarInput(chatId) {
        const form = document.getElementById('wa-form');
        if (!form) return;
        form.style.display = '';
        form.onsubmit = e => {
            e.preventDefault();
            const inp = document.getElementById('wa-input');
            const txt = inp.value.trim();
            if (!txt) return;
            enviarMensaje(chatId, txt);
            inp.value = '';
        };
    }

    function obtenerNumeroCliente(chatId) {
        const chat = chatsCache.find(c => c.id === chatId);
        return chat?.numero || '';
    }

    function enviarMensaje(chatId, texto) {
        const numero = obtenerNumeroCliente(chatId);
        if (!numero) {
            alert('Número de cliente no disponible');
            log('❌ Número vacío para chatId ' + chatId, 'error');
            return;
        }

        fetch('https://integra.smartinicio.com/webhook/copflow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cliente: { numero }, message: { texto } })
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok || data.message === 'Workflow was started') {
                fetch('/public_html/dashboard/paginas/whatsapp/api/registrar_mensaje.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_chat: chatId, texto })
                })
                .then(() => {
                    insertarMensaje(chatId, texto);
                    log('Mensaje enviado y registrado en BD', 'success');
                })
                .catch(err => {
                    log('⚠️ Error al registrar mensaje en BD: ' + err.message, 'warn');
                });
            } else {
                alert('No se pudo enviar el mensaje');
                log('Error enviando mensaje: ' + JSON.stringify(data), 'error');
            }
        })
        .catch(err => {
            alert('Error de red al enviar mensaje');
            log(`Error de red al enviar mensaje: ${err.message}`, 'error');
        });
    }

    function insertarMensaje(chatId, texto) {
        const cont = document.getElementById('wa-messages');
        if (!cont || chatSeleccionado !== chatId) return;

        const nuevo = document.createElement('div');
        nuevo.className = 'wa-bubble mia';
        nuevo.innerHTML = `<div>${texto}</div><div class="wa-msg-meta">${formatearHora(new Date())}</div>`;
        cont.appendChild(nuevo);
        cont.scrollTop = cont.scrollHeight;
    }

    function formatearHora(fecha) {
        return fecha.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
    }

    function inicializarWhatsapp() {
        log('Inicializando módulo WhatsApp...', 'success');
        chatSeleccionado = null;
        cargarChats();
        // Iniciar polling de la señal de actualización (archivo JSON escrito por la API)
        // Si ya hay un poll activo, limpiarlo (evita duplicados cuando se llama varias veces)
        if (_wa_signalPollId) clearInterval(_wa_signalPollId);
        // Ejecutar una primera comprobación inmediata y luego cada 2s
        pollSignal();
        _wa_signalPollId = setInterval(pollSignal, 2000);
    }

    // Consulta el archivo de señal para detectar eventos generados por la API
    function pollSignal() {
        // Llamamos a un endpoint PHP que devuelve la señal (maneja permisos y filenames especiales)
        const url = '/public_html/dashboard/paginas/whatsapp/api/leer_senal.php?ts=' + Date.now();
        fetch(url, { credentials: 'include', cache: 'no-store' })
            .then(r => {
                if (!r.ok) throw new Error('No hay señal');
                return r.json();
            })
            .then(data => {
                if (!data || !data.timestamp) return;
                // Si cambió la marca de tiempo, procesar la señal
                if (data.timestamp !== _wa_lastSignalTimestamp) {
                    log('Señal de actualización detectada: ' + JSON.stringify(data), 'info');
                    _wa_lastSignalTimestamp = data.timestamp;
                    handleSignal(data);
                }
            })
            .catch(err => {
                // No mostrar errores ruidosos por falta de archivo; solo log si DEBUG
                log('pollSignal: ' + err.message, 'warn');
            });
    }

    function handleSignal(signal) {
        const id = Number(signal.chat_id) || null;
        // Si el chat activo es el indicado, recargar mensajes; si no, recargar lista de chats
        if (id && Number(chatSeleccionado) === id) {
            cargarMensajes(id, true);
            // Marcar como leídos automáticamente si el chat está abierto y llega un mensaje nuevo
            fetch('/public_html/dashboard/paginas/whatsapp/api/marcar_leido.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_chat=' + encodeURIComponent(id)
            }).then(() => {
                cargarChats();
            });
            return;
        }
        // Si hay chat seleccionado distinto, refrescar lista y -si existe- destacar
        cargarChats();
    }

    window.inicializarWhatsapp = inicializarWhatsapp;
})();
