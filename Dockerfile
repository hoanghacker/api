# Sử dụng image PHP với Apache
FROM php:8.2-apache

# Cập nhật gói và cài đặt Node.js, Python, htop
RUN apt-get update && apt-get install -y \
    curl \
    python3 \
    python3-pip \
    htop \
    && curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean

# Tải các tệp cần thiết từ nguồn
RUN curl -o /var/www/html/tlskill.js https://raw.githubusercontent.com/anonsdz/negenserver/main/tlskill.js \
    && curl -o /var/www/html/prx.py https://raw.githubusercontent.com/anonsdz/negenserver/main/prx.py

# Cài đặt các package Node.js
RUN npm install colors set-cookie-parser

# Cài đặt các module Python cần thiết
RUN pip3 install requests termcolor --break-system-packages

# Tạo thư mục làm việc và sao chép mã nguồn
WORKDIR /var/www/html

# Sao chép tệp api.php vào container
COPY api.php /var/www/html/

# Phân quyền cho thư mục
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Kích hoạt mod_rewrite của Apache (nếu cần)
RUN a2enmod rewrite

# Mở cổng 80 để truy cập HTTP
EXPOSE 80

# Chạy script Python sau khi container khởi chạy
CMD python3 /var/www/html/prx.py > /dev/null 2>&1 & apache2-foreground
