FROM php:8.2-apache

# نسخ ملفات البوت إلى مجلد السيرفر
COPY . /var/www/html/

# إعطاء صلاحيات كاملة للمجلد لإنشاء ملفات التكست تلقائياً
RUN chmod -R 777 /var/www/html/

# ضبط المنفذ ليتوافق مع Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# تشغيل سيرفر Apache
CMD ["apache2-foreground"]
