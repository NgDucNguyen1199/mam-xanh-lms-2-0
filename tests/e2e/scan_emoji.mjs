import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..', '..');
const dirs = (process.argv[2] ? process.argv[2].split(',') : ['moodle_custom', 'scripts']);
const exts = new Set(['.php', '.mustache', '.js', '.scss', '.css', '.html', '.json', '.xml', '.md', '.mjs', '.py', '.sh', '.txt']);
const skip = ['node_modules', 'pdfjs', '.git', 'vendor', 'packages', 'h5p_base'];
// Extended pictographic + variation selector + ZWJ + keycap + arrows/dingbats frequently used as icons
const re = /[\p{Extended_Pictographic}\uFE0F\u200D\u20E3]/gu;
let total = 0; const out = [];
function walk(d) {
  for (const e of fs.readdirSync(d, { withFileTypes: true })) {
    const p = path.join(d, e.name);
    if (e.isDirectory()) { if (!skip.includes(e.name)) walk(p); continue; }
    if (!exts.has(path.extname(e.name).toLowerCase())) continue;
    let t; try { t = fs.readFileSync(p, 'utf8'); } catch { continue; }
    const m = t.match(re);
    if (m && m.length) { total += m.length; out.push([m.length, path.relative(root, p), [...new Set(m)].slice(0, 8).join(' ')]); }
  }
}
for (const d of dirs) { const p = path.join(root, d); if (fs.existsSync(p)) walk(p); }
out.sort((a, b) => b[0] - a[0]);
for (const o of out) console.log(o.join(' | '));
console.log('TOTAL', total, 'files', out.length);
