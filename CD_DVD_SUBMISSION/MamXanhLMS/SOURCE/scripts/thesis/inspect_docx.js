import fs from 'fs';
import JSZip from 'jszip';

const buf = fs.readFileSync('../../docs/BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx');
const zip = await JSZip.loadAsync(buf);
const xml = await zip.file('word/document.xml').async('string');

const textMatches = xml.match(/<w:t[^>]*>(.*?)<\/w:t>/g) || [];
const fullText = textMatches.map(m => m.replace(/<[^>]+>/g, '')).join(' ');
const words = fullText.split(/\s+/).filter(Boolean);
const pCount = (xml.match(/<w:p[ >]/g) || []).length;
const tCount = (xml.match(/<w:tbl[ >]/g) || []).length;
const imgCount = (xml.match(/<w:drawing[ >]/g) || []).length;
const breaks = (xml.match(/w:type="page"/g) || []).length;

console.log('=== THESIS DOCUMENT METRICS ===');
console.log('Total Words:', words.length);
console.log('Total Paragraphs:', pCount);
console.log('Total Tables:', tCount);
console.log('Total Embedded Images:', imgCount);
console.log('Section & Page Breaks:', breaks);
const estPages = Math.round(words.length / 320 + imgCount * 0.45 + tCount * 0.5 + breaks * 0.8);
console.log('Estimated Pages in MS Word:', estPages);
