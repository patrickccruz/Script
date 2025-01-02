CREATE DATABASE IF NOT EXISTS sou_digital;

USE sou_digital;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  username VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL -- A senha deve ser criptografada antes de ser inserida aqui
);

DELIMITER //

CREATE TRIGGER before_insert_users
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  SET NEW.password = bcrypt(NEW.password);
END; //

DELIMITER ;