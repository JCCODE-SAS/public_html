document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 Iniciando dashboard...');

  if (!window.rutas) {
    window.rutas = {
      usuarios: '/public_html/dashboard/paginas/usuarios/usuarios.php',
      operarios: '/public_html/dashboard/paginas/operarios/operarios.php',
      whatsapp: '/public_html/dashboard/paginas/whatsapp/whatsapp.php',
      perfil: '/public_html/dashboard/paginas/perfil/perfil.php',
      configuracion: '/public_html/dashboard/paginas/configuracion/configuracion.php',
      logout: '/public_html/sesiones/destruir_sesion.php',
      login: '/index.php'
    };
  }

  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const mobileOverlay = document.getElementById('mobileOverlay');
  const navLinks = document.querySelectorAll('.nav-link');
  const contentSections = document.querySelectorAll('.content-section');
  const logoutBtn = document.getElementById('logoutBtn');
  let isMobile = window.innerWidth <= 1024;
  let sidebarCollapsed = false;

  function toggleSidebar() {
    if (isMobile) {
      sidebar.classList.toggle('mobile-open');
      mobileOverlay.classList.toggle('active');
      document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    } else {
      sidebarCollapsed = !sidebarCollapsed;
      sidebar.classList.toggle('collapsed', sidebarCollapsed);
    }
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', toggleSidebar);
  }
  
  if (mobileOverlay) {
    mobileOverlay.addEventListener('click', () => {
      if (sidebar.classList.contains('mobile-open')) toggleSidebar();
    });
  }

  if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
      const titulo = 'Cerrar sesión';
      const mensaje = '¿Está seguro que desea cerrar sesión?';
      const doLogout = () => { window.location.href = window.rutas.logout; };

      if (typeof mostrarModalConfirmacion === 'function') {
        mostrarModalConfirmacion(titulo, mensaje, doLogout);
      } else {
        if (confirm(mensaje)) {
          doLogout();
        }
      }
    });
  }

  function cargarSeccion(key, container) {
    console.log(`📄 Cargando sección: ${key}`);

    const url = window.rutas[key];
    if (!url) {
      console.error(`❌ No existe ruta para: ${key}`);
      container.innerHTML = `<div class="p-8 text-center">
        <h3 class="text-xl text-red-600">Sección no disponible</h3>
        <p class="text-gray-600">La sección "${key}" no está configurada.</p>
      </div>`;
      return;
    }

    // 🔄 Loading para usuarios y operarios
    if (key === 'usuarios' || key === 'operarios') {
      container.innerHTML = `
        <div style="
          display: flex; align-items: center; justify-content: center;
          min-height: 60vh; padding: 2rem; font-family: 'Inter', sans-serif;
        ">
          <div style="
            text-align: center; background: rgba(255, 255, 255, 0.95);
            padding: 3rem 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);
          ">
            <div style="
              width: 48px; height: 48px; margin: 0 auto 20px;
              border: 4px solid #e5e7eb; border-top-color: #667eea;
              border-radius: 50%; animation: copflowSpin 1s linear infinite;
            "></div>
            <h3 style="margin: 0 0 8px 0; color: #374151; font-size: 18px; font-weight: 600;">
              ${key === 'usuarios' ? '🧑‍💼 Cargando Gestión de Usuarios' : '👷 Cargando Gestión de Operarios'}
            </h3>
            <p style="margin: 0 0 4px 0; color: #6b7280; font-size: 14px;">
              CopFlow v3.0 - Sistema de ${key.charAt(0).toUpperCase() + key.slice(1)}
            </p>
            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
              Desarrollado por Diomedes Madrigal
            </p>
          </div>
        </div>
      `;
      if (!document.getElementById('copflow-loading-styles')) {
        const style = document.createElement('style');
        style.id = 'copflow-loading-styles';
        style.textContent = `
          @keyframes copflowSpin {
            to { transform: rotate(360deg); }
          }
        `;
        document.head.appendChild(style);
      }
    }

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.text())
      .then(html => {
        container.innerHTML = html;
        setTimeout(() => {
          if (key === 'usuarios' && typeof window.inicializarUsuarios === 'function') {
            window.inicializarUsuarios();
            console.log('✅ Módulo de usuarios inicializado correctamente');
          }
          if (key === 'operarios' && typeof window.inicializarOperarios === 'function') {
            window.inicializarOperarios();
            console.log('✅ Módulo de operarios inicializado correctamente');
          }
        }, 200);
        console.log(`✅ Sección ${key} cargada exitosamente`);
      })
      .catch(err => {
        console.error(`❌ Error cargando ${key}:`, err);
        container.innerHTML = `
          <div class="p-8 text-center" style="min-height: 50vh; display: flex; align-items: center; justify-content: center;">
            <div style="text-align: center; max-width: 400px;">
              <div style="
                width: 60px; height: 60px; margin: 0 auto 20px;
                background: linear-gradient(135deg, #ef4444, #dc2626);
                border-radius: 50%; display: flex; align-items: center;
                justify-content: center; font-size: 24px; color: white;
              ">❌</div>
              <h3 class="text-xl font-bold text-red-600 mb-2">Error al cargar sección</h3>
              <p class="text-gray-600 mb-4">No se pudo cargar la sección "${key}"</p>
              <p class="text-sm text-gray-500 mb-6">Error: ${err.message}</p>
              <button onclick="location.reload()" 
                      class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                🔄 Reintentar
              </button>
              <div class="mt-4 text-xs text-gray-400">
                CopFlow v3.0 by Diomedez98
              </div>
            </div>
          </div>
        `;
      });
  }

  navLinks.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const sectionId = link.dataset.section;
      const key = sectionId.replace('Section', '').toLowerCase();
      const container = document.getElementById(sectionId);

      if (!container) {
        console.error(`❌ No se encontró contenedor: ${sectionId}`);
        return;
      }

      if (!container.dataset.loaded) {
        cargarSeccion(key, container);
        container.dataset.loaded = 'true';
      }

      navLinks.forEach(l => l.classList.remove('active'));
      contentSections.forEach(sec => sec.classList.remove('active'));
      link.classList.add('active');
      container.classList.add('active');

      if (isMobile && sidebar.classList.contains('mobile-open')) {
        toggleSidebar();
      }
    });
  });

  setTimeout(() => {
    const activeLink = document.querySelector('.nav-link.active');
    if (activeLink) {
      console.log('🎯 Cargando sección inicial...');
      activeLink.click();
    }
  }, 100);

  window.addEventListener('resize', () => {
    const prevMobile = isMobile;
    isMobile = window.innerWidth <= 1024;
    if (prevMobile !== isMobile) {
      sidebar.classList.remove('collapsed', 'mobile-open');
      mobileOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });

  document.querySelectorAll('.nav-link').forEach(btn => {
    btn.addEventListener('click', function() {
        const section = btn.getAttribute('data-section');
        // Oculta todas las secciones
        document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
        // Muestra solo la sección seleccionada
        document.getElementById(section).classList.add('active');
        
        // Carga contenido dinámico según la sección
        if (section === 'usuariosSection') window.recargarSeccionUsuarios();
        if (section === 'operariosSection') window.recargarSeccionOperarios();
        if (section === 'whatsappSection') window.recargarSeccionwhatsapp(); 
        // ...otros casos si tienes más secciones
    });
});

  console.log('✅ Dashboard inicializado correctamente - CopFlow v3.0 by Diomedez98');
});