# Use docker buildx build --platform linux/arm64 -t sanjeevgiri/nextcloud-arm64-ffmpeg:28.0.2 --push . for creating image for arm64 (raspberry pi)
# https://www.docker.com/blog/getting-started-with-docker-for-arm-on-linux/
FROM --platform=linux/arm64/v8 nextcloud:28.0.2 as deps

RUN apt-get update && apt-get install -y \
    ffmpeg \
    ghostscript \
    poppler-utils \
    libreoffice \
    imagemagick \
    && rm -rf /var/lib/apt/lists/*
