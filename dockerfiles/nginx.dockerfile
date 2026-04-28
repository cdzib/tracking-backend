FROM nginx:stable-alpine
WORKDIR /etc/nginx/conf.d
COPY nginx-vagonetas_backend.conf ./default.conf
RUN mv default.conf default.conf
WORKDIR /var/www/html
COPY src .