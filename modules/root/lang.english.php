<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */

$strScript = array("admin.php" =>
    array('morning' => "good morning",
        'afternoon' => "good afternoon",
        'evening' => "good evening",
        'night' => "good night",
        'errors' => array(' It is necessary to align its Database version ',
            ' to version ',
            '  by clicking HERE ',
            ' He remembers that for the good application working the directive magic_quotes_gpc must be set  Off into php.ini file!',
            ' Attenzione il backup esterno risale a più di 10 giorni, fallo adesso '
        ),
        'access' => ", this is your access n.",
        'pass' => "<br />Last password update: ",
        'logout' => "If you want exit click the button",
        'company' => " You are running the company:<br /> ",
        'mesg_co' => array('The search yielded no results!', 'Insert at least 2 characters!', 'Change company'),
        'upd_company' => "Update company config",
        'business' => "for the business management.",
        'proj' => "Project manager: ",
        'devel' => "Development, documentation, bug report: ",
        'change_usr' => "Change your data",
        'auth' => "Author web site",
        'strBottom' => array(
            array('href' => "http://www.kernel.org/",
                'img' => "linux.gif",
                'title' => "Linux (kernel)"),
            array('href' => "http://www.apache.org",
                'img' => "apache.gif",
                'title' => "Apache the Web Server more used in the world!"),
            array('href' => "http://dev.mysql.com/downloads/",
                'img' => "mysqldbms.gif",
                'title' => "This is MySQL official web site. The database inside which GAzie memorizes hits data!"),
            array('href' => "http://www.php.net",
                'img' => "phppower.gif",
                'title' => "Go to PHP official web site, the language for Dynamic Web!"),
            array('href' => "http://sourceforge.net/projects/tcpdf/",
                'img' => "tcpdf.jpg",
                'title' => "You find TCPDF here, the PHP class FPDF based used to produce the GAzie's documents!"),
            array('href' => "https://jquery.com/",
                'img' => "jquery.png",
                'title' => "La libreria javascript per il web"),
            array('href' => "http://getbootstrap.com/",
                'img' => "bootstrap.png",
                'title' => "Bootstrap, front end web library"),
            array('href' => "http://www.mozilla.org/products/firefox/all.html",
                'img' => "firefox.gif",
                'title' => "Download FIREFOX, the browser GAzie has been tested with!")
        ),
        'sca_scacli' => 'Scadenzario Clienti',
        'sca_scafor' => 'Scadenzario Fornitori',
        'sca_cliente' => 'Cliente',
        'sca_fornitore' => 'Fornitore',
        'sca_avere' => 'Avere',
        'sca_dare' => 'Dare',
        'sca_scadenza' => 'Scadenza'
    ),
    "login_admin.php" =>
    array(/* 0 */ " The new password to be long at least ",
        /* 1 */ " characters,<BR> various from previous and the equal one to that one of control !<br>",
        /* 2 */ " You have had approached the program but yours password it is past due,<br /> you must insert of one new!",
        /* 3 */ " User and/or Password incorrect!<br>",
        /* 4 */ " Denied access to this module !",
        /* 5 */ " New password",
        /* 6 */ " New confirmation password",
        'log' => "Access to system localized in:",
        'welcome' => "Welcome to GAzie",
        'intro' => "the Enterprise Resource Planning that allows you to keep track of the accounts, documentation, sales, purchases, warehouses and more, for many companies simultaneously.",
        'usr_psw' => "Enter your username and password that you have been assigned to start:",
        'ins_psw' => "Enter Password",
        'label_conf_psw' => "Confirm Password",
        'conf_psw' => "Re-enter Password",
        'label_new_psw' => "New Password",
        'new_psw' => "Enter New Password",
        'student'=>'If you are a student you can log from here'
        ));
$errors = array(
    'access' => 'You have no right of access to the module'
);
?>