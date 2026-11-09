import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..', '..');
const dirs = (process.argv[2] ? process.argv[2].split(',') : ['moodle_custom', 'scripts']);
const exts = new Set(['.php', '.mustache', '.js', '.scss', '.css', '.html', '.json', '.xml', '.mjs', '.py', '.sh', '.txt']);
const skip = ['node_modules', 'pdfjs', '.git', 'vendor', 'packages', 'h5p_base'];
const re = /(?![\u00A9\u00AE\u2122\u2139])[\p{Extended_Pictographic}\uFE0F\u200D\u20E3\u2794\u27A1\u2B05]+ ?/gu;
let n = 0;
function walk(d) {
  for (const e of fs.readdirSync(d, { withFileTypes: true })) {
    const p = path.join(d, e.name);
    if (e.isDirectory()) { if (!skip.includes(e.name)) walk(p); continue; }
    if (!exts.has(path.extname(e.name).toLowerCase())) continue;
    const t = fs.readFileSync(p, 'utf8');
    const t2 = t.replace(re, '');
    if (t2 !== t) { fs.writeFileSync(p, t2); n++; console.log('stripped', path.relative(root, p)); }
  }
}
for (const d of dirs) { const p = path.join(root, d); if (fs.existsSync(p)) walk(p); }
console.log('files changed', n);
