import fs from 'fs';
import JSZip from 'jszip';

async function inspectThesis() {
  console.log('=== AUDITING BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx ===');
  const buf = fs.readFileSync('../../docs/BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx');
  const zip = await JSZip.loadAsync(buf);

  // 1. Document XML
  const docXml = await zip.file('word/document.xml').async('string');

  // Check Sections
  const sectPrMatches = docXml.match(/<w:sectPr[^>]*>[\s\S]*?<\/w:sectPr>/g) || [];
  console.log(`[SECTIONS] Detected ${sectPrMatches.length} section(s). Expected: 3.`);
  sectPrMatches.forEach((sect, idx) => {
    const hasPgNum = sect.includes('w:pgNumType');
    const fmt = sect.match(/w:fmt="([^"]+)"/)?.[1] || 'none';
    const start = sect.match(/w:start="([^"]+)"/)?.[1] || 'none';
    console.log(`  Section ${idx + 1}: pgNumType fmt=${fmt}, start=${start}, hasPgNum=${hasPgNum}`);
  });

  // Check Headings
  const h1Matches = docXml.match(/<w:pStyle w:val="Heading1"\/>/g) || [];
  const h2Matches = docXml.match(/<w:pStyle w:val="Heading2"\/>/g) || [];
  const h3Matches = docXml.match(/<w:pStyle w:val="Heading3"\/>/g) || [];
  console.log(`[HEADINGS] Heading1: ${h1Matches.length}, Heading2: ${h2Matches.length}, Heading3: ${h3Matches.length}`);

  // Check Bookmarks
  const bookmarkMatches = docXml.match(/<w:bookmarkStart[^>]*w:name="([^"]+)"/g) || [];
  const bookmarkNames = bookmarkMatches.map(m => m.match(/w:name="([^"]+)"/)[1]);
  console.log(`[BOOKMARKS] Detected ${bookmarkNames.length} bookmarks:`);
  console.log('  Samples:', bookmarkNames.slice(0, 15).join(', '));
  console.log('  Includes sec_modau:', bookmarkNames.includes('sec_modau'));
  console.log('  Includes sec_ch1:', bookmarkNames.includes('sec_ch1'));
  console.log('  Includes sec_conclusion:', bookmarkNames.includes('sec_conclusion'));
  console.log('  Includes sec_future:', bookmarkNames.includes('sec_future'));
  console.log('  Includes fig_1_1:', bookmarkNames.includes('fig_1_1'));
  console.log('  Includes fig_4_2:', bookmarkNames.includes('fig_4_2'));
  console.log('  Includes tbl_1_1:', bookmarkNames.includes('tbl_1_1'));

  // Check Hyperlinks
  const hyperlinkMatches = docXml.match(/<w:hyperlink[^>]*w:anchor="([^"]+)"/g) || [];
  const hyperlinkAnchors = hyperlinkMatches.map(m => m.match(/w:anchor="([^"]+)"/)[1]);
  console.log(`[HYPERLINKS] Detected ${hyperlinkAnchors.length} internal hyperlink anchors:`);
  console.log('  Samples:', hyperlinkAnchors.slice(0, 15).join(', '));

  // Check Table of Contents element (should NOT have duplicate automatic w:sdt TOC)
  const hasDuplicateAutoToc = docXml.includes('<w:sdt>');
  console.log('[TOC CHECK] Duplicate automatic <w:sdt> TOC removed:', !hasDuplicateAutoToc, '(Expected: true)');

  // Check Right Tab Stops with Dotted Leader (pos="8787" w:leader="dot")
  const rightTabLeaders = docXml.match(/<w:tab w:val="right" w:pos="8787" w:leader="dot"\/>/g) || [];
  console.log(`[TAB LEADERS] Detected ${rightTabLeaders.length} right-aligned dotted tab stops (pos=8787). Expected: 98 (56 TOC + 29 Figures + 13 Tables).`);

  // Check cantSplit & tblHeader on Table Rows
  const cantSplitRows = docXml.match(/<w:cantSplit\/>/g) || [];
  const tblHeaderRows = docXml.match(/<w:tblHeader\/>/g) || [];
  console.log(`[TABLE PROPS] cantSplit rows: ${cantSplitRows.length}, tblHeader rows: ${tblHeaderRows.length}`);

  // Check Typo "hoành thành" vs "hoàn thành"
  const hasTypoHoanhThanh = docXml.includes('hoành thành');
  console.log('[TYPO CHECK] Contains typo "hoành thành":', hasTypoHoanhThanh, '(Expected: false)');

  // Check 5 containers & Caddy 2 vs old Nginx
  const hasNginx = docXml.includes('Nginx');
  const hasCaddy2 = docXml.includes('Caddy 2');
  const hasRedis7 = docXml.includes('Redis 7');
  console.log('[DOCKER ARCH CHECK] Mentions Nginx:', hasNginx, '(Expected: false) | Mentions Caddy 2:', hasCaddy2, '| Mentions Redis 7:', hasRedis7);

  // Check ERD Table Names synchronization
  const hasLocalMamxanhcert = docXml.includes('local_mamxanhcert');
  const hasFieldpracticeSub = docXml.includes('fieldpractice_sub');
  const hasLocalAgriopsLog = docXml.includes('local_agriops_log');
  console.log('[DB ERD CHECK] local_mamxanhcert:', hasLocalMamxanhcert, '| fieldpractice_sub:', hasFieldpracticeSub, '| local_agriops_log:', hasLocalAgriopsLog);

  // Check Specific texts
  const hasOldSelfPraise = docXml.includes('Nhóm sinh viên thể hiện tinh thần học tập');
  console.log('[M02 CHECK] Contains old student self-praise:', hasOldSelfPraise, '(Expected: false)');

  const hasDottedLines = docXml.includes('............................................................');
  console.log('[M02 CHECK] Contains blank dotted lines for supervisor:', hasDottedLines, '(Expected: true)');

  const hasKeywordsLine = docXml.includes('Từ khóa:') || docXml.includes('Keywords:');
  console.log('[ABSTRACT CHECK] Contains Từ khóa/Keywords line:', hasKeywordsLine, '(Expected: false)');

  const hasDuplicatedStep = docXml.includes('Bước 1: Bước 1:') || docXml.includes('B1: Bước 1:');
  console.log('[APPENDIX CHECK] Contains duplicated "Bước 1: Bước 1:":', hasDuplicatedStep, '(Expected: false)');

  const hasFakeRef = docXml.includes('Trần Ngô Như Khánh, Nghiên cứu ứng dụng công nghệ thông tin trong đổi mới');
  console.log('[REFERENCES CHECK] Contains fake ref [12]:', hasFakeRef, '(Expected: false)');

  const hasTcvnRef = docXml.includes('TCVN 11892-1:2017');
  console.log('[REFERENCES CHECK] Contains official TCVN 11892-1:2017:', hasTcvnRef, '(Expected: true)');

  const hasSupervisorThay = docXml.includes('Thầy Trần Ngô Như Khánh')
    && !docXml.includes('Thầy Thầy Trần Ngô Như Khánh')
    && !docXml.includes('Thạc sĩ Trần Ngô Như Khánh')
    && !docXml.includes('ThS. Trần Ngô Như Khánh')
    && !docXml.includes('Tiến sĩ Trần Ngô Như Khánh')
    && !docXml.includes('TS. Trần Ngô Như Khánh');
  console.log('[ADMIN CHECK] Supervisor title is strictly "Thầy Trần Ngô Như Khánh" (no duplicates):', hasSupervisorThay, '(Expected: true)');

  // Check Images in media
  const mediaFiles = Object.keys(zip.files).filter(k => k.startsWith('word/media/'));
  console.log(`[IMAGES] Total embedded media files: ${mediaFiles.length}`);

  // Summary
  const textMatches = docXml.match(/<w:t[^>]*>(.*?)<\/w:t>/g) || [];
  const fullText = textMatches.map(m => m.replace(/<[^>]+>/g, '')).join(' ');
  const words = fullText.split(/\s+/).filter(Boolean);
  console.log(`[WORDS] Total word count: ${words.length}`);
}

inspectThesis().catch(console.error);
