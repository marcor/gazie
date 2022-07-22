UPDATE `gaz_config` SET `cvalue` = '149' WHERE `id` =2;
UPDATE `gaz_breadcrumb` SET `file`='config/dash_numclick_widget.php' WHERE `file`='root/dash_numclick_widget.php';
UPDATE `gaz_breadcrumb` SET `file`='config/dash_lastclick_widget.php' WHERE `file`='root/dash_lastclick_widget.php';
UPDATE `gaz_breadcrumb` SET `file`='config/dash_company_widget.php' WHERE `file`='root/dash_company_widget.php';
UPDATE `gaz_breadcrumb` SET `file`='config/dash_user_widget.php' WHERE `file`='root/dash_user_widget.php';
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione )
