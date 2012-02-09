UPDATE `gaz_config` SET `cvalue` = '79' WHERE `id` =2;

CREATE TABLE IF NOT EXISTS `gaz_currency` ( `id` int(11) NOT NULL AUTO_INCREMENT, `Currency` varchar(50) DEFAULT NULL, `Cambio` double(11,4) DEFAULT NULL, PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
ALTER TABLE `gaz_XXXcontract_row` DROP PRIMARY KEY , ADD PRIMARY KEY ( `id_row` , `id_contract` );

CREATE TABLE IF NOT EXISTS `gaz_XXXstaff` ( `id_staff` int(9) NOT NULL AUTO_INCREMENT, `id_clfoco` int(9) NOT NULL, `id_contract` int(9) NOT NULL, `Login_admin` varchar(20) DEFAULT NULL, `JObTitle` varchar(100) DEFAULT NULL, `EmploymentStatus` int(2) DEFAULT NULL, `JoinedDate` date NOT NULL DEFAULT '0000-00-00', `JobGrade` varchar(50) DEFAULT NULL, `Title` varchar(100) DEFAULT NULL, `Institution` varchar(100) DEFAULT NULL, `GraduationYear` date NOT NULL DEFAULT '0000-00-00', `StartDate` date NOT NULL DEFAULT '0000-00-00', `EndDate` date NOT NULL DEFAULT '0000-00-00', PRIMARY KEY (`id_staff`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `gaz_XXXstaff_lang` ( `id_lang` int(9) NOT NULL AUTO_INCREMENT, `id_staff` int(9) NOT NULL, `Language` varchar(50) DEFAULT NULL, `Speakinglevel` varchar(50) DEFAULT NULL, `Listeninglevel` varchar(50) DEFAULT NULL, `Writinglevel` varchar(50) DEFAULT NULL, `Readinglevel` varchar(50) DEFAULT NULL, PRIMARY KEY (`id_lang`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- STOP_WHILE( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query seguenti su tutte le aziende dell'installazione)