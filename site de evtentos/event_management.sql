-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29-Dez-2025 às 18:48
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `event_management`
--

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `admin_dashboard`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `admin_dashboard` (
`total_active_events` bigint(21)
,`upcoming_events_count` bigint(21)
,`total_customers` bigint(21)
,`total_purchases` bigint(21)
,`total_revenue` decimal(32,2)
,`monthly_revenue` decimal(32,2)
,`daily_revenue` decimal(32,2)
,`total_tickets_sold` decimal(32,0)
,`total_tickets_available` decimal(32,0)
,`avg_purchase_value` decimal(14,6)
,`avg_tickets_per_purchase` decimal(14,4)
,`most_popular_event` varchar(200)
,`top_customer` varchar(100)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `event_id`, `quantity`, `added_at`) VALUES
(3, 6, 19, 1, '2025-12-10 11:25:31'),
(4, 6, 14, 1, '2025-12-10 11:25:44');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `customer_sales_summary`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `customer_sales_summary` (
`customer_id` int(11)
,`customer_name` varchar(100)
,`email` varchar(100)
,`phone` varchar(20)
,`registration_date` timestamp
,`total_purchases` bigint(21)
,`events_attended` bigint(21)
,`total_tickets_purchased` decimal(32,0)
,`total_spent` decimal(32,2)
,`avg_purchase_value` decimal(14,6)
,`first_purchase_date` timestamp
,`last_purchase_date` timestamp
,`customer_tier` varchar(11)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL,
  `available_tickets` int(11) NOT NULL DEFAULT 100,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','cancelled','completed') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `ticket_price`, `available_tickets`, `image`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Festival de Música 2025', 'Grande festival de música com artistas nacionais e internacionais', '2025-12-15', '18:00:00', 'Parque das Nações, Lisboa', 45.00, 1000, 'rock_concert.jpg', 'active', 1, '2025-11-16 14:54:45', '2025-12-10 11:13:36'),
(2, 'Conferência Tech Summit', 'Conferência sobre as últimas tendências em tecnologia e inovação', '2025-11-25', '09:00:00', 'Centro de Congressos, Porto', 75.00, 500, 'tech_conference.jpg', 'active', 1, '2025-11-16 14:54:45', '2025-12-10 11:13:36'),
(3, 'Show de Comédia Stand-Up', 'Noite de comédia com os melhores comediantes portugueses', '2025-11-30', '21:00:00', 'Teatro Municipal, Coimbra', 20.00, 300, 'theater_play.jpg', 'active', 1, '2025-11-16 14:54:45', '2025-12-10 11:13:36'),
(4, 'Exposição de Arte Moderna', 'Exposição com obras de artistas contemporâneos', '2025-12-01', '10:00:00', 'Museu de Arte, Lisboa', 12.00, 200, 'photo_exhibition.jpg', 'active', 1, '2025-11-16 14:54:45', '2025-12-10 11:13:36'),
(5, 'Maratona de Lisboa', 'Corrida internacional com percurso pela cidade', '2026-03-15', '08:00:00', 'Centro de Lisboa', 25.00, 5000, 'marathon.jpg', 'active', 1, '2025-11-16 14:54:45', '2025-12-10 11:13:36'),
(6, 'Rock in Rio Lisboa 2026', 'O maior festival de música do mundo regressa a Lisboa com artistas internacionais de renome. Três dias de música, diversão e entretenimento para toda a família.', '2026-06-20', '16:00:00', 'Parque da Bela Vista, Lisboa', 85.00, 2000, 'rock_concert.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(7, 'Concerto de Orquestra Sinfónica', 'Noite especial com a Orquestra Sinfónica Portuguesa interpretando obras de Mozart, Beethoven e compositores portugueses. Uma experiência cultural inesquecível.', '2026-01-15', '20:30:00', 'Casa da Música, Porto', 35.00, 800, 'orchestra.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(8, 'Festival de Jazz no Jardim', 'Três dias de jazz ao ar livre com artistas nacionais e internacionais. Ambiente descontraído num dos jardins mais bonitos da cidade.', '2026-07-10', '19:00:00', 'Jardim da Estrela, Lisboa', 30.00, 1500, 'jazz_festival.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(9, 'Noite Electrónica - DJ Internacional', 'A melhor música electrónica com DJs de renome mundial. Produção de luz e som de última geração para uma noite inesquecível.', '2026-02-28', '23:00:00', 'Pavilhão Multiusos, Guimarães', 40.00, 3000, 'electronic_music.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(10, 'Festival Gastronómico de Portugal', 'Descubra os sabores de Portugal num festival que reúne os melhores chefs e restaurantes do país. Workshops, showcooking e muito mais.', '2026-05-15', '12:00:00', 'Ribeira das Naus, Lisboa', 15.00, 5000, 'food_festival.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(11, 'Peça de Teatro - Os Lusíadas', 'Adaptação moderna da obra-prima de Camões. Uma produção espetacular com efeitos especiais e um elenco de renome.', '2026-03-20', '21:00:00', 'Teatro Nacional D. Maria II, Lisboa', 28.00, 400, 'theater_play.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(12, 'Exposição de Fotografia Contemporânea', 'Mostra de fotografia com obras de artistas portugueses e internacionais. Temas atuais e técnicas inovadoras.', '2026-04-01', '10:00:00', 'Centro Cultural de Belém, Lisboa', 8.00, 1000, 'photo_exhibition.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(13, 'Festival de Cinema Independente', 'Uma semana dedicada ao cinema independente com filmes de todo o mundo, debates com realizadores e workshops.', '2026-09-05', '14:00:00', 'Cinema São Jorge, Lisboa', 12.00, 600, 'film_festival.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(14, 'Final da Taça de Portugal', 'Assista ao vivo à grande final da Taça de Portugal de Futebol. Emoção garantida no maior estádio do país.', '2026-05-25', '17:00:00', 'Estádio da Luz, Lisboa', 50.00, 60000, 'football_match.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(15, 'Meia Maratona do Porto', 'Participe na corrida mais bonita de Portugal. Percurso à beira-mar com paisagens deslumbrantes.', '2026-09-20', '09:00:00', 'Marginal do Porto', 20.00, 8000, 'marathon.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(16, 'Circo Mágico - Espetáculo Familiar', 'Espetáculo de circo com acrobatas, malabaristas, palhaços e muita magia. Diversão garantida para toda a família.', '2026-12-20', '15:00:00', 'Pavilhão Atlântico, Lisboa', 18.00, 4000, 'circus.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(17, 'Festival de Natal - Mercado e Concertos', 'Mercado de Natal tradicional com artesanato, gastronomia e concertos diários. Ambiente festivo para toda a família.', '2026-12-10', '10:00:00', 'Praça do Comércio, Lisboa', 5.00, 10000, 'christmas_market.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(18, 'Web Summit Portugal 2026', 'A maior conferência de tecnologia da Europa. Palestras com CEOs de empresas globais, startups e networking.', '2026-11-08', '09:00:00', 'FIL - Parque das Nações, Lisboa', 299.00, 70000, 'tech_conference.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(19, 'Workshop de Inteligência Artificial', 'Aprenda sobre IA, Machine Learning e suas aplicações práticas. Workshop hands-on com especialistas da área.', '2026-02-15', '14:00:00', 'ISCTE, Lisboa', 45.00, 150, 'ai_workshop.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(20, 'Retiro de Yoga e Meditação', 'Fim de semana de relaxamento com sessões de yoga, meditação e workshops de bem-estar. Inclui refeições vegetarianas.', '2026-04-25', '09:00:00', 'Quinta da Comporta, Setúbal', 120.00, 80, 'yoga_retreat.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39'),
(21, 'Festival de Dança Urbana', 'Competições de breakdance, hip-hop e danças urbanas. Workshops com coreógrafos internacionais.', '2026-08-15', '15:00:00', 'Parque das Nações, Lisboa', 15.00, 2000, 'dance_festival.jpg', 'active', 1, '2025-12-10 11:12:39', '2025-12-10 11:12:39');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `event_details`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `event_details` (
`id` int(11)
,`title` varchar(200)
,`description` text
,`event_date` date
,`event_time` time
,`location` varchar(255)
,`ticket_price` decimal(10,2)
,`available_tickets` int(11)
,`image` varchar(255)
,`status` enum('active','cancelled','completed')
,`created_at` timestamp
,`created_by_name` varchar(100)
,`tickets_sold` decimal(32,0)
,`total_revenue` decimal(32,2)
,`occupancy_percentage` decimal(38,2)
,`availability_status` varchar(16)
,`days_until_event` int(7)
,`time_status` varchar(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `event_sales_summary`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `event_sales_summary` (
`event_id` int(11)
,`event_name` varchar(200)
,`event_date` date
,`location` varchar(255)
,`ticket_price` decimal(10,2)
,`total_purchases` bigint(21)
,`unique_customers` bigint(21)
,`total_tickets_sold` decimal(32,0)
,`total_revenue` decimal(32,2)
,`avg_tickets_per_purchase` decimal(14,4)
,`first_sale_date` timestamp
,`last_sale_date` timestamp
,`tickets_remaining` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `popular_events`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `popular_events` (
`id` int(11)
,`title` varchar(200)
,`event_date` date
,`location` varchar(255)
,`ticket_price` decimal(10,2)
,`tickets_sold` decimal(32,0)
,`revenue` decimal(32,2)
,`unique_buyers` bigint(21)
,`popularity_rank` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed',
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `event_id`, `quantity`, `total_price`, `purchase_date`, `status`, `payment_method`) VALUES
(1, 2, 1, 2, 90.00, '2025-11-16 14:54:45', 'confirmed', 'Cartão de Crédito'),
(3, 2, 3, 3, 60.00, '2025-11-16 14:54:45', 'confirmed', 'PayPal');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `tickets_status`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `tickets_status` (
`event_id` int(11)
,`event_name` varchar(200)
,`event_date` date
,`event_status` enum('active','cancelled','completed')
,`initial_capacity` decimal(33,0)
,`tickets_sold` decimal(32,0)
,`tickets_pending` decimal(32,0)
,`tickets_cancelled` decimal(32,0)
,`tickets_available` int(11)
,`percentage_sold` decimal(38,2)
,`stock_status` varchar(10)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `upcoming_events`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `upcoming_events` (
`id` int(11)
,`title` varchar(200)
,`description` text
,`event_date` date
,`event_time` time
,`location` varchar(255)
,`ticket_price` decimal(10,2)
,`available_tickets` int(11)
,`image` varchar(255)
,`days_until` int(7)
,`urgency_label` varchar(11)
,`tickets_sold` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@eventos.pt', '$2y$10$7/5yH2moqtMA2h59QctWiu8nLwqeDnJtAXrJrG2/KzbaHclo/pzLu', '', 'Porto', 'admin', '2025-11-16 14:54:44', '2025-12-13 18:29:30'),
(2, 'João Silva', 'joao@email.pt', '$2y$10$K6zyfKNjCooa3aK/w/bkN.ODuBp7Cfnw3pVcoUUTnENlNTJk9oOZO', '912345678', 'porto, Portugal', 'user', '2025-11-16 14:54:44', '2025-11-16 14:59:05'),
(6, 'Sérgio Silva', 'sergiocost86@gmail.com', '$2y$10$W/q/XyLNPjVskt5Reh94Ie9QUfpqrde/J1KXVQ2z/bRCxqBPh0Fa6', '224542857', 'avenida', 'user', '2025-12-10 11:23:30', '2025-12-10 11:23:30');

-- --------------------------------------------------------

--
-- Estrutura para vista `admin_dashboard`
--
DROP TABLE IF EXISTS `admin_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `admin_dashboard`  AS SELECT (select count(0) from `events` where `events`.`status` = 'active') AS `total_active_events`, (select count(0) from `events` where `events`.`status` = 'active' and `events`.`event_date` >= curdate()) AS `upcoming_events_count`, (select count(0) from `users` where `users`.`role` = 'user') AS `total_customers`, (select count(0) from `purchases` where `purchases`.`status` = 'confirmed') AS `total_purchases`, (select coalesce(sum(`purchases`.`total_price`),0) from `purchases` where `purchases`.`status` = 'confirmed') AS `total_revenue`, (select coalesce(sum(`purchases`.`total_price`),0) from `purchases` where `purchases`.`status` = 'confirmed' and month(`purchases`.`purchase_date`) = month(curdate())) AS `monthly_revenue`, (select coalesce(sum(`purchases`.`total_price`),0) from `purchases` where `purchases`.`status` = 'confirmed' and cast(`purchases`.`purchase_date` as date) = curdate()) AS `daily_revenue`, (select coalesce(sum(`purchases`.`quantity`),0) from `purchases` where `purchases`.`status` = 'confirmed') AS `total_tickets_sold`, (select coalesce(sum(`events`.`available_tickets`),0) from `events` where `events`.`status` = 'active') AS `total_tickets_available`, (select coalesce(avg(`purchases`.`total_price`),0) from `purchases` where `purchases`.`status` = 'confirmed') AS `avg_purchase_value`, (select coalesce(avg(`purchases`.`quantity`),0) from `purchases` where `purchases`.`status` = 'confirmed') AS `avg_tickets_per_purchase`, (select `e`.`title` from (`events` `e` left join `purchases` `p` on(`e`.`id` = `p`.`event_id`)) where `p`.`status` = 'confirmed' group by `e`.`id`,`e`.`title` order by sum(`p`.`quantity`) desc limit 1) AS `most_popular_event`, (select `u`.`name` from (`users` `u` left join `purchases` `p` on(`u`.`id` = `p`.`user_id`)) where `p`.`status` = 'confirmed' group by `u`.`id`,`u`.`name` order by sum(`p`.`total_price`) desc limit 1) AS `top_customer` ;

-- --------------------------------------------------------

--
-- Estrutura para vista `customer_sales_summary`
--
DROP TABLE IF EXISTS `customer_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `customer_sales_summary`  AS SELECT `u`.`id` AS `customer_id`, `u`.`name` AS `customer_name`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`created_at` AS `registration_date`, count(distinct `p`.`id`) AS `total_purchases`, count(distinct `p`.`event_id`) AS `events_attended`, coalesce(sum(`p`.`quantity`),0) AS `total_tickets_purchased`, coalesce(sum(`p`.`total_price`),0) AS `total_spent`, coalesce(avg(`p`.`total_price`),0) AS `avg_purchase_value`, min(`p`.`purchase_date`) AS `first_purchase_date`, max(`p`.`purchase_date`) AS `last_purchase_date`, CASE WHEN coalesce(sum(`p`.`total_price`),0) >= 500 THEN 'VIP' WHEN coalesce(sum(`p`.`total_price`),0) >= 200 THEN 'Premium' WHEN coalesce(sum(`p`.`total_price`),0) >= 50 THEN 'Regular' WHEN coalesce(sum(`p`.`total_price`),0) > 0 THEN 'Novo' ELSE 'Sem Compras' END AS `customer_tier` FROM (`users` `u` left join `purchases` `p` on(`u`.`id` = `p`.`user_id` and `p`.`status` = 'confirmed')) WHERE `u`.`role` = 'user' GROUP BY `u`.`id`, `u`.`name`, `u`.`email`, `u`.`phone`, `u`.`created_at` ORDER BY coalesce(sum(`p`.`total_price`),0) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para vista `event_details`
--
DROP TABLE IF EXISTS `event_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `event_details`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`description` AS `description`, `e`.`event_date` AS `event_date`, `e`.`event_time` AS `event_time`, `e`.`location` AS `location`, `e`.`ticket_price` AS `ticket_price`, `e`.`available_tickets` AS `available_tickets`, `e`.`image` AS `image`, `e`.`status` AS `status`, `e`.`created_at` AS `created_at`, `u`.`name` AS `created_by_name`, coalesce(sum(`p`.`quantity`),0) AS `tickets_sold`, coalesce(sum(`p`.`total_price`),0) AS `total_revenue`, round(coalesce(sum(`p`.`quantity`),0) / (`e`.`available_tickets` + coalesce(sum(`p`.`quantity`),0)) * 100,2) AS `occupancy_percentage`, CASE WHEN `e`.`available_tickets` = 0 THEN 'Esgotado' WHEN `e`.`available_tickets` < 50 THEN 'Últimos Bilhetes' ELSE 'Disponível' END AS `availability_status`, to_days(`e`.`event_date`) - to_days(curdate()) AS `days_until_event`, CASE WHEN `e`.`event_date` < curdate() THEN 'Passado' WHEN `e`.`event_date` = curdate() THEN 'Hoje' WHEN to_days(`e`.`event_date`) - to_days(curdate()) <= 7 THEN 'Esta Semana' WHEN to_days(`e`.`event_date`) - to_days(curdate()) <= 30 THEN 'Este Mês' ELSE 'Futuro' END AS `time_status` FROM ((`events` `e` left join `users` `u` on(`e`.`created_by` = `u`.`id`)) left join `purchases` `p` on(`e`.`id` = `p`.`event_id` and `p`.`status` = 'confirmed')) GROUP BY `e`.`id`, `e`.`title`, `e`.`description`, `e`.`event_date`, `e`.`event_time`, `e`.`location`, `e`.`ticket_price`, `e`.`available_tickets`, `e`.`image`, `e`.`status`, `e`.`created_at`, `u`.`name` ;

-- --------------------------------------------------------

--
-- Estrutura para vista `event_sales_summary`
--
DROP TABLE IF EXISTS `event_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `event_sales_summary`  AS SELECT `e`.`id` AS `event_id`, `e`.`title` AS `event_name`, `e`.`event_date` AS `event_date`, `e`.`location` AS `location`, `e`.`ticket_price` AS `ticket_price`, count(distinct `p`.`id`) AS `total_purchases`, count(distinct `p`.`user_id`) AS `unique_customers`, coalesce(sum(`p`.`quantity`),0) AS `total_tickets_sold`, coalesce(sum(`p`.`total_price`),0) AS `total_revenue`, coalesce(avg(`p`.`quantity`),0) AS `avg_tickets_per_purchase`, min(`p`.`purchase_date`) AS `first_sale_date`, max(`p`.`purchase_date`) AS `last_sale_date`, `e`.`available_tickets` AS `tickets_remaining` FROM (`events` `e` left join `purchases` `p` on(`e`.`id` = `p`.`event_id` and `p`.`status` = 'confirmed')) GROUP BY `e`.`id`, `e`.`title`, `e`.`event_date`, `e`.`location`, `e`.`ticket_price`, `e`.`available_tickets` ORDER BY coalesce(sum(`p`.`total_price`),0) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para vista `popular_events`
--
DROP TABLE IF EXISTS `popular_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `popular_events`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`event_date` AS `event_date`, `e`.`location` AS `location`, `e`.`ticket_price` AS `ticket_price`, coalesce(sum(`p`.`quantity`),0) AS `tickets_sold`, coalesce(sum(`p`.`total_price`),0) AS `revenue`, count(distinct `p`.`user_id`) AS `unique_buyers`, rank() over ( order by coalesce(sum(`p`.`quantity`),0) desc) AS `popularity_rank` FROM (`events` `e` left join `purchases` `p` on(`e`.`id` = `p`.`event_id` and `p`.`status` = 'confirmed')) WHERE `e`.`status` = 'active' GROUP BY `e`.`id`, `e`.`title`, `e`.`event_date`, `e`.`location`, `e`.`ticket_price` HAVING `tickets_sold` > 0 ORDER BY coalesce(sum(`p`.`quantity`),0) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para vista `tickets_status`
--
DROP TABLE IF EXISTS `tickets_status`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tickets_status`  AS SELECT `e`.`id` AS `event_id`, `e`.`title` AS `event_name`, `e`.`event_date` AS `event_date`, `e`.`status` AS `event_status`, `e`.`available_tickets`+ coalesce(sum(`p`.`quantity`),0) AS `initial_capacity`, coalesce(sum(case when `p`.`status` = 'confirmed' then `p`.`quantity` else 0 end),0) AS `tickets_sold`, coalesce(sum(case when `p`.`status` = 'pending' then `p`.`quantity` else 0 end),0) AS `tickets_pending`, coalesce(sum(case when `p`.`status` = 'cancelled' then `p`.`quantity` else 0 end),0) AS `tickets_cancelled`, `e`.`available_tickets` AS `tickets_available`, round(coalesce(sum(case when `p`.`status` = 'confirmed' then `p`.`quantity` else 0 end),0) / (`e`.`available_tickets` + coalesce(sum(`p`.`quantity`),0)) * 100,2) AS `percentage_sold`, CASE WHEN `e`.`available_tickets` = 0 THEN 'ESGOTADO' WHEN `e`.`available_tickets` < 10 THEN 'CRÍTICO' WHEN `e`.`available_tickets` < 50 THEN 'BAIXO' ELSE 'DISPONÍVEL' END AS `stock_status` FROM (`events` `e` left join `purchases` `p` on(`e`.`id` = `p`.`event_id`)) GROUP BY `e`.`id`, `e`.`title`, `e`.`event_date`, `e`.`status`, `e`.`available_tickets` ORDER BY `e`.`event_date` ASC ;

-- --------------------------------------------------------

--
-- Estrutura para vista `upcoming_events`
--
DROP TABLE IF EXISTS `upcoming_events`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `upcoming_events`  AS SELECT `e`.`id` AS `id`, `e`.`title` AS `title`, `e`.`description` AS `description`, `e`.`event_date` AS `event_date`, `e`.`event_time` AS `event_time`, `e`.`location` AS `location`, `e`.`ticket_price` AS `ticket_price`, `e`.`available_tickets` AS `available_tickets`, `e`.`image` AS `image`, to_days(`e`.`event_date`) - to_days(curdate()) AS `days_until`, CASE WHEN to_days(`e`.`event_date`) - to_days(curdate()) = 0 THEN 'HOJE' WHEN to_days(`e`.`event_date`) - to_days(curdate()) = 1 THEN 'AMANHÃ' WHEN to_days(`e`.`event_date`) - to_days(curdate()) <= 7 THEN 'ESTA SEMANA' WHEN to_days(`e`.`event_date`) - to_days(curdate()) <= 30 THEN 'ESTE MÊS' ELSE 'PRÓXIMO' END AS `urgency_label`, coalesce(sum(`p`.`quantity`),0) AS `tickets_sold` FROM (`events` `e` left join `purchases` `p` on(`e`.`id` = `p`.`event_id` and `p`.`status` = 'confirmed')) WHERE `e`.`status` = 'active' AND `e`.`event_date` >= curdate() GROUP BY `e`.`id`, `e`.`title`, `e`.`description`, `e`.`event_date`, `e`.`event_time`, `e`.`location`, `e`.`ticket_price`, `e`.`available_tickets`, `e`.`image` ORDER BY `e`.`event_date` ASC, `e`.`event_time` ASC LIMIT 0, 10 ;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_event` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Índices para tabela `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
