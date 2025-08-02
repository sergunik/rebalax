#!/bin/bash

cp /etc/grafana/provisioning/datasources/datasource.yml.example /etc/grafana/provisioning/datasources/datasource.yml

sed -i "s|\$env.GRAFANA_DB_USERNAME|${GRAFANA_DB_USERNAME}|g" /etc/grafana/provisioning/datasources/datasource.yml
sed -i "s|\$env.GRAFANA_DB_PASSWORD|${GRAFANA_DB_PASSWORD}|g" /etc/grafana/provisioning/datasources/datasource.yml
sed -i "s|\$env.GRAFANA_DB_DATABASE|${GRAFANA_DB_DATABASE}|g" /etc/grafana/provisioning/datasources/datasource.yml


exec /run.sh
