import {
  Document,
  Packer,
  Paragraph,
  TextRun,
  Footer,
  Header,
  PageNumber,
  NumberFormat,
  AlignmentType,
  BorderStyle,
  SectionType,
} from 'docx';
import fs from 'fs';
import path from 'path';

import {
  FONT_FAMILY,
  COLOR_PRIMARY,
  COLOR_MUTED,
  PAGE_PROPS,
} from './styles.js';
import { THESIS_META } from './metadata.js';

import {
  buildCoverPage,
  buildSupervisorEvaluationPage,
  buildAcknowledgementsPage,
  buildProjectOutlinePage,
  buildTableOfContentsPage,
  buildListOfFiguresPage,
  buildListOfTablesPage,
  buildListOfAbbreviationsPage,
  buildAbstractPage,
} from './section_frontmatter.js';

import { buildIntroSection } from './section_intro.js';
import { buildChapter1Section } from './section_ch1.js';
import { buildChapter2Section } from './section_ch2.js';
import { buildChapter3Section } from './section_ch3.js';
import { buildChapter4Section } from './section_ch4.js';
import { buildConclusionSection } from './section_conclusion.js';
import { buildReferencesSection } from './section_references.js';

async function generateThesisDocument() {
  console.log('[1/4] Preparing Section 1 (Cover Page M01)...');
  const coverChildren = [
    ...buildCoverPage(), // Exactly 1 official cover page
  ];

  console.log('[2/4] Preparing Section 2 (Frontmatter: M02, Ack, M03, TOC, Lists, Abstract)...');
  const frontmatterChildren = [
    ...buildSupervisorEvaluationPage(),
    ...buildAcknowledgementsPage(),
    ...buildProjectOutlinePage(),
    ...buildTableOfContentsPage(),
    ...buildListOfFiguresPage(),
    ...buildListOfTablesPage(),
    ...buildListOfAbbreviationsPage(),
    ...buildAbstractPage(),
  ];

  console.log('[3/4] Preparing Section 3 (Main Body: Intro, Ch1-4, Conclusion, Future, Refs, Appendix)...');
  const bodyChildren = [
    ...buildIntroSection(),
    ...buildChapter1Section(),
    ...buildChapter2Section(),
    ...buildChapter3Section(),
    ...buildChapter4Section(),
    ...buildConclusionSection(),
    ...buildReferencesSection(),
  ];

  // Header for Main Body
  const runningHeader = new Header({
    children: [
      new Paragraph({
        alignment: AlignmentType.RIGHT,
        spacing: { before: 0, after: 120 },
        border: {
          bottom: { style: BorderStyle.SINGLE, size: 4, color: '000000' },
        },
        children: [
          new TextRun({
            text: `${THESIS_META.university} - ${THESIS_META.faculty} | `,
            font: FONT_FAMILY,
            size: 18, // 9pt
            color: COLOR_MUTED,
          }),
          new TextRun({
            text: THESIS_META.titleShort,
            font: FONT_FAMILY,
            size: 18, // 9pt
            italic: true,
            color: COLOR_PRIMARY,
          }),
        ],
      }),
    ],
  });

  // Empty footer for Cover (Section 1)
  const emptyFooter = new Footer({
    children: [
      new Paragraph({
        children: [],
      }),
    ],
  });

  // Lowercase Roman footer for Frontmatter (Section 2: i, ii, iii...)
  const frontmatterFooter = new Footer({
    children: [
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 100, after: 0 },
        children: [
          new TextRun({
            children: [PageNumber.CURRENT],
            font: FONT_FAMILY,
            size: 24, // 12pt
            color: COLOR_MUTED,
          }),
        ],
      }),
    ],
  });

  // Arabic Decimal footer for Main Body (Section 3: 1, 2, 3...)
  const bodyFooter = new Footer({
    children: [
      new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 100, after: 0 },
        children: [
          new TextRun({
            children: [PageNumber.CURRENT],
            font: FONT_FAMILY,
            size: 24, // 12pt
            bold: true,
            color: '000000',
          }),
        ],
      }),
    ],
  });

  console.log('[4/4] Creating Document structure with 3 distinct Sections & updating fields...');
  const doc = new Document({
    title: THESIS_META.title,
    description: `Báo cáo Đồ án Tốt nghiệp CNTT - Trường Đại học Đà Lạt - Nhóm sinh viên: ${THESIS_META.students.map(s => s.name).join(', ')}`,
    creator: 'Nhóm sinh viên Mầm Xanh LMS 2.0 - DLU CTK46',
    features: {
      updateFields: true,
    },
    sections: [
      // SECTION 1: Bìa chính M01 (Không số trang)
      {
        properties: {
          type: SectionType.NEXT_PAGE,
          page: {
            ...PAGE_PROPS.page,
          },
        },
        footers: { default: emptyFooter },
        children: coverChildren,
      },
      // SECTION 2: Đầu trang (Trang M02, Lời cảm ơn, M03, Mục lục, Danh mục, Tóm tắt - Đánh số i, ii, iii...)
      {
        properties: {
          type: SectionType.NEXT_PAGE,
          page: {
            ...PAGE_PROPS.page,
            pageNumbers: { start: 1, formatType: NumberFormat.LOWER_ROMAN },
          },
        },
        footers: { default: frontmatterFooter },
        children: frontmatterChildren,
      },
      // SECTION 3: Thân chính (Mở đầu, Chương 1, 2, 3, 4, Kết luận, Hướng phát triển, Tài liệu tham khảo, Phụ lục - Đánh số Ả-rập 1, 2, 3...)
      {
        properties: {
          type: SectionType.NEXT_PAGE,
          page: {
            ...PAGE_PROPS.page,
            pageNumbers: { start: 1, formatType: NumberFormat.DECIMAL },
          },
        },
        headers: { default: runningHeader },
        footers: { default: bodyFooter },
        children: bodyChildren,
      },
    ],
  });

  const outPath = path.resolve('../../docs/BAO_CAO_DO_AN_TOT_NGHIEP_DLU.docx');
  const buffer = await Packer.toBuffer(doc);
  fs.writeFileSync(outPath, buffer);

  const stats = fs.statSync(outPath);
  console.log(`[SUCCESS] Thesis document regenerated successfully!`);
  console.log(`  File: ${outPath}`);
  console.log(`  Size: ${(stats.size / (1024 * 1024)).toFixed(2)} MB (${stats.size} bytes)`);
}

generateThesisDocument().catch(err => {
  console.error('[FATAL ERROR]:', err);
  process.exit(1);
});
