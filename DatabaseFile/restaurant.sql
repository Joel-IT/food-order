-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 20. Nov 2024 um 13:48
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
                                                             (1, 'Burger', 'images/Burger_selber_machen_rezept.jpg', 0.00),
                                                             (2, 'Pizza', 'images/Pizza-Salami-1200x900.jpg', 0.00),
                                                             (3, 'Pasta', 'images/nudeln-tomaten-sahnesosse.webp', 0.00),
                                                             (4, 'Sushi', 'images/shutterstock_sushi-aufmacher-2640.webp', 0.00),
                                                             (5, 'Salad', 'images/Epic-summer-salad-hub-2646e6e.jpg', 0.00),
                                                             (6, 'Steak', 'images/AR-14554-sirloin-steak-with-garlic-butter-hero-4x3-d12fa79836754fcf850388e4677bbf55.jpg', 0.00);

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
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

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
