CREATE DATABASE IF NOT EXISTS sou_digital;

USE sou_digital;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  username VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL, -- A senha deve ser criptografada antes de ser inserida aqui
  profile_image VARCHAR(255), -- Adiciona a coluna para armazenar o caminho da imagem de perfil
  is_admin BOOLEAN DEFAULT FALSE -- Coluna para controle de acesso administrativo
);

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  data_chamado DATE NOT NULL,
  numero_chamado INT NOT NULL,
  cliente VARCHAR(255) NOT NULL,
  nome_informante VARCHAR(255) NOT NULL,
  quantidade_patrimonios INT NOT NULL,
  km_inicial INT NOT NULL,
  km_final INT NOT NULL,
  hora_chegada TIME NOT NULL,
  hora_saida TIME NOT NULL,
  endereco_partida VARCHAR(255) NOT NULL,
  endereco_chegada VARCHAR(255) NOT NULL,
  informacoes_adicionais TEXT,
  arquivo_path VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS reembolsos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  data_chamado DATE NOT NULL,
  numero_chamado INT NOT NULL,
  informacoes_adicionais TEXT,
  arquivo_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

DELIMITER //

CREATE TRIGGER before_insert_users
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  -- Remover a linha que usa bcrypt
  -- SET NEW.password = bcrypt(NEW.password);
END; //

DELIMITER ;