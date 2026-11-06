// Renders the brand mark of public/favicon.svg to the PNG, ICO and touch icons the browsers
// still ask for. Runs with the Playwright image: the generated files are versioned, so neither
// CI nor the Docker build depend on this script.
//
//   docker run --rm -v "$PWD":/app -w /app -e HOME=/tmp mcr.microsoft.com/playwright:v1.65.0-noble node scripts/render-brand.mjs
import { chromium } from '@playwright/test';
import { copyFile, mkdir, readFile, writeFile } from 'node:fs/promises';

const SIZES = [16, 32, 180, 256, 512];
const svg = await readFile('public/favicon.svg', 'utf8');

await mkdir('public/img/brand', { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage({ deviceScaleFactor: 1 });
const pngs = {};

for (const size of SIZES) {
    await page.setViewportSize({ width: size, height: size });
    await page.setContent(
        `<!doctype html><html><head><style>html,body{margin:0;background:transparent}svg{display:block;width:${size}px;height:${size}px}</style></head><body>${svg}</body></html>`,
    );
    pngs[size] = await page.locator('svg').screenshot({ omitBackground: true });
}

await browser.close();

for (const size of [32, 180, 256, 512]) {
    await writeFile(`public/img/brand/mark-${size}.png`, pngs[size]);
}

await copyFile('public/img/brand/mark-32.png', 'public/favicon-32x32.png');
await copyFile('public/img/brand/mark-180.png', 'public/apple-touch-icon.png');
await writeFile('public/favicon.ico', ico([pngs[16], pngs[32]], [16, 32]));

console.log(
    'brand: favicon.ico, favicon-32x32.png, apple-touch-icon.png and public/img/brand/mark-*.png written',
);

/** ICO container with PNG-compressed images (supported by every current browser). */
function ico(images, sizes) {
    const header = Buffer.alloc(6);
    header.writeUInt16LE(0, 0); // reserved
    header.writeUInt16LE(1, 2); // type: icon
    header.writeUInt16LE(images.length, 4);

    const entries = [];
    let offset = 6 + 16 * images.length;

    images.forEach((png, i) => {
        const entry = Buffer.alloc(16);
        entry.writeUInt8(sizes[i] === 256 ? 0 : sizes[i], 0); // width
        entry.writeUInt8(sizes[i] === 256 ? 0 : sizes[i], 1); // height
        entry.writeUInt8(0, 2); // colours in palette
        entry.writeUInt8(0, 3); // reserved
        entry.writeUInt16LE(1, 4); // colour planes
        entry.writeUInt16LE(32, 6); // bits per pixel
        entry.writeUInt32LE(png.length, 8);
        entry.writeUInt32LE(offset, 12);
        entries.push(entry);
        offset += png.length;
    });

    return Buffer.concat([header, ...entries, ...images]);
}
