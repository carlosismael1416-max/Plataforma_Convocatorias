// Comprueba que lo que se escribe se guarda: en cada página llena los campos
// enlazados con wire:model con un texto único, pulsa los botones de guardar /
// agregar, recarga y busca cada texto en la página.
// Uso (desde web/): node tests/Recorrido/llenar.cjs <panel> [filtroUrl]
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const HOME = process.env.HOME;
const { chromium } = require(process.env.PLAYWRIGHT_CORE || `${HOME}/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core`);
const CHROMIUM = process.env.CHROMIUM || `${HOME}/.cache/ms-playwright/chromium_headless_shell-1194/chrome-linux/headless_shell`;
const BASE = process.env.BASE || 'http://127.0.0.1:8010';
const SALIDA = path.join(__dirname, 'salida');
const [panel, filtro = ''] = process.argv.slice(2);
const GUARDAR = /guardar|agregar|añadir|registrar|actualizar/i;
const PAGINAS = /propuesta|perfil|mis-convocatorias|notificaciones|calendario/;

(async () => {
  const datos = JSON.parse(fs.readFileSync(path.join(SALIDA, 'urls.json')))[panel];
  const linea = execSync(`php tests/Recorrido/sesion.php ${datos.usuario}`, { cwd: path.resolve(__dirname, '../..'), env: { ...process.env, DB_DATABASE: 'convocatorias_itsva_test' } }).toString().trim();
  const i = linea.indexOf('=');
  const navegador = await chromium.launch({ executablePath: CHROMIUM });
  const contexto = await navegador.newContext({ viewport: { width: 1400, height: 900 } });
  await contexto.addCookies([{ name: linea.slice(0, i), value: linea.slice(i + 1), domain: '127.0.0.1', path: '/' }]);
  const p = await contexto.newPage();
  p.on('dialog', d => d.accept());
  const reporte = path.join(SALIDA, `llenado_${panel}.txt`);
  fs.writeFileSync(reporte, '');
  const anotar = (t) => { fs.appendFileSync(reporte, t + '\n'); console.log(t); };
  let n = 0;

  for (const url of datos.urls.filter(u => PAGINAS.test(u) && u.includes(filtro) && !u.includes('generar') && !u.includes('revisar'))) {
    await p.goto(BASE + url, { waitUntil: 'networkidle' });
    const campos = await p.$$('main input[wire\\:model], main textarea[wire\\:model], main input[wire\\:model\\.live], main textarea[wire\\:model\\.live], main [wire\\:model\\.live\\.debounce\\.700ms], main [wire\\:model\\.defer], main [wire\\:model\\.blur]');
    const escritos = [];
    for (const campo of campos) {
      if (!(await campo.isVisible()) || (await campo.isDisabled()) || !(await campo.isEditable())) continue;
      const tipo = (await campo.getAttribute('type')) || 'text';
      if (/date|time|file|checkbox|radio|hidden|password|email/.test(tipo)) continue;
      const nombre = await campo.evaluate(e => [...e.attributes].find(a => a.name.startsWith('wire:model'))?.value);
      const valor = tipo === 'number' ? String(4100 + (++n)) : `ZZ${++n}x`;
      await campo.fill(valor);
      escritos.push({ nombre, valor });
    }
    if (!escritos.length) continue;
    await p.waitForTimeout(1200);
    for (const boton of await p.$$('main button')) {
      const texto = ((await boton.innerText().catch(() => '')) || '').trim();
      if (GUARDAR.test(texto) && (await boton.isVisible()) && !(await boton.isDisabled())) {
        await boton.click().catch(() => null);
        await p.waitForLoadState('networkidle').catch(() => null);
        await p.waitForTimeout(400);
      }
    }
    await p.goto(BASE + url, { waitUntil: 'networkidle' });
    const html = await p.content() + JSON.stringify(await p.$$eval('input, textarea', e => e.map(x => x.value)));
    const perdidos = escritos.filter(e => !html.includes(e.valor) && !html.includes(Number(e.valor).toLocaleString('en-US')));
    anotar(`${url}: ${escritos.length} campos, ${perdidos.length ? 'NO SE GUARDARON → ' + perdidos.map(e => e.nombre).join(', ') : 'todos guardados'}`);
  }
  await navegador.close();
})();
