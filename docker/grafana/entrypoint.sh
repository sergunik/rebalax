#!/bin/bash

cp /etc/grafana/provisioning/datasources/datasource.yml.example /etc/grafana/provisioning/datasources/datasource.yml

sed -i "s|\${{ env.DB_USERNAME }}|${DB_USERNAME}|g" /etc/grafana/provisioning/datasources/datasource.yml
sed -i "s|\${{ env.DB_PASSWORD }}|${DB_PASSWORD}|g" /etc/grafana/provisioning/datasources/datasource.yml
sed -i "s|\${{ env.DB_DATABASE }}|${DB_DATABASE}|g" /etc/grafana/provisioning/datasources/datasource.yml


exec /run.sh
