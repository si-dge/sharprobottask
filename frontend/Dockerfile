FROM node:22-alpine AS build
WORKDIR /app
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build
RUN test -f /app/dist/catalog/browser/index.html
FROM nginx:1.27-alpine
COPY --from=build /app/dist/catalog/browser /usr/share/nginx/html
COPY --from=build /app/node_modules/swagger-ui-dist/swagger-ui.css /usr/share/nginx/html/swagger-ui/swagger-ui.css
COPY --from=build /app/node_modules/swagger-ui-dist/swagger-ui-bundle.js /usr/share/nginx/html/swagger-ui/swagger-ui-bundle.js
COPY docs/swagger.html /usr/share/nginx/html/docs/swagger.html
COPY docs/openapi.yaml /usr/share/nginx/html/docs/openapi.yaml
COPY frontend/.htpasswd /etc/nginx/.htpasswd
COPY frontend/nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
