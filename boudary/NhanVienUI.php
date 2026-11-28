<?php
/**
 * =========================================================================
 * GÓI BOUNDARY: Giao diện Quản Lý Nhân Viên (Light Theme & Phân Quyền Quản Lý)
 * File: boundary/NhanVienUI.php
 * Chức năng: Xem danh sách, thêm, cấp quyền Quản lý cho tài khoản, bảo vệ admin hệ thống
 * Nguồn: Báo cáo nhóm (Mục 4)
 * =========================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controller/TaiKhoanController.php';
require_once __DIR__ . '/../controller/NhanVienController.php';
require_once __DIR__ . '/../utils/Formatter.php';
require_once __DIR__ . '/_nav.php';

// Chỉ quản lý mới được phép truy cập
TaiKhoanController::requireRole('QUAN_LY');

$controller = new NhanVienController();
$keyword = $_GET['keyword'] ?? '';
$action = $_POST['action'] ?? '';

// Xử lý các thao tác từ form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'them') {
        try {
            $controller->themNhanVien($_POST);
            header("Location: " . url('boundary/NhanVienUI.php'));
            exit;
        } catch (Exception $e) {
            setFlash($e->getMessage(), 'danger');
        }
    } elseif ($action === 'doi_trang_thai') {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $trangThaiMoi = $_POST['trang_thai_moi'] ?? 'NGUNG_HOAT_DONG';
            $controller->doiTrangThai($id, $trangThaiMoi);
            header("Location: " . url('boundary/NhanVienUI.php'));
            exit;
        } catch (Exception $e) {
            setFlash($e->getMessage(), 'danger');
        }
    } elseif ($action === 'doi_vai_tro') {
        try {
            $taiKhoanId = (int)($_POST['tai_khoan_id'] ?? 0);
            $vaiTroMoi = $_POST['vai_tro_moi'] ?? 'NHAN_VIEN';
            $controller->doiVaiTro($taiKhoanId, $vaiTroMoi);
            header("Location: " . url('boundary/NhanVienUI.php'));
            exit;
        } catch (Exception $e) {
            setFlash($e->getMessage(), 'danger');
        }
    }
}

$danhSach = $controller->layDanhSach($keyword);

renderNavHeader("Quản Lý Nhân Viên");
?>

<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="users" class="w-6 h-6 text-sky-600"></i>
                <span>Quản Lý Nhân Sự & Phân Quyền Hệ Thống</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium">
                Tài khoản <strong class="text-indigo-600 font-bold">admin</strong> là tài khoản hệ thống mặc định cố định. Bạn có thể cấp quyền Quản lý cho bất kỳ nhân viên nào.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Search Form -->
            <form action="NhanVienUI.php" method="GET" class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm tên, SĐT, tài khoản..."
                    class="pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 shadow-xs transition w-56 sm:w-64">
            </form>

            <!-- Add Button -->
            <button type="button" onclick="toggleModal('modal-them-nv')" class="py-2 px-4 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl transition shadow-md shadow-sky-600/20 flex items-center gap-2 shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Thêm Nhân Viên</span>
            </button>
        </div>
    </div>

    <!-- Data Table (Light Theme) -->
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Mã NV</th>
                        <th class="py-3 px-4">Họ và Tên</th>
                        <th class="py-3 px-4">Tài Khoản Đăng Nhập</th>
                        <th class="py-3 px-4">Liên Hệ</th>
                        <th class="py-3 px-4">Chức Vụ</th>
                        <th class="py-3 px-4">Quyền Hạn (Vai Trò)</th>
                        <th class="py-3 px-4">Trạng Thái</th>
                        <th class="py-3 px-4 text-right">Phân Quyền & Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($danhSach)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">
                                Không tìm thấy nhân viên nào phù hợp.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSach as $nv): ?>
                            <?php 
                            $isAdminRoot = (strtolower($nv['ten_dang_nhap'] ?? '') === 'admin' || (int)($nv['tai_khoan_id'] ?? 0) === 1);
                            $isManager = (($nv['vai_tro'] ?? 'NHAN_VIEN') === 'QUAN_LY');
                            ?>
                            <tr class="hover:bg-slate-50/80 transition <?= $isAdminRoot ? 'bg-indigo-50/20' : '' ?>">
                                <td class="py-3.5 px-4 font-mono font-bold text-sky-600"><?= htmlspecialchars($nv['ma_nv']) ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span><?= htmlspecialchars($nv['ho_ten']) ?></span>
                                        <?php if ($isAdminRoot): ?>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[9px] font-bold border border-amber-300">
                                                <i data-lucide="crown" class="w-3 h-3 text-amber-600"></i>
                                                ADMIN GỐC
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-normal"><?= htmlspecialchars($nv['gioi_tinh'] ?? 'Nam') ?> • Sinh: <?= Formatter::date($nv['ngay_sinh'] ?? '') ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200 font-bold">@<?= htmlspecialchars($nv['ten_dang_nhap'] ?? 'chưa cấp') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="font-semibold"><?= htmlspecialchars($nv['so_dien_thoai']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($nv['email'] ?? '—') ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-medium"><?= htmlspecialchars($nv['chuc_vu'] ?? 'Nhân viên') ?></td>
                                
                                <!-- Cột Vai trò -->
                                <td class="py-3.5 px-4">
                                    <?php if ($isAdminRoot): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-300 shadow-2xs">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                            Quản Trị Hệ Thống
                                        </span>
                                    <?php else: ?>
                                        <?= Formatter::badgeRole($nv['vai_tro'] ?? 'NHAN_VIEN') ?>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột Trạng thái -->
                                <td class="py-3.5 px-4">
                                    <?= Formatter::badgeStatus($nv['trang_thai_tk'] ?? 'HOAT_DONG') ?>
                                </td>

                                <!-- Cột Phân Quyền & Thao Tác -->
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ($isAdminRoot): ?>
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-semibold px-2 py-1 bg-slate-100 rounded-lg border border-slate-200 cursor-not-allowed" title="Tài khoản admin mặc định không thể thay đổi">
                                            <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i>
                                            Cố định
                                        </span>
                                    <?php else: ?>
                                        <div class="inline-flex items-center gap-1.5">
                                            
                                            <!-- Nút Cấp / Thu Hồi Quyền Quản Lý -->
                                            <form action="NhanVienUI.php" method="POST" class="inline" onsubmit="return confirm('<?= $isManager ? 'Thu hồi quyền Quản lý và chuyển thành Nhân viên?' : 'Cấp quyền Quản lý cho tài khoản này?' ?>')">
                                                <input type="hidden" name="action" value="doi_vai_tro">
                                                <input type="hidden" name="taiKhoanId" value="<?= $nv['tai_khoan_id'] ?>">
                                                <input type="hidden" name="tai_khoan_id" value="<?= $nv['tai_khoan_id'] ?>">
                                                
                                                <?php if ($isManager): ?>
                                                    <input type="hidden" name="vai_tro_moi" value="NHAN_VIEN">
                                                    <button type="submit" title="Hạ xuống Nhân viên" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition font-bold text-[11px] border border-slate-200 flex items-center gap-1 shadow-2xs">
                                                        <i data-lucide="user-minus" class="w-3.5 h-3.5 text-slate-500"></i>
                                                        <span>Về Nhân Viên</span>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="vai_tro_moi" value="QUAN_LY">
                                                    <button type="submit" title="Cấp quyền Quản lý" class="px-2.5 py-1 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition font-bold text-[11px] border border-indigo-200 flex items-center gap-1 shadow-2xs">
                                                        <i data-lucide="shield-plus" class="w-3.5 h-3.5 text-indigo-600"></i>
                                                        <span>Cấp Quyền Quản Lý</span>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Nút Khóa / Mở Khóa Tài Khoản -->
                                            <form action="NhanVienUI.php" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn đổi trạng thái tài khoản nhân viên này?')">
                                                <input type="hidden" name="action" value="doi_trang_thai">
                                                <input type="hidden" name="id" value="<?= $nv['id'] ?>">
                                                <?php if (($nv['trang_thai_tk'] ?? 'HOAT_DONG') === 'HOAT_DONG'): ?>
                                                    <input type="hidden" name="trang_thai_moi" value="NGUNG_HOAT_DONG">
                                                    <button type="submit" title="Khóa tài khoản" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition shadow-2xs">
                                                        <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="trang_thai_moi" value="HOAT_DONG">
                                                    <button type="submit" title="Mở khóa tài khoản" class="p-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 transition shadow-2xs">
                                                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Thêm Nhân Viên (Light Theme) -->
<div id="modal-them-nv" class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop">
    <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4 text-sky-600"></i>
                <span>Thêm Mới Nhân Viên & Cấp Tài Khoản</span>
            </h3>
            <button type="button" onclick="toggleModal('modal-them-nv')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="NhanVienUI.php" method="POST" class="space-y-3.5 text-xs">
            <input type="hidden" name="action" value="them">

            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-slate-700 font-bold mb-1">Họ và tên nhân viên *</label>
                    <input type="text" name="ho_ten" required placeholder="Nguyễn Văn A" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Số điện thoại *</label>
                    <input type="text" name="so_dien_thoai" required placeholder="0901234567" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Email</label>
                    <input type="email" name="email" placeholder="email@gymfit.vn" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Giới tính</label>
                    <select name="gioi_tinh" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 font-bold mb-1">Chức vụ</label>
                    <input type="text" name="chuc_vu" value="Nhân viên lễ tân" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                </div>
            </div>

            <!-- Khối tạo tài khoản đăng nhập -->
            <div class="pt-3 border-t border-slate-100">
                <p class="font-extrabold text-[11px] text-sky-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    <span>Tài Khoản & Phân Quyền Ban Đầu</span>
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Tên đăng nhập *</label>
                        <input type="text" name="ten_dang_nhap" required placeholder="vidu: nguyenvana" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                    </div>
                    <div>
                        <label class="block text-slate-700 font-bold mb-1">Mật khẩu ban đầu</label>
                        <input type="text" name="mat_khau" value="123456" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-slate-700 font-bold mb-1">Cấp quyền ban đầu</label>
                        <select name="vai_tro" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition font-medium">
                            <option value="NHAN_VIEN">Nhân Viên (Check-in, Hội viên, Hóa đơn)</option>
                            <option value="QUAN_LY">Quản Lý (Có thêm quyền Nhân viên, Gói tập, Thống kê doanh thu)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="toggleModal('modal-them-nv')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl shadow-md shadow-sky-600/20 transition">Lưu & Cấp Quyền</button>
            </div>
        </form>

    </div>
</div>

<?php renderNavFooter(); ?>
