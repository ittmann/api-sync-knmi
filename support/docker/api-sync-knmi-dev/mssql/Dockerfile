FROM mcr.microsoft.com/mssql/rhel/server:2022-latest

USER root
RUN mkdir -p /usr/config
COPY ./db-init /usr/config
RUN chmod +x /usr/config/import-data.sh

COPY ./entrypoint.sh /

ENTRYPOINT /bin/bash /entrypoint.sh
