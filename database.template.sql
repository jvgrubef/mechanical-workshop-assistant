SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-03:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `AOM`
--

DELIMITER $$
--
-- Funções
--
CREATE FUNCTION `get_cash_balance` () RETURNS DECIMAL(65,2) DETERMINISTIC BEGIN
    DECLARE balance DECIMAL(65, 2);
    
    -- Soma os valores da tabela 'cash_book' para calcular o saldo
    SELECT SUM(amount) INTO balance
    FROM cash_book;
    
    RETURN balance;
END$$

CREATE FUNCTION `get_model_names_by_ids` (`ids` VARCHAR(255)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE result VARCHAR(255);
    SET ids = REPLACE(ids, ' ', '');
    SELECT GROUP_CONCAT(m.name SEPARATOR ', ') 
    INTO result
    FROM models AS m
    WHERE FIND_IN_SET(m.id, ids) > 0;

    RETURN result;
END$$

CREATE FUNCTION `get_month_balance` (`month` INT, `year` INT) RETURNS DECIMAL(65,2) DETERMINISTIC BEGIN
    DECLARE balance DECIMAL(65, 2);
    
    -- Soma os valores da tabela 'cash_book' filtrando pelo mês e ano
    SELECT SUM(amount) INTO balance
    FROM cash_book
    WHERE MONTH(transaction_date) = month 
    AND YEAR(transaction_date) = year;
    
    RETURN balance;
END$$

CREATE FUNCTION `tools_remove_accents` (`text` TEXT) RETURNS TEXT CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE accented_chars TEXT DEFAULT 'ÁÉÍÓÚÀÈÌÒÙÂÊÎÔÛÄËÏÖÜÃÕÑáéíóúàèìòùâêîôûäëïöüãõñçÇ';
    DECLARE unaccented_chars TEXT DEFAULT 'AEIOUAEIOUAEIOUAEIOUAONaeiouaeiouaeiouaeiouaoncC';

    WHILE i < CHAR_LENGTH(accented_chars) DO
        SET text = REPLACE(text, SUBSTRING(accented_chars, i+1, 1), SUBSTRING(unaccented_chars, i+1, 1));
        SET i = i + 1;
    END WHILE;

    RETURN text;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cash_book`
--

CREATE TABLE `cash_book` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(65,2) NOT NULL,
  `transaction_date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phones` longtext DEFAULT '[]',
  `address` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `models`
--

CREATE TABLE `models` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `order_date` date DEFAULT current_timestamp(),
  `items` longtext NOT NULL CHECK (json_valid(`items`)),
  `status` int(11) NOT NULL DEFAULT 0,
  `model` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `reminder_day` int(11) DEFAULT NULL,
  `reminder_type` int(11) NOT NULL,
  `reminder_date` date DEFAULT NULL,
  `reminder_category` int(11) NOT NULL,
  `reminder_deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `admin_level` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `image_path` varchar(200) NOT NULL DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `password_hash`, `admin_level`, `created_at`, `image_path`) VALUES
(1, 'admin', 'Administrador', 'User', '$2y$10$QOq9O1mMNt5FuUjXD02fGO9uMU1iroVwD/cPzuKsIN57/7tzF6nla', 3, NULL, 'default.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cash_book`
--
ALTER TABLE `cash_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Índices de tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Índices de tabela `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`title`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`username`);

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cash_book`
--
ALTER TABLE `cash_book`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
