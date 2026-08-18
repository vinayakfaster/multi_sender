FROM php:8.2-cli

WORKDIR /app

# System dependencies install karo
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions install karo
RUN docker-php-ext-install curl

COPY . .

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000"]
