UPDATE `gaz_config` SET `cvalue` = '110' WHERE `id` =2;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_scontr.php'), 'admin_scontr_fast.php?tipdoc=VCO&Insert&Prezzo_IVA=S', '', '', 49, '', 6  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_broacq.php'), 'prop_ordine.php', '', '', 18, '', 3  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_artico.php'), 'situaz_magazz.php', '', '', 13, '', 4  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_doctra.php'), 'report_cmr.php', '', '', 51, '', 3  FROM `gaz_menu_script`;
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_doctra.php'), 'admin_cmr.php', '', '', 50, '', 4  FROM `gaz_menu_script`;
UPDATE `gaz_menu_script` SET `weight`='5' WHERE  `link`='admin_docacq.php?Insert&tipdoc=AFA';
UPDATE `gaz_menu_script` SET `weight`='10' WHERE  `link`='admin_docacq.php?Insert&tipdoc=AFC';
UPDATE `gaz_menu_script` SET `weight`='15' WHERE  `link`='accounting_documents.php?type=A';
UPDATE `gaz_menu_script` SET `weight`='20' WHERE  `link`='admin_assets.php?Insert';
INSERT INTO `gaz_menu_script` SELECT MAX(id)+1, (SELECT MIN(id) FROM `gaz_menu_module` WHERE `link`='report_docacq.php'), 'acquire_invoice.php', '', '', 19, '', 1  FROM `gaz_menu_script`;
DELETE FROM `gaz_admin_module` WHERE  `moduleid`=14;
CREATE TABLE IF NOT EXISTS `gaz_camp_fitofarmaci` (
  `NUMERO_REGISTRAZIONE` int(6) NOT NULL,
  `PRODOTTO` varchar(40) NOT NULL,
  `IMPRESA` varchar(30) NOT NULL,
  `SEDE_LEGALE_IMPRESA` varchar(30) NOT NULL,
  `SCADENZA_AUTORIZZAZIONE` varchar(12) NOT NULL,
  `INDICAZIONI_DI_PERICOLO` varchar(45) NOT NULL,
  `DESCRIZIONE_FORMULAZIONE` varchar(30) NOT NULL,
  `SOSTANZE_ATTIVE` varchar(30) NOT NULL,
  PRIMARY KEY (`NUMERO_REGISTRAZIONE`)
)  COMMENT='Viene utilizzato dal modulo Registro di campagna (camp) e serve per contenere la tabella del ministero della salute delle sostanze' ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `gaz_camp_avversita` (
  `id_avv` int(3) NOT NULL DEFAULT '0',
  `nome_avv` varchar(30) CHARACTER SET utf8 NOT NULL,
  `adminid` varchar(30) CHARACTER SET utf8 NOT NULL,
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avv`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `gaz_camp_avversita` (`id_avv`, `nome_avv`) VALUES (4, 'Minatori fogliari'),(2, 'Afidi'),(3, 'Tripidi'),(1, 'Psille'),(5, 'Antonomo'),(6, 'Tentredine'),(7, 'Metcalfe'),(8, 'Aleurodidi'),(9, 'Cicaline'),(10, 'Cocciniglie'),(11, 'Dorifore'),(12, 'Acari ragnetti'),(13, 'Cimici'),(14, 'Eriofidi'),(15, 'Nottue'),(16, 'Mamestra'),(17, 'Piralide'),(18, 'Cavolaie'),(19, 'Tignole'),(20, 'Margaronie'),(21, 'Carpocapse'),(22, 'Tortricidi'),(23, 'Limacce'),(24, 'Elateridi'),(25, 'Oidio'),(26, 'Botrytis cinerea (muffa grigia'),(27, 'Sclerotinia'),(28, 'Monilia'),(29, 'Pseudomonas (picchiettatura)'),(30, 'Peronospora'),(31, 'Corineo'),(32, 'Fumaggine'),(33, 'Lebbra'),(34, 'Occhio di pavone'),(35, 'Batteriosi'),(36, 'Antracnosi'),(37, 'Alternaria'),(38, 'Ruggine'),(39, 'Mosca olivo dacus oleae'),(40, 'Rogna'),(41, 'Cancrena pedale'),(42, 'Septoriosi'),(43, 'Cladosporiosi'),(44, 'Marciume apicale');
CREATE TABLE IF NOT EXISTS `gaz_camp_colture` (
  `id_colt` int(3) NOT NULL DEFAULT '0',
  `nome_colt` varchar(30) NOT NULL,
  `adminid` varchar(30) NOT NULL,
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_colt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `gaz_camp_colture` (`id_colt`, `nome_colt`) VALUES (4, 'Lattughe'),(1, 'Asparagi'),(2, 'Carote'),(3, 'Broccoli'),(5, 'Pomodori'),(6, 'Peperoni'),(7, 'Melanzane'),(8, 'Cucurbitacee'),(9, 'Carciofi'),(10, 'Cavoli'),(11, 'Patate'),(12, 'Fragole'),(13, 'Fagioli e fagiolini'),(14, 'Actinidia Kiwi'),(15, 'Agrumi'),(16, 'Olivi'),(17, 'Vite uva da vino e tavola'),(18, 'Mele'),(19, 'Pere'),(20, 'Cotogni'),(21, 'Albicocchi'),(22, 'Pesche'),(23, 'Susine'),(24, 'Ciliegie'),(25, 'Girasole'),(26, 'Frumento tenero'),(27, 'Frumento duro'),(28, 'Mais'),(29, 'Barbabietole da zucchero'),(30, 'Soia'),(31, 'Colza'),(32, 'Rape e rapanelli'),(33, 'Noccioli'),(34, 'Mandorli'),(35, 'Fichi'),(36, 'Fave'),(37, 'Farro'),(38, 'Orzo'),(39, 'Riso'),(40, 'Avena'),(41, 'Segale'),(42, 'Sorgo'),(43, 'Miglio'),(44, 'Peperoncini piccanti');
CREATE TABLE IF NOT EXISTS `gaz_camp_uso_fitofarmaci` (
  `id` int(4) NOT NULL,
  `cod_art` varchar(15) NOT NULL,
  `id_colt` int(3) NOT NULL,
  `id_avv` int(3) NOT NULL,
  `dose` decimal(8,3) NOT NULL,
  `tempo_sosp` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE `gaz_breadcrumb` (
	`id_bread` INT(11) NOT NULL AUTO_INCREMENT,
	`file` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'latin1_swedish_ci',
	`titolo` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'latin1_swedish_ci',
	`link` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'latin1_swedish_ci',
	INDEX `Indice 1` (`id_bread`)
)
COLLATE='utf8_general_ci' ENGINE=MyISAM AUTO_INCREMENT=3;
INSERT INTO `gaz_breadcrumb` (`id_bread`, `file`, `titolo`, `link`) VALUES (1, 'modules/root/admin_breadcrumb.php', 'Gestione tasti rapidi', '../../modules/root/admin_breadcrumb.php');
INSERT INTO `gaz_breadcrumb` (`id_bread`, `file`, `titolo`, `link`) VALUES (2, 'modules/magazz/admin_artico_compost.php', 'Gestione articoli composti', '../../modules/magazz/admin_artico_compost.php');
INSERT INTO `gaz_breadcrumb` (`id_bread`, `file`, `titolo`, `link`) VALUES (3, 'modules/magazz/admin_artico_compost.php', 'Gestione merci e servizi', '../../modules/magazz/report_artico.php');
INSERT INTO `gaz_config` (`description`, `variable`, `cvalue`) VALUES ('Utenti non amministratori (abilit < 8) possono accedere a tutte le aziende', 'users_noadmin_all_company', '0');
-- START_WHILE ( questo e' un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dell'installazione)
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Controlla regolarità indirizzo in inserimento cliente (0=No, 1=Si)', 'check_cust_address', '1');
ALTER TABLE `gaz_XXXclfoco`	ADD COLUMN `external_resp` TINYINT(1) NOT NULL AFTER `print_map`;
ALTER TABLE `gaz_XXXclfoco`	ADD COLUMN `external_service_descri` VARCHAR(100) NOT NULL COMMENT 'Descrizione del servizio esternalizzato, verrà riportato sulla nomina a responsabile esterno del trattamento dei dati' AFTER `external_resp`;
ALTER TABLE `gaz_XXXtesdoc`	ADD COLUMN `id_order` INT(9) NOT NULL DEFAULT '0' COMMENT 'Serve a memorizzare l\'ID dell\'ordine di provenienza nel documento' AFTER `id_con`;
CREATE TABLE `gaz_XXXregistro_trattamento_dati` (
	`revision` INT(9) NOT NULL AUTO_INCREMENT,
	`data_emissione` DATE NOT NULL,
	`A01` TINYTEXT NOT NULL COMMENT 'Attività svolta',
	`A02` TINYTEXT NOT NULL COMMENT 'Forma sociale',
	`A03` TINYTEXT NOT NULL COMMENT 'Sede dell\'attività',
	`A04` TINYTEXT NOT NULL COMMENT 'Struttura dei luoghi di svolgimento dell\'attività',
	`A05` INT(4) NOT NULL DEFAULT '0' COMMENT 'Numero dei titolari dell\'attività',
	`A06` INT(4) NOT NULL DEFAULT '0' COMMENT 'Numero dei dipendenti',
	`A07` INT(4) NOT NULL DEFAULT '0' COMMENT 'Numero dei collaboratori',
	`A08E` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Raccolta e gestione dei dati elettronica',
	`A08C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Raccolta e gestione dei dati cartacea',
	`A09` TEXT NOT NULL COMMENT 'Descrizione sistemi informatici presenti',
	`A10` TINYTEXT NOT NULL COMMENT 'Titolare del trattamento (Nominativo e dati di contatto)',
	`A11` TINYTEXT NOT NULL COMMENT 'Contitolare del trattamento (se presente, nominativo e dati di contatto)',
	`A12` TINYTEXT NOT NULL COMMENT 'Rappresentante del titolare del trattamento (se presente, nominativo e dati di contatto)',
	`A13` TINYTEXT NOT NULL COMMENT 'Responsabile del titolare del trattamento (se presente, nominativo e dati di contatto)',
	`A14` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Il titolare ha aderito ad un codice di condotta (si-no)',
	`A15` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Il titolare ha aderito ad un sistema di certificazione art.42 Regolamento (si-no)',
	`B01A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: anagrafica',
	`B01B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: svolgimento attività principali e accessorie',
	`B01C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: promozione servizi del titolare',
	`B01D` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: promozione servizi terzi',
	`B01E` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: rilevazione abitudini di consumo',
	`B01F` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: profilazione dei dati ex art.4 n.4 del Regolamento',
	`B01G` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Finalità del trattamento dei dati personali degli interessati raccolti dal titolare: rapporto di lavoro /collaborazione',
	`B02A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità anagrafica: clienti',
	`B02B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità anagrafica: fornitori',
	`B02C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità anagrafica: dipendenti/collaboratori',
	`B03A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di svolgimento delle attività principali e accessorie del titolare: clienti',
	`B03B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di svolgimento delle attività principali e accessorie del titolare: fornitori',
	`B03C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di svolgimento delle attività principali e accessorie del titolare: dipendenti/collaboratori',
	`B04A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi del titolare: clienti',
	`B04B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi del titolare: fornitori',
	`B04C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi del titolare: dipendenti/collaboratori',
	`B05A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi di terzi: clienti',
	`B05B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi di terzi: fornitori',
	`B05C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità di promozione dei servizi di terzi: dipendenti/collaboratori',
	`B06A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per rilevamento delle abitudini di consumo dell\'interessato: clienti',
	`B06B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per rilevamento delle abitudini di consumo dell\'interessato: fornitori',
	`B06C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per rilevamento delle abitudini di consumo dell\'interessato: dipendenti/collaboratori',
	`B07A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per i fini e con le modalità di cui all\'art.4 n.4 del Regolamento (profilazione dati): clienti',
	`B07B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per i fini e con le modalità di cui all\'art.4 n.4 del Regolamento (profilazione dati): fornitori',
	`B07C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per i fini e con le modalità di cui all\'art.4 n.4 del Regolamento (profilazione dati): dipendenti/collaboratori',
	`B08A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità relative al rapporto di lavoro/collaborazione: clienti',
	`B08B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità relative al rapporto di lavoro/collaborazione: fornitori',
	`B08C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Trattamento dei dati personali per finalità relative al rapporto di lavoro/collaborazione: dipendenti/collaboratori',
	`C01A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Base giuridica del trattamento dei dati: legge',
	`C01B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Base giuridica del trattamento dei dati: contratto',
	`C01C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Base giuridica del trattamento dei dati: standard internazionale',
	`C02A` INT(3) NOT NULL DEFAULT '0' COMMENT 'Termini massimi consentiti per la cancellazione dei dati: Dati personali art.4 (Ai fini del presente regolamento s\'intende per: 1) «dato personale»: qualsiasi informazione riguardante una persona fisica identificata o identificabile («interessato»); si considera identificabile la persona fisica che può essere identificata, direttamente o indirettamente, con particolare riferimento a un identificativo come il nome, un numero di identificazione, dati relativi all\'ubicazione, un identificativo online o a uno o più elementi caratteristici della sua identità fisica, fisiologica, genetica, psichica, economica, culturale o sociale)',
	`C02B` INT(3) NOT NULL DEFAULT '0' COMMENT 'Termini massimi consentiti per la cancellazione dei dati: Dati personali art.9 ( È vietato trattare dati personali che rivelino l\'origine razziale o etnica, le opinioni politiche, le convinzioni religiose o filosofiche, o l\'appartenenza sindacale, nonché trattare dati genetici, dati biometrici intesi a identificare in modo univoco una persona fisica, dati relativi alla salute o alla vita sessuale o all\'orientamento sessuale della persona)',
	`C02C` INT(3) NOT NULL DEFAULT '0' COMMENT 'Termini massimi consentiti per la cancellazione dei dati: Dati personali art.10 (Il trattamento dei dati personali relativi alle condanne penali e ai reati o a connesse misure di sicurezza sulla base dell\'articolo 6, paragrafo 1, deve avvenire soltanto sotto il controllo dell\'autorità pubblica o se il trattamento è autorizzato dal diritto dell\'Unione o degli Stati membri che preveda garanzie appropriate per i diritti e le libertà degli interessati. Un eventuale registro completo delle condanne penali deve essere tenuto soltanto sotto il controllo dell\'autorità pubblica)',
	`C03` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Vengono eseguiti trattamenti in cui i dati sono comunicati a terzi? (0=NO, 1=SI)',
	`C03_tipo_descri` TEXT NOT NULL COMMENT 'Se Si quali trattamenti? descrivi',
	`C03A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Se Si verso quali categorie di terzi: 1) fornitori di annunci / pubblicità, 2) mediatori (art. 1754 c.c.), 3) notai, 4) assicurazioni, 5) architetti, 6) avvocati, 7) commercialisti, 8) istituti di credito, 9) altri soggetti esercenti attività finanziarie, 0) altre categorie descritte sotto',
	`C03A_altra_cat_descri` TINYTEXT NOT NULL COMMENT 'Descrizione altra  categorie di terzi (quando sopra = 0)',
	`C04` TEXT NOT NULL COMMENT 'Vengono eseguiti trattamenti in cui i dati sono comunicati a destinatari di Paesi terzi e/o Organizzazioni internazionali? (Se no lasciare vuoto)',
	`D01A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: computer singolo',
	`D01B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: più computer non collegati tra loro',
	`D01C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: sistemi connessi ad internet',
	`D01D` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: sistemi connessi ad entranet',
	`D01E` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: sistemi connessi a cloud',
	`D01F` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Descrizione dei sistemi informatici: presenza di server',
	`D02A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: pseudonimizzazione ',
	`D02B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: cifratura',
	`D02C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: sistemi protetti da password',
	`D02D` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: sistemi antivirus',
	`D02E` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: sistemi anti malware',
	`D02F` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: sistemi firewall',
	`D02G` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sistemi di sicurezza dei dati personali: sistemi di backup',
	`D03` TEXT NOT NULL COMMENT 'Sistemi di pseudonimizzazione dei dati adottati, Art. 4 Regolamento 679/2016: “1. 5) «pseudonimizzazione»: il trattamento dei dati personali in modo tale che i dati personali non possano più essere attribuiti a un interessato specifico senza l\'utilizzo di informazioni aggiuntive, a condizione che tali informazioni aggiuntive siano conservate separatamente e soggette a misure tecniche e organizzative intese a garantire che tali dati personali non siano attribuiti a una persona fisica identificata o identificabile"',
	`D04` TEXT NOT NULL COMMENT 'Sistemi di cifratura dei dati adottati',
	`D05` TEXT NOT NULL COMMENT 'Sistemi di protezione mediante password adottati',
	`D05_giorni` INT(3) NOT NULL DEFAULT '0' COMMENT 'Giorni aggiornamento password',
	`D06` TEXT NOT NULL COMMENT 'Sistemi antivirus adottati',
	`D06_giorni` INT(3) NOT NULL DEFAULT '0' COMMENT 'Giorni aggiornamento antivirus',
	`D07` TEXT NOT NULL COMMENT 'Sistemi anti malware adottati',
	`D07_giorni` INT(3) NOT NULL DEFAULT '0' COMMENT 'Giorni aggiornamento anti malware',
	`D08` TEXT NOT NULL COMMENT 'Sistemi firewall adottati',
	`D08_giorni` INT(3) NOT NULL DEFAULT '0' COMMENT 'Giorni aggiornamento firewall',
	`D09` TEXT NOT NULL COMMENT 'Sistemi back up adottati',
	`D09_giorni` INT(3) NOT NULL DEFAULT '0' COMMENT 'Giorni intervallo backup',
	`D10` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Le misure di sicurezza adottate nel loro insieme garantiscono un adeguato livello di sicurezza?',
	`D11` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Livello di attuazione del codice di condotta (0=nullo, 1-8=parziale, 9=totale)',
	`D11_descri` TEXT NOT NULL COMMENT 'Il codice di condotta adottato quali adempimenti prescrive in materia di sicurezza? (es."ogni utente è stato edotto tramite il REGOLAMENTO PER L\'UTILIZZO DELLE RISORSE INFORMATICHE")',
	`D12` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Livello di attuazione delle prescrizioni imposte da sistema di certificazione adottato (0=nullo, 1-8=parziale, 9=totale)',
	`D12_descri` TEXT NOT NULL COMMENT 'Descrizione degli adempimenti del sistema di certificazione adottato in materia di sicurezza dei dati',
	`E01` TEXT NOT NULL COMMENT 'Archivi elettronici: descrizione dei sistemi hardware e software utilizzati per archiviare i dati',
	`E02` TEXT NOT NULL COMMENT 'Archivi cartacei: Descrizione dei sistemi utilizzati per archiviare i dati',
	`E03` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Luogo di presenza degli archivi elettronici (0=presso il titolare nella propria sede, 1=presso il titolare ma in altri luoghi in territorio italiano, 2=presso il titolare ma in altri luoghi in territorio UE, 3=presso il titolare ma in altri luoghi in territorio extra UE, 4=presso soggeti terzi in territorio Italiano, 5=presso soggeti terzi in territorio UE, 6=presso soggeti terzi in territorio extra UE)',
	`E03_extra` TEXT NOT NULL COMMENT 'eventuale territorio extra UE',
	`E04` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Luogo di presenza degli archivi cartacei (0=presso il titolare nella propria sede, 1=presso il titolare ma in altri luoghi in territorio italiano, 2=presso il titolare ma in altri luoghi in territorio UE, 3=presso il titolare ma in altri luoghi in territorio extra UE, 4=presso soggeti terzi in territorio Italiano, 5=presso soggeti terzi in territorio UE, 6=presso soggeti terzi in territorio extra UE)',
	`E04_extra` TEXT NOT NULL COMMENT 'eventuale territorio extra UE',
	`F01` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sono effettuati trattamenti che possono presentare rischi per i diritti e le libertà degli interessati? ',
	`F02` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'In caso di risposta positiva al punto F.01: i trattamenti sono occasionali?',
	`F03` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'In caso di risposta positiva al punto F.01: i trattamenti includono categorie di dati di cui all’art. 9 del Regolamento (dati sensibili, genetici e biometrici)?',
	`F04` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'In caso di risposta positiva al punto F.01: i trattamenti includono categorie di dati di cui all’art. 10 del Regolamento (dati relativi a condanne penali e/o a reati)?',
	`G01A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: a) rottura dei sistemi di archiviazione dati elettronici ',
	`G01B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: b) interruzione non programmata dell’alimentazione dei sistemi informatici',
	`G01C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: c) furto degli archivi informatici',
	`G01D` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: d) furto degli archivi cartacei',
	`G01E` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: e) perdita dei dati informatici a causa di virus o di altri agenti informatici automatizzati',
	`G01F` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: f) perdita dei dati informatici a causa di intrusione nei sistemi informatici dall’esterno',
	`G01G` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: g) cancellazione non volontaria dei dati',
	`G01H` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile perdita di dati: h) distruzione non volontaria di documenti contenti dati',
	`G02A` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile sottrazione e divulgazione non autorizzata di dati: a) furto degli archivi informatici',
	`G02B` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile sottrazione e divulgazione non autorizzata di dati: b) furto degli archivi cartacei',
	`G02C` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile sottrazione e divulgazione non autorizzata di dati: c) sottrazione di dati contenuti in archivi informatici a causa di virus o di altri agenti informatici automatizzati',
	`G02D` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Rischi individuati relativi alla possibile sottrazione e divulgazione non autorizzata di dati: d) sottrazione di dati contenuti in archivi informatici a causa di intrusione di nei sistemi informatici di soggetti dall’esterno',
	`G03A` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: a) rottura dei sistemi di archiviazione dati elettronici ',
	`G03B` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: b) interruzione non programmata dell’alimentazione dei sistemi informatici',
	`G03C` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: c) furto degli archivi informatici',
	`G03D` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: d) furto degli archivi cartacei',
	`G03E` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: e) perdita dei dati informatici a causa di virus o di altri agenti informatici automatizzati',
	`G03F` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: f) perdita dei dati informatici a causa di intrusione nei sistemi informatici dall’esterno',
	`G03G` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: g) cancellazione non volontaria dei dati',
	`G03H` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile perdita di dati (G.01) per: h) distruzione non volontaria di documenti contenti dati',
	`G04A` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile sottrazione e divulgazione non autorizzata di dati (G.02):  a) furto degli archivi informatici',
	`G04B` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile sottrazione e divulgazione non autorizzata di dati (G.02):  b) furto degli archivi cartacei',
	`G04C` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile sottrazione e divulgazione non autorizzata di dati (G.02): c) sottrazione di dati contenuti in archivi informatici a causa di virus o di altri agenti informatici automatizzati',
	`G04D` TEXT NOT NULL COMMENT 'Individuazione soluzioni relative ai rischi inerenti alla possibile sottrazione e divulgazione non autorizzata di dati (G.02): d) sottrazione di dati contenuti in archivi informatici a causa di intrusione di nei sistemi informatici di soggetti dall’esterno',
	`G05` TEXT NOT NULL COMMENT 'Procedure di comunicazione di Data Breach relative ai rischi inerenti alla possibile perdita di dati (G.01)',
	`G06` TEXT NOT NULL COMMENT 'Procedure di comunicazione di Data Breach relative alla possibile sottrazione e divulgazione non autorizzata di dati (G.02)',
	`G07` TEXT NOT NULL COMMENT 'Registro dei Data Breach (Data, tipo di evento, rimedi posti in atto e comunicazione)',
	`H01` TINYTEXT NOT NULL COMMENT 'Modalità di conservazione del Registro (cartaceo o elettronico, in quest\'ultimo caso indicare il software utilizzato: es. GAzie) ',
	`H02` TINYTEXT NOT NULL COMMENT 'Elenco dei soggetti autorizzati ad accedere al Registro',
	`H03` TINYTEXT NOT NULL COMMENT 'Soggetto responsabile alla conservazione e distribuzione del Registro',
	`H04` DATE NOT NULL COMMENT 'Prossima revisione del Registro',
	`adminid` VARCHAR(20) NOT NULL,
	`last_modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`revision`)
) COMMENT='Registro dei trattamenti (ex art.30 Regolamento UE 2016/679 - GDPR)' ENGINE=MyISAM DEFAULT CHARSET=utf8;
ALTER TABLE `gaz_XXXmovmag`	ADD COLUMN `type_mov` INT(1) NOT NULL DEFAULT '0' COMMENT 'Quaderno di campagna: 1=si 0=no  ' AFTER `caumag`;
ALTER TABLE `gaz_XXXmovmag`	ADD COLUMN `campo_coltivazione` INT(3) NOT NULL DEFAULT '0' COMMENT 'Ref.alla colonna codice della tabella gaz_001campi' AFTER `scorig`;
ALTER TABLE `gaz_XXXmovmag`	ADD COLUMN `id_avversita` INT(3) NOT NULL DEFAULT '0' COMMENT 'Utilizzato da quaderno di campagna' AFTER `campo_coltivazione`;
ALTER TABLE `gaz_XXXmovmag`	ADD COLUMN `id_colture` INT(3) NOT NULL DEFAULT '0' COMMENT 'Utilizzato da quaderno di campagna' AFTER `id_avversita`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `codice_fornitore` VARCHAR(50) NOT NULL AFTER `descri`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `dose_massima` DECIMAL(8,3) NOT NULL DEFAULT '0' COMMENT 'Utilizzato in quaderno di campagna' AFTER `volume_specifico`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `rame_metallico` DECIMAL(8,3) NOT NULL DEFAULT '0' COMMENT 'Utilizzato in quaderno di campagna' AFTER `dose_massima`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `tempo_sospensione` INT(2) NOT NULL DEFAULT '0' COMMENT 'Utilizzato in quaderno di campagna' AFTER `rame_metallico`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `ordinabile` VARCHAR(1) NOT NULL AFTER `codice_fornitore`, ADD COLUMN `movimentabile`  VARCHAR(1) NOT NULL AFTER `ordinabile`;
ALTER TABLE `gaz_XXXrigbro`	ADD COLUMN `codice_fornitore` VARCHAR(50) NOT NULL AFTER `codart`;
ALTER TABLE `gaz_XXXcampi`	ADD COLUMN `giorno_decadimento` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `ricarico`, ADD COLUMN `codice_prodotto_usato` VARCHAR(15) NOT NULL AFTER `giorno_decadimento`, ADD COLUMN `id_mov` INT(9) NULL DEFAULT '0' AFTER `codice_prodotto_usato`, ADD COLUMN `id_colture` INT(3) NULL DEFAULT '0' AFTER `id_mov`;
ALTER TABLE `gaz_XXXartico`	ADD COLUMN `classif_amb` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Classificazione ambientale come da art.16  comma 2 del D.Lgs 150/2012 (Utilizzato in quaderno di campagna)' AFTER `uniacq`;
ALTER TABLE `gaz_XXXartico` ADD COLUMN `mostra_qdc` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Mostra nei movimenti del quaderno di campagna: 1=si , 0=no' AFTER `classif_amb`;
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Permetti caratteri speciali su codici articoli (0=No, 1=Si)', 'codart_special_char', '0');
INSERT INTO `gaz_XXXcompany_config` (`description`, `var`, `val`) VALUES ('Visualizza articoli composti in documento (0=No, 1=Si)', 'show_artico_composit', '0');
ALTER TABLE `gaz_XXXrigdoc`	ADD COLUMN `id_orderman` INT(9) NOT NULL COMMENT 'Ref. alla tabella gaz_001orderman (produzioni-contabilità industriale) ' DEFAULT '0' AFTER `id_mag`;
ALTER TABLE `gaz_XXXrigmoc`	ADD COLUMN `id_orderman` INT(9) NOT NULL COMMENT 'Ref. alla tabella gaz_001orderman (produzioni-contabilità industriale) ' DEFAULT '0' AFTER `import`;
ALTER TABLE `gaz_XXXmovmag`	ADD COLUMN `id_orderman` INT(9) NOT NULL COMMENT 'Ref. alla tabella gaz_001orderman (produzioni-contabilità industriale) ' AFTER `id_lotmag`;
ALTER TABLE `gaz_XXXorderman` ADD COLUMN `campo_impianto` INT(3) NOT NULL COMMENT 'Se valorizzata questa referenza assegna l\'ordine/commessa/produzione ad un impianto specifico, ovvero al campo del modulo camp (se azienda agricola)' AFTER `id_tesbro`;
ALTER TABLE `gaz_XXXorderman` ADD COLUMN `id_colture` INT(3) NOT NULL COMMENT 'Utilizzato dal modulo camp (Registro di campagna) per riferire la produzione ad una particolare coltura' AFTER `campo_impianto`;
ALTER TABLE `gaz_XXXtesdoc`	ADD COLUMN `fattura_elettronica_original_name` VARCHAR(100) NULL DEFAULT NULL AFTER `id_con`,	ADD COLUMN `fattura_elettronica_original_content` MEDIUMBLOB NULL DEFAULT NULL AFTER `fattura_elettronica_original_name`;
CREATE TABLE `gaz_XXXdistinta_base` ( `id` INT(9) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
	`codice_composizione` VARCHAR(15) NOT NULL COMMENT 'è il codice dell\'articolo composito',
	`codice_artico_base` VARCHAR(15) NOT NULL COMMENT 'codice dell\'articolo base',
	`quantita_artico_base` DECIMAL(14,5) NOT NULL DEFAULT '0.00000' COMMENT 'quantità di articoli base necessari per costituire l\'articolo composito',
	PRIMARY KEY (`id`)
)
COMMENT='Tabella per creare gli "articoli compositi" (distinta base). Un articolo è composito quando è presente in almeno una di queste righe e qundi fa riferimento ad almeno un altro articolo di magazzino. Questi righi si potranno aggiungere tramite lo script admin_artico,  alla fine con il bottone "aggiungi articolo di base" ed immettendo la sola quantità, l\'unità di misura ed il prezzo per i documenti di vendita  saranno quelli di sempre ma presi dall\'articolo composito, molto facile perchè resterà tutto come prima, mentre la contabilità di magazzino verrà aggiornata tenendo conto di questi righi e non dell\'articolo composito.' ENGINE=MyISAM;
UPDATE `gaz_XXXtesdoc` SET `template` = 'FatturaSemplice' WHERE `tipdoc` = 'FNC';
ALTER TABLE `gaz_XXXrigdoc`	CHANGE COLUMN `descri` `descri` VARCHAR(1000) NOT NULL COMMENT '1000 caratteri per uniformarsi al tracciato della fattura elettronica' AFTER `codart`;
ALTER TABLE `gaz_XXXrigbro`	CHANGE COLUMN `descri` `descri` VARCHAR(1000) NOT NULL COMMENT '1000 caratteri per uniformarsi al tracciato della fattura elettronica' AFTER `codice_fornitore`;
ALTER TABLE `gaz_XXXtesdoc`	ADD COLUMN `datreg` DATE NULL DEFAULT NULL COMMENT 'Data in cui si vuole venga registrata in contabilità la fattura d\'acquisto (prima veniva messo in impropriamente in datemi)' AFTER `id_con`;
UPDATE `gaz_XXXtesdoc` SET `datreg`=`datemi` WHERE `tipdoc` LIKE 'AF_';
UPDATE `gaz_XXXtesdoc` SET `datemi`=FALSE WHERE `tipdoc` LIKE 'AF_';
UPDATE `gaz_XXXtesdoc` SET `datemi`=FALSE WHERE `datemi` <= '2004-01-27';
UPDATE `gaz_XXXtesdoc` SET `data_ordine`=FALSE WHERE `data_ordine` <= '2004-01-27';
UPDATE `gaz_XXXtesdoc` SET `datfat`=FALSE WHERE `datfat` <= '2004-01-27';
CREATE TABLE `gaz_XXXrigcmr` (
  `id_rig` int(9) NOT NULL AUTO_INCREMENT,
  `id_tes` int(9) NOT NULL DEFAULT '0',
  `tiprig` int(1) NOT NULL DEFAULT '0',
  `codart` varchar(15) NOT NULL DEFAULT '',
  `codice_fornitore` varchar(50) NOT NULL,
  `descri` varchar(1000) NOT NULL COMMENT '1000 caratteri per uniformarsi al tracciato della fattura elettronica',
  `id_body_text` int(9) NOT NULL,
  `unimis` char(3) NOT NULL DEFAULT '',
  `quanti` decimal(12,3) DEFAULT NULL,
  `prelis` decimal(14,5) DEFAULT '0.00000',
  `sconto` decimal(4,1) DEFAULT '0.0',
  `codvat` int(2) NOT NULL DEFAULT '0',
  `pervat` decimal(3,1) DEFAULT '0.0',
  `codric` int(9) NOT NULL DEFAULT '0',
  `provvigione` decimal(4,2) NOT NULL,
  `ritenuta` decimal(3,1) NOT NULL,
  `delivery_date` date DEFAULT '2004-01-27',
  `id_doc` int(9) NOT NULL,
  `id_mag` int(9) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_rig`,`id_tes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Righi dei CMR (Convention des Marchandises par Route - Lettere di vettura internazionale). Le cui testate sono contenute in gaz_NNNtescmr';
CREATE TABLE `gaz_XXXtescmr` (
  `id_tes` int(9) NOT NULL AUTO_INCREMENT,
  `seziva` int(1) NOT NULL DEFAULT '0',
  `tipdoc` char(3) NOT NULL DEFAULT '',
  `template` varchar(32) DEFAULT '',
  `print_total` int(1) NOT NULL,
  `delivery_time` int(3) NOT NULL,
  `day_of_validity` int(3) NOT NULL,
  `datemi` date DEFAULT '2004-01-27',
  `weekday_repeat` int(2) NOT NULL,
  `protoc` int(9) NOT NULL DEFAULT '0',
  `numdoc` bigint(14) NOT NULL DEFAULT '0',
  `numfat` varchar(20) NOT NULL DEFAULT '0',
  `datfat` date DEFAULT '2004-01-27',
  `clfoco` int(9) NOT NULL DEFAULT '0',
  `pagame` int(2) NOT NULL DEFAULT '0',
  `banapp` int(3) NOT NULL DEFAULT '0',
  `vettor` int(2) NOT NULL DEFAULT '0',
  `listin` int(1) NOT NULL DEFAULT '0',
  `destin` varchar(100) NOT NULL,
  `id_des` int(9) NOT NULL,
  `id_des_same_company` int(9) NOT NULL,
  `spediz` varchar(50) NOT NULL DEFAULT '',
  `portos` varchar(50) NOT NULL DEFAULT '',
  `imball` varchar(50) NOT NULL DEFAULT '',
  `traspo` decimal(6,2) DEFAULT '0.00',
  `speban` decimal(6,2) DEFAULT '0.00',
  `spevar` decimal(6,2) DEFAULT '0.00',
  `round_stamp` tinyint(3) NOT NULL DEFAULT '5',
  `cauven` int(2) NOT NULL,
  `caucon` char(3) NOT NULL,
  `caumag` int(2) NOT NULL,
  `id_agente` int(9) NOT NULL,
  `id_pro` int(9) NOT NULL,
  `sconto` decimal(5,2) DEFAULT '0.00',
  `expense_vat` int(2) NOT NULL DEFAULT '0',
  `stamp` decimal(6,2) NOT NULL,
  `taxstamp` decimal(5,2) NOT NULL,
  `virtual_taxstamp` tinyint(1) NOT NULL,
  `net_weight` decimal(12,2) NOT NULL,
  `gross_weight` decimal(12,2) NOT NULL,
  `units` int(6) NOT NULL,
  `volume` decimal(8,2) NOT NULL,
  `initra` datetime DEFAULT '2004-01-27 18:20:00',
  `geneff` char(1) NOT NULL DEFAULT '',
  `id_contract` int(9) NOT NULL DEFAULT '0',
  `id_con` int(9) NOT NULL DEFAULT '0',
  `id_orderman` int(9) NOT NULL DEFAULT '0',
  `status` varchar(10) NOT NULL DEFAULT '',
  `adminid` varchar(20) NOT NULL DEFAULT '',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Testate dei CMR (Convention des Marchandises par Route) - Lettere di vettura internazionale';
ALTER TABLE `gaz_XXXorderman` ADD COLUMN `id_lotmag` INT(9) NOT NULL COMMENT 'Riferimento al lotto (tabella gaz_NNNlotmag) per la tracciabilità e/o certificazione delle produzioni ' AFTER `id_colture`;
ALTER TABLE `gaz_XXXorderman` ADD COLUMN `id_rigbro` INT(9) NOT NULL COMMENT 'Riferimento al rigo ordine da cliente del modulo vendite' AFTER `id_tesbro`;
-- STOP_WHILE ( questo e' un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dell'installazione)