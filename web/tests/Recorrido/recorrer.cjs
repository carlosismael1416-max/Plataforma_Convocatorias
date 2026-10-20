// Recorre las páginas de un panel y hace clic en cada botón de la zona
// principal (recargando antes de cada clic). Reporta:
//   ERROR       respuesta 5xx o excepción/errores de JavaScript
//   SIN EFECTO  ni petición, ni navegación, ni diálogo, ni cambio en la página
// Omite acciones destructivas o que llamarían al motor real.
//
// Uso (desde web/, con la copia de prueba en BASE): node tests/Recorrido/recorrer.cjs <panel> [filtroUrl]
// Variables: BASE (http://127.0.0.1:8010), PLAYWRIGHT_CORE, CHROMIUM.
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const HOME = process.env.HOME;
const { chromium } = require(process.env.PLAYWRIGHT_CORE || `${HOME}/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core`);
const CHROMIUM = process.env.CHROMIUM || `${HOME}/.cache/ms-playwright/chromium_headless_shell-1194/chrome-linux/headless_shell`;
const BASE = process.env.BASE || 'http://127.0.0.1:8010';
const WEB = path.resolve(__dirname, '../..');
const SALIDA = path.join(__dirname, 'salida');
const [panel, filtro = ''] = process.argv.slice(2);
const OMITIR = /eliminar|borrar|quitar archivo|cerrar sesi|salir|ejecutar|analizar|encender|apagar|desactivar|restablecer|descargar|exportar|pdf|excel|csv|enviar a revisi|aprobar|rechazar|devolver/i;
const SELECTOR = 'main button:visible, main [wire\\:click]:visible, .fi-main button:visible';

function cookie(usuario) {
  const linea = execSync(`php tests/Recorrido/sesion.php ${usuario}`, { cwd: WEB, env: { ...process.env, DB_DATABASE: 'convocatorias_itsva_test' } }).toString().trim();
  const i = linea.indexOf('=');
  return { name: linea.slice(0, i), value: linea.slice(i + 1), domain: new URL(BASE).hostname, path: '/' };
}

(async () => {
  const datos = JSON.parse(fs.readFileSync(path.join(SALIDA, 'urls.json')))[panel];
  const reporte = path.join(SALIDA, `reporte_${panel}.txt`);
  fs.writeFileSync(reporte, '');
  const anotar = (linea) => { fs.appendFileSync(reporte, linea + '\n'); console.log(linea); };

  const navegador = await chromium.launch({ executablePath: CHROMIUM });
  const contexto = await navegador.newContext({ viewport: { width: 1400, height: 900 } });
  await contexto.addCookies([cookie(datos.usuario)]);
  const pagina = await contexto.newPage();

  let errores = [], peticiones = [], dialogos = 0;
  pagina.on('pageerror', e => errores.push('JS: ' + e.message.slice(0, 150)));
  pagina.on('console', m => { if (m.type() === 'error' && !/favicon|status of 404/.test(m.text())) errores.push('consola: ' + m.text().slice(0, 150)); });
  pagina.on('dialog', async d => { dialogos++; await d.dismiss(); });
  pagina.on('response', r => { if (r.request().method() === 'POST' || r.status() >= 500) peticiones.push(r.status() + ' ' + r.url().replace(BASE, '')); });

  const cargar = async (url) => pagina.goto(BASE + url, { waitUntil: 'networkidle', timeout: 30000 }).catch(() => null);
  const huella = () => pagina.evaluate(() => (document.querySelector('main, .fi-main')?.innerHTML.length ?? 0) + ':' + location.href).catch(() => 'navegó');
  let probados = 0, hallazgos = 0;

  for (const url of datos.urls.filter(u => u.includes(filtro))) {
    errores = []; peticiones = [];
    const respuesta = await cargar(url);
    const estado = respuesta ? respuesta.status() : 'sin respuesta';
    if (estado !== 200 || errores.length) { hallazgos++; anotar(`CARGA ${url} → HTTP ${estado} ${errores.join(' | ')}`); }
    if (estado !== 200) continue;

    const textos = await pagina.$$eval(SELECTOR, els => els.map(e => (e.innerText || e.getAttribute('aria-label') || e.getAttribute('title') || '').trim().replace(/\s+/g, ' ').slice(0, 45)));
    const vistos = new Set();

    for (let i = 0; i < Math.min(textos.length, 45); i++) {
      const texto = textos[i];
      if (!texto || OMITIR.test(texto) || vistos.has(texto)) continue;
      vistos.add(texto);

      await cargar(url);
      errores = []; peticiones = []; dialogos = 0;
      const el = (await pagina.$$(SELECTOR))[i];
      if (!el) continue;
      if (await el.isDisabled().catch(() => false)) continue;

      const antes = await huella();
      const pestana = contexto.waitForEvent('page', { timeout: 1500 }).catch(() => null);
      try { await el.click({ timeout: 3000 }); } catch { hallazgos++; anotar(`NO CLICABLE ${url} [${texto}]`); continue; }
      await pagina.waitForLoadState('networkidle', { timeout: 6000 }).catch(() => null);
      await pagina.waitForTimeout(300);
      const nueva = await pestana; if (nueva) await nueva.close();
      const despues = await huella();
      probados++;

      const fallos = peticiones.filter(p => /^5/.test(p));
      if (fallos.length || errores.length) { hallazgos++; anotar(`ERROR ${url} [${texto}] → ${[...fallos, ...errores].join(' | ')}`); }
      else if (antes === despues && !peticiones.length && !dialogos && !nueva) { hallazgos++; anotar(`SIN EFECTO ${url} [${texto}]`); }
    }
  }

  anotar(`[${panel}] páginas: ${datos.urls.filter(u => u.includes(filtro)).length}, botones probados: ${probados}, hallazgos: ${hallazgos}`);
  await navegador.close();
})();
