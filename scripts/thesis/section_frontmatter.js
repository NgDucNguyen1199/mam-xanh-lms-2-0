import {
  Paragraph,
  TextRun,
  Table,
  TableRow,
  TableCell,
  WidthType,
  AlignmentType,
  BorderStyle,
  ShadingType,
  VerticalAlign,
  HeightRule,
  ImageRun,
  TableLayoutType,
} from 'docx';
import fs from 'fs';
import path from 'path';
import {
  FONT_FAMILY,
  COLOR_PRIMARY,
  COLOR_SECONDARY,
  COLOR_TEXT,
  COLOR_MUTED,
  p,
  pRuns,
  h1,
  bullet,
  createAcademicTable,
  tocLeaderLine,
  pageBreak,
} from './styles.js';
import { THESIS_META } from './metadata.js';

/** Helper to create M01 Cover Table (Single official Main Cover with double border matching exact A4 print area) */
function createCoverTable() {
  const borderSpec = {
    style: BorderStyle.DOUBLE,
    size: 24, // Double black decorative border
    color: '000000',
  };
  const borderNone = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
    insideHorizontal: borderNone,
    insideVertical: borderNone,
  };

  // Read logo if available
  let logoParagraph = null;
  const logoPath = path.resolve('../../docs/logo_fit_dlu.png');
  if (fs.existsSync(logoPath)) {
    const logoBuf = fs.readFileSync(logoPath);
    logoParagraph = new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 360, after: 560 },
      children: [
        new ImageRun({
          data: logoBuf,
          transformation: { width: 100, height: 100 },
        }),
      ],
    });
  }

  // Borderless 2-column sub-table for pixel-perfect vertical axis alignment of GVHD & SV
  const infoTable = new Table({
    width: { size: 8227, type: WidthType.DXA },
    layout: TableLayoutType.FIXED,
    columnWidths: [3780, 4447],
    borders: {
      top: borderNone,
      bottom: borderNone,
      left: borderNone,
      right: borderNone,
      insideHorizontal: borderNone,
      insideVertical: borderNone,
    },
    rows: [
      new TableRow({
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 3780, type: WidthType.DXA },
            margins: { top: 40, bottom: 40, left: 40, right: 60 },
            children: [
              new Paragraph({
                alignment: AlignmentType.RIGHT,
                spacing: { before: 0, after: 0 },
                children: [
                  new TextRun({ text: 'Giảng viên hướng dẫn:', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
          new TableCell({
            width: { size: 4447, type: WidthType.DXA },
            margins: { top: 40, bottom: 40, left: 60, right: 40 },
            children: [
              new Paragraph({
                alignment: AlignmentType.LEFT,
                spacing: { before: 0, after: 0 },
                children: [
                  new TextRun({ text: `${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name}`, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
        ],
      }),
      new TableRow({
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 3780, type: WidthType.DXA },
            margins: { top: 60, bottom: 40, left: 40, right: 60 },
            children: [
              new Paragraph({
                alignment: AlignmentType.RIGHT,
                spacing: { before: 0, after: 0 },
                children: [
                  new TextRun({ text: 'Sinh viên thực hiện:', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
          new TableCell({
            width: { size: 4447, type: WidthType.DXA },
            margins: { top: 60, bottom: 40, left: 60, right: 40 },
            children: [
              new Paragraph({
                alignment: AlignmentType.LEFT,
                spacing: { before: 0, after: 0 },
                children: [
                  new TextRun({ text: `${THESIS_META.students[0].id} – ${THESIS_META.students[0].name}`, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
        ],
      }),
      ...THESIS_META.students.slice(1).map(s => new TableRow({
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 3780, type: WidthType.DXA },
            margins: { top: 40, bottom: 40, left: 40, right: 60 },
            children: [new Paragraph({ children: [] })],
          }),
          new TableCell({
            width: { size: 4447, type: WidthType.DXA },
            margins: { top: 40, bottom: 40, left: 60, right: 40 },
            children: [
              new Paragraph({
                alignment: AlignmentType.LEFT,
                spacing: { before: 0, after: 0 },
                children: [
                  new TextRun({ text: `${s.id} – ${s.name}`, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
        ],
      })),
    ],
  });

  const coverContent = [
    // 1. Top university info (13pt uppercase; faculty bold)
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 80, after: 40 },
      children: [
        new TextRun({
          text: THESIS_META.university,
          font: FONT_FAMILY,
          size: 26, // 13pt
          color: '000000',
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 80 },
      children: [
        new TextRun({
          text: THESIS_META.faculty,
          font: FONT_FAMILY,
          size: 26, // 13pt
          bold: true,
          color: '000000',
        }),
      ],
    }),

    // 2. Logo Khoa Công nghệ Thông tin
    ...(logoParagraph ? [logoParagraph] : []),

    // 3. Big Title (20pt, bold, uppercase)
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 200, after: 480 },
      children: [
        new TextRun({
          text: THESIS_META.reportType,
          font: FONT_FAMILY,
          size: 40, // 20pt
          bold: true,
          color: '000000',
        }),
      ],
    }),

    // 4. Topic title (15pt, bold, uppercase, line 400)
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 120, after: 1400, line: 400, lineRule: 'auto' },
      children: [
        new TextRun({
          text: `ĐỀ TÀI: ${THESIS_META.title}`,
          font: FONT_FAMILY,
          size: 30, // 15pt
          bold: true,
          color: '000000',
        }),
      ],
    }),

    // 5. Author & Supervisor info block
    infoTable,

    // Spacer pushing bottom date close to bottom inside frame
    new Paragraph({
      spacing: { before: 0, after: 2800 },
      children: [],
    }),

    // 6. Sát đáy khung bìa: Đà Lạt, tháng 11 năm 2026 (13pt, in nghiêng, căn giữa)
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 60 },
      children: [
        new TextRun({
          text: 'Đà Lạt, tháng 11 năm 2026',
          font: FONT_FAMILY,
          size: 26, // 13pt
          italic: true,
          color: '000000',
        }),
      ],
    }),
  ];

  return new Table({
    width: { size: 8787, type: WidthType.DXA },
    layout: TableLayoutType.FIXED,
    columnWidths: [8787],
    borders,
    rows: [
      new TableRow({
        height: { value: 13600, rule: HeightRule.EXACT },
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 8787, type: WidthType.DXA },
            margins: { top: 240, bottom: 240, left: 280, right: 280 },
            verticalAlign: VerticalAlign.TOP,
            children: coverContent,
          }),
        ],
      }),
    ],
  });
}

/** Create decorative cover page (Mẫu M01) - Single official Main Cover (No extra pageBreak or sub-cover) */
export function buildCoverPage() {
  const mainCover = createCoverTable();
  // Tiny 1pt paragraph so Section 1 terminates cleanly on page 1 before SectionType.NEXT_PAGE
  const sectionEndSpacer = new Paragraph({
    spacing: { before: 0, after: 0, line: 20, lineRule: 'exact' },
    children: [],
  });
  return [mainCover, sectionEndSpacer];
}

/** Supervisor Evaluation Page (Mẫu M02) - Standard DLU IT Faculty Blank Remarks Form matching exact template */
export function buildSupervisorEvaluationPage() {
  const borderSpec = {
    style: BorderStyle.SINGLE,
    size: 8, // 1pt solid black border
    color: '000000',
  };
  const borderNone = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };

  const borders = {
    top: borderSpec,
    bottom: borderSpec,
    left: borderSpec,
    right: borderSpec,
  };

  // Full width dot line inside 8227 DXA usable cell width (Times New Roman 13pt)
  const fullDotLine = '.'.repeat(126);
  const cellChildren = [
    // Line 1: Centered title inside frame
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 60, after: 220 },
      children: [
        new TextRun({
          text: 'NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN',
          font: FONT_FAMILY,
          size: 26, // 13pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
  ];

  // Exactly 20 spacious dotted lines spanning full width
  for (let i = 0; i < 20; i++) {
    cellChildren.push(
      new Paragraph({
        spacing: { before: 0, after: 40, line: 430, lineRule: 'auto' },
        children: [
          new TextRun({
            text: fullDotLine,
            font: FONT_FAMILY,
            size: 26, // 13pt
            color: '000000',
          }),
        ],
      })
    );
  }

  // Khối chữ ký ở góc dưới bên phải khung bằng bảng không viền cố định chiều rộng
  const signTableM02 = new Table({
    width: { size: 8227, type: WidthType.DXA },
    layout: TableLayoutType.FIXED,
    columnWidths: [3700, 4527],
    borders: {
      top: borderNone,
      bottom: borderNone,
      left: borderNone,
      right: borderNone,
      insideHorizontal: borderNone,
      insideVertical: borderNone,
    },
    rows: [
      new TableRow({
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 3700, type: WidthType.DXA },
            children: [new Paragraph({ children: [] })],
          }),
          new TableCell({
            width: { size: 4527, type: WidthType.DXA },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 260, after: 40 },
                children: [
                  new TextRun({
                    text: 'Đà Lạt, ngày ..... tháng ..... năm 2026',
                    font: FONT_FAMILY,
                    size: 26, // 13pt
                    color: '000000',
                  }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 40 },
                children: [
                  new TextRun({
                    text: 'Giảng viên hướng dẫn',
                    font: FONT_FAMILY,
                    size: 26, // 13pt
                    color: '000000',
                  }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 200 },
                children: [
                  new TextRun({
                    text: '[Ký tên và ghi rõ họ tên]',
                    font: FONT_FAMILY,
                    size: 26, // 13pt
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

  cellChildren.push(signTableM02);

  const m02Table = new Table({
    width: { size: 8787, type: WidthType.DXA },
    layout: TableLayoutType.FIXED,
    columnWidths: [8787],
    borders,
    rows: [
      new TableRow({
        height: { value: 13600, rule: HeightRule.EXACT },
        cantSplit: true,
        children: [
          new TableCell({
            width: { size: 8787, type: WidthType.DXA },
            margins: { top: 240, bottom: 240, left: 280, right: 280 },
            verticalAlign: VerticalAlign.TOP,
            children: cellChildren,
          }),
        ],
      }),
    ],
  });

  const m02EndSpacer = new Paragraph({
    spacing: { before: 0, after: 0, line: 20, lineRule: 'exact' },
    children: [],
  });
  return [m02Table, m02EndSpacer];
}

/** Acknowledgements Page */
export function buildAcknowledgementsPage() {
  return [
    h1('LỜI CẢM ƠN', 'front_ack', { pageBreakBefore: true }),
    p('Để hoàn thành Đồ án Tốt nghiệp này, nhóm chúng em xin gửi lời cảm ơn chân thành và sâu sắc nhất đến Ban Giám hiệu Trường Đại học Đà Lạt, Ban Chủ nhiệm Khoa Công nghệ Thông tin cùng toàn thể quý Thầy, Cô giáo trong Khoa đã tận tình giảng dạy, truyền đạt những tri thức nền tảng và chuyên sâu quý báu về khoa học máy tính và kỹ thuật phần mềm trong suốt bốn năm đại học vừa qua.'),
    p(`Đặc biệt, nhóm chúng em xin bày tỏ lòng biết ơn sâu sắc nhất tới Thầy ${THESIS_META.supervisor.title} ${THESIS_META.supervisor.name} – Cán bộ hướng dẫn khoa học của đề tài. Thầy đã dành nhiều thời gian quý báu định hướng kiến trúc hệ thống, gợi mở những bài toán thực tiễn cốt lõi về công nghệ giáo dục (EdTech), truyền cảm hứng nghiên cứu và nghiêm khắc chỉ dẫn từng chi tiết học thuật, giúp chúng em không chỉ hoàn thiện phần mềm mà còn trưởng thành về tư duy nghiên cứu độc lập.`),
    p('Nhóm nghiên cứu cũng xin chân thành cảm ơn các kỹ sư Chi cục Trồng trọt & Bảo vệ Thực vật tỉnh Lâm Đồng, Ban quản trị Hợp tác xã Nông nghiệp Công nghệ cao Lạc Dương, Đơn Dương và Đà Lạt đã nhiệt tình chia sẻ dữ liệu thực nghiệm, góp ý về quy trình thực tế đồng ruộng và tham gia thử nghiệm đối chiếu hệ thống Mầm Xanh LMS 2.0.'),
    p('Cuối cùng, chúng con xin gửi lời tri ân sâu nặng nhất tới cha mẹ, gia đình và bạn bè đã luôn là điểm tựa tinh thần vững chắc, động viên và tạo mọi điều kiện tốt nhất cho chúng con trong suốt quá trình học tập và hoàn thành đồ án.'),
    p('Mặc dù nhóm đã nỗ lực hết mình với tinh thần trách nhiệm cao nhất, song do giới hạn về thời gian và kinh nghiệm thực tiễn, đề tài khó tránh khỏi những thiếu sót nhất định. Nhóm chúng em rất mong nhận được những ý kiến đóng góp, chỉ bảo quý báu từ quý Thầy Cô trong Hội đồng để sản phẩm tiếp tục được hoàn thiện và phát triển hơn nữa trong tương lai.'),
    p('Chúng em xin trân trọng cảm ơn!', { bold: true }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 180, after: 40 },
      children: [
        new TextRun({
          text: THESIS_META.dateFull,
          font: FONT_FAMILY,
          size: 26,
          italic: true,
        }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 40, after: 100 },
      children: [
        new TextRun({
          text: 'NHÓM SINH VIÊN THỰC HIỆN       ',
          font: FONT_FAMILY,
          size: 26,
          bold: true,
          color: COLOR_PRIMARY,
        }),
      ],
    }),
    ...THESIS_META.students.map(s => new Paragraph({
      alignment: AlignmentType.RIGHT,
      spacing: { before: 20, after: 20 },
      children: [
        new TextRun({
          text: `${s.name}       `,
          font: FONT_FAMILY,
          size: 26,
          color: COLOR_TEXT,
        }),
      ],
    })),
    pageBreak(),
  ];
}

/** Project Outline Page (Mẫu M03) */
export function buildProjectOutlinePage() {
  const elements = [];

  // Header đầu trang đề cương
  elements.push(
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 60, after: 30 },
      children: [
        new TextRun({ text: THESIS_META.university, font: FONT_FAMILY, size: 26, color: '000000' }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 40 },
      children: [
        new TextRun({ text: THESIS_META.faculty, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
      ],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { before: 0, after: 180 },
      children: [
        new TextRun({ text: '---oOo---', font: FONT_FAMILY, size: 24, color: '000000' }),
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
          color: '000000',
        }),
      ],
    })
  );

  // Tên đề tài
  elements.push(
    p(`Tên đề tài: ${THESIS_META.title} (${THESIS_META.titleShort})`, {
      indent: 0,
      bold: true,
      after: 120,
    })
  );

  // Bảng thông tin sinh viên thực hiện
  elements.push(p('Sinh viên thực hiện:', { indent: 0, bold: true, after: 60 }));
  const studentHeaders = ['STT', 'Họ và tên SV', 'MSSV', 'Lớp', 'Email liên hệ'];
  const studentRows = [
    ['1', 'Nguyễn Đức Nguyên (Trưởng nhóm)', '2212429', 'CTK46A', '2212429@dlu.edu.vn'],
    ['2', 'Mai Xuân Trưởng', '2212484', 'CTK46A', '2212484@dlu.edu.vn'],
    ['3', 'Nguyễn Ngọc Tuệ Quang', '2212447', 'CTK46A', '2212447@dlu.edu.vn'],
  ];
  const studentWidths = [8, 32, 16, 14, 30];
  elements.push(...createAcademicTable(studentHeaders, studentRows, studentWidths));

  // Giảng viên hướng dẫn
  elements.push(
    p(`Giảng viên hướng dẫn: ${THESIS_META.supervisor.titleShort} ${THESIS_META.supervisor.name} (${THESIS_META.department} – ${THESIS_META.faculty})`, {
      indent: 0,
      bold: true,
      after: 160,
    })
  );

  // I. Mục tiêu đề tài
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'I. Mục tiêu đề tài:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
    p('1. Mục tiêu tổng quát: Triển khai, tối ưu hóa và phát triển các phân hệ chuyên sâu trên nền tảng Moodle 4.5 LTS thành môi trường e-Learning tập trung toàn diện cho công tác tập huấn kỹ thuật nông nghiệp công nghệ cao (Mầm Xanh LMS 2.0). Hệ thống giải quyết triệt để rào cản địa lý và tính thời vụ, hỗ trợ cán bộ kỹ thuật chuẩn hóa quy trình sư phạm, theo dõi tiến độ đào tạo theo thời gian thực; đồng thời giúp nông hộ và chủ trang trại học tập linh hoạt trên thiết bị di động.'),
    p('2. Mục tiêu chức năng quản lý đào tạo chuẩn học thuật: Xây dựng hệ thống hoàn chỉnh tương đương chuẩn mực của các hệ thống LMS đại học chính quy (như lms.dlu.edu.vn): phân quyền RBAC đa vai trò (Quản trị viên, Cán bộ kỹ thuật, Giảng viên, Nông hộ); quản lý tiến độ học tập (Activity Completion); ngân hàng đề thi trắc nghiệm (Quiz); bài tập thực hành giao việc (Assignment); diễn đàn giải đáp kỹ thuật (Forum); và sổ điểm điện tử Gradebook minh bạch với công thức tính điểm trọng số.'),
    p('3. Mục tiêu đặc thù phục vụ nông nghiệp công nghệ cao: Số hóa hoàn chỉnh 08 khóa học kỹ thuật công nghệ cao chủ lực tại Lâm Đồng (Canh tác & dinh dưỡng tự động, Sơ chế & bảo quản chuỗi lạnh, Vận hành nhà màng IoT, Thủy canh hồi lưu NFT, Sầu riêng xuất khẩu, Phân hữu cơ vi sinh & Trichoderma, Dâu tây & Hoa cúc CNC Đà Lạt, Dâu tây & Hoa cúc CNC Đơn Dương); phát triển bộ 5 module/plugin Moodle tùy biến gồm: thực hành thực địa GPS kèm ma trận Rubric (mod_fieldpractice), chứng chỉ số mã hóa HMAC-SHA256 & QR (local_mamxanhcert), nhật ký canh tác điện tử VietGAP (local_agriops), hồ sơ học thuật & bảng điểm tổng kết (local_mamxanhapp), và giao diện công thái học tối giản Minimalist Off-White Botanical (agritrain_child).'),
    p('4. Mục tiêu chất lượng và vận hành: Giao diện song ngữ Việt - Anh linh hoạt, responsive 100% trên thiết bị di động, an toàn thông tin theo chuẩn OWASP Top 10, đóng gói tự động hóa 5 container vi dịch vụ trên nền tảng Docker Turbo và kiểm thử hồi quy Pass 100% trên 66 URL chức năng.')
  );

  // II. Nội dung đề tài & Phân công nhiệm vụ
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'II. Nội dung đề tài và Phân công nhiệm vụ:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
    p('Đề tài được triển khai toàn diện qua 6 giai đoạn kỹ thuật với sự phối hợp chặt chẽ giữa các thành viên:'),
    bullet('Giai đoạn 1 – Khảo sát và phân tích yêu cầu: Khảo sát thực địa các mô hình nông nghiệp công nghệ cao tại Đà Lạt, Lạc Dương và Đơn Dương; xác định các rào cản tiếp cận e-Learning của người nông dân; xây dựng tài liệu đặc tả yêu cầu phần mềm (SRS), ma trận truy vết và sơ đồ ca sử dụng Use Case.'),
    bullet('Giai đoạn 2 – Thiết kế kiến trúc tổng thể & CSDL: Thiết kế kiến trúc 4 tầng (Client – Gateway Proxy – Application – Database & Cache); chuẩn hóa Database Schema gồm 508 bảng dữ liệu (502 bảng lõi Moodle và 6 bảng tùy biến: local_mamxanhcert, fieldpractice, fieldpractice_sub, local_farmerprofile, local_agriops_batch, local_agriops_log); thiết kế sơ đồ thực thể liên kết ERD và lưu đồ nghiệp vụ Swimlane.'),
    bullet('Giai đoạn 3 – Triển khai hạ tầng ảo hóa Docker Turbo & Moodle 4.5 LTS: Cấu hình hạ tầng 5 dịch vụ vi dịch vụ với Docker Compose (Caddy 2 Proxy, PHP 8.3 App, MariaDB 10.11, Redis 7 Cache, Moodle Cron Worker); tối ưu hóa OPcache JIT, bộ đệm Redis và cấu hình hệ thống sao lưu dự phòng tự động.'),
    bullet('Giai đoạn 4 – Số hóa 8 chương trình tập huấn công nghệ cao: Xây dựng đồng bộ 8 khóa học kỹ thuật theo cấu trúc sư phạm chuẩn mực (Thông tin chung -> 4 Chuyên đề kỹ thuật -> Kiểm tra & Đánh giá); tích hợp gói bài giảng SCORM 1.2, hoạt động tương tác H5P, video thực địa và tài liệu hướng dẫn kỹ thuật chính thống từ Bộ NN&PTNT.'),
    bullet('Giai đoạn 5 – Lập trình 5 phân hệ plugin chuyên sâu: Hiện thực hóa mod_fieldpractice (điểm danh QR xoay vòng kèm tọa độ GPS, chấm điểm Rubric 4 tiêu chí 100 điểm); local_mamxanhcert (ký số HMAC-SHA256, tra cứu công khai QR); local_agriops (nhật ký canh tác VietGAP TCVN 11892-1:2017); local_mamxanhapp (bảng điểm tổng kết Transcript, công cụ nhân bản khóa học đồng bộ); và theme agritrain_child (công thái học Minimalist Off-White).'),
    bullet('Giai đoạn 6 – Tự động hóa kiểm thử E2E & Đánh giá công thái học: Xây dựng kịch bản kiểm thử tự động với Puppeteer trên toàn bộ 66 URL chức năng; kiểm tra phân quyền bảo mật RBAC; đánh giá độ tiện dụng theo thang đo công thái học tiêu chuẩn SUS (System Usability Scale đạt 86.5/100 – Hạng A+).'),
    p('Bảng phân công nhiệm vụ chi tiết các thành viên trong nhóm nghiên cứu:', { indent: 0, bold: true, before: 120, after: 60 })
  );

  const taskHeaders = ['STT', 'Họ và tên SV', 'MSSV', 'Nhiệm vụ nghiên cứu và phát triển được phân công', 'Tỷ lệ', 'Kết quả'];
  const taskRows = [
    [
      '1',
      'Nguyễn Đức Nguyên\n(Trưởng nhóm)',
      '2212429',
      '- Khảo sát yêu cầu, thiết kế kiến trúc tổng thể 4 tầng và Database Schema (508 bảng).\n- Thiết lập môi trường Docker Turbo 5 vi dịch vụ (Caddy 2, PHP 8.3, MariaDB, Redis, Cron).\n- Lập trình phân hệ plugin: local_mamxanhcert (mã hóa HMAC-SHA256 & QR), local_mamxanhapp.\n- Xây dựng bộ công cụ dòng lệnh PHP CLI phục vụ bảo trì tự động và đồng bộ CSDL.',
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
  elements.push(...createAcademicTable(taskHeaders, taskRows, taskWidths, 'Bảng 0.1', 'Phân công nhiệm vụ các thành viên trong nhóm thực hiện đề tài', 'tbl_0_1'));

  // III. Phần mềm và công cụ sử dụng
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'III. Phần mềm và công cụ sử dụng:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
    p('Đề tài ưu tiên lựa chọn các công nghệ mã nguồn mở hiện đại, có tính ổn định cao, được hỗ trợ lâu dài (LTS) và phù hợp với tiêu chuẩn chuyển đổi số ngành nông nghiệp:')
  );

  const toolHeaders = ['STT', 'Nhóm thành phần', 'Phần mềm & Công cụ sử dụng', 'Mục đích áp dụng trong đồ án'];
  const toolRows = [
    ['1', 'Nền tảng lõi LMS', 'Moodle LMS 4.5.15+ LTS (PHP 8.3, Apache 2.4)', 'Vận hành toàn bộ nghiệp vụ quản lý đào tạo, phân quyền RBAC và quản lý khóa học.'],
    ['2', 'Cơ sở dữ liệu & Cache', 'MariaDB 10.11 LTS & Redis 7.2 In-Memory Cache', 'Lưu trữ 508 bảng dữ liệu (502 core + 6 tùy biến), đệm phiên làm việc và tăng tốc truy vấn.'],
    ['3', 'Cổng kết nối bảo mật', 'Caddy 2 Reverse Proxy', 'Tự động hóa chứng chỉ số SSL/TLS, nén dữ liệu gzip/zstd, định tuyến truy cập HTTPS bảo mật.'],
    ['4', 'Ảo hóa hạ tầng', 'Docker Engine v27.2 & Docker Compose v2.29', 'Đóng gói 5 dịch vụ vi dịch vụ thành môi trường Docker Turbo triển khai tự động chỉ với 1 lệnh.'],
    ['5', 'Chuẩn học liệu số', 'SCORM 1.2 & H5P Interactive Content', 'Xây dựng bài giảng tương tác đa phương tiện, video tương tác và theo dõi tiến độ chuẩn quốc tế.'],
    ['6', 'Giao diện công thái học', 'HTML5, SCSS/CSS3, JavaScript (Bootstrap 5)', 'Phát triển theme agritrain_child chuẩn Minimalist Off-White Botanical tối ưu cho nông hộ.'],
    ['7', 'Bảo mật & Mã hóa', 'OpenSSL, Thuật toán HMAC-SHA256 & QR Code', 'Sinh chữ ký số cho chứng chỉ điện tử chống làm giả và hỗ trợ quét QR tra cứu công khai.'],
    ['8', 'Kiểm thử tự động hóa', 'Node.js v24 & Puppeteer v24 Testing Framework', 'Tự động hóa kiểm thử luồng chức năng E2E trên toàn bộ 66 URL của hệ thống.'],
    ['9', 'Quản lý mã nguồn', 'Git & GitHub Version Control System', 'Quản lý phiên bản mã nguồn phân tán, theo dõi commit và phối hợp nhóm đa thành viên.'],
  ];
  const toolWidths = [6, 22, 32, 40];
  elements.push(...createAcademicTable(toolHeaders, toolRows, toolWidths));

  // IV. Kế hoạch tiến độ thực hiện đề tài
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'IV. Kế hoạch tiến độ thực hiện đề tài:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
    p('Kế hoạch triển khai bám sát 11 mốc thời gian chuẩn do Khoa Công nghệ Thông tin – Trường Đại học Đà Lạt ban hành, bảo đảm hoàn thành đầy đủ các sản phẩm đầu ra theo đúng tiến độ:')
  );

  const planHeaders = ['STT', 'Nội dung công việc triển khai', 'Thời gian (2026)', 'Sản phẩm đầu ra'];
  const planRows = [
    ['1', 'Hoàn thiện đề cương chi tiết, nộp hệ thống LMS Khoa CNTT', '03/08 - 10/08', 'Bản Đề cương chi tiết hoàn chỉnh'],
    ['2', 'Nhận phản hồi từ GVHD, sửa đề cương, trình duyệt kế hoạch', '11/08 - 16/08', 'Kế hoạch triển khai được phê duyệt'],
    ['3', 'Khảo sát thực địa yêu cầu nông hộ, xác định phạm vi và tài liệu SRS', '17/08 - 30/08', 'Tài liệu SRS và sơ đồ Use Case'],
    ['4', 'Thiết kế kiến trúc 4 tầng, CSDL 508 bảng, dựng hạ tầng Docker Turbo', '31/08 - 13/09', 'Sơ đồ kiến trúc, Docker Compose'],
    ['5', 'Cấu hình Moodle lõi, phân quyền RBAC và số hóa 02 khóa học đầu tiên', '14/09 - 20/09', 'Báo cáo tiến độ 1 & Demo ban đầu'],
    ['6', 'Xây dựng 06 khóa học tiếp theo (SCORM, H5P), lập trình 5 plugin chuyên sâu', '21/09 - 11/10', 'Bản chức năng chính hoàn chỉnh'],
    ['7', 'Tích hợp sổ điểm Gradebook, báo cáo tiến độ, kiểm thử liên thông vai trò', '12/10 - 18/10', 'Báo cáo tiến độ 2'],
    ['8', 'Kiểm thử hồi quy tự động E2E Puppeteer (66 URLs), đo lường SUS, lấy phản hồi', '19/10 - 15/11', 'Bản demo hoàn thiện & Biên bản test'],
    ['9', 'Viết bản thảo Báo cáo Đồ án tốt nghiệp, chuẩn bị Slide thuyết trình', '16/11 - 22/11', 'Bản thảo Đồ án tốt nghiệp'],
    ['10', 'Sửa chữa, hoàn thiện tài liệu báo cáo theo góp ý của GVHD', '23/11 - 29/11', 'Quyển Báo cáo Đồ án hoàn chỉnh'],
    ['11', 'Đóng gói hồ sơ đĩa CD/DVD và bảo vệ đồ án tốt nghiệp trước Hội đồng', '30/11 - 06/12', 'Hồ sơ nộp đĩa & Bảo vệ thành công'],
  ];
  const planWidths = [6, 50, 18, 26];
  elements.push(...createAcademicTable(planHeaders, planRows, planWidths, 'Bảng 0.2', 'Kế hoạch tiến độ triển khai đồ án tốt nghiệp theo tuần', 'tbl_0_2'));

  // V. Dự kiến kết quả đạt được
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'V. Dự kiến kết quả đạt được:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    }),
    p('Đề tài cam kết hoàn thành đầy đủ 6 nhóm sản phẩm ứng dụng và học thuật chất lượng cao:'),
    bullet('(1) Hệ thống Mầm Xanh LMS 2.0 vận hành ổn định trên hạ tầng Docker Turbo 5 dịch vụ vi dịch vụ (Caddy 2, PHP 8.3, MariaDB 10.11, Redis 7, Cron), đáp ứng đầy đủ nghiệp vụ quản lý đào tạo chuẩn học thuật;'),
    bullet('(2) Bộ học liệu số hóa 08 chương trình tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao chủ lực của tỉnh Lâm Đồng theo chuẩn e-Learning đa phương tiện (SCORM 1.2, H5P, Quiz, Rubric thực hành);'),
    bullet('(3) Bộ 5 module/plugin Moodle tùy biến chuyên biệt: mod_fieldpractice (thực hành GPS & Rubric), local_mamxanhcert (chứng chỉ số HMAC-SHA256 & QR), local_agriops (nhật ký điện tử VietGAP), local_mamxanhapp (bảng điểm học thuật Transcript & nhân bản khóa học), và theme agritrain_child (công thái học Minimalist Off-White Botanical);'),
    bullet('(4) Bộ công cụ kiểm thử tự động hóa E2E hoàn chỉnh với Puppeteer kiểm tra toàn diện 66 URL chức năng với kết quả Pass 100%, kèm kết quả đánh giá công thái học SUS đạt 86.5/100 (Hạng A+ - Xuất sắc);'),
    bullet('(5) Bộ hồ sơ tài liệu học thuật đầy đủ và chỉn chu: Bản Đề cương đồ án tốt nghiệp hoàn chỉnh, Quyển Báo cáo Đồ án tốt nghiệp 62 trang A4 đạt chuẩn Khoa CNTT, và Bộ Slide thuyết trình 26 trang;'),
    bullet('(6) Đĩa CD/DVD nộp đề tài đóng gói chuẩn theo quy định Khoa CNTT Trường Đại học Đà Lạt (gồm báo cáo, slide, mã nguồn, CSDL sao lưu, hướng dẫn triển khai và nhãn đĩa Mẫu M04).')
  );

  // VI. Tài liệu tham khảo của đề cương
  elements.push(
    new Paragraph({
      alignment: AlignmentType.LEFT,
      spacing: { before: 200, after: 100 },
      children: [
        new TextRun({
          text: 'VI. Tài liệu tham khảo của đề cương:',
          font: FONT_FAMILY,
          size: 28, // 14pt
          bold: true,
          color: '000000',
        }),
      ],
    })
  );

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
          new TextRun({ text: r, font: FONT_FAMILY, size: 26, color: '000000' }),
        ],
      })
    );
  });

  // Khối chữ ký xác nhận 4 bên
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
          color: '000000',
        }),
      ],
    })
  );

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
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 60, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Giảng viên hướng dẫn', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1200 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 300 },
                children: [
                  new TextRun({ text: `${THESIS_META.supervisor.titleShort} ${THESIS_META.supervisor.name}`, font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
            ],
          }),
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 60, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Sinh viên thực hiện', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 800 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 20 },
                children: [
                  new TextRun({ text: THESIS_META.students[0].name, font: FONT_FAMILY, size: 24, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 20 },
                children: [
                  new TextRun({ text: THESIS_META.students[1].name, font: FONT_FAMILY, size: 24, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 300 },
                children: [
                  new TextRun({ text: THESIS_META.students[2].name, font: FONT_FAMILY, size: 24, bold: true, color: '000000' }),
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
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 120, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Ban Chủ nhiệm Khoa', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1200 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: '000000' }),
                ],
              }),
            ],
          }),
          new TableCell({
            width: { size: 50, type: WidthType.PERCENTAGE },
            margins: { top: 120, bottom: 60, left: 100, right: 100 },
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 30 },
                children: [
                  new TextRun({ text: 'Tổ trưởng bộ môn', font: FONT_FAMILY, size: 26, bold: true, color: '000000' }),
                ],
              }),
              new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 0, after: 1200 },
                children: [
                  new TextRun({ text: '(Ký và ghi rõ họ tên)', font: FONT_FAMILY, size: 22, italic: true, color: '000000' }),
                ],
              }),
            ],
          }),
        ],
      }),
    ],
  });

  elements.push(signTable);
  elements.push(pageBreak());
  return elements;
}

/** Table of Contents (Mục lục) with Word TOC + Clickable Hyperlinks */
export function buildTableOfContentsPage() {
  const tocEntries = [
    { title: 'LỜI CẢM ƠN', anchor: 'front_ack', page: 'ii', bold: true },
    { title: 'ĐỀ CƯƠNG THỰC HIỆN ĐỒ ÁN TỐT NGHIỆP', anchor: 'tbl_0_1', page: 'iii', bold: true },
    { title: 'TÓM TẮT ĐỒ ÁN', anchor: 'front_summary', page: 'vii', bold: true },
    { title: 'ABSTRACT', anchor: 'front_abstract', page: 'viii', bold: true },
    { title: 'DANH MỤC CÁC HÌNH VẼ', anchor: 'front_figures', page: 'ix', bold: true },
    { title: 'DANH MỤC CÁC BẢNG BIỂU', anchor: 'front_tables', page: 'xi', bold: true },
    { title: 'DANH MỤC CÁC TỪ VIẾT TẮT', anchor: 'front_abbrev', page: 'xii', bold: true },
    { title: 'MỞ ĐẦU', anchor: 'sec_modau', page: '1', bold: true },
    { title: '1. Lí do chọn đề tài', anchor: 'sec_intro_1', page: '1', indent: 1 },
    { title: '2. Mục đích nghiên cứu của đề tài', anchor: 'sec_intro_2', page: '2', indent: 1 },
    { title: '3. Đối tượng và phạm vi nghiên cứu', anchor: 'sec_intro_3', page: '3', indent: 1 },
    { title: '4. Phương pháp nghiên cứu', anchor: 'sec_intro_4', page: '3', indent: 1 },
    { title: '5. Ý nghĩa khoa học và giá trị thực tiễn', anchor: 'sec_intro_5', page: '4', indent: 1 },
    { title: '6. Bố cục của đồ án tốt nghiệp', anchor: 'sec_intro_6', page: '5', indent: 1 },
    { title: 'CHƯƠNG 1: TỔNG QUAN VỀ HỆ THỐNG E-LEARNING TRONG ĐÀO TẠO NÔNG NGHIỆP CÔNG NGHỆ CAO', anchor: 'sec_ch1', page: '6', bold: true },
    { title: '1.1. Bối cảnh chuyển đổi số nông nghiệp tại tỉnh Lâm Đồng và Đà Lạt', anchor: 'sec_1_1', page: '6', indent: 1, bold: true },
    { title: '1.2. Thực trạng công tác tập huấn kỹ thuật và rào cản tiếp cận của nông hộ', anchor: 'sec_1_2', page: '8', indent: 1, bold: true },
    { title: '1.3. Khảo sát các giải pháp e-Learning và nền tảng LMS hiện nay', anchor: 'sec_1_3', page: '10', indent: 1, bold: true },
    { title: '1.4. Những vấn đề tồn tại và thách thức cốt lõi đối với e-Learning nông nghiệp', anchor: 'sec_1_4', page: '13', indent: 1, bold: true },
    { title: '1.5. Mục tiêu, yêu cầu và phạm vi giải quyết của hệ thống Mầm Xanh LMS 2.0', anchor: 'sec_1_5', page: '15', indent: 1, bold: true },
    { title: 'CHƯƠNG 2: CƠ SỞ LÝ THUYẾT VÀ CÔNG NGHỆ ÁP DỤNG', anchor: 'sec_ch2', page: '17', bold: true },
    { title: '2.1. Nền tảng Moodle LMS 4.5 LTS và kiến trúc module hóa', anchor: 'sec_2_1', page: '17', indent: 1, bold: true },
    { title: '2.2. Các chuẩn dữ liệu e-Learning: SCORM 1.2 và H5P Interactive Content', anchor: 'sec_2_2', page: '20', indent: 1, bold: true },
    { title: '2.3. Phương pháp đánh giá năng lực thực hành với thang đo Rubric đa tiêu chí', anchor: 'sec_2_3', page: '23', indent: 1, bold: true },
    { title: '2.4. Cơ chế bảo mật và xác thực chứng chỉ số bằng mã hóa HMAC-SHA256 & QR', anchor: 'sec_2_4', page: '25', indent: 1, bold: true },
    { title: '2.5. Nguyên lý thiết kế giao diện công thái học Minimalist Off-White cho nông dân', anchor: 'sec_2_5', page: '27', indent: 1, bold: true },
    { title: '2.6. Kiến trúc ảo hóa và tối ưu hóa hiệu năng container với Docker Turbo', anchor: 'sec_2_6', page: '29', indent: 1, bold: true },
    { title: 'CHƯƠNG 3: PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG', anchor: 'sec_ch3', page: '32', bold: true },
    { title: '3.1. Phân tích yêu cầu hệ thống', anchor: 'sec_3_1', page: '32', indent: 1, bold: true },
    { title: '3.1.1. Yêu cầu chức năng theo từng nhóm tác nhân', anchor: 'sec_3_1_1', page: '32', indent: 2 },
    { title: '3.1.2. Yêu cầu phi chức năng về hiệu năng và bảo mật', anchor: 'sec_3_1_2', page: '35', indent: 2 },
    { title: '3.2. Thiết kế kiến trúc tổng thể 4 tầng của hệ thống Mầm Xanh LMS 2.0', anchor: 'sec_3_2', page: '36', indent: 1, bold: true },
    { title: '3.3. Biểu đồ ca sử dụng (Use Case) và mô hình hóa tác nhân', anchor: 'sec_3_3', page: '38', indent: 1, bold: true },
    { title: '3.4. Thiết kế quy trình nghiệp vụ tập huấn canh tác thông minh (Swimlane Flow)', anchor: 'sec_3_4', page: '40', indent: 1, bold: true },
    { title: '3.5. Thiết kế cơ sở dữ liệu và cấu trúc các bảng plugin tùy biến', anchor: 'sec_3_5', page: '42', indent: 1, bold: true },
    { title: '3.6. Thiết kế giao diện người dùng và trải nghiệm học tập', anchor: 'sec_3_6', page: '46', indent: 1, bold: true },
    { title: 'CHƯƠNG 4: XÂY DỰNG, TRIỂN KHAI VÀ THỰC NGHIỆM HỆ THỐNG', anchor: 'sec_ch4', page: '48', bold: true },
    { title: '4.1. Môi trường triển khai và cấu hình hạ tầng ảo hóa Docker Turbo', anchor: 'sec_4_1', page: '48', indent: 1, bold: true },
    { title: '4.2. Hiện thực hóa và chuẩn hóa 8 khóa học nông nghiệp công nghệ cao', anchor: 'sec_4_2', page: '50', indent: 1, bold: true },
    { title: '4.3. Chi tiết hiện thực hóa các module và plugin mở rộng cốt lõi', anchor: 'sec_4_3', page: '53', indent: 1, bold: true },
    { title: '4.3.1. Phân hệ chứng chỉ số và tra cứu công khai: local_mamxanhcert', anchor: 'sec_4_3_1', page: '53', indent: 2 },
    { title: '4.3.2. Phân hệ thực hành thực địa có tọa độ GPS: mod_fieldpractice', anchor: 'sec_4_3_2', page: '55', indent: 2 },
    { title: '4.3.3. Phân hệ nhật ký canh tác điện tử và truy xuất nguồn gốc: local_agriops', anchor: 'sec_4_3_3', page: '56', indent: 2 },
    { title: '4.3.4. Phân hệ bảng điểm học thuật và phiếu phản hồi Rubric: local_mamxanhapp', anchor: 'sec_4_3_4', page: '58', indent: 2 },
    { title: '4.3.5. Giao diện chuyên biệt Agritrain Boost Child Theme', anchor: 'sec_4_3_5', page: '59', indent: 2 },
    { title: '4.4. Bộ công cụ tự động hóa, kiểm định dữ liệu và bảo trì CLI', anchor: 'sec_4_4', page: '60', indent: 1, bold: true },
    { title: '4.5. Kiểm thử hệ thống và đánh giá kết quả thực nghiệm', anchor: 'sec_4_5', page: '61', indent: 1, bold: true },
    { title: '4.5.1. Kiểm thử tự động E2E với Puppeteer trên 66 URL chức năng', anchor: 'sec_4_5_1', page: '61', indent: 2 },
    { title: '4.5.2. Kiểm thử bảo mật phân quyền RBAC và an toàn dữ liệu', anchor: 'sec_4_5_2', page: '63', indent: 2 },
    { title: '4.5.3. Đánh giá hiệu năng và mức độ hài lòng của người dùng', anchor: 'sec_4_5_3', page: '64', indent: 2 },
    { title: 'KẾT LUẬN', anchor: 'sec_conclusion', page: '66', bold: true },
    { title: 'HƯỚNG PHÁT TRIỂN', anchor: 'sec_future', page: '68', bold: true },
    { title: 'DANH MỤC TÀI LIỆU THAM KHẢO', anchor: 'sec_references', page: '70', bold: true },
    { title: 'PHỤ LỤC', anchor: 'sec_appendix', page: '73', bold: true },
    { title: 'Phụ lục A: Hướng dẫn cài đặt và vận hành hệ thống bằng Docker Turbo', anchor: 'sec_app_a', page: '73', indent: 1 },
    { title: 'Phụ lục B: Quy trình 10 bước chuẩn hóa khóa học canh tác công nghệ cao', anchor: 'sec_app_b', page: '76', indent: 1 },
  ];

  return [
    h1('MỤC LỤC', 'front_toc'),
    ...tocEntries.map(e =>
      tocLeaderLine({
        text: e.title,
        page: e.page,
        anchor: e.anchor,
        indent: e.indent || 0,
        bold: !!e.bold,
      })
    ),
    pageBreak(),
  ];
}

/** List of Figures (Danh mục hình vẽ) with Clickable Hyperlinks */
export function buildListOfFiguresPage() {
  const figures = [
    { num: 'Hình 1.1', text: 'Nhà màng nông nghiệp công nghệ cao ứng dụng hệ thống tưới tự động tại Đà Lạt', anchor: 'fig_1_1', page: '7' },
    { num: 'Hình 1.2', text: 'Giao diện Cổng thông tin nông nghiệp công nghệ cao Mầm Xanh LMS dành cho khách', anchor: 'fig_1_2', page: '14' },
    { num: 'Hình 2.1', text: 'Cấu trúc module hóa các phân hệ chức năng mở rộng trên nền tảng Moodle 4.5', anchor: 'fig_2_1', page: '19' },
    { num: 'Hình 2.2', text: 'Gói bài giảng SCORM 1.2 mô phỏng kỹ thuật phối trộn phân bón hòa tan', anchor: 'fig_2_2', page: '21' },
    { num: 'Hình 2.3', text: 'Hoạt động tương tác H5P kiểm tra thao tác cắt tỉa cành theo chuẩn kỹ thuật', anchor: 'fig_2_3', page: '22' },
    { num: 'Hình 2.4', text: 'Cơ chế xác thực chữ ký số HMAC-SHA256 trên mã QR chứng chỉ điện tử', anchor: 'fig_2_4', page: '26' },
    { num: 'Hình 2.5', text: 'Cấu trúc sư phạm chuẩn hóa của một khóa học canh tác (Thông tin chung + 4 Chuyên đề + Kiểm tra & Đánh giá)', anchor: 'fig_2_5', page: '28' },
    { num: 'Hình 2.6', text: 'Mô hình kiến trúc ảo hóa vi dịch vụ 5 container Docker Turbo cho hệ thống Mầm Xanh LMS 2.0 (Sơ đồ D1)', anchor: 'fig_2_6', page: '30' },
    { num: 'Hình 3.1', text: 'Kiến trúc phân tầng 4 lớp của hệ thống Mầm Xanh LMS 2.0 (Sơ đồ D2)', anchor: 'fig_3_1', page: '37' },
    { num: 'Hình 3.2', text: 'Biểu đồ ca sử dụng (Use Case Diagram) tổng thể phân rã theo 4 nhóm tác nhân (Sơ đồ D3)', anchor: 'fig_3_2', page: '39' },
    { num: 'Hình 3.3', text: 'Quy trình nghiệp vụ tập huấn kỹ thuật và nghiệm thu thực địa liên vai trò (Sơ đồ D5)', anchor: 'fig_3_3', page: '41' },
    { num: 'Hình 3.4', text: 'Biểu đồ quan hệ thực thể (ERD) giữa Moodle Core và các bảng plugin tùy biến (Sơ đồ D4)', anchor: 'fig_3_4', page: '44' },
    { num: 'Hình 3.5', text: 'Quy trình ký số và tra cứu công khai chứng nhận điện tử (Sơ đồ D6)', anchor: 'fig_3_5', page: '45' },
    { num: 'Hình 3.6', text: 'Quy trình nhân bản nhanh khóa học cấp địa phương với Course Cloner (Sơ đồ D7)', anchor: 'fig_3_6', page: '46' },
    { num: 'Hình 4.1', text: 'Giao diện Bảng điều khiển học tập (Dashboard) của nông hộ với thanh điều hướng đơn', anchor: 'fig_4_1', page: '51' },
    { num: 'Hình 4.2', text: 'Giao diện khóa học với thanh Navbar đơn và đã ẩn tab Danh sách thành viên', anchor: 'fig_4_2', page: '52' },
    { num: 'Hình 4.3', text: 'Mục Kiểm tra & Đánh giá đã được chuẩn hóa UTF-8 tiếng Việt hoàn chỉnh', anchor: 'fig_4_3', page: '52' },
    { num: 'Hình 4.4', text: 'Giao diện danh sách 8 khóa học nông nghiệp công nghệ cao đã đồng bộ trên trang Các khóa học của tôi', anchor: 'fig_4_4', page: '53' },
    { num: 'Hình 4.5', text: 'Mẫu chứng chỉ số điện tử cấp cho học viên hoàn thành khóa tập huấn', anchor: 'fig_4_5', page: '54' },
    { num: 'Hình 4.6', text: 'Trang kiểm tra tính hợp lệ của chứng chỉ số qua mã định danh bảo mật QR', anchor: 'fig_4_6', page: '55' },
    { num: 'Hình 4.7', text: 'Giao diện trích xuất báo cáo thực hành nhật ký đồng ruộng VietGAP phục vụ nộp bài thực địa', anchor: 'fig_4_7', page: '56' },
    { num: 'Hình 4.8', text: 'Phiếu kết quả chấm điểm thực hành hiện trường theo ma trận Rubric 4 tiêu chí của Cán bộ kỹ thuật', anchor: 'fig_4_8', page: '56' },
    { num: 'Hình 4.9', text: 'Giao diện sổ tay nhật ký canh tác điện tử VietGAP dành cho nông hộ', anchor: 'fig_4_9', page: '57' },
    { num: 'Hình 4.10', text: 'Hộ chiếu nông sản điện tử công khai phục vụ truy xuất nguồn gốc lô hàng', anchor: 'fig_4_10', page: '58' },
    { num: 'Hình 4.11', text: 'Bảng điểm học thuật chính thức và biểu mẫu phản hồi chi tiết tiêu chí Rubric', anchor: 'fig_4_11', page: '59' },
    { num: 'Hình 4.12', text: 'Bảng điều khiển nghiệp vụ quản lý tiến độ và đánh giá của Cán bộ kỹ thuật', anchor: 'fig_4_12', page: '60' },
    { num: 'Hình 4.13', text: 'Sơ đồ luồng kiểm thử tự động End-to-End với Puppeteer trên 66 URL (Sơ đồ D9)', anchor: 'fig_4_13', page: '62' },
    { num: 'Hình 4.14', text: 'Hệ thống bảo vệ phân quyền RBAC ngăn chặn truy cập trái phép vào trang nhập điểm', anchor: 'fig_4_14', page: '64' },
    { num: 'Hình 4.15', text: 'Bảng kiểm toán hạ tầng kỹ thuật và trạng thái phân quyền hệ thống thời gian thực', anchor: 'fig_4_15', page: '65' },
  ];

  return [
    h1('DANH MỤC CÁC HÌNH VẼ', 'front_figures'),
    ...figures.map(f =>
      tocLeaderLine({
        prefix: f.num,
        text: f.text,
        page: f.page,
        anchor: f.anchor,
      })
    ),
    pageBreak(),
  ];
}

/** List of Tables (Danh mục bảng biểu) with Clickable Hyperlinks */
export function buildListOfTablesPage() {
  const tables = [
    { num: 'Bảng 0.1', text: 'Phân công nhiệm vụ các thành viên trong nhóm thực hiện đề tài', anchor: 'tbl_0_1', page: 'iii' },
    { num: 'Bảng 0.2', text: 'Kế hoạch tiến độ triển khai đồ án tốt nghiệp theo tuần', anchor: 'tbl_0_2', page: 'iv' },
    { num: 'Bảng 1.1', text: 'So sánh tính năng giữa các nền tảng e-Learning phổ biến với nhu cầu đào tạo nông nghiệp', anchor: 'tbl_1_1', page: '12' },
    { num: 'Bảng 2.1', text: 'Quy cách các tiêu chí đánh giá thực hành kỹ thuật theo ma trận Rubric', anchor: 'tbl_2_1', page: '24' },
    { num: 'Bảng 2.2', text: 'Bảng mã màu công thái học áp dụng trong giao diện Agritrain Boost Theme', anchor: 'tbl_2_2', page: '29' },
    { num: 'Bảng 3.1', text: 'Bảng ma trận phân quyền RBAC chi tiết giữa 4 nhóm tác nhân hệ thống', anchor: 'tbl_3_1', page: '34' },
    { num: 'Bảng 3.2', text: 'Cấu trúc các trường dữ liệu của bảng chứng chỉ số local_mamxanhcert', anchor: 'tbl_3_2', page: '43' },
    { num: 'Bảng 3.3', text: 'Cấu trúc các trường dữ liệu của bảng bài tập thực địa fieldpractice và fieldpractice_sub', anchor: 'tbl_3_3', page: '44' },
    { num: 'Bảng 3.4', text: 'Cấu trúc các trường dữ liệu của bảng nhật ký canh tác local_agriops_log', anchor: 'tbl_3_4', page: '45' },
    { num: 'Bảng 4.1', text: 'Danh sách 8 chương trình tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao', anchor: 'tbl_4_1', page: '50' },
    { num: 'Bảng 4.2', text: 'Tổng hợp số liệu thành phần học tập được xây dựng trong hệ thống', anchor: 'tbl_4_2', page: '51' },
    { num: 'Bảng 4.3', text: 'Kết quả kiểm thử tự động End-to-End với Puppeteer trên 66 kịch bản', anchor: 'tbl_4_3', page: '63' },
    { num: 'Bảng 4.4', text: 'Kết quả khảo sát mức độ hài lòng về tính dễ dùng theo thang điểm SUS (N=30)', anchor: 'tbl_4_4', page: '65' },
  ];

  return [
    h1('DANH MỤC CÁC BẢNG BIỂU', 'front_tables'),
    ...tables.map(t =>
      tocLeaderLine({
        prefix: t.num,
        text: t.text,
        page: t.page,
        anchor: t.anchor,
      })
    ),
    pageBreak(),
  ];
}

/** List of Abbreviations (Danh mục ký hiệu và chữ viết tắt) */
export function buildListOfAbbreviationsPage() {
  const abbrevs = [
    ['API', 'Application Programming Interface', 'Giao diện lập trình ứng dụng'],
    ['CBKT', 'Cán bộ Kỹ thuật', 'Cán bộ khuyến nông / Giảng viên hướng dẫn kỹ thuật'],
    ['CMS', 'Course Management System', 'Hệ thống quản lý khóa học'],
    ['CSS', 'Cascading Style Sheets', 'Ngôn ngữ định kiểu tầng cho trang web'],
    ['DBMS', 'Database Management System', 'Hệ quản trị cơ sở dữ liệu'],
    ['DLU', 'Dalat University', 'Trường Đại học Đà Lạt'],
    ['ERP', 'Enterprise Resource Planning', 'Hệ thống hoạch định tài nguyên doanh nghiệp'],
    ['GPS', 'Global Positioning System', 'Hệ thống định vị toàn cầu'],
    ['HMAC', 'Hash-based Message Authentication Code', 'Mã xác thực thông điệp dựa trên hàm băm mật mã'],
    ['HTML5', 'HyperText Markup Language 5', 'Ngôn ngữ đánh dấu siêu văn bản phiên bản 5'],
    ['H5P', 'HTML5 Package', 'Chuẩn đóng gói nội dung tương tác đa phương tiện'],
    ['IoT', 'Internet of Things', 'Mạng lưới vạn vật kết nối Internet'],
    ['LMS', 'Learning Management System', 'Hệ thống quản lý học tập'],
    ['LTS', 'Long-Term Support', 'Phiên bản hỗ trợ dài hạn'],
    ['PHP', 'PHP: Hypertext Preprocessor', 'Ngôn ngữ lập trình kịch bản máy chủ mã nguồn mở'],
    ['QR', 'Quick Response Code', 'Mã phản hồi nhanh (mã vạch ma trận 2D)'],
    ['RBAC', 'Role-Based Access Control', 'Mô hình kiểm soát truy cập dựa trên vai trò'],
    ['REST', 'Representational State Transfer', 'Kiến trúc truyền tải trạng thái đại diện'],
    ['SCORM', 'Sharable Content Object Reference Model', 'Mô hình tham chiếu đối tượng nội dung có thể chia sẻ'],
    ['SQL', 'Structured Query Language', 'Ngôn ngữ truy vấn có cấu trúc'],
    ['SSL/TLS', 'Secure Sockets Layer / Transport Layer Security', 'Giao thức bảo mật tầng truyền vận'],
    ['SUS', 'System Usability Scale', 'Thang đo chuẩn quốc tế về độ khả dụng của hệ thống'],
    ['TCVN', 'Tiêu chuẩn Việt Nam', 'Hệ thống tiêu chuẩn quốc gia của Việt Nam'],
    ['UI/UX', 'User Interface / User Experience', 'Giao diện người dùng / Trải nghiệm người dùng'],
    ['VietGAP', 'Vietnamese Good Agricultural Practices', 'Quy trình thực hành sản xuất nông nghiệp tốt tại Việt Nam'],
    ['XML', 'Extensible Markup Language', 'Ngôn ngữ đánh dấu mở rộng'],
  ];

  const headers = ['Viết tắt', 'Thuật ngữ Tiếng Anh', 'Ý nghĩa giải thích'];
  const colWidths = [15, 45, 40];

  return [
    h1('DANH MỤC CÁC TỪ VIẾT TẮT', 'front_abbrev'),
    p('Các từ viết tắt dưới đây được sắp xếp theo thứ tự bảng chữ cái Latinh (A - Z) nhằm giúp người đọc thuận tiện tra cứu các thuật ngữ chuyên ngành được sử dụng xuyên suốt trong đồ án:', { indent: 0, italic: true }),
    ...createAcademicTable(headers, abbrevs, colWidths),
    pageBreak(),
  ];
}

/** Abstract Page (Tóm tắt đồ án & Abstract) - Clean without Keywords line */
export function buildAbstractPage() {
  return [
    h1('TÓM TẮT ĐỒ ÁN', 'front_summary'),
    p('Trong bối cảnh tỉnh Lâm Đồng và vùng Đà Lạt đang đẩy mạnh chuyển đổi số ngành nông nghiệp hướng tới mục tiêu phát triển nông nghiệp sinh thái, tuần hoàn và công nghệ cao, việc đào tạo và nâng cao năng lực kỹ thuật cho đội ngũ nông hộ đóng vai trò then chốt. Tuy nhiên, các phương thức tập huấn truyền thống đang bộc lộ nhiều hạn chế về chi phí tổ chức, tính bất tiện về thời gian và sự thiếu hụt công cụ kiểm soát chất lượng thực hành. Đề tài "Xây dựng hệ thống quản lý học tập và tập huấn kỹ thuật canh tác nông nghiệp công nghệ cao trên nền tảng Moodle" (Mầm Xanh LMS 2.0) được nghiên cứu và hiện thực hóa nhằm giải quyết triệt để bài toán này.'),
    p('Hệ thống được thiết kế theo kiến trúc 4 tầng phân tách độc lập, vận hành trên nền tảng mã nguồn mở Moodle 4.5 LTS với hạ tầng ảo hóa Docker Turbo tối ưu hiệu năng. Đề tài đã tập trung giải quyết 4 đóng góp kỹ thuật trọng tâm: (1) Nghiên cứu và áp dụng tiêu chuẩn công thái học UI/UX Minimalist Off-White Botanical (#F9FAF9 / #234E3E) loại bỏ các yếu tố rườm rà, tối ưu cho môi trường ánh sáng ngoài trời và thiết bị di động của nông hộ; (2) Số hóa 8 chương trình tập huấn kỹ thuật công nghệ cao chủ lực (Dâu tây nhà kính, Rau thủy canh, Cây ăn quả xuất khẩu, Phân bón vi sinh, IoT cảm biến) theo chuẩn SCORM 1.2 và H5P Interactive Content; (3) Lập trình 5 module và plugin chuyên biệt gồm: mod_fieldpractice (thực hành thực địa có tọa độ GPS và chấm điểm theo Rubric), local_mamxanhcert (cấp chứng chỉ số xác thực bằng mã hóa HMAC-SHA256 kết hợp mã QR chống giả mạo), local_agriops (nhật ký canh tác điện tử và hộ chiếu nông sản số liên thông VietGAP), local_mamxanhapp (bảng điểm học thuật và phiếu phản hồi tiêu chí) và theme_agritrain_child; (4) Xây dựng bộ công cụ tự động hóa kiểm thử E2E với Puppeteer trên 66 kịch bản, đạt tỷ lệ chính xác và toàn vẹn 100%.'),
    p('Kết quả thực nghiệm trên 18 tài khoản người dùng, 136 hoạt động học tập, 65 phiên điểm danh, 27 lô nông sản VietGAP và 42 chứng chỉ số được cấp cho thấy hệ thống vận hành mượt mà, đạt độ hài lòng SUS 86.5/100 điểm (mức Xuất sắc - A+). Hệ thống mở ra tiềm năng lớn trong việc chuẩn hóa năng lực nông dân số, phục vụ hiệu quả cho chuỗi giá trị nông nghiệp công nghệ cao của địa phương.'),

    new Paragraph({ spacing: { before: 200, after: 100 } }),
    h1('ABSTRACT', 'front_abstract'),
    p('In the era of agricultural digital transformation in Lam Dong province and Da Lat city towards ecological, circular, and high-tech agriculture, capacity building and technical training for smallholder farmers play a decisive role. However, conventional training workshops reveal critical limitations regarding operational costs, rigid scheduling, and lack of verifiable field practice assessment. The graduation project "Developing a Learning Management and High-Tech Agricultural Cultivation Training System on Moodle Platform" (Mam Xanh LMS 2.0) is proposed and implemented to thoroughly address these challenges.', { italic: true }),
    p('The platform is architected with a decoupled 4-tier model, deployed on the open-source Moodle 4.5 LTS engine utilizing an optimized Docker Turbo containerized infrastructure. The thesis focuses on four primary engineering contributions: (1) Formulating and applying an ergonomic Minimalist Off-White Botanical UI/UX (#F9FAF9 / #234E3E), eliminating visual clutters and maximizing readability under intense outdoor sunlight on mobile screens; (2) Standardizing and digitizing 8 high-tech agricultural training curricula (Smart Greenhouse Strawberries, Hydroponic Vegetables, Export Fruit Trees, Biofertilizer Fermentation, IoT Sensor Monitoring) complying with SCORM 1.2 and H5P Interactive Content standards; (3) Engineering 5 specialized Moodle modules: mod_fieldpractice (GPS-tagged field assignment submissions evaluated via multi-criteria Rubrics), local_mamxanhcert (tamper-proof digital certificates secured by HMAC-SHA256 signatures and instant public QR verification), local_agriops (electronic farming logbook connected to VietGAP digital traceability passports), local_mamxanhapp (academic transcripts and feedback sheets), and theme_agritrain_child; (4) Constructing an automated End-to-End testing harness using Puppeteer across 66 critical functional URLs, achieving a 100% pass rate.', { italic: true }),
    p('Empirical testing across 18 user accounts, 136 learning modules, 65 attendance sessions, 27 VietGAP batches, and 42 verified certificates demonstrates robust performance with a System Usability Scale (SUS) score of 86.5/100 (Grade A+). The system demonstrates substantial practical value in advancing digital farmers and accelerating high-tech agricultural value chains in Vietnam.', { italic: true }),
    pageBreak(),
  ];
}
