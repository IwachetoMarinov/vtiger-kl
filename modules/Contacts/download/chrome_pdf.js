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

    if (process.platform === "linux") {
      fs.mkdirSync("/tmp/puppeteer-live/user-data", { recursive: true });
      fs.mkdirSync("/tmp/puppeteer-live/config", { recursive: true });
      fs.mkdirSync("/tmp/puppeteer-live/cache", { recursive: true });
      fs.mkdirSync("/tmp/puppeteer-live/runtime", { recursive: true });

      process.env.XDG_CONFIG_HOME = "/tmp/puppeteer-live/config";
      process.env.XDG_CACHE_HOME = "/tmp/puppeteer-live/cache";
      process.env.XDG_RUNTIME_DIR = "/tmp/puppeteer-live/runtime";
    }

    const PROD_CHROME =
      "/var/www/html/crm_kl/.puppeteer-cache/chrome/linux-146.0.7680.66/chrome-linux64/chrome";

    let chromePath = puppeteer.executablePath();

    if (process.platform === "linux") {
      if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        chromePath = process.env.PUPPETEER_EXECUTABLE_PATH;
      } else if (fs.existsSync(PROD_CHROME)) {
        chromePath = PROD_CHROME;
      }
    }

    console.log("Using chromePath:", chromePath);

    const browser = await puppeteer.launch({
      headless: true,
      executablePath: chromePath,
      userDataDir:
        process.platform === "linux" ? "/tmp/puppeteer-live/user-data" : undefined,
      args:
        process.platform === "linux"
          ? ["--no-sandbox", "--disable-setuid-sandbox", "--disable-dev-shm-usage"]
          : [],
    });

    const page = await browser.newPage();

    await page.goto("file://" + absHtml, { waitUntil: "networkidle0" });

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