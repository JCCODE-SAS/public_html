(function () {
    "use strict";
    // --- SOLO PERMITE UNA INSTANCIA GLOBAL ---
    if (window.__WA_MODULE_LOADED__) return;
    window.__WA_MODULE_LOADED__ = true;
    // --- FIN bandera instancia única ---

    // Configuración mínima
    const CONFIG = {
        DEBUG_MODE: true
    };

    let chatSeleccionado = null;
    let chatsCache = [];

    function log(msg, type = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        const prefix = `[${new Date().toLocaleTimeString()}] [WHATSAPP.JS]`;
        switch (type) {
            case 'error': console.error(`${prefix} ❌`, msg); break;
            case 'warn':  console.warn(`${prefix} ⚠️`, msg); break;
            case 'success': console.log(`${prefix} ✅`, msg); break;
            default: console.log(`${prefix} 🔄`, msg); break;
        }
    }

    function cargarChats() {
        fetch('/public_html/dashboard/paginas/whatsapp/api/obtener_chats.php', { credentials: 'include' })
            .then(resp => resp.json())
            .then(data => {
                const lista = document.getElementById('wa-chatlist');
                if (!lista) return;
                lista.innerHTML = '';

                if (!data.ok) {
                    lista.innerHTML = `<li style="color:#d33;padding:20px;">${data.error || 'Error cargando chats'}</li>`;
                    log(data.error || 'Error cargando chats', 'error');
                    return;
                }

                if (!Array.isArray(data.chats) || data.chats.length === 0) {
                    lista.innerHTML = `<li style="color:#aaa;padding:20px;">No hay chats activos</li>`;
                    return;
                }

                chatsCache = data.chats;

                data.chats.forEach(chat => {
                    const li = document.createElement('li');
                    li.className = 'wa-chat';
                    li.dataset.chatId = chat.id;

                    li.innerHTML = `
                        <div><strong>${chat.cliente}</strong></div>
                        <div>${chat.estado}</div>
                    `;

                    li.addEventListener('click', () => {
                        if (chatSeleccionado === chat.id) return;
                        document.querySelectorAll('.wa-chat').forEach(c => c.classList.remove('selected'));
                        li.classList.add('selected');
                        chatSeleccionado = chat.id;
                        cargarMensajes(chat.id, true);
                    });

                    lista.appendChild(li);
                });

                log(`Chats activos cargados (${data.chats.length})`, 'success');
            })
            .catch(err => {
                const lista = document.getElementById('wa-chatlist');
                if (lista) lista.innerHTML = `<li style="color:#d33;padding:20px;">Error de red al cargar chats</li>`;
                log(`Error de red al cargar chats: ${err.message}`, 'error');
            });
    }

    function cargarMensajes(chatId, scrollToEnd) {
        const cont = document.getElementById('wa-messages');
        if (!cont) return;
        cont.innerHTML = '<div style="color:#bbb;text-align:center;margin-top:60px;">Cargando mensajes...</div>';
        fetch(`/public_html/dashboard/paginas/whatsapp/api/obtener_mensajes.php?id_chat=${encodeURIComponent(chatId)}`, { credentials: 'include' })
            .then(r => r.json())
            .then(data => {
                cont.innerHTML = '';
                if (!data.ok) {
                    cont.innerHTML = `<div style="color:#d33;text-align:center;margin-top:60px;">${data.error || 'Error cargando mensajes'}</div>`;
                    log(data.error || 'Error cargando mensajes', 'error');
                    return;
                }

                if (!Array.isArray(data.mensajes) || data.mensajes.length === 0) {
                    cont.innerHTML = '<div style="color:#bbb;text-align:center;margin-top:60px;">No hay mensajes en este chat</div>';
                } else {
                    data.mensajes.forEach(msg => {
                        const div = document.createElement('div');
                        const tipo = msg.enviado_por === 'cliente' ? 'cliente' :
                                     msg.enviado_por === 'operador' ? 'operador' : 'mia';
                        div.className = 'wa-bubble ' + tipo;
                        div.innerHTML = `
                            <div>${msg.texto}</div>
                            <div class="wa-msg-meta">${msg.fecha} ${msg.hora}</div>
                        `;
                        cont.appendChild(div);
                    });
                    if (scrollToEnd !== false) cont.scrollTop = cont.scrollHeight;
                }

                mostrarInput(chatId);
                log(`Mensajes cargados para chat ${chatId} (${data.mensajes.length || 0})`, 'success');
            })
            .catch(err => {
                cont.innerHTML = '<div style="color:#d33;text-align:center;margin-top:60px;">Error de red al cargar mensajes</div>';
                log(`Error de red al cargar mensajes: ${err.message}`, 'error');
            });
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
    
        // Enviar mensaje al webhook de SIA
        fetch('https://integra.smartinicio.com/webhook/copflow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cliente: { numero },
                message: { texto }
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok || data.message === 'Workflow was started') {
                // Guardar mensaje en la base de datos
                fetch('/public_html/dashboard/paginas/whatsapp/api/registrar_mensaje.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_chat: chatId, texto })
                })
                .then(() => {
                    cargarMensajes(chatId, true);
                    cargarChats();
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
    

    

    function inicializarWhatsapp() {
        log('Inicializando módulo WhatsApp...', 'success');
        chatSeleccionado = null;
        cargarChats();
    }

    window.inicializarWhatsapp = inicializarWhatsapp;
})();
