CREATE USER 'evidentia'@'%' IDENTIFIED BY 'secret';
GRANT All PRIVILEGES ON *.* TO 'evidentia'@'%';
FLUSH PRIVILEGES;
DROP DATABASE  IF EXISTS `evidentia`;
CREATE DATABASE `evidentia` COLLATE 'utf8_general_ci';