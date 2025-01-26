#!/bin/bash

DUMP_DIR="/docker-entrypoint-initdb.d/dump"

if [ -d "$DUMP_DIR" ] && [ "$(ls -A $DUMP_DIR)" ]; then
  for dump_file in "$DUMP_DIR"/*; {
    mysql -u "${MYSQL_USER}" --password="${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" < "$dump_file"
  }
else
  echo "❓ No dump files found in $DUMP_DIR"
fi