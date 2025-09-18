#!/bin/bash

# ---------------------------------------------------------------------------
# Creative Commons CC BY 4.0 - David Romero - Diverso Lab
# ---------------------------------------------------------------------------
# This script is licensed under the Creative Commons Attribution 4.0 
# International License. You are free to share and adapt the material 
# as long as appropriate credit is given, a link to the license is provided, 
# and you indicate if changes were made.
#
# For more details, visit:
# https://creativecommons.org/licenses/by/4.0/
# ---------------------------------------------------------------------------

echo "Starting wait-for-db.sh"
echo "Host: $DB_HOST, Port: 3306, User: $DB_USERNAME"

# Wait until the database is available
while ! mysql --skip-ssl -h"$DB_HOST" -P"3306" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e 'SELECT 1' 2>/dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done

echo "MySQL is up - executing command"
exec "$@"
