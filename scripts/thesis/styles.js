import {
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  WidthType,
  AlignmentType,
  HeadingLevel,
  BorderStyle,
  ImageRun,
  ShadingType,
  VerticalAlign,
  PageBreak,
  BookmarkStart,
  BookmarkEnd,
  InternalHyperlink,
  TabStopType,
  LeaderType,
} from 'docx';
import fs from 'fs';
import path from 'path';
import sizeOf from 'image-size';

export const FONT_FAMILY = 'Times New Roman';
export const COLOR_PRIMARY = '000000'; // 100% Monochrome Black
export const COLOR_SECONDARY = '000000'; // 100% Monochrome Black
export const COLOR_TEXT = '000000'; // 100% Monochrome Black
export const COLOR_MUTED = '000000'; // 100% Monochrome Black
export const COLOR_BG_HEADER = 'F2F2F2'; // Neutral light gray for table header
export const COLOR_BORDER = '000000'; // Sharp black border

export const PAGE_PROPS = {
  page: {
    size: { width: 11906, height: 16838 }, // A4
    margin: {
      top: 1418, // 2.5 cm
      bottom: 1418, // 2.5 cm
      left: 1985, // 3.5 cm
      right: 1134, // 2.0 cm
    },
  },
};

/** Normal body paragraph (Times New Roman 13pt, 1.5 line spacing, justified, first line indent) */
export function p(text, options = {}) {
  const {
    indent = 567, // ~1cm first line indent
    bold = false,
    italic = false,
    align = AlignmentType.JUSTIFIED,
    after = 120,
    before = 0,
    color = COLOR_TEXT,
    size = 26, // 13pt
  } = options;

  return new Paragraph({
    alignment: align,
    indent: indent ? { firstLine: indent } : undefined,
    spacing: { before, after, line: 360, lineRule: 'auto' },
    children: [
      new TextRun({
        text,
        font: FONT_FAMILY,
        size,
        bold,
        italic,
        color,
      }),
    ],
  });
}

/** Compound paragraph with multiple styled runs */
export function pRuns(runs, options = {}) {
  const {
    indent = 567,
    align = AlignmentType.JUSTIFIED,
    after = 120,
    before = 0,
  } = options;

  return new Paragraph({
    alignment: align,
    indent: indent ? { firstLine: indent } : undefined,
    spacing: { before, after, line: 360, lineRule: 'auto' },
    children: runs.map(r => new TextRun({
      text: r.text || '',
      font: FONT_FAMILY,
      size: r.size || 26,
      bold: !!r.bold,
      italic: !!r.italic,
      color: r.color || COLOR_TEXT,
    })),
  });
}

/** Chapter Title (Heading 1 with Bookmark & OutlineLevel 0) */
export function h1(text, bookmarkId = '', options = {}) {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text: text.toUpperCase(),
      font: FONT_FAMILY,
      size: 32, // 16pt
      bold: true,
      color: COLOR_PRIMARY,
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    alignment: options.align || AlignmentType.CENTER,
    pageBreakBefore: !!options.pageBreakBefore,
    spacing: { before: 300, after: 200, line: 360, lineRule: 'auto' },
    children,
  });
}

/** Section Title (Heading 2 with Bookmark & OutlineLevel 1, e.g. 1.1.) */
export function h2(text, bookmarkId = '') {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text,
      font: FONT_FAMILY,
      size: 28, // 14pt
      bold: true,
      color: COLOR_SECONDARY,
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    alignment: AlignmentType.LEFT,
    spacing: { before: 240, after: 120, line: 360, lineRule: 'auto' },
    children,
  });
}

/** Sub-section Title (Heading 3 with Bookmark & OutlineLevel 2, e.g. 1.1.1.) */
export function h3(text, bookmarkId = '') {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text,
      font: FONT_FAMILY,
      size: 26, // 13pt
      bold: true,
      color: COLOR_TEXT,
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    alignment: AlignmentType.LEFT,
    spacing: { before: 180, after: 100, line: 360, lineRule: 'auto' },
    children,
  });
}

/** Sub-sub-section Title (Heading 4 with Bookmark & OutlineLevel 3, e.g. 1.1.1.1.) */
export function h4(text, bookmarkId = '') {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text,
      font: FONT_FAMILY,
      size: 26, // 13pt
      bold: true,
      italic: true,
      color: COLOR_TEXT,
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    heading: HeadingLevel.HEADING_4,
    alignment: AlignmentType.LEFT,
    spacing: { before: 140, after: 80, line: 360, lineRule: 'auto' },
    children,
  });
}

/** Bullet item with clean prefix deduplication */
export function bullet(text, options = {}) {
  const { boldPrefix = '', level = 0 } = options;
  let cleanText = text;

  // Clean deduplication if text already starts with boldPrefix
  if (boldPrefix && cleanText.startsWith(boldPrefix)) {
    cleanText = cleanText.slice(boldPrefix.length).trim();
  }

  const children = [];
  if (boldPrefix) {
    children.push(new TextRun({
      text: boldPrefix + ' ',
      font: FONT_FAMILY,
      size: 26,
      bold: true,
      color: COLOR_TEXT,
    }));
  }
  children.push(new TextRun({
    text: cleanText,
    font: FONT_FAMILY,
    size: 26,
    color: COLOR_TEXT,
  }));

  return new Paragraph({
    alignment: AlignmentType.JUSTIFIED,
    indent: { left: 400 + level * 300, hanging: 240 },
    spacing: { before: 40, after: 80, line: 360, lineRule: 'auto' },
    children: [
      new TextRun({ text: '•  ', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
      ...children,
    ],
  });
}

/** Figure caption with optional Bookmark */
export function figureCaption(figNumber, captionText, bookmarkId = '') {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text: `${figNumber}: `,
      font: FONT_FAMILY,
      size: 24, // 12pt
      bold: true,
      color: '000000',
    }),
    new TextRun({
      text: captionText,
      font: FONT_FAMILY,
      size: 24, // 12pt
      italic: true,
      color: '000000',
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 80, after: 180, line: 300, lineRule: 'auto' },
    children,
  });
}

/** Table caption with optional Bookmark */
export function tableCaption(tableNumber, captionText, bookmarkId = '') {
  const children = [];
  if (bookmarkId) children.push(new BookmarkStart(bookmarkId));
  children.push(
    new TextRun({
      text: `${tableNumber}: `,
      font: FONT_FAMILY,
      size: 24, // 12pt
      bold: true,
      color: '000000',
    }),
    new TextRun({
      text: captionText,
      font: FONT_FAMILY,
      size: 24, // 12pt
      bold: true,
      color: '000000',
    })
  );
  if (bookmarkId) children.push(new BookmarkEnd(bookmarkId));

  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 160, after: 80, line: 300, lineRule: 'auto' },
    children,
  });
}

/** Embedded image with scaled dimensions and caption */
export function imageFigure(imageRelPath, figNumber, captionText, bookmarkId = '', maxWidth = 540, maxHeight = 330) {
  let absPath = path.resolve(imageRelPath);
  if (!fs.existsSync(absPath)) {
    absPath = path.resolve('../../', imageRelPath);
  }
  if (!fs.existsSync(absPath)) {
    console.warn(`[WARN] Image not found: ${imageRelPath}`);
    return [
      pRuns([
        { text: `[Lỗi hiển thị hình ảnh: Không tìm thấy tệp ${imageRelPath}]`, italic: true, color: '000000' }
      ], { align: AlignmentType.CENTER })
    ];
  }

  const dim = sizeOf(absPath);
  let w = dim.width;
  let h = dim.height;
  const ratio = w / h;
  if (w > maxWidth) {
    w = maxWidth;
    h = Math.round(w / ratio);
  }
  if (h > maxHeight) {
    h = maxHeight;
    w = Math.round(h * ratio);
  }

  const imgData = fs.readFileSync(absPath);

  return [
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 140, after: 60 },
      children: [
        new ImageRun({
          data: imgData,
          transformation: { width: w, height: h },
        }),
      ],
    }),
    figureCaption(figNumber, captionText, bookmarkId),
  ];
}

/** Standard Academic Table Generator with optional Bookmark */
export function createAcademicTable(headers, rows, colWidthsPct = [], tableNumber = '', captionText = '', bookmarkId = '') {
  const elements = [];
  if (tableNumber && captionText) {
    elements.push(tableCaption(tableNumber, captionText, bookmarkId));
  }

  const borderSpec = {
    style: BorderStyle.SINGLE,
    size: 4, // 0.5 pt
    color: '000000',
  };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
    insideHorizontal: borderSpec,
    insideVertical: borderSpec,
  };

  const headerRow = new TableRow({
    tableHeader: true,
    cantSplit: true,
    children: headers.map((h, i) => new TableCell({
      width: colWidthsPct[i] ? { size: colWidthsPct[i], type: WidthType.PERCENTAGE } : undefined,
      shading: { fill: 'F2F2F2', type: ShadingType.CLEAR },
      verticalAlign: VerticalAlign.CENTER,
      margins: { top: 100, bottom: 100, left: 140, right: 140 },
      children: [
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { before: 0, after: 0, line: 260, lineRule: 'auto' },
          children: [
            new TextRun({
              text: h,
              font: FONT_FAMILY,
              size: 24, // 12pt
              bold: true,
              color: '000000',
            }),
          ],
        }),
      ],
    })),
  });

  const bodyRows = rows.map((row, rIdx) => new TableRow({
    cantSplit: true,
    children: row.map((cellText, cIdx) => {
      const isFirstCol = cIdx === 0;
      const align = isFirstCol && row.length > 3 ? AlignmentType.CENTER : AlignmentType.LEFT;
      return new TableCell({
        width: colWidthsPct[cIdx] ? { size: colWidthsPct[cIdx], type: WidthType.PERCENTAGE } : undefined,
        verticalAlign: VerticalAlign.CENTER,
        margins: { top: 100, bottom: 100, left: 140, right: 140 },
        children: String(cellText).split('\n').map((line, lIdx, arr) => new Paragraph({
          alignment: align,
          spacing: {
            before: lIdx === 0 ? 0 : 20,
            after: lIdx === arr.length - 1 ? 0 : 20,
            line: 280,
            lineRule: 'auto',
          },
          children: [
            new TextRun({
              text: line,
              font: FONT_FAMILY,
              size: 24, // 12pt
              color: '000000',
            }),
          ],
        })),
      });
    }),
  }));

  const table = new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders,
    rows: [headerRow, ...bodyRows],
  });

  elements.push(table);
  elements.push(new Paragraph({ spacing: { after: 160 } }));
  return elements;
}

/** Callout block */
export function callout(title, content) {
  const borderSpec = {
    style: BorderStyle.SINGLE,
    size: 8,
    color: '000000',
  };
  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: {
      left: borderSpec,
      top: { style: BorderStyle.NONE },
      right: { style: BorderStyle.NONE },
      bottom: { style: BorderStyle.NONE },
    },
    rows: [
      new TableRow({
        children: [
          new TableCell({
            shading: { fill: 'F9F9F9', type: ShadingType.CLEAR },
            margins: { top: 120, bottom: 120, left: 180, right: 140 },
            children: [
              new Paragraph({
                spacing: { before: 0, after: 60 },
                children: [
                  new TextRun({
                    text: title,
                    font: FONT_FAMILY,
                    size: 26,
                    bold: true,
                    color: '000000',
                  }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.JUSTIFIED,
                spacing: { before: 0, after: 0, line: 340, lineRule: 'auto' },
                children: [
                  new TextRun({
                    text: content,
                    font: FONT_FAMILY,
                    size: 24,
                    italic: true,
                    color: '000000',
                  }),
                ],
              }),
            ],
          }),
        ],
      }),
    ],
  });
}

/** Right-Aligned Dotted Tab Leader Line wrapped in InternalHyperlink (Ctrl + Click) */
export function tocLeaderLine({ prefix = '', text = '', page = '', anchor = '', indent = 0, bold = false }) {
  const runs = [];
  if (prefix) {
    runs.push(
      new TextRun({
        text: `${prefix}: `,
        font: FONT_FAMILY,
        size: 24, // 12pt
        bold: true,
        color: '000000',
      })
    );
  }
  runs.push(
    new TextRun({
      text: `${text}\t`,
      font: FONT_FAMILY,
      size: bold ? 25 : 24,
      bold: !!bold,
      color: '000000',
    }),
    new TextRun({
      text: String(page),
      font: FONT_FAMILY,
      size: 24,
      bold: !!bold || !!prefix,
      color: '000000',
    })
  );

  return new Paragraph({
    alignment: AlignmentType.LEFT,
    indent: indent ? { left: indent * 300 } : undefined,
    tabStops: [
      {
        type: TabStopType.RIGHT,
        position: 8787, // 11906 (A4) - 1985 (3.5cm left) - 1134 (2.0cm right)
        leader: LeaderType.DOT,
      },
    ],
    spacing: { before: bold ? 80 : 40, after: bold ? 60 : 40, line: 300, lineRule: 'auto' },
    children: [
      new InternalHyperlink({
        anchor,
        children: runs,
      }),
    ],
  });
}

/** Page break */
export function pageBreak() {
  return new Paragraph({ children: [new PageBreak()] });
}
