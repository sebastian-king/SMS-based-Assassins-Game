# assassins installation script

mysql -e 'CREATE DATABASE assassins;'
mysql -e 'GRANT SELECT,UPDATE,INSERT,DELETE PRIVILEGES ON `assassins` . * TO "assassins_web_user"@"localhost" IDENTIFIED BY "HDR3Z22c6bWyNZwJdkeCPAWL";'

