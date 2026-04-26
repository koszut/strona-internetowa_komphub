SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




CREATE TABLE `kategorie` (
  `category_id` int(11) NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `opis` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `kategorie` (`category_id`, `nazwa`, `opis`) VALUES
(1, 'Komputery stacjonarne', 'Gotowe zestawy PC do domu i biura'),
(2, 'Laptopy', 'Laptopy do pracy, nauki i gamingu'),
(3, 'Podzespoły komputerowe', 'Procesory, karty graficzne, RAM, dyski'),
(4, 'Urządzenia peryferyjne', 'Myszki, klawiatury, monitory, słuchawki');



CREATE TABLE `oceny` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ocena` tinyint(4) NOT NULL CHECK (`ocena` between 1 and 5),
  `komentarz` text DEFAULT NULL,
  `kiedy_dodany` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `oceny` (`review_id`, `product_id`, `user_id`, `ocena`, `komentarz`, `kiedy_dodany`) VALUES
(10, 7, 1, 5, 'ekstra bluza', '2025-11-28 05:53:44');

-- --------------------------------------------------------



CREATE TABLE `produkty` (
  `product_id` int(11) NOT NULL,
  `nazwa` varchar(200) NOT NULL,
  `opis` text DEFAULT NULL,
  `zdjecie` varchar(150) DEFAULT NULL,
  `cena` decimal(10,2) NOT NULL,
  `kategoria` int(11) DEFAULT NULL,
  `ilosc` int(11) DEFAULT 0,
  `aktywny` tinyint(1) DEFAULT 1,
  `data_utworzenia` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `produkty` (`product_id`, `nazwa`, `opis`, `zdjecie`, `cena`, `kategoria`, `ilosc`, `aktywny`, `data_utworzenia`) VALUES
(1, 'Komputer gamingowy RTX 4060', 'Wydajny komputer do gier z kartą RTX 4060, 16GB RAM i szybkim dyskiem SSD.', '6.webp', 4999.99, 1, 50, 1, NOW()),
(2, 'Komputer biurowy Intel i5', 'Zestaw komputerowy idealny do pracy biurowej i nauki.', '8.webp', 2499.99, 1, 80, 1, NOW()),
(3, 'Laptop Lenovo IdeaPad 5', 'Lekki laptop do codziennych zastosowań, 16GB RAM, SSD 512GB.', '7.webp', 3299.99, 2, 60, 1, NOW()),
(4, 'Laptop gamingowy ASUS TUF', 'Laptop gamingowy z RTX 4050 i procesorem Ryzen 7.', '3.webp', 5799.99, 2, 40, 1, NOW()),
(5, 'Karta graficzna RTX 4070', 'Nowoczesna karta graficzna do gier i pracy graficznej.', '5.webp', 2999.99, 3, 30, 1, NOW()),
(6, 'Procesor Intel Core i7', 'Wydajny procesor do wymagających zastosowań.', '2.webp', 1899.99, 3, 45, 1, NOW()),
(7, 'Mysz gamingowa Logitech', 'Precyzyjna mysz dla graczy z podświetleniem RGB.', '1.webp', 199.99, 4, 100, 1, NOW()),
(8, 'Klawiatura mechaniczna RGB', 'Klawiatura dla graczy z przełącznikami mechanicznymi.', '4.webp', 299.99, 4, 90, 1, NOW());




CREATE TABLE `statystyki_oceny` (
  `product_id` int(11) NOT NULL,
  `srednia_ocena` decimal(3,2) DEFAULT 0.00,
  `ilosc_ocen` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `statystyki_oceny` (`product_id`, `srednia_ocena`, `ilosc_ocen`) VALUES
(7, 5.00, 1);



CREATE TABLE `uzytkownicy` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `haslo_hash` varchar(250) NOT NULL,
  `login` varchar(100) NOT NULL,
  `rola` enum('user','admin') DEFAULT 'user',
  `data_utworzenia` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `uzytkownicy` (`user_id`, `email`, `haslo_hash`, `login`, `rola`, `data_utworzenia`) VALUES
(1, 'piotrblaszczyk67@gmail.com', '$2y$10$HVKqax1oEH6zLymZRz3qNO7drRRmx0s9.AP2PsJJ.15edDnzUTbNm', 'Piotrasss', 'admin', '2025-11-28 02:05:09');



CREATE TABLE `zamowienia` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `kwota_suma` decimal(10,2) NOT NULL,
  `status_zamowienia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'oczekujace',
  `data_zamowienia` datetime DEFAULT current_timestamp(),
  `adres_dostawy` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `zamowienia` (`order_id`, `user_id`, `kwota_suma`, `status_zamowienia`, `data_zamowienia`, `adres_dostawy`) VALUES
(37, 1, 159.96, 'opłacone', '2025-11-28 05:25:59', 'Weronika12..');



CREATE TABLE `zamowienia_zawartosc` (
  `id` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ilosc` int(11) NOT NULL,
  `cena` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `zamowienia_zawartosc` (`id`, `order_id`, `product_id`, `ilosc`, `cena`) VALUES
(NULL, 37, 8, 4, 39.99);


ALTER TABLE `kategorie`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `nazwa` (`nazwa`);


ALTER TABLE `oceny`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `warunek` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);


ALTER TABLE `produkty`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `kategoria` (`kategoria`);



ALTER TABLE `statystyki_oceny`
  ADD PRIMARY KEY (`product_id`);


ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `zamowienia`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);




ALTER TABLE `kategorie`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;


ALTER TABLE `oceny`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;


ALTER TABLE `produkty`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;


ALTER TABLE `uzytkownicy`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;


ALTER TABLE `zamowienia`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;




ALTER TABLE `oceny`
  ADD CONSTRAINT `oceny_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `produkty` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oceny_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `uzytkownicy` (`user_id`) ON DELETE CASCADE;


ALTER TABLE `produkty`
  ADD CONSTRAINT `produkty_ibfk_1` FOREIGN KEY (`kategoria`) REFERENCES `kategorie` (`category_id`) ON DELETE CASCADE;


ALTER TABLE `statystyki_oceny`
  ADD CONSTRAINT `statystyki_oceny_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `produkty` (`product_id`) ON DELETE CASCADE;


ALTER TABLE `zamowienia`
  ADD CONSTRAINT `zamowienia_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `uzytkownicy` (`user_id`);
COMMIT;

ALTER TABLE `zamowienia_zawartosc`
  MODIFY `id` INT NULL;


