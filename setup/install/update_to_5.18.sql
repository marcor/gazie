UPDATE `gaz_config` SET `cvalue` = '77' WHERE `id` =2;
UPDATE `gaz_001tesmov` SET id_doc = ( SELECT `gaz_001tesdoc`.id_tes FROM `gaz_001tesdoc` WHERE `gaz_001tesmov`.id_tes = `gaz_001tesdoc`.id_con LIMIT 1) WHERE `id_doc`=0;
