# 🐳 Hướng dẫn chạy project với Docker

## 📋 Yêu cầu

- Docker >= 20.10
- Docker Compose >= 2.0

## 🚀 Cách sử dụng

### 1. Khởi động các container

```bash
docker-compose up -d
```

Lần đầu tiên chạy, Docker sẽ:
- Build image PHP với Apache
- Tạo và khởi động MySQL container
- Tự động import database từ `database/ecommercedb.sql`
- Khởi động phpMyAdmin

### 2. Truy cập ứng dụng

- **API**: http://localhost:8080/api
- **phpMyAdmin**: http://localhost:8081
  - Server: `db`
  - Username: `root`
  - Password: `root_password`

### 3. Cấu hình Database

Database sẽ được tự động tạo và import khi container MySQL khởi động lần đầu.

Thông tin kết nối:
- **Host**: `db` (trong Docker network) hoặc `localhost:3307` (từ máy host)
- **Database**: `ecommercedb`
- **Username**: `ecommerce_user`
- **Password**: `ecommerce_pass`
- **Root Password**: `root_password`

### 4. Các lệnh hữu ích

#### Xem logs
```bash
# Xem logs của tất cả services
docker-compose logs -f

# Xem logs của service cụ thể
docker-compose logs -f web
docker-compose logs -f db
```

#### Dừng containers
```bash
docker-compose stop
```

#### Dừng và xóa containers
```bash
docker-compose down
```

#### Xóa containers và volumes (xóa cả database)
```bash
docker-compose down -v
```

#### Rebuild containers
```bash
docker-compose up -d --build
```

#### Truy cập vào container
```bash
# Vào container web
docker-compose exec web bash

# Vào container database
docker-compose exec db bash

# Truy cập MySQL CLI
docker-compose exec db mysql -u root -proot_password ecommercedb
```

#### Chạy Composer commands
```bash
docker-compose exec web composer install
docker-compose exec web composer update
```

## 🔧 Cấu hình

### Thay đổi ports

Chỉnh sửa file `docker-compose.yml`:

```yaml
services:
  web:
    ports:
      - "8080:80"  # Thay đổi port 8080 thành port bạn muốn
  
  db:
    ports:
      - "3307:3306"  # Thay đổi port 3307 thành port bạn muốn
```

### Thay đổi database credentials

Chỉnh sửa file `docker-compose.yml`:

```yaml
services:
  db:
    environment:
      MYSQL_ROOT_PASSWORD: your_root_password
      MYSQL_DATABASE: your_database_name
      MYSQL_USER: your_username
      MYSQL_PASSWORD: your_password
  
  web:
    environment:
      - DB_HOST=db
      - DB_NAME=your_database_name
      - DB_USER=your_username
      - DB_PASS=your_password
```

### Thay đổi JWT Secret

Chỉnh sửa file `config/constants.php`:

```php
define('JWT_SECRET', 'your-production-secret-key');
```

## 📁 Cấu trúc Volumes

- `./` → `/var/www/html` - Mount toàn bộ project code
- `./public/images` → `/var/www/html/public/images` - Mount thư mục images
- `db_data` → `/var/lib/mysql` - Persistent database storage

## 🐛 Troubleshooting

### Lỗi kết nối database

1. Kiểm tra database container đã chạy:
   ```bash
   docker-compose ps
   ```

2. Kiểm tra logs:
   ```bash
   docker-compose logs db
   ```

3. Đảm bảo database đã được import:
   ```bash
   docker-compose exec db mysql -u root -proot_password -e "SHOW DATABASES;"
   ```

### Lỗi permissions

Nếu gặp lỗi permissions với thư mục `public/images`:

```bash
docker-compose exec web chmod -R 775 /var/www/html/public/images
docker-compose exec web chown -R www-data:www-data /var/www/html/public/images
```

### Reset database

Để reset database về trạng thái ban đầu:

```bash
# Dừng và xóa volumes
docker-compose down -v

# Khởi động lại
docker-compose up -d
```

### Rebuild lại image

Nếu thay đổi Dockerfile hoặc dependencies:

```bash
docker-compose build --no-cache
docker-compose up -d
```

## 📝 Notes

- Database sẽ tự động được import khi container MySQL khởi động lần đầu
- File `config/database.php` sẽ được tự động cập nhật với thông tin từ environment variables
- Code changes sẽ được reflect ngay lập tức nhờ volume mounting
- Để thay đổi có hiệu lực trong container, có thể cần restart:
  ```bash
  docker-compose restart web
  ```

## 🔒 Security Notes

⚠️ **Quan trọng cho Production:**

1. Thay đổi tất cả passwords mặc định
2. Không expose database port ra ngoài (xóa `ports` trong service `db`)
3. Sử dụng strong JWT secret
4. Cấu hình SSL/HTTPS
5. Review và harden Apache configuration
6. Sử dụng secrets management cho sensitive data

