# Khảo sát giao diện & cấu trúc khóa học `lms.dlu.edu.vn` (tham chiếu thiết kế)

Phạm vi: chỉ đọc, bằng tài khoản sinh viên được cấp; không nộp hay chỉnh sửa gì.

## 1. Trang chủ / Dashboard
- Theme Lambda (kiểu Moodle 3.x), 3 cột (nội dung giữa, block phải, drawer nhỏ). Header trắng cao 90px, logo trường; navbar nền đen, mục đầu nền xanh lá DLU (~#5AA100).
- Font Open Sans 13px, chữ #555, nhấn xanh lá (#5AA100 / #54880B). Block nền #F2F2F2, bo góc 2px.
- "Course overview" là cột dọc thẻ lớn (62 khóa), thẻ đổ bóng, ảnh bìa nhiều màu, không có thanh tiến độ rõ ràng.
- Footer 3 cột (phòng chức năng, khoa, liên hệ).
- Nhận xét: mật độ thông tin cao, chữ nhỏ, không dùng emoji, không có chỉ báo hoàn thành.

## 2. Cấu trúc khóa học (format-topics)
| Khu vực | Nội dung thực tế |
|---|---|
| Section 0 "Chung" | Forum Announcements, Điểm danh (mod_attendance), nhãn thông báo, file điểm |
| "Tài nguyên" / "Tham khảo" | File PDF giáo trình, bài Lab, URL đề cương môn học, sách tham khảo |
| Chương 1..N / Lý thuyết / Thực hành | 1 slide PDF mỗi chương; cặp "Lab" (PDF) + "Nộp bài Lab" (mod_assign, có Đã mở / Hạn nộp) |
| "Kiểm tra - Đánh giá" | 1-2 mod_quiz |
| Báo cáo cuối kỳ | Nộp bằng mod_assign |

Đặt tên theo kiểu học thuật Việt (Chương N, Lý thuyết, Thực hành, Bài tập nhóm, Tài nguyên, Tham khảo, Kiểm tra - Đánh giá).

## 3. Điều rút ra cho Mầm Xanh LMS 2.0
1. Giữ giao diện phẳng, học thuật, không emoji; thêm phần mà LMS tham chiếu thiếu: chỉ báo hoàn thành và thanh tiến độ mảnh.
2. Section 0 "Thông tin chung": thông báo, đề cương, điểm danh, thư mục tài liệu tham khảo.
3. Các chuyên đề đặt tên "Chuyên đề N", mỗi chuyên đề có bài giảng, video/H5P, bài tập (mod_assign) hoặc quiz.
4. Phần cuối: kiểm tra cuối khóa (mod_quiz) và bài thu hoạch (mod_assign) có rubric.
