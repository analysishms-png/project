@echo off
set PATH=C:\xampp\php;C:\Program Files\ImageMagick-7.1.2-Q16-HDRI;%PATH%
cd /d C:\xampp\htdocs\analysishms
php artisan serve
