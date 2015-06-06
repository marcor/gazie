INSERT INTO `gaz_menu_module` SELECT MAX(id)+1, '8', 'ruburl.php', '', '', '6', '', '6'  FROM `gaz_menu_module`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'report_ruburl.php', '', '', '4', '', '1'  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MAX(id) FROM `gaz_menu_module`), 'admin_ruburl.php?Insert', '', '', '5', '', '2'  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` (`id`, `description`, `var`, `val`) VALUES (SELECT MAX(id)+1, 'Gazie - Gestionale', 'ruburl', 'http://gazie.sourceforge.net/');
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)