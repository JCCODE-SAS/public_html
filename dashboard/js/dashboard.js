function cargarSeccion(key, container){
  // limpiar módulos anteriores
  if(window.limpiarOperariosIntervals) window.limpiarOperariosIntervals();
  if(window.limpiarUsuariosIntervals) window.limpiarUsuariosIntervals();
  if(window.limpiarWhatsappIntervals) window.limpiarWhatsappIntervals();

  fetch(window.rutas[key],{credentials:'include'})
    .then(r=>r.text())
    .then(html=>{
      container.innerHTML = html;
      // si es operarios, carga su script
      if(key==='operarios') cargarScript('js/operarios.js');
      if(key==='usuarios')  cargarScript('js/usuarios.js');
      if(key==='whatsapp')  cargarScript('comportamientos/whatsapp.js');
    });
}