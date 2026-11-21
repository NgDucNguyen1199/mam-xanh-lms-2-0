import {
  Document,
  Packer,
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  WidthType,
  AlignmentType,
  BorderStyle,
  VerticalAlign,
  SectionType,
  HeightRule,
  PageBreak,
  ImageRun,
  ShadingType,
} from 'docx';
import fs from 'fs';
import path from 'path';

const FONT_FAMILY = 'Times New Roman';
const COLOR_BLACK = '000000';
const COLOR_BG_HEADER = 'F2F2F2';

const PAGE_PROPS = {
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

/** Helper for standard body paragraph (Times New Roman 13pt, 1.5 line spacing, justified) */
function p(text, options = {}) {
  const {
    indent = 567, // ~1cm first line indent
    bold = false,
    italic = false,
    align = AlignmentType.JUSTIFIED,
    after = 120,
    before = 0,
    size = 26, // 13pt
    color = COLOR_BLACK,
    line = 360, // 1.5 lines
  } = options;

  return new Paragraph({
    alignment: align,
    indent: indent ? { firstLine: indent } : undefined,
    spacing: { before, after, line, lineRule: 'auto' },
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

/** Helper for bullet points */
function bullet(text, options = {}) {
  const { after = 60, before = 0, size = 26 } = options;
  return new Paragraph({
    alignment: AlignmentType.JUSTIFIED,
    indent: { left: 720, hanging: 360 },
    spacing: { before, after, line: 360, lineRule: 'auto' },
    children: [
      new TextRun({ text: '•  ', font: FONT_FAMILY, size, bold: true, color: COLOR_BLACK }),
      new TextRun({ text, font: FONT_FAMILY, size, color: COLOR_BLACK }),
    ],
  });
}

/** Helper for section headings */
function sectionHeading(text, level = 1) {
  const size = level === 1 ? 28 : 26; // 14pt or 13pt
  const before = level === 1 ? 240 : 180;
  const after = level === 1 ? 120 : 80;

  return new Paragraph({
    alignment: AlignmentType.LEFT,
    spacing: { before, after, line: 360, lineRule: 'auto' },
    children: [
      new TextRun({
        text,
        font: FONT_FAMILY,
        size,
        bold: true,
        color: COLOR_BLACK,
      }),
    ],
  });
}

/** Helper for academic tables with 100% black borders and neutral gray header */
function academicTable(headers, rows, colWidthsPct = [], captionText = '') {
  const elements = [];

  if (captionText) {
    elements.push(
      new Paragraph({
        alignment: AlignmentType.LEFT,
        spacing: { before: 160, after: 80, line: 300, lineRule: 'auto' },
        children: [
          new TextRun({
            text: captionText,
            font: FONT_FAMILY,
            size: 26,
            bold: true,
            color: COLOR_BLACK,
          }),
        ],
      })
    );
  }

  const borderSpec = {
    style: BorderStyle.SINGLE,
    size: 4, // 0.5pt
    color: COLOR_BLACK,
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
      shading: { fill: COLOR_BG_HEADER, type: ShadingType.CLEAR },
      verticalAlign: VerticalAlign.CENTER,
      margins: { top: 120, bottom: 120, left: 140, right: 140 },
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
              color: COLOR_BLACK,
            }),
          ],
        }),
      ],
    })),
  });

  const bodyRows = rows.map((row) => new TableRow({
    cantSplit: true,
    children: row.map((cellText, cIdx) => {
      const isCenter = cIdx === 0 || (cIdx === 2 && row.length >= 4) || (cIdx === 3 && row.length === 5);
      const align = isCenter ? AlignmentType.CENTER : AlignmentType.LEFT;
      return new TableCell({
        width: colWidthsPct[cIdx] ? { size: colWidthsPct[cIdx], type: WidthType.PERCENTAGE } : undefined,
        verticalAlign: VerticalAlign.CENTER,
        margins: { top: 100, bottom: 100, left: 140, right: 140 },
        children: String(cellText).split('\n').map(lineStr => new Paragraph({
          alignment: align,
          spacing: { before: 20, after: 20, line: 280, lineRule: 'auto' },
          children: [
            new TextRun({
              text: lineStr,
              font: FONT_FAMILY,
              size: 24, // 12pt
              color: COLOR_BLACK,
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
  elements.push(new Paragraph({ spacing: { after: 140 } }));
  return elements;
}

/** 1. TRANG BÌA (CÓ LOGO KHOA CNTT ĐH ĐÀ LẠT) */
function buildCoverPage(logoBuffer) {
  const borderSpec = {
    style: BorderStyle.DOUBLE,
    size: 24, // double decorative border
    color: COLOR_BLACK,
  };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
  };

  const coverContent = [
    // 1. Top university info
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 60, after: 40 },
      children: [
        new TextRun({
          text: 'TRƯỜNG ĐẠI HỌC ĐÀ LẠT',
          font: FONT_FAMILY,
          size: 28, // 14pt (in hoa thường)
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 120 },
      children: [
        new TextRun({
          text: 'KHOA CÔNG NGHỆ THÔNG TIN',
          font: FONT_FAMILY,
          size: 28, // 14pt (in hoa đậm)
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),

    // 2. Logo Khoa Công nghệ Thông tin
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 80, after: 160 },
      children: [
        new ImageRun({
          data: logoBuffer,
          transformation: { width: 95, height: 95 },
        }),
      ],
    }),

    // 3. Tiêu đề lớn
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 600, after: 160 },
      children: [
        new TextRun({
          text: 'ĐỀ CƯƠNG ĐỒ ÁN TỐT NGHIỆP',
          font: FONT_FAMILY,
          size: 40, // 20pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),

    // 4. Tên đề tài
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 1000, line: 360, lineRule: 'auto' },
      children: [
        new TextRun({
          text: 'ĐỀ TÀI: XÂY DỰNG HỆ THỐNG QUẢN LÝ HỌC TẬP VÀ TẬP HUẤN KỸ THUẬT CANH TÁC NÔNG NGHIỆP CÔNG NGHỆ CAO TRÊN NỀN TẢNG MOODLE',
          font: FONT_FAMILY,
          size: 30, // 15pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),

    // 5. Khối thông tin tác giả căn thẳng trục
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 1600 },
      spacing: { before: 200, after: 80 },
      children: [
        new TextRun({
          text: 'Giảng viên hướng dẫn: ',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
        new TextRun({
          text: 'TS. Trần Ngô Như Khánh',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 1600 },
      spacing: { before: 60, after: 60 },
      children: [
        new TextRun({
          text: 'Sinh viên thực hiện:',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 2000 },
      spacing: { before: 30, after: 30 },
      children: [
        new TextRun({
          text: '2212429 – Nguyễn Đức Nguyên (Lớp CTK46A)',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 2000 },
      spacing: { before: 30, after: 30 },
      children: [
        new TextRun({
          text: '2212484 – Mai Xuân Trưởng (Lớp CTK46A)',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.LEFT,
      indent: { left: 2000 },
      spacing: { before: 30, after: 30 },
      children: [
        new TextRun({
          text: '2212447 – Nguyễn Ngọc Tuệ Quang (Lớp CTK46A)',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),

    // 6. Sát đáy khung bìa
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 1200, after: 60 },
      children: [
        new TextRun({
          text: 'Đà Lạt, tháng 11 năm 2026',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
  ];

  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders,
    rows: [
      new TableRow({
        height: { value: 13700, rule: HeightRule.EXACT },
        cantSplit: true,
        children: [
          new TableCell({
            margins: { top: 260, bottom: 260, left: 350, right: 350 },
            verticalAlign: VerticalAlign.TOP,
            children: coverContent,
          }),
        ],
      }),
    ],
  });
}

/** 2. TRANG NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN (22 DÒNG CHẤM THEO MẪU KHÓA TRÊN) */
function buildSupervisorRemarksPage() {
  const borderSpec = {
    style: BorderStyle.SINGLE,
    size: 8, // 1pt solid black border
    color: COLOR_BLACK,
  };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
  };

  const fullDotLine = '.'.repeat(114);
  const cellChildren = [
    // Tiêu đề căn giữa, in đậm, chữ hoa
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 80, after: 160 },
      children: [
        new TextRun({
          text: 'NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN',
          font: FONT_FAMILY,
          size: 26, // 13pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
  ];

  // Khung kẻ đúng 22 dòng chấm ngang
  for (let i = 0; i < 22; i++) {
    cellChildren.push(
      new Paragraph({
        spacing: { before: 14, after: 14, line: 360, lineRule: 'auto' },
        children: [
          new TextRun({
            text: fullDotLine,
            font: FONT_FAMILY,
            size: 22,
            color: COLOR_BLACK,
          }),
        ],
      })
    );
  }

  // Góc dưới bên phải: Ngày tháng & Chữ ký
  cellChildren.push(
    new Paragraph({
      alignment: AlignmentType.CENTER,
      indent: { left: 4200 },
      spacing: { before: 160, after: 40 },
      children: [
        new TextRun({
          text: 'Đà Lạt, ngày ..... tháng ..... năm 2026',
          font: FONT_FAMILY,
          size: 26, // 13pt
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      indent: { left: 4200 },
      spacing: { before: 0, after: 40 },
      children: [
        new TextRun({
          text: 'Giảng viên hướng dẫn',
          font: FONT_FAMILY,
          size: 26, // 13pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      indent: { left: 4200 },
      spacing: { before: 0, after: 600 }, // Khoảng trống ký tên
      children: [
        new TextRun({
          text: '[Ký tên và ghi rõ họ tên]',
          font: FONT_FAMILY,
          size: 26, // 13pt
          color: COLOR_BLACK,
        }),
      ],
    })
  );

  return new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders,
    rows: [
      new TableRow({
        height: { value: 13700, rule: HeightRule.EXACT },
        cantSplit: true,
        children: [
          new TableCell({
            margins: { top: 220, bottom: 220, left: 260, right: 260 },
            verticalAlign: VerticalAlign.TOP,
            children: cellChildren,
          }),
        ],
      }),
    ],
  });
}

/** 3. TRANG LỜI CẢM ƠN */
function buildAcknowledgementsPage() {
  return [
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 120, after: 300 },
      children: [
        new TextRun({
          text: 'LỜI CẢM ƠN',
          font: FONT_FAMILY,
          size: 32, // 16pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),

    p('Đề cương Đồ án Tốt nghiệp là bước khởi đầu mang tính quyết định, định hình toàn bộ cơ sở lý thuyết, kiến trúc giải pháp và kế hoạch thực hiện cho đề tài nghiên cứu chuyên ngành Công nghệ Thông tin tại Trường Đại học Đà Lạt. Để hoàn thiện bản đề cương chi tiết này, nhóm chúng em xin gửi lời cảm ơn chân thành và sâu sắc nhất đến Ban Giám hiệu Trường Đại học Đà Lạt, Ban Chủ nhiệm Khoa Công nghệ Thông tin cùng toàn thể quý Thầy, Cô giáo trong Khoa đã tận tình truyền đạt những tri thức nền tảng và chuyên sâu quý báu trong suốt bốn năm đại học vừa qua.'),

    p('Đặc biệt, nhóm chúng em xin bày tỏ lòng biết ơn sâu sắc nhất tới Thầy TS. Trần Ngô Như Khánh – Giảng viên hướng dẫn khoa học của đề tài. Thầy đã dành nhiều thời gian quý báu định hướng cấu trúc đề cương, gợi mở các bài toán thực tiễn cốt lõi về công nghệ giáo dục (EdTech) trong nông nghiệp, chỉ dẫn phương pháp luận tích hợp mật mã học HMAC-SHA256 và nghiêm khắc hoàn thiện từng chuẩn mực học thuật trong đề cương cũng như hệ thống phần mềm.'),

    p('Nhóm nghiên cứu cũng xin chân thành cảm ơn quý Thầy, Cô giáo trong Hội đồng phản biện và các Thầy, Cô bộ môn đã đóng góp những ý kiến phản biện quý báu ngay từ giai đoạn xét duyệt đề cương; đồng thời cảm ơn các kỹ sư Chi cục Trồng trọt & Bảo vệ Thực vật tỉnh Lâm Đồng, Ban quản trị các Hợp tác xã Nông nghiệp Công nghệ cao Lạc Dương, Đơn Dương và Đà Lạt đã nhiệt tình chia sẻ dữ liệu thực nghiệm đồng ruộng, góp ý về quy trình tập huấn thực tế để nhóm xây dựng các kịch bản học tập bám sát thực tiễn sản xuất.'),

    p('Cuối cùng, chúng con xin gửi lời tri ân sâu nặng nhất tới cha mẹ, gia đình và bạn bè đã luôn là điểm tựa tinh thần vững chắc, động viên và tạo mọi điều kiện tốt nhất cho chúng con trong suốt quá trình học tập và hoàn thành đề cương đồ án tốt nghiệp này.'),

    p('Nhóm chúng em xin trân trọng cảm ơn!', { bold: true }),

    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 240, after: 40 },
      children: [
        new TextRun({
          text: 'Đà Lạt, tháng 11 năm 2026',
          font: FONT_FAMILY,
          size: 26,
          italic: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 20, after: 80 },
      children: [
        new TextRun({
          text: 'NHÓM SINH VIÊN THỰC HIỆN        ',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 20, after: 20 },
      children: [
        new TextRun({ text: 'Nguyễn Đức Nguyên        ', font: FONT_FAMILY, size: 26, color: COLOR_BLACK }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 20, after: 20 },
      children: [
        new TextRun({ text: 'Mai Xuân Trưởng        ', font: FONT_FAMILY, size: 26, color: COLOR_BLACK }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 20, after: 20 },
      children: [
        new TextRun({ text: 'Nguyễn Ngọc Tuệ Quang        ', font: FONT_FAMILY, size: 26, color: COLOR_BLACK }),
      ],
    }),
  ];
}

/** 4. NỘI DUNG ĐỀ CƯƠNG THỰC HIỆN ĐỒ ÁN */
function buildOutlineContent() {
  const elements = [];

  // Header đầu trang đề cương
  elements.push(
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 60, after: 30 },
      children: [
        new TextRun({ text: 'TRƯỜNG ĐẠI HỌC ĐÀ LẠT', font: FONT_FAMILY, size: 26, color: COLOR_BLACK }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 40 },
      children: [
        new TextRun({ text: 'KHOA CÔNG NGHỆ THÔNG TIN', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 180 },
      children: [
        new TextRun({ text: '---oOo---', font: FONT_FAMILY, size: 24, color: COLOR_BLACK }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 100, after: 200 },
      children: [
        new TextRun({
          text: 'ĐỀ CƯƠNG THỰC HIỆN ĐỒ ÁN TỐT NGHIỆP',
          font: FONT_FAMILY,
          size: 32, // 16pt
          bold: true,
          color: COLOR_BLACK,
        }),
      ],
    })
  );

  // Tên đề tài
  elements.push(
    p('Tên đề tài: Xây dựng hệ thống quản lý học tập và tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao trên nền tảng Moodle (Mầm Xanh LMS 2.0)', {
      indent: 0,
      bold: true,
      after: 140,
    })
  );

  // Bảng thông tin sinh viên thực hiện
  elements.push(p('Sinh viên thực hiện:', { indent: 0, bold: true, after: 60 }));
  const studentHeaders = ['STT', 'Họ và tên', 'MSSV', 'Lớp', 'Email liên hệ'];
  const studentRows = [
    ['1', 'Nguyễn Đức Nguyên (Trưởng nhóm)', '2212429', 'CTK46A', '2212429@dlu.edu.vn'],
    ['2', 'Mai Xuân Trưởng', '2212484', 'CTK46A', '2212484@dlu.edu.vn'],
    ['3', 'Nguyễn Ngọc Tuệ Quang', '2212447', 'CTK46A', '2212447@dlu.edu.vn'],
  ];
  const studentWidths = [8, 32, 16, 14, 30];
  elements.push(...academicTable(studentHeaders, studentRows, studentWidths));

  // Giảng viên hướng dẫn
  elements.push(
    p('Giảng viên hướng dẫn: TS. Trần Ngô Như Khánh (Bộ môn Công nghệ Thông tin – Khoa CNTT)', {
      indent: 0,
      bold: true,
      after: 180,
    })
  );

  // I. Mục tiêu đề tài
  elements.push(sectionHeading('I. Mục tiêu đề tài:'));
  elements.push(
    p('1. Mục tiêu tổng quát: Triển khai, tối ưu hóa và phát triển các phân hệ chuyên sâu trên nền tảng Moodle 4.5 LTS thành môi trường e-Learning tập trung toàn diện cho công tác tập huấn kỹ thuật nông nghiệp công nghệ cao (Mầm Xanh LMS 2.0). Hệ thống giải quyết triệt để rào cản địa lý và tính thời vụ, hỗ trợ cán bộ kỹ thuật chuẩn hóa quy trình sư phạm, theo dõi tiến độ đào tạo theo thời gian thực; đồng thời giúp nông hộ và chủ trang trại học tập linh hoạt trên thiết bị di động.')
  );
  elements.push(
    p('2. Mục tiêu chức năng quản lý đào tạo chuẩn học thuật: Xây dựng hệ thống hoàn chỉnh tương đương chuẩn mực của các hệ thống LMS đại học chính quy (như lms.dlu.edu.vn): phân quyền RBAC đa vai trò (Quản trị viên, Cán bộ kỹ thuật, Giảng viên, Nông hộ); quản lý tiến độ học tập (Activity Completion); ngân hàng đề thi trắc nghiệm (Quiz); bài tập thực hành giao việc (Assignment); diễn đàn giải đáp kỹ thuật (Forum); và sổ điểm điện tử Gradebook minh bạch với công thức tính điểm trọng số.')
  );
  elements.push(
    p('3. Mục tiêu đặc thù phục vụ nông nghiệp công nghệ cao: Số hóa hoàn chỉnh 08 khóa học kỹ thuật công nghệ cao chủ lực tại Lâm Đồng (Dâu tây, Rau thủy canh, Nấm linh chi, Cà chua bi, Chè Oolong, Sầu riêng VietGAP, Phân bón vi sinh, IoT Nhà màng); phát triển bộ 5 module/plugin Moodle tùy biến gồm: thực hành thực địa GPS kèm ma trận Rubric (mod_fieldpractice), chứng chỉ số mã hóa HMAC-SHA256 & QR (local_mamxanhcert), nhật ký canh tác điện tử VietGAP (local_agriops), hồ sơ học thuật & bảng điểm tổng kết (local_mamxanhapp), và giao diện công thái học tối giản Minimalist Off-White Botanical (agritrain_child).')
  );
  elements.push(
    p('4. Mục tiêu chất lượng và vận hành: Giao diện song ngữ Việt - Anh linh hoạt, responsive 100% trên thiết bị di động, an toàn thông tin theo chuẩn OWASP Top 10, đóng gói tự động hóa 5 container vi dịch vụ trên nền tảng Docker Turbo và kiểm thử hồi quy Pass 100% trên 66 URL chức năng.')
  );

  // II. Nội dung đề tài & Phân công nhiệm vụ
  elements.push(sectionHeading('II. Nội dung đề tài và Phân công nhiệm vụ:'));
  elements.push(
    p('Đề tài được triển khai toàn diện qua 6 giai đoạn kỹ thuật với sự phối hợp chặt chẽ giữa các thành viên:')
  );
  elements.push(
    bullet('Giai đoạn 1 – Khảo sát và phân tích yêu cầu: Khảo sát thực địa các mô hình nông nghiệp công nghệ cao tại Đà Lạt, Lạc Dương và Đơn Dương; xác định các rào cản tiếp cận e-Learning của người nông dân; xây dựng tài liệu đặc tả yêu cầu phần mềm (SRS), ma trận truy vết và sơ đồ ca sử dụng Use Case.'),
    bullet('Giai đoạn 2 – Thiết kế kiến trúc tổng thể & CSDL: Thiết kế kiến trúc 4 tầng (Client – Gateway Proxy – Application – Database & Cache); chuẩn hóa Database Schema gồm 508 bảng dữ liệu lõi Moodle và 5 bảng tùy biến (local_mamxanhcert, fieldpractice, fieldpractice_sub, local_agriops_log, farmer_profile); thiết kế sơ đồ thực thể liên kết ERD và lưu đồ nghiệp vụ Swimlane.'),
    bullet('Giai đoạn 3 – Triển khai hạ tầng ảo hóa Docker Turbo & Moodle 4.5 LTS: Cấu hình hạ tầng 5 dịch vụ vi dịch vụ với Docker Compose (Caddy 2 Proxy, PHP 8.3 App, MariaDB 10.11, Redis 7 Cache, Moodle Cron Worker); tối ưu hóa OPcache JIT, bộ đệm Redis và cấu hình hệ thống sao lưu dự phòng tự động.'),
    bullet('Giai đoạn 4 – Số hóa 8 chương trình tập huấn công nghệ cao: Xây dựng đồng bộ 8 khóa học kỹ thuật theo cấu trúc sư phạm chuẩn mực (Thông tin chung -> 4 Chuyên đề kỹ thuật -> Kiểm tra & Đánh giá); tích hợp gói bài giảng SCORM 1.2, hoạt động tương tác H5P, video thực địa và tài liệu hướng dẫn kỹ thuật chính thống từ Bộ NN&PTNT.'),
    bullet('Giai đoạn 5 – Lập trình 5 phân hệ plugin chuyên sâu: Hiện thực hóa mod_fieldpractice (điểm danh QR xoay vòng kèm tọa độ GPS, chấm điểm Rubric 4 tiêu chí 100 điểm); local_mamxanhcert (ký số HMAC-SHA256, tra cứu công khai QR); local_agriops (nhật ký canh tác VietGAP TCVN 11892-1:2017); local_mamxanhapp (bảng điểm tổng kết Transcript, công cụ nhân bản khóa học đồng bộ); và theme agritrain_child (công thái học Minimalist Off-White).'),
    bullet('Giai đoạn 6 – Tự động hóa kiểm thử E2E & Đánh giá công thái học: Xây dựng kịch bản kiểm thử tự động với Puppeteer trên toàn bộ 66 URL chức năng; kiểm tra phân quyền bảo mật RBAC; đánh giá độ tiện dụng theo thang đo công thái học tiêu chuẩn SUS (System Usability Scale đạt 84.2/100 – Hạng A).')
  );

  // Bảng phân công nhiệm vụ chi tiết
  elements.push(p('Bảng phân công nhiệm vụ chi tiết các thành viên trong nhóm nghiên cứu:', { indent: 0, bold: true, before: 120, after: 60 }));
  const taskHeaders = ['STT', 'Họ và tên SV', 'MSSV', 'Nhiệm vụ nghiên cứu và phát triển được phân công', 'Tỷ lệ', 'Kết quả'];
  const taskRows = [
    [
      '1',
      'Nguyễn Đức Nguyên\n(Trưởng nhóm)',
      '2212429',
      '- Khảo sát yêu cầu, thiết kế kiến trúc tổng thể 4 tầng và Database Schema (513 bảng).\n- Thiết lập môi trường Docker Turbo 5 vi dịch vụ (Caddy 2, PHP 8.3, MariaDB, Redis, Cron).\n- Lập trình phân hệ plugin: local_mamxanhcert (mã hóa HMAC-SHA256 & QR), local_mamxanhapp.\n- Xây dựng bộ công cụ dòng lệnh PHP CLI phục vụ bảo trì tự động và đồng bộ CSDL.',
      '35%',
      '100%',
    ],
    [
      '2',
      'Mai Xuân Trưởng',
      '2212484',
      '- Nghiên cứu tiêu chuẩn công thái học UI/UX Minimalist Off-White Botanical cho nông dân.\n- Phát triển theme agritrain_child, chuẩn hóa breadcrumbs, navbar đơn và thông báo học tập.\n- Lập trình phân hệ thực hành thực địa mod_fieldpractice (chấm điểm Rubric, tọa độ GPS).\n- Thiết kế bộ sơ đồ kiến trúc hệ thống chuẩn UML/Swimlane (D1 - D9).',
      '33%',
      '100%',
    ],
    [
      '3',
      'Nguyễn Ngọc Tuệ Quang',
      '2212447',
      '- Chuẩn hóa nội dung số hóa 8 khóa học theo chuẩn e-Learning (SCORM 1.2, H5P Activity).\n- Lập trình plugin Nhật ký điện tử canh tác và truy xuất nguồn gốc nông sản local_agriops.\n- Thiết kế và thực thi kiểm thử hồi quy tự động E2E với Puppeteer trên 66 URL chức năng.\n- Tổng hợp số liệu thực nghiệm, viết báo cáo đề cương và đóng gói đĩa CD/DVD nộp đề tài.',
      '32%',
      '100%',
    ],
  ];
  const taskWidths = [6, 20, 12, 48, 7, 7];
  elements.push(...academicTable(taskHeaders, taskRows, taskWidths));

  // III. Phần mềm và công cụ sử dụng
  elements.push(sectionHeading('III. Phần mềm và công cụ sử dụng:'));
  elements.push(
    p('Đề tài ưu tiên lựa chọn các công nghệ mã nguồn mở hiện đại, có tính ổn định cao, được hỗ trợ lâu dài (LTS) và phù hợp với tiêu chuẩn chuyển đổi số ngành nông nghiệp:')
  );
  const toolHeaders = ['STT', 'Nhóm thành phần', 'Phần mềm & Công cụ sử dụng', 'Mục đích áp dụng trong đồ án'];
  const toolRows = [
    ['1', 'Nền tảng lõi LMS', 'Moodle LMS 4.5.15+ LTS (PHP 8.3, Apache 2.4)', 'Vận hành toàn bộ nghiệp vụ quản lý đào tạo, phân quyền RBAC và quản lý khóa học.'],
    ['2', 'Cơ sở dữ liệu & Cache', 'MariaDB 10.11 LTS & Redis 7.2 In-Memory Cache', 'Lưu trữ 513 bảng dữ liệu (508 core + 5 tùy biến), đệm phiên làm việc và tăng tốc truy vấn.'],
    ['3', 'Cổng kết nối bảo mật', 'Caddy 2 Reverse Proxy', 'Tự động hóa chứng chỉ số SSL/TLS, nén dữ liệu gzip/zstd, định tuyến truy cập HTTPS bảo mật.'],
    ['4', 'Ảo hóa hạ tầng', 'Docker Engine v27.2 & Docker Compose v2.29', 'Đóng gói 5 dịch vụ vi dịch vụ thành môi trường Docker Turbo triển khai tự động chỉ với 1 lệnh.'],
    ['5', 'Chuẩn học liệu số', 'SCORM 1.2 & H5P Interactive Content', 'Xây dựng bài giảng tương tác đa phương tiện, video tương tác và theo dõi tiến độ chuẩn quốc tế.'],
    ['6', 'Giao diện công thái học', 'HTML5, SCSS/CSS3, JavaScript (Bootstrap 5)', 'Phát triển theme agritrain_child chuẩn Minimalist Off-White Botanical tối ưu cho nông hộ.'],
    ['7', 'Bảo mật & Mã hóa', 'OpenSSL, Thuật toán HMAC-SHA256 & QR Code', 'Sinh chữ ký số cho chứng chỉ điện tử chống làm giả và hỗ trợ quét QR tra cứu công khai.'],
    ['8', 'Kiểm thử tự động hóa', 'Node.js v24 & Puppeteer v24 Testing Framework', 'Tự động hóa kiểm thử luồng chức năng E2E trên toàn bộ 66 URL của hệ thống.'],
    ['9', 'Quản lý mã nguồn', 'Git & GitHub Version Control System', 'Quản lý phiên bản mã nguồn phân tán, theo dõi commit và phối hợp nhóm đa thành viên.'],
  ];
  const toolWidths = [6, 22, 32, 40];
  elements.push(...academicTable(toolHeaders, toolRows, toolWidths));

  // IV. Kế hoạch thực hiện
  elements.push(sectionHeading('IV. Kế hoạch thực hiện:'));
  elements.push(
    p('Kế hoạch triển khai bám sát 11 mốc thời gian chuẩn do Khoa Công nghệ Thông tin – Trường Đại học Đà Lạt ban hành, bảo đảm hoàn thành đầy đủ các sản phẩm đầu ra theo đúng tiến độ:')
  );
  const planHeaders = ['STT', 'Nội dung công việc triển khai', 'Thời gian (2026)', 'Sản phẩm đầu ra'];
  const planRows = [
    ['1', 'Hoàn thiện đề cương chi tiết, nộp hệ thống LMS Khoa CNTT', '03/08 - 10/08', 'Bản Đề cương chi tiết hoàn chỉnh'],
    ['2', 'Nhận phản hồi từ GVHD, sửa đề cương, trình duyệt kế hoạch', '11/08 - 16/08', 'Kế hoạch triển khai được phê duyệt'],
    ['3', 'Khảo sát thực địa yêu cầu nông hộ, xác định phạm vi và tài liệu SRS', '17/08 - 30/08', 'Tài liệu SRS và sơ đồ Use Case'],
    ['4', 'Thiết kế kiến trúc 4 tầng, CSDL 513 bảng, dựng hạ tầng Docker Turbo', '31/08 - 13/09', 'Sơ đồ kiến trúc, Docker Compose'],
    ['5', 'Cấu hình Moodle lõi, phân quyền RBAC và số hóa 02 khóa học đầu tiên', '14/09 - 20/09', 'Báo cáo tiến độ 1 & Demo ban đầu'],
    ['6', 'Xây dựng 06 khóa học tiếp theo (SCORM, H5P), lập trình 5 plugin chuyên sâu', '21/09 - 11/10', 'Bản chức năng chính hoàn chỉnh'],
    ['7', 'Tích hợp sổ điểm Gradebook, báo cáo tiến độ, kiểm thử liên thông vai trò', '12/10 - 18/10', 'Báo cáo tiến độ 2'],
    ['8', 'Kiểm thử hồi quy tự động E2E Puppeteer (66 URLs), đo lường SUS, lấy phản hồi', '19/10 - 15/11', 'Bản demo hoàn thiện & Biên bản test'],
    ['9', 'Viết bản thảo Báo cáo Đồ án tốt nghiệp, chuẩn bị Slide thuyết trình', '16/11 - 22/11', 'Bản thảo Đồ án tốt nghiệp'],
    ['10', 'Sửa chữa, hoàn thiện tài liệu báo cáo theo góp ý của GVHD', '23/11 - 29/11', 'Quyển Báo cáo Đồ án hoàn chỉnh'],
    ['11', 'Đóng gói hồ sơ đĩa CD/DVD và bảo vệ đồ án tốt nghiệp trước Hội đồng', '30/11 - 06/12', 'Hồ sơ nộp đĩa & Bảo vệ thành công'],
  ];
  const planWidths = [6, 50, 18, 26];
  elements.push(...academicTable(planHeaders, planRows, planWidths));

  // V. Dự kiến kết quả đạt được
  elements.push(sectionHeading('V. Dự kiến kết quả đạt được:'));
  elements.push(
    p('Đề tài cam kết hoàn thành đầy đủ 6 nhóm sản phẩm ứng dụng và học thuật chất lượng cao:')
  );
  elements.push(
    bullet('(1) Hệ thống Mầm Xanh LMS 2.0 vận hành ổn định trên hạ tầng Docker Turbo 5 dịch vụ vi dịch vụ (Caddy 2, PHP 8.3, MariaDB 10.11, Redis 7, Cron), đáp ứng đầy đủ nghiệp vụ quản lý đào tạo chuẩn học thuật;'),
    bullet('(2) Bộ học liệu số hóa 08 chương trình tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao chủ lực của tỉnh Lâm Đồng theo chuẩn e-Learning đa phương tiện (SCORM 1.2, H5P, Quiz, Rubric thực hành);'),
    bullet('(3) Bộ 5 module/plugin Moodle tùy biến chuyên biệt: mod_fieldpractice (thực hành GPS & Rubric), local_mamxanhcert (chứng chỉ số HMAC-SHA256 & QR), local_agriops (nhật ký điện tử VietGAP), local_mamxanhapp (bảng điểm học thuật Transcript & nhân bản khóa học), và theme agritrain_child (công thái học Minimalist Off-White Botanical);'),
    bullet('(4) Bộ công cụ kiểm thử tự động hóa E2E hoàn chỉnh với Puppeteer kiểm tra toàn diện 66 URL chức năng với kết quả Pass 100%, kèm kết quả đánh giá công thái học SUS đạt 84.2/100 (Hạng A);'),
    bullet('(5) Bộ hồ sơ tài liệu học thuật đầy đủ và chỉn chu: Bản Đề cương đồ án tốt nghiệp hoàn chỉnh, Quyển Báo cáo Đồ án tốt nghiệp 62 trang A4 đạt chuẩn Khoa CNTT, và Bộ Slide thuyết trình 26 trang;'),
    bullet('(6) Đĩa CD/DVD nộp đề tài đóng gói chuẩn theo quy định Khoa CNTT Trường Đại học Đà Lạt (gồm báo cáo, slide, mã nguồn, CSDL sao lưu, hướng dẫn triển khai và nhãn đĩa Mẫu M04).')
  );

  // VI. Tài liệu tham khảo
  elements.push(sectionHeading('VI. Tài liệu tham khảo:'));
  const refs = [
    '[1] Moodle HQ, Moodle 4.5 LTS Documentation & Developer Guides (2024). https://docs.moodle.org/405/en/ và https://moodledev.io/general/releases/4.5',
    '[2] Advanced Distributed Learning (ADL) Initiative, Sharable Content Object Reference Model (SCORM) 1.2 Specification (2001). https://adlnet.gov/projects/scorm/',
    '[3] H5P Group, H5P Interactive Content Authoring Documentation & Activity API (2024). https://h5p.org/documentation',
    '[4] Bộ Nông nghiệp & Phát triển Nông thôn, TCVN 11892-1:2017 – Thực hành nông nghiệp tốt (VietGAP) – Phần 1: Trồng trọt, Nhà xuất bản Tiêu chuẩn (2017).',
    '[5] Bộ Nông nghiệp & Phát triển Nông thôn, Thông tư số 48/2012/TT-BNNPTNT về Quy định chứng nhận sản phẩm thủy sản, trồng trọt, chăn nuôi được sản xuất, sơ chế phù hợp với Quy trình thực hành sản xuất nông nghiệp tốt (2012).',
    '[6] H. Krawczyk, M. Bellare, R. Canetti, RFC 2104: HMAC: Keyed-Hashing for Message Authentication, Internet Engineering Task Force (IETF) (1997). https://datatracker.ietf.org/doc/html/rfc2104',
    '[7] W3C Web Accessibility Initiative, Web Content Accessibility Guidelines (WCAG) 2.2, W3C Recommendation (2023). https://www.w3.org/TR/WCAG22/',
  ];
  refs.forEach(r => {
    elements.push(
      new Paragraph({
        alignment: AlignmentType.JUSTIFIED,
        indent: { left: 567, hanging: 567 },
        spacing: { before: 60, after: 60, line: 360, lineRule: 'auto' },
        children: [
          new TextRun({ text: r, font: FONT_FAMILY, size: 26, color: COLOR_BLACK }),
        ],
      })
    );
  });

  // Khối chữ ký xác nhận cuối đề cương (Theo mẫu khóa trên)
  elements.push(
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 240, after: 120 },
      children: [
        new TextRun({
          text: 'Đà Lạt, ngày ..... tháng ..... năm 2026',
          font: FONT_FAMILY,
          size: 26,
          italic: true,
          color: COLOR_BLACK,
        }),
      ],
    })
  );

  // Bảng không viền 2 cột cân đối cho khối chữ ký 4 bên
  const borderNone = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
  const noBorders = {
    top: borderNone,
    bottom: borderNone,
    left: borderNone,
    right: borderNone,
    insideHorizontal: borderNone,
    insideVertical: borderNone,
  };

  const signTable = new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    borders: noBorders,
    rows: [
      // Hàng 1: GVHD & Sinh viên thực hiện
      new TableRow({
        cantSplit: true,
        children: [
          // Cột trái: GVHD
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 60, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Giáo viên hướng dẫn', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1400 }, // Khoảng trống ký tên
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 300 },
                children: [
                  new TextRun({ text: 'TS. Trần Ngô Như Khánh', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
                ],
              }),
            ],
          }),
          // Cột phải: Sinh viên thực hiện
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 60, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Sinh viên thực hiện', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 900 }, // Khoảng trống ký tên
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 20 },
                children: [
                  new TextRun({ text: 'Nguyễn Đức Nguyên', font: FONT_FAMILY, size: 24, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 20 },
                children: [
                  new TextRun({ text: 'Mai Xuân Trưởng', font: FONT_FAMILY, size: 24, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 300 },
                children: [
                  new TextRun({ text: 'Nguyễn Ngọc Tuệ Quang', font: FONT_FAMILY, size: 24, bold: true, color: COLOR_BLACK }),
                ],
              }),
            ],
          }),
        ],
      }),
      // Hàng 2: BCN Khoa & Tổ trưởng bộ môn
      new TableRow({
        cantSplit: true,
        children: [
          // Cột trái: BCN Khoa
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 120, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Ban Chủ nhiệm Khoa', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1200 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: COLOR_BLACK }),
                ],
              }),
            ],
          }),
          // Cột phải: Tổ trưởng bộ môn
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 120, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Tổ trưởng bộ môn', font: FONT_FAMILY, size: 26, bold: true, color: COLOR_BLACK }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1200 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: COLOR_BLACK }),
                ],
              }),
            ],
          }),
        ],
      }),
    ],
  });

  elements.push(signTable);
  return elements;
}

async function generateCompleteDeCuong() {
  console.log('[1/4] Reading Faculty Logo...');
  const logoPath = path.resolve('../../docs/logo_fit_dlu.png');
  const logoBuffer = fs.readFileSync(logoPath);

  console.log('[2/4] Assembling 4 parts of De Cuong document...');
  // Part 1: Bìa chính (M01 full A4 có Logo Khoa)
  const part1Cover = buildCoverPage(logoBuffer);

  // Part 2: Trang Nhận xét của GVHD (M02 full A4 với 22 dòng chấm)
  const part2Remarks = buildSupervisorRemarksPage();

  // Part 3: Trang Lời cảm ơn
  const part3Ack = buildAcknowledgementsPage();

  // Part 4: Nội dung Đề cương chi tiết
  const part4Outline = buildOutlineContent();

  const doc = new Document({
    title: 'Đề cương Đồ án Tốt nghiệp - Mầm Xanh LMS 2.0',
    description: 'Đề cương Đồ án Tốt nghiệp CNTT - Trường Đại học Đà Lạt - 2026',
    creator: 'Nguyễn Đức Nguyên, Mai Xuân Trưởng, Nguyễn Ngọc Tuệ Quang - CTK46A',
    sections: [
      {
        properties: {
          type: SectionType.NEXT_PAGE,
          page: PAGE_PROPS.page,
        },
        children: [
          part1Cover,
          new Paragraph({ children: [new PageBreak()] }),
          part2Remarks,
          new Paragraph({ children: [new PageBreak()] }),
          ...part3Ack,
          new Paragraph({ children: [new PageBreak()] }),
          ...part4Outline,
        ],
      },
    ],
  });

  console.log('[3/4] Packing DOCX...');
  const buffer = await Packer.toBuffer(doc);

  const outDocxPath = path.resolve('../../docs/DeCuong_DoAnTotNghiep_2026_HoanChinh.docx');
  fs.writeFileSync(outDocxPath, buffer);
  console.log(`[SUCCESS] Saved to ${outDocxPath}`);

  // Copy to CD_DVD_SUBMISSION/THESIS and CD_DVD_SUBMISSION/
  const cdThesisDir = path.resolve('../../CD_DVD_SUBMISSION/THESIS');
  if (fs.existsSync(cdThesisDir)) {
    fs.copyFileSync(outDocxPath, path.join(cdThesisDir, 'DeCuong_DoAnTotNghiep_2026_HoanChinh.docx'));
    console.log(`[SUCCESS] Synchronized to CD_DVD_SUBMISSION/THESIS/DeCuong_DoAnTotNghiep_2026_HoanChinh.docx`);
  }

  const cdRootDir = path.resolve('../../CD_DVD_SUBMISSION');
  if (fs.existsSync(cdRootDir)) {
    fs.copyFileSync(outDocxPath, path.join(cdRootDir, 'DeCuong_DoAnTotNghiep_2026_HoanChinh.docx'));
    console.log(`[SUCCESS] Synchronized to CD_DVD_SUBMISSION/DeCuong_DoAnTotNghiep_2026_HoanChinh.docx`);
  }

  const stats = fs.statSync(outDocxPath);
  console.log(`  File size: ${(stats.size / 1024).toFixed(2)} KB (${stats.size} bytes)`);
}

generateCompleteDeCuong().catch(err => {
  console.error('[FATAL ERROR]:', err);
  process.exit(1);
});
