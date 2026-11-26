# Gym_Manager
## 1.Sơ đồ thư mục
gym-management/

├── boundary/ lớp biên – giao diện (*.php)

├── controller/ lớp điều khiển (*.php)

├── model/ lớp dữ liệu / entity (*.php)

├── data/ Database.php, gym_db.sql

├── config/ config.php

├── utils/ Validator, Formatter,

│ ExcelExporter

└── public/

├── css/

├── js/

└── images/

Dự án được thiết kế chuẩn hóa theo **Biểu đồ gói (Package Diagram)** với kiến trúc phân tách trách nhiệm chặt chẽ:
- **Boundary (`/boundary/`)**: Lớp biên (Giao diện người dùng) – Tiếp nhận tương tác, hiển thị dữ liệu và gửi yêu cầu tới Controller.
- **Controller (`/controller/`)**: Lớp điều khiển – Tiếp nhận yêu cầu, kiểm tra tính hợp lệ dữ liệu qua `Utils`, điều phối logic nghiệp vụ bằng cách gọi `Model`, và trả kết quả về `Boundary`.
- **Model (`/model/`)**: Lớp thực thể dữ liệu – Mỗi file tương ứng với một bảng trong CSDL, chịu trách nhiệm đọc/ghi dữ liệu của chính nó thông qua `Data`.
- **Data (`/data/`)**: Lớp kết nối và truy vấn CSDL – Đọc cấu hình từ `Config`, quản lý kết nối PDO tới MySQL server và cung cấp các hàm truy vấn.
- **Config (`/config/`)**: Chứa thông số môi trường (Host, CSDL, Port, User, Password) độc lập với mã nguồn.
- **Utils (`/utils/`)**: Các hàm tiện ích dùng chung (Kiểm tra dữ liệu, định dạng tiền tệ/ngày tháng, xuất báo cáo Excel).
- **Public (`/public/`)**: Tài nguyên tĩnh gồm CSS, JavaScript và hình ảnh/logo.
- **MySQL (`gym_db`)**: Cơ sở dữ liệu thực tế lưu trữ toàn bộ dữ liệu nghiệp vụ.

```
Luồng phụ thuộc: Boundary ──► Controller ──► Model ──► Data ──► MySQL (gym_db)
                     │              │           │
                     ▼              ▼           ▼
                   Public         Utils       Config
```
