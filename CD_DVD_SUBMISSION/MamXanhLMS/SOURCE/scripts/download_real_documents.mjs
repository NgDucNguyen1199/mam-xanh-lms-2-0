import fs from 'fs';
import path from 'path';
import https from 'https';
import http from 'http';

const targetDir = path.resolve('assets/real_documents');
if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
}

const docs = [
    {
        name: 'tai-lieu-vietgap-trong-trot-bo-nnptnt.pdf',
        url: 'https://chicucclcbpttt.lamdong.gov.vn/UpLoaded/files/tieu-chuan-tcvn-11892-1-2017-yeu-cau-thuc-hanh-nong-nghiep-tot-trong-trong-trot.pdf',
        title: 'Tiêu chuẩn Quốc gia TCVN 11892-1:2017 - Thực hành nông nghiệp tốt (VietGAP) Phần 1: Trồng trọt',
        source: 'Chi cục Quản lý Chất lượng và Phát triển Thị trường Lâm Đồng - Bộ NN&PTNT'
    },
    {
        name: 'so-tay-huong-dan-ky-thuat-tuoi-tiet-kiem-lam-dong.pdf',
        url: 'https://khuyennongvn.gov.vn/portals/0/tailieu/2021/05/hangweb/sotay_hd_sx_gapmanual_for_production_da_nen.pdf',
        title: 'Sổ tay hướng dẫn kỹ thuật canh tác và tưới tiết kiệm theo tiêu chuẩn GAP',
        source: 'Trung tâm Khuyến nông Quốc gia - Bộ Nông nghiệp & Phát triển Nông thôn'
    },
    {
        name: 'so-tay-ipm-phong-tru-sau-benh-sinh-hoc.pdf',
        url: 'https://apictt.tuyenquang.gov.vn/uploads/attachments/cay-rau-an-la-final.pdf',
        title: 'Sổ tay kỹ thuật canh tác và Quản lý Dịch hại Tổng hợp (IPM) thích ứng biến đổi khí hậu',
        source: 'Viện Nghiên cứu Rau quả & Cục Bảo vệ Thực vật'
    },
    {
        name: 'giao-trinh-quan-ly-dinh-duong-cay-trong-nha-mang.pdf',
        url: 'https://apictt.tuyenquang.gov.vn/uploads/attachments/1-so-tay-tofcay-camvietgap.pdf',
        title: 'Sổ tay hướng dẫn quản lý dinh dưỡng và kỹ thuật canh tác VietGAP',
        source: 'Tài liệu đào tạo giảng viên TOT/TOF - Bộ NN&PTNT'
    }
];

function downloadFile(url, dest) {
    return new Promise((resolve, reject) => {
        const client = url.startsWith('https') ? https : http;
        const options = {
            rejectUnauthorized: false,
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        };

        const req = client.get(url, options, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                let redirectUrl = res.headers.location;
                if (!redirectUrl.startsWith('http')) {
                    const u = new URL(url);
                    redirectUrl = u.origin + redirectUrl;
                }
                return resolve(downloadFile(redirectUrl, dest));
            }

            if (res.statusCode !== 200) {
                return reject(new Error(`Failed to download ${url}: status code ${res.statusCode}`));
            }

            const file = fs.createWriteStream(dest);
            res.pipe(file);
            file.on('finish', () => {
                file.close(() => {
                    const stats = fs.statSync(dest);
                    resolve(stats.size);
                });
            });
            file.on('error', (err) => {
                fs.unlink(dest, () => {});
                reject(err);
            });
        });

        req.on('error', (err) => {
            reject(err);
        });
        req.setTimeout(45000, () => {
            req.destroy();
            reject(new Error(`Timeout downloading ${url}`));
        });
    });
}

async function main() {
    console.log('Downloading 4 genuine agricultural documents...');
    for (const doc of docs) {
        const dest = path.join(targetDir, doc.name);
        try {
            console.log(`Downloading: ${doc.name} from ${doc.url}...`);
            const bytes = await downloadFile(doc.url, dest);
            console.log(`-> SUCCESS: ${doc.name} (${(bytes / 1024).toFixed(1)} KB)`);
        } catch (err) {
            console.error(`-> ERROR downloading ${doc.name}: ${err.message}`);
        }
    }
}

main();
