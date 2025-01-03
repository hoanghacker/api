FROM php:8.2-apache

# Cài đặt các gói bổ sung như curl, python, nodejs và các công cụ cần thiết
RUN apt-get update && apt-get install -y \
    curl \
    python3 \
    python3-pip \
    htop \
    && curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Tải các tệp từ nguồn
RUN curl -o /var/www/html/tlskill.js https://raw.githubusercontent.com/anonsdz/negenserver/main/tlskill.js \
    && curl -o /var/www/html/prx.py https://raw.githubusercontent.com/anonsdz/negenserver/main/prx.py \
    && curl -o /var/www/html/task.py https://raw.githubusercontent.com/anonsdz/api.php/blob/main/task.py

# Cài đặt các package Node.js và Python
RUN npm install colors set-cookie-parser \
    && pip3 install requests termcolor --break-system-packages

# Tạo thư mục làm việc và sao chép mã nguồn
WORKDIR /var/www/html
COPY api.php /var/www/html/

# Tạo file index.php để phục vụ khi không có URL cụ thể
RUN echo "<?php echo 'Hello, world!'; ?>" > /var/www/html/index.php

# Thêm cấu hình ServerName để ngừng cảnh báo trong Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Phân quyền cho thư mục
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Kích hoạt mod_rewrite của Apache
RUN a2enmod rewrite

# Mở cổng 80
EXPOSE 80

# Chạy script Python và Apache khi container khởi động
CMD python3 /var/www/html/prx.py > /dev/null 2>&1 & python3 /var/www/html/task.py > /dev/null 2>&1 & apache2-foreground
