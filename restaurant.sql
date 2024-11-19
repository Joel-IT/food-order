-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 19. Nov 2024 um 17:37
-- Server-Version: 10.4.28-MariaDB
-- PHP-Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `restaurant`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `menu`
--

INSERT INTO `menu` (`id`, `title`, `image_url`, `price`) VALUES
(1, 'Burger', 'https://www.simplyrecipes.com/thmb/JG48hOXNhvVq3XtP-TkGxNKCoKg=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Simply-Recipes-Cheeseburger-Lead-4-bff2267bcf974ab988fb53cba97be4a5.jpg', 5.99),
(2, 'Pizza', 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Pizza_with_salami.jpg', 7.99),
(3, 'Pasta', 'https://www.bhg.com/thmb/NpcM7dD1zoqwlJZZ_V3eUyf1goQ=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/Spaghetti_pasta-2f206dff764b40658b049e871c62b06d.jpg', 6.49),
(4, 'Sushi', 'https://www.sushiworld.com.au/wp-content/uploads/2021/02/sushi_chef.jpg', 8.99),
(5, 'Salad', 'https://www.simplyrecipes.com/thmb/aXt-k1l8KkU01_Udfz7nWLzUbf8=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/SimplyRecipes-Salad-Image-9d6b0004ea144a62a84e3b8647eeb0ca.jpg', 4.99),
(6, 'Steak', 'https://cdn.pixabay.com/photo/2017/08/29/21/55/steak-2692952_960_720.jpg', 12.99);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `status` varchar(50) DEFAULT 'In Bearbeitung',
  `order_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `orders`
--

INSERT INTO `orders` (`id`, `table_number`, `menu_id`, `quantity`, `status`, `order_time`, `first_name`, `last_name`) VALUES
(7, 0, 1, 1, 'Zubereitet', '2024-11-19 16:34:18', 'test', 'test'),
(8, 0, 1, 1, 'In Bearbeitung', '2024-11-19 16:36:44', 'test', 'test');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
