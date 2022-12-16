CREATE USER 'database_user'@'%' IDENTIFIED BY 'database_password';
GRANT All PRIVILEGES ON *.* TO 'database_user'@'%';
FLUSH PRIVILEGES;

DROP DATABASE IF EXISTS `database_name`;
CREATE DATABASE `database_name` COLLATE `utf8_general_ci`;