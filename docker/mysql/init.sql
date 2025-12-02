CREATE DATABASE IF NOT EXISTS hom_eat;
USE hom_eat;

DROP USER IF EXISTS 'hom_eat_user'@'%';
CREATE USER 'hom_eat_user'@'%' IDENTIFIED BY 'hom_eat_password';
GRANT ALL PRIVILEGES ON hom_eat.* TO 'hom_eat_user'@'%';
FLUSH PRIVILEGES;
