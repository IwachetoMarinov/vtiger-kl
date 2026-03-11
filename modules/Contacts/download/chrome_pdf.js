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

    const browser = await puppeteer.launch({
      headless: true,
      args: [
        "--no-sandbox",
        "--disable-setuid-sandbox",
        "--disable-dev-shm-usage",
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