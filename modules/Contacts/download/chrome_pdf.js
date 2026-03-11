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

    const chromeRoot = "/tmp/puppeteer-live";
    const userDataDir = path.join(chromeRoot, "user-data");
    const configHome = path.join(chromeRoot, "config");
    const cacheHome = path.join(chromeRoot, "cache");
    const runtimeDir = path.join(chromeRoot, "runtime");

    const PROD_CHROME =
      "/home/adm-panomatics/.cache/puppeteer/chrome/linux-146.0.7680.66/chrome-linux64/chrome";

    let chromePath = puppeteer.executablePath();

    if (process.platform === "linux") {
      if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        chromePath = process.env.PUPPETEER_EXECUTABLE_PATH;
      } else if (fs.existsSync(PROD_CHROME)) {
        chromePath = PROD_CHROME;
      }
    }

    const browser = await puppeteer.launch({
      headless: true,
      executablePath: chromePath,
      ...(process.platform === "linux" && process.env.NODE_ENV === "production"
        ? { userDataDir }
        : {}),
      args:
        process.platform === "linux" && process.env.NODE_ENV === "production"
          ? [
              "--no-sandbox",
              "--disable-setuid-sandbox",
              "--disable-dev-shm-usage",
            ]
          : [],
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
