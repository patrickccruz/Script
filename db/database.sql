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

-- Tabelas do Blog

-- Tabela para posts do blog
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    imagem_capa VARCHAR(255) NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela para links dos posts
CREATE TABLE IF NOT EXISTS blog_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
);

-- Tabela para comentários
CREATE TABLE IF NOT EXISTS blog_comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comentario TEXT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela para reações
CREATE TABLE IF NOT EXISTS blog_reacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    tipo_reacao ENUM('curtir', 'amar', 'rir', 'surpreso', 'triste', 'bravo') NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reacao (post_id, user_id)
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