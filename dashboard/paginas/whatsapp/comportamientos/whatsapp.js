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

    function agregarBotonMiaGlobal() {
        const aside = document.querySelector('.wa-chats');
        if (!aside || document.getElementById('wa-mia-global-btn')) return;
        const btn = document.createElement('button');
        btn.id = 'wa-mia-global-btn';
        btn.textContent = 'Apagar MIA en todos mis chats';
        btn.className = 'wa-mia-global-btn';
        btn.style = 'margin:10px 15px 0 15px;padding:7px 16px;background:#e53935;color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;';
        btn.onclick = function() {
            btn.disabled = true;
            btn.textContent = 'Procesando...';
            fetch('/dashboard/paginas/whatsapp/api/apagar_mia_global.php', {
                method: 'POST',
                credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message || 'Listo');
                cargarChats();
                btn.disabled = false;
                btn.textContent = 'Apagar MIA en todos mis chats';
            })
            .catch(() => {
                alert('Error de red');
                btn.disabled = false;
                btn.textContent = 'Apagar MIA en todos mis chats';
            });
        };
        aside.insertBefore(btn, aside.children[1] || aside.firstChild);
    }

    function cargarChats() {
    fetch('/dashboard/paginas/whatsapp/api/obtener_chats.php', { credentials: 'include' })
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
                    // Solo nombre y badge, sin botón MIA
                    li.innerHTML = `<div><strong>${chat.cliente}</strong> ${badge}</div><div>${chat.estado}</div>`;
                    li.addEventListener('click', (e) => {
                        if (chatSeleccionado === chat.id) return;
                        document.querySelectorAll('.wa-chat').forEach(c => c.classList.remove('selected'));
                        li.classList.add('selected');
                        chatSeleccionado = chat.id;
                        // Limpiar cache y DOM antes de recargar mensajes
                        const cont = document.getElementById('wa-messages');
                        if (cont) cont.innerHTML = '';
                        mensajesRenderizados[chat.id] = [];
                        cargarMensajes(chat.id, true);
                        fetch('/dashboard/paginas/whatsapp/api/marcar_leido.php', {
                            method: 'POST',
                            credentials: 'include',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id_chat=' + encodeURIComponent(chat.id)
                        }).then(() => {
                            actualizarBadgeGlobal();
                        });
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
    fetch('/dashboard/paginas/whatsapp/api/contar_no_leidos.php', { credentials: 'include' })
            .then(r => r.json())
            .then (data => {
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

    fetch(`/dashboard/paginas/whatsapp/api/obtener_mensajes.php?id_chat=${encodeURIComponent(chatId)}`, { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                log('Mensajes recibidos:', data.mensajes);
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
                const chat = chatsCache.find(c => c.id === chatId);
                renderBotonMiaChat(chat);

                log(`Mensajes cargados para chat ${chatId} (${data.mensajes.length || 0})`, 'success');
            })
            .catch(err => {
                cont.innerHTML = '<div style=\"color:#d33;text-align:center;margin-top:60px;\">Error de red al cargar mensajes</div>';
                log(`Error de red al cargar mensajes: ${err.message}`, 'error');
            });
    }

    function renderMensaje(msg, cont) {
        const div = document.createElement('div');
        let tipo = 'mia';
        let quien = 'MIA';
        if (msg.enviado_por === 'cliente') {
            tipo = 'cliente';
            quien = 'User';
        } else if (msg.enviado_por === 'operador') {
            tipo = 'operador';
            quien = msg.nombre_operador || 'Operador';
        } else if (msg.enviado_por === 'mia') {
            tipo = 'mia';
            quien = 'MIA';
        }
        div.className = 'wa-bubble ' + tipo;
        div.innerHTML = `<div>${msg.texto}</div><div class=\"wa-msg-meta\">${msg.fecha} ${msg.hora} <span class='wa-quien'>${quien}</span></div>`;
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
        let chat = chatsCache.find(c => c.id === chatId);
        if (chat && chat.numero) return chat.numero;
        // Si no está en cache, intentar recargar y buscar de nuevo (sincrónico no es posible, así que advertir)
        log('Número de cliente no encontrado en cache para chatId ' + chatId, 'warn');
        // Opcional: podrías forzar recarga de chats aquí y reintentar, pero por ahora muestra error claro
        return '';
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
                fetch('/dashboard/paginas/whatsapp/api/registrar_mensaje.php', {
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

    // Reemplaza la función pollSignal en whatsapp.js con esta versión con más logging

function pollSignal() {
    const url = '/paginas/whatsapp/api/leer_senal.php?ts=' + Date.now();
    
    fetch(url, { credentials: 'include', cache: 'no-store' })
        .then(r => {
            console.log('🔍 [pollSignal] Status:', r.status, r.ok);
            if (!r.ok) throw new Error('No hay señal');
            return r.json();
        })
        .then(data => {
            console.log('📡 [pollSignal] Señal recibida:', data);
            
            if (!data || !data.timestamp) {
                console.log('⚠️ [pollSignal] Señal sin timestamp válido');
                return;
            }
            
            console.log('🕐 [pollSignal] Timestamp anterior:', _wa_lastSignalTimestamp);
            console.log('🕐 [pollSignal] Timestamp nuevo:', data.timestamp);
            
            // Si cambió la marca de tiempo, procesar la señal
            if (data.timestamp !== _wa_lastSignalTimestamp) {
                console.log('✅ [pollSignal] SEÑAL NUEVA DETECTADA - Procesando...');
                _wa_lastSignalTimestamp = data.timestamp;
                handleSignal(data);
            } else {
                console.log('⏸️ [pollSignal] Sin cambios en timestamp');
            }
        })
        .catch(err => {
            console.log('❌ [pollSignal] Error:', err.message);
        });
}

// También actualiza handleSignal para mejor logging
function handleSignal(signal) {
    console.log('🎯 [handleSignal] Procesando señal:', signal);
    console.log('🎯 [handleSignal] Chat seleccionado:', chatSeleccionado);
    
    const id = Number(signal.chat_id) || null;
    
    // Siempre que el chat activo esté seleccionado, recargar mensajes
    if (chatSeleccionado) {
        console.log('🔄 [handleSignal] Recargando mensajes del chat', chatSeleccionado);
        cargarMensajes(chatSeleccionado, true);
        
        // Marcar como leídos automáticamente si el chat está abierto y llega un mensaje nuevo
    fetch('/dashboard/paginas/whatsapp/api/marcar_leido.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_chat=' + encodeURIComponent(chatSeleccionado)
        }).then(() => {
            console.log('✅ [handleSignal] Chat marcado como leído');
            cargarChats();
        });
        return;
    }
    
    // Si no hay chat seleccionado, solo refrescar lista
    console.log('📋 [handleSignal] No hay chat seleccionado, refrescando lista');
    cargarChats();
}
    // Botón MIA debajo del input, solo para el chat seleccionado
    function renderBotonMiaChat(chat) {
        let cont = document.getElementById('wa-mia-chat-btn-wrap');
        if (!cont) {
            cont = document.createElement('div');
            cont.id = 'wa-mia-chat-btn-wrap';
            cont.style = 'display:flex;justify-content:flex-end;padding:10px 0 0 0;';
            const form = document.getElementById('wa-form');
            if (form && form.parentNode) {
                form.parentNode.insertBefore(cont, form.nextSibling);
            }
        }
        cont.innerHTML = '';
        if (!chat) return;
        const iaSVG = `<svg width='18' height='18' viewBox='0 0 20 20' fill='none' style='vertical-align:middle;margin-right:4px;' xmlns='http://www.w3.org/2000/svg'><circle cx='10' cy='10' r='8' stroke='#2563eb' stroke-width='2' fill='#e0e7ef'/><rect x='7' y='7' width='6' height='6' rx='2' fill='#2563eb'/></svg>`;
        const activa = chat.mia_activa === 1;
        const btn = document.createElement('button');
        btn.className = 'wa-mia-btn-chat';
        btn.type = 'button';
        btn.style = `background:${activa ? '#22c55e' : '#64748b'};color:#fff;padding:6px 18px 6px 10px;border:none;border-radius:7px;cursor:pointer;font-weight:600;display:inline-flex;align-items:center;gap:4px;transition:background 0.2s;font-size:15px;`;
        btn.innerHTML = `${iaSVG}${activa ? 'MIA ACTIVA' : 'MIA DESACTIVADA'}`;
        btn.title = activa ? 'Desactivar MIA para este chat' : 'Activar MIA para este chat';
        btn.onclick = function() {
            btn.disabled = true;
            fetch(`/dashboard/paginas/whatsapp/api/${activa ? 'apagar_mia' : 'activar_mia'}.php`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_chat=' + encodeURIComponent(chat.id)
            })
            .then(r => r.json())
            .then(data => {
                cargarMensajes(chat.id, false);
                cargarChats();
                btn.disabled = false;
            })
            .catch(() => {
                alert('Error de red');
                btn.disabled = false;
            });
        };
        cont.appendChild(btn);
    }

    window.inicializarWhatsapp = inicializarWhatsapp;
})();
