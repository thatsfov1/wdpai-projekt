FROM postgres:16

ENV POSTGRES_PASSWORD docker
ENV POSTGRES_DB db
ENV POSTGRES_USER docker

COPY docker/db/init.sql /docker-entrypoint-initdb.d/

COPY . /app/database