document.addEventListener('DOMContentLoaded', function() {
    // Esto asegura que solo se ejecute si el módulo WhatsApp está presente
    if (!document.getElementById('wa-chatlist')) return;

    // 1. Cargar lista de chats al abrir la sección
    cargarChats();

    function cargarChats() {
        fetch('api/obtener_chats.php')
            .then(resp => resp.json())
            .then(data => {
                const lista = document.getElementById('wa-chatlist');
                lista.innerHTML = '';
                if (!data || !Array.isArray(data.chats) || data.chats.length === 0) {
                    lista.innerHTML = `<li style="color:#aaa;padding:20px;">No hay chats activos</li>`;
                    return;
                }
                data.chats.forEach(chat => {
                    const li = document.createElement('li');
                    li.className = 'wa-chat';
                    li.dataset.chatId = chat.id;
                    li.innerHTML = `
                        <div><strong>${chat.cliente}</strong></div>
                        <div style="color:#888;font-size:13px;">${chat.ultimo_mensaje || '(sin mensajes)'}</div>
                        <div style="font-size:11px;color:#25d366;">${chat.estado}</div>
                    `;
                    li.addEventListener('click', () => {
                        document.querySelectorAll('.wa-chat').forEach(c => c.classList.remove('selected'));
                        li.classList.add('selected');
                        cargarMensajes(chat.id);
                    });
                    lista.appendChild(li);
                });
            })
            .catch(err => {
                document.getElementById('wa-chatlist').innerHTML = `<li style="color:#d33;padding:20px;">Error cargando chats</li>`;
                console.error('[WhatsApp] Error al cargar chats:', err);
            });
    }

    // 2. Cargar mensajes de un chat
    function cargarMensajes(chatId) {
        const mensajesBox = document.getElementById('wa-messages');
        mensajesBox.innerHTML = '<div style="color:#bbb;text-align:center;margin-top:60px;">Cargando mensajes...</div>';
        fetch(`api/obtener_mensajes.php?id_chat=${encodeURIComponent(chatId)}`)
            .then(resp => resp.json())
            .then(data => {
                mensajesBox.innerHTML = '';
                if (!data || !Array.isArray(data.mensajes) || data.mensajes.length === 0) {
                    mensajesBox.innerHTML = '<div style="color:#bbb;text-align:center;margin-top:60px;">No hay mensajes en este chat</div>';
                    return;
                }
                data.mensajes.forEach(msg => {
                    const div = document.createElement('div');
                    let tipo = '';
                    if (msg.enviado_por === 'cliente') tipo = 'cliente';
                    else if (msg.enviado_por === 'operador') tipo = 'operador';
                    else tipo = 'mia'; // Mensajes automáticos del sistema

                    div.className = 'wa-bubble ' + tipo;
                    div.innerHTML = `
                        <div>${msg.texto}</div>
                        <div class="wa-msg-meta">${msg.fecha} ${msg.hora}</div>
                    `;
                    mensajesBox.appendChild(div);
                });
                // Scroll al final
                mensajesBox.scrollTop = mensajesBox.scrollHeight;
                mostrarInput(chatId);
            })
            .catch(err => {
                mensajesBox.innerHTML = '<div style="color:#d33;text-align:center;margin-top:60px;">Error cargando mensajes</div>';
                console.error('[WhatsApp] Error al cargar mensajes:', err);
            });
    }

    // 3. Mostrar el input para enviar mensaje
    function mostrarInput(chatId) {
        const form = document.getElementById('wa-form');
        form.style.display = '';
        form.onsubmit = function(e) {
            e.preventDefault();
            const input = document.getElementById('wa-input');
            if (!input.value.trim()) return;
            enviarMensaje(chatId, input.value.trim());
            input.value = '';
        }
    }

    // 4. Enviar mensaje
    function enviarMensaje(chatId, texto) {
        fetch('api/enviar_mensaje.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_chat: chatId, texto: texto })
        })
        .then(resp => resp.json())
        .then(data => {
            if (data && data.ok) {
                cargarMensajes(chatId); // Refresca mensajes
            } else {
                alert('No se pudo enviar el mensaje');
            }
        })
        .catch(err => {
            alert('Error de red al enviar mensaje');
            console.error('[WhatsApp] Error al enviar mensaje:', err);
        });
    }

    // 5. Recarga automática de chats cada 30s
    setInterval(cargarChats, 30000);

    // 6. Si quieres, puedes agregar recarga de mensajes cada X segundos para el chat abierto
    // (Opcional: implementar un watcher para el chatId seleccionado)
});