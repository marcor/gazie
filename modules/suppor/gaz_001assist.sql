-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Feb 23, 2015 alle 18:13
-- Versione del server: 5.5.42-cll
-- Versione PHP: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hopnbxoo_gazie`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `gaz_001assist`
--

CREATE TABLE IF NOT EXISTS `gaz_001assist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` int(10) NOT NULL,
  `utente` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `tecnico` varchar(50) NOT NULL,
  `oggetto` varchar(80) NOT NULL,
  `descrizione` text NOT NULL,
  `clfoco` int(9) NOT NULL,
  `ore` decimal(6,2) NOT NULL,
  `stato` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=82 ;

INSERT INTO `gaz_001assist` (`id`, `codice`, `utente`, `data`, `tecnico`, `oggetto`, `descrizione`, `clfoco`, `ore`, `stato`) VALUES
(1, 12, '', '2015-02-01', '', 'Prova2', 'Prova2', 103000001, '1.00', 'aperto'),
(2, 13, '', '2015-01-30', '', 'Prova1', 'Prova1', 103000001, '1.00', 'chiuso');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
