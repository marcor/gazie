<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
         (http://www.devincentiis.it)
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

$strScript = array ("report_letter.php" =>
                   array(  "Letters Report ",
                           "Date ",
                           "Number ",
                           "Type ",
                           "Company name ",
                           "Object ",
                           "Write new letter",
                         'mail_alert0'=>'Invio lettera con email',
                         'mail_alert1'=>'Hai scelto di inviare una e-mail all\'indirizzo: ',
                         'mail_alert2'=>'con allegato la seguente lettera:'),
                    "admin_letter.php" =>
                   array(  'title' => " Letter ",
                           'mesg'=>array('The search yielded no results!',
                                         'Insert at least 2 characters!',
                                         'Changing customer'
                                          ),
                           array("LET" => " Normal ","DIC" => "Declaration","SOL" => " Sollecito "),
                           " of ",
                           " at ",
                           " number ",
                           "Object ",
                           "to the kind attention ",
                           "affix user signature ",
                           "Type ",
                           "Body ",
                           "User Name",
                           "The date is not corrected!",
                           "You must select a customer or a supplier!"
                    ),
                    "update_control.php" =>
                    array(  'title' => " Check for updates ",
                           'new_ver1'=>'It\'s available upgrade to new version (',
                           'new_ver2'=>') of GAzie! <br>To upgrade you can download files from: ',
                           'is_align'=>'No new updates. This version is updated to the latest available GAzie.',
                           'no_conn'=>'There are problems connecting to the server to version control!',
                           'disabled'=>'The latest version control has been disabled. You can reactivate it by choosing one of the services available to check the following sites',
                           'zone'=>'Zone',
                           'city'=>'City',
                           'sms'=>'SMS',
                           'web'=>'WEB',
                           'choice'=>'CHOICE',
                           'check_value'=>array(0=>'Enable!',1=>'Enabled'),
                           'check_title_value'=>array(0=>'Enable version control from this site!',1=>'Disable version control from this site!'),
                           'all_disabling'=>array(0=>'Disable all!',1=>'Disable all sites for Version Control!')
                         ),
                    "gaziecart_update.php" =>
                   array(  'title' => "Updating the online catalog, GAzieCart extension for Joomla!",
                           'errors'=>array('FTP server not found',
                                           'Authentication: the credentials are incorrect',
                                           'Directory not found',
                                           'One or more files were not updated'
                                          ),
                           'server'=>'Name of FTP server Ex: devincentiis.it',
                           'user'=>'User - User name for authentication',
                           'pass'=>'Password for authentication',
                           'path'=>'Joomla root directory Ex: joomla/ or nothing',
                           'listin'=>'Price list',
                           'listin_value'=>array(1=>' of sales 1',2=>' of sales 2',3=>' of sales 3','web'=>'of web sales')
                         ),
                    "backup.php" =>
                   array(  'title' => "Store up data to avoid losing work!",
                           'errors'=>array(),
                           'instructions'=>'Add the following statements',
                           'table_selection'=>'Backup of',
                           'table_selection_value'=>array(0=>' all tables of database ',1=>' only tables with prefix '),
                           'text_encoding'=>'Encoding',
                           'sql_submit'=>'Generate sql file',
                        )
                   );
?>