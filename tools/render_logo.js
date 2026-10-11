// Renders submission/logo.html to high-resolution transparent PNGs using the bundled Chromium.
const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const [,, htmlPath, outDir] = process.argv;
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 2048, height: 2048 }, deviceScaleFactor: 1 });
  await page.goto('file://' + path.resolve(htmlPath));
  await page.waitForTimeout(300);
  for (const id of ['full', 'icon', 'horizontal']) {
    const el = await page.$('#' + id);
    if (el) await el.screenshot({ path: path.join(outDir, `logo_${id}.png`), omitBackground: true });
  }
  await browser.close();
})();
