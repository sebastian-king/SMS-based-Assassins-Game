# assassins installation script

CONFIG_FILE="config.php";

DATABASE_PASS=`sed -n '/DATABASE_PASS/p' "${CONFIG_FILE}" | awk -F',' '{print $2}' | cut -d '"' -f2`

mysql -e 'CREATE DATABASE assassins;'
mysql -e 'GRANT SELECT,UPDATE,INSERT,DELETE PRIVILEGES ON `assassins` . * TO "assassins_web_user"@"localhost" IDENTIFIED BY "${DATABASE_PASS}";'
