-- `gaz_config` SET `cvalue` = '129' WHERE `id` =2; 

SET @id_mod := 0;
SELECT @id_mod := `id` FROM  `gaz_module` WHERE `name` = 'camp' LIMIT 1;
UPDATE `gaz_menu_module` SET `link` = 'camp_report_artico.php' WHERE `id_module` = @id_mod AND `link`= 'report_artico.php';
UPDATE `gaz_menu_module` SET `link` = 'camp_report_catmer.php' WHERE `id_module` = @id_mod AND `link`= 'report_catmer.php';
UPDATE `gaz_menu_module` SET `link` = 'camp_report_movmag.php' WHERE `id_module` = @id_mod AND `link`= 'report_movmag.php';
UPDATE `gaz_menu_module` SET `link` = 'camp_report_caumag.php' WHERE `id_module` = @id_mod AND `link`= 'report_caumag.php';
SET @id_menu_mod := 0;
SELECT  @id__menu_mod := `id` FROM  `gaz_menu_module` WHERE `link` = 'camp_report_artico.php' LIMIT 1;
UPDATE `gaz_menu_script` SET `link` = 'camp_admin_artico.php?Insert' WHERE `id_menu` = @id_menu_mod AND `link`= 'admin_artico.php?Insert';
UPDATE `gaz_menu_script` SET `link` = 'camp_inventory_stock.php' WHERE `id_menu` = @id_menu_mod AND `link`= 'inventory_stock.php';
UPDATE `gaz_menu_script` SET `link` = 'camp_stampa_invmag.php' WHERE `id_menu` = @id_menu_mod AND `link`= 'stampa_invmag.php';
UPDATE `gaz_menu_script` SET `link` = 'camp_browse_document.php' WHERE `id_menu` = @id_menu_mod AND `link`= 'browse_document.php';
SELECT  @id__menu_mod := `id` FROM  `gaz_menu_module` WHERE `link` = 'camp_report_catmer.php' LIMIT 1;
UPDATE `gaz_menu_script` SET `link` = 'camp_admin_catmer.php?Insert' WHERE `id_menu` = @id_menu_mod AND `link`= 'admin_catmer.php?Insert';
SELECT  @id__menu_mod := `id` FROM  `gaz_menu_module` WHERE `link` = 'camp_report_movmag.php' LIMIT 1;
UPDATE `gaz_menu_script` SET `link` = 'camp_admin_movmag.php?Insert' WHERE `id_menu` = @id_menu_mod AND `link`= 'admin_movmag.php?Insert';
UPDATE `gaz_menu_script` SET `link` = 'camp_select_schart.php' WHERE `id_menu` = @id_menu_mod AND `link`= 'select_schart.php';
UPDATE `gaz_menu_script` SET `link` = 'camp_select_giomag.php' WHERE `id_menu` = @id_menu_mod AND `link`= 'select_giomag.php';
SELECT  @id__menu_mod := `id` FROM  `gaz_menu_module` WHERE `link` = 'camp_report_caumag.php' LIMIT 1;
UPDATE `gaz_menu_script` SET `link` = 'camp_admin_caumag.php?Insert' WHERE `id_menu` = @id_menu_mod AND `link`= 'admin_caumag.php?Insert';

-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)