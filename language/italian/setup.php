<?php
/* $Id: setup.php,v 1.2 2011/01/01 11:07:11 devincen Exp $
 --------------------------------------------------------------------------
                            Gazie - Gestione Azienda
    Copyright (C) 2004-2011 - Antonio De Vincentiis Montesilvano (PE)
                                (www.devincentiis.it)
                        <http://gazie.sourceforge.net>
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
$errors                  = Array();
$errors['is_align']      = "Il database &egrave; allineato con il software";
$errors['no_conn']       = "La connessione al database non &egrave; andata a buon fine.<br/>Impostare correttamente username, password <br/>e nome del database nel file config/config/gconfig.php";

$msg                     = Array();
$msg['title']            = "Installa o Aggiorna la Base Dati di GAzie ";
$msg['install']          = "Installa";
$msg['upgrade']          = "Aggiorna";
$msg['error']            = "Errore";
$msg['gi_install']       = "Installazione Base Dati di ";
$msg['gi_upgrade']       = "Aggiornamento Base Dati di ";
$msg['gi_upg_to']        = "alla versione";
$msg['gi_upg_from']      = "dalla versione";
$msg['gi_lang']          = "Seleziona lingua";
$msg['gi_error']         = "";
$msg['gi_is_align']      = "Clicca qui per entrare";
$msg['gi_usr_psw']       = "User = amministratore <br />Password = password";
?>