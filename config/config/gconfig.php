<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.altervista.org>
 --------------------------------------------------------------------------
    Questo programma e` free software;   e` lecito redistribuirlo  e/o
    modificarlo secondo i  termini della Licenza Pubblica Generica GNU
    come e` pubblicata dalla Free Software Foundation; o la versione 2
    della licenza o (a propria scelta) una versione successiva.

    Questo programma  e` distribuito nella speranza  che sia utile, ma
    SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
    NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
    veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

    Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
    Generica GNU insieme a   questo programma; in caso  contrario,  si
    scriva   alla   Free  Software Foundation,  Inc.,   59
    Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
 --------------------------------------------------------------------------
*/
if (isset($_SERVER['SCRIPT_FILENAME']) && (str_replace('\\','/',__FILE__) == $_SERVER['SCRIPT_FILENAME'])) {
    exit('Accesso diretto non consentito') ;
}
//versione software
$versSw = '5.18';

//array con le descrizioni della valuta utilizzata
$money = array("euro","é¼","é¼");

//nome DBMS usato per la libreria specifica (MySQL=mysql.lib, SQLite=sqlite.lib, ecc)
//per il momento disponibile solo la libreria mysql.lib
$NomeDB = "mysql";

//
// Parametri di accesso: server, db, utente, passwd e prefisso per le tabelle
//
// ATTENZIONE: il prefisso delle tabelle predefinito è "gaz". Eventualmente, si
// possono usare altri prefissi, ma composti sempre dai primi tre caratteri
// "gaz" e seguiti da un massimo di nove caratteri, costituiti da lettere
// minuscole e cifre numeriche. Per esempio, "gaz123" è valido, mentre "gaga1"
// o "gaz_123" non sono validi.
//
$Host     = "localhost";
$Database = "gazie";
$User     = "root";
$Password = "";
$table_prefix = "gaz";

// definisce il nome della sessione ma solo in caso di uso dei domini di livello superiore al secondo, in
// caso di installazione su domini di secondo livello viene attribuito automaticamente
// il nome del direttorio di installazione che normalmente e', appunto:  gazie
define('_SESSION_NAME','gazie');

//url di default per l'aggiornamento di gazie
$update_URI_files= "http://sourceforge.net/projects/gazie";
?>