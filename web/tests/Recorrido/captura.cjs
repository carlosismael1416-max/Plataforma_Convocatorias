// Captura de pantalla de una página de la copia de prueba, con sesión.
// Uso (desde web/): node tests/Recorrido/captura.cjs <user_id> <ruta> <salida.png> [ancho] [alto]
const { execSync } = require('child_process');
const HOME = process.env.HOME;
const { chromium } = require(process.env.PLAYWRIGHT_CORE || `${HOME}/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core`);
(async () => {
  const [usuario, ruta, salida, ancho = 1440, alto = 900] = process.argv.slice(2);
  const linea = execSync(`php tests/Recorrido/sesion.php ${usuario}`, { env: { ...process.env, DB_DATABASE: 'convocatorias_itsva_test' } }).toString().trim();
  const i = linea.indexOf('=');
  const b = await chromium.launch({ executablePath: process.env.CHROMIUM || `${HOME}/.cache/ms-playwright/chromium_headless_shell-1194/chrome-linux/headless_shell` });
  const c = await b.newContext({ viewport: { width: +ancho, height: +alto } });
  await c.addCookies([{ name: linea.slice(0, i), value: linea.slice(i + 1), domain: '127.0.0.1', path: '/' }]);
  const p = await c.newPage();
  await p.goto((process.env.BASE || 'http://127.0.0.1:8010') + ruta, { waitUntil: 'networkidle' });
  await p.screenshot({ path: salida, fullPage: true });
  await b.close();
})();
