UPDATE `gaz_config` SET `cvalue` = '135' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='staff_report.php'), 'pay_salary.php', '', '', 3, '', 20  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='staff_report.php'), 'report_pay_salary.php', '', '', 4, '', 30  FROM `gaz_menu_script`;
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT IGNORE INTO `gaz_XXXcaucon` (`codice`, `descri`, `insdoc`, `regiva`, `operat`, `pay_schedule`) VALUES ('BBH', 'BONIFICO SALARI & STIPENDI', 1, 0, 0, 0),('BBA', 'PAGATO FORNITORE CON BONIFICO', 1, 0, 0, 0);
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )