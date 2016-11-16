<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2016 - Antonio De Vincentiis Montesilvano (PE)
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

$strScript = array(
    "report_teachers.php" =>
    array('title' => 'Elenco degli insegnanti',
        'Login' => "Login",
        'Cognome' => "Cognome",
        'Nome' => "Nome"
    ),
    "report_classrooms.php" =>
    array('title' => 'Elenco delle classi',
        'teacher' => "Insegnante",
        'anno_scolastico' => 'Anno scolastico',
        'classe' => "Classe",
        'sezione' => "Sezione"
    ),
    "admin_classroom.php" =>
    array('title' => 'Gestione della classe',
        'ins_this' => 'Inserimento classe',
        'upd_this' => 'Modifica la classe ',
        'err' => array(
            'classe' => 'La classe non &egrave; stata descritta',
            'sezione' => 'Manca la descrizione della sezione',
            'anno_scolastico' => 'Manca l\'anno scolastico di riferimento',
            'teacher' => 'Manca l\'insegnante di riferimento',
        ),
        'classe' => "Classe",
        'sezione' => "Sezione",
        'anno_scolastico' => 'Anno scolastico',
        'teacher' => "Insegnante",
        'location' => "Ubicazione",
        'title_note'=>"Annotazioni"
    ),
    "report_students.php" =>
    array('title' => 'Elenco degli alunni',
        'classe'=>'Classe',
        'Cognome' => "Insegnante",
        'Nome' => 'Anno scolastico',
        'email' => "E-Mail",
        'telephone' => "Telefono"
    ),
);
?>