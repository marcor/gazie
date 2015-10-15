-- START_WHILE ( questo e un tag che serve per istruire install.php ad INIZIARE ad eseguire le query seguenti su tutte le aziende dellinstallazione)
create table gaz_XXXragstat (
    codice char(15) NOT NULL,
    descri varchar(50) NOT NULL DEFAULT ,
    image blob NOT NULL,
    web_url varchar(255) NOT NULL,
    ricarico decimal(4,1) NOT NULL,
    annota varchar(50) DEFAULT NULL,
    adminid varchar(20) NOT NULL DEFAULT ,
    last_modified timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (codice)
);
ALTER TABLE gaz_XXXartico ADD ragstat CHAR(15) DEFAULT NULL;
ALTER TABLE gaz_XXXartico ADD FOREIGN KEY (ragstat) REFERENCES gaz_XXXragstat(codice);
ALTER TABLE gaz_XXXartico ADD sconto decimal(6,3);
ALTER TABLE gaz_XXXrigdoc MODIFY COLUMN sconto decimal(6,3);
ALTER TABLE gaz_XXXtesdoc ADD data_ordine DATE;
ALTER TABLE gaz_XXXtesdoc ADD ragbol char(1) NOT NULL DEFAULT 'A';
ALTER TABLE gaz_XXXtesdoc ADD da_fatturare boolean DEFAULT true;


-- STOP_WHILE ( questo e un tag che serve per istruire install.php a SMETTERE di eseguire le query su tutte le aziende dellinstallazione)

