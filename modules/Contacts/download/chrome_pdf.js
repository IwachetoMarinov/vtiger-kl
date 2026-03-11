const fs = require("fs");
const path = require("path");
const puppeteer = require("puppeteer");

(async () => {
  try {
    const htmlPath = process.argv[2];
    const pdfPath = process.argv[3];

    if (!htmlPath || !pdfPath) {
      console.error("Usage: node chrome_pdf.js <input.html> <output.pdf>");
      process.exit(1);
    }

    const absHtml = path.resolve(htmlPath);
    const absPdf = path.resolve(pdfPath);

    if (!fs.existsSync(absHtml)) {
      console.error("HTML file not found: " + absHtml);
      process.exit(1);
    }

    // Keep EVERYTHING writable and local to the runtime user
    const chromeRoot = "/tmp/puppeteer-live";
    const userDataDir = path.join(chromeRoot, "user-data");
    const configHome = path.join(chromeRoot, "config");
    const cacheHome = path.join(chromeRoot, "cache");
    const runtimeDir = path.join(chromeRoot, "runtime");

    [chromeRoot, userDataDir, configHome, cacheHome, runtimeDir].forEach((dir) => {
      fs.mkdirSync(dir, { recursive: true, mode: 0o777 });
    });

    process.env.HOME = chromeRoot;
    process.env.XDG_CONFIG_HOME = configHome;
    process.env.XDG_CACHE_HOME = cacheHome;
    process.env.XDG_RUNTIME_DIR = runtimeDir;

    // Use Puppeteer's installed Chrome
    const chromePath = puppeteer.executablePath();

    const browser = await puppeteer.launch({
      headless: true,
      executablePath: chromePath,
      userDataDir,
      dumpio: true,
      args: [
        "--no-sandbox",
        "--disable-setuid-sandbox",
        "--disable-dev-shm-usage",
        "--disable-features=Crashpad",
        "--no-first-run",
        "--no-default-browser-check",
      ],
    });

    const page = await browser.newPage();

    await page.goto("file://" + absHtml, {
      waitUntil: "networkidle0",
    });

    await page.pdf({
      path: absPdf,
      format: "A4",
      printBackground: true,
      preferCSSPageSize: true,
      displayHeaderFooter: false,
      margin: {
        top: "0mm",
        right: "0mm",
        bottom: "0mm",
        left: "0mm",
      },
    });

    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error(err && err.stack ? err.stack : String(err));
    process.exit(1);
  }
})();