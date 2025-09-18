(function(){
  "use strict";
  if(window.__OP_MODULE_LOADED__) return;
  window.__OP_MODULE_LOADED__ = true;

  const REFRESH_INTERVAL = 3*60*1000; 
  let operariosInterval = null;

  function fetchStats(){
    fetch('/api/estadisticas_operarios.api', {credentials:'include'})
      .then(r=>r.json())
      .then(data=>{ console.log('✅ SUCCESS: Estadísticas obtenidas correctamente',data); })
      .catch(e=>console.error(e));
  }

  function init(){
    fetchStats();
    operariosInterval = setInterval(fetchStats, REFRESH_INTERVAL);
  }
  init();

  window.limpiarOperariosIntervals = ()=>{
    if(operariosInterval) clearInterval(operariosInterval);
    window.__OP_MODULE_LOADED__ = false;
  };
})();