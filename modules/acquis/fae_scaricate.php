<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
 function fae_scaricate()
 {
$admin_aziend = checkAdmin();
global $gTables;
// directory dove io metto le fatture acquisti scaricate dalla pec
$pathacquisti = DATA_DIR . 'files/' . $admin_aziend['codice'] . '/FAE_ACQUISTI/' ;
$fatture = array();
// Apro la directory
//echo $pathacquisti . "<br/>";
if (is_dir($pathacquisti)) {
//Apro l'oggetto directory
if ($directory_handle = opendir($pathacquisti)) {
//Scorro l'oggetto fino a quando non è termnato cioè false
while (($file = readdir($directory_handle)) !== false) {
//Se l'elemento trovato è diverso da una directory
//o dagli elementi . e .. lo visualizzo a schermo
if((!is_dir($file))&($file!=".")&($file!="..")) {
  $v=explode("_",$file);
  if (count($v)>2) continue ;
  //gaz_dbi_get_single_value($table, $campo, $where)
  if (! gaz_dbi_get_single_value($gTables["tesdoc"],"fattura_elettronica_original_name","tipdoc = 'AFA' AND fattura_elettronica_original_name LIKE '".$file ."'"))
  {
  $fatture[]=trim($file) ;

}
}
}

//Chiudo la lettura della directory.
closedir($directory_handle);
}

}
return $fatture ;
}


/**
 *
 */
class fae_scaricate
{

  function __construct($path)
  {
    $this->errore = false ;
    if (is_dir($path)) {
      if ($this->handle = opendir($path)) {
        $this->elenco_fatture = array() ;
      } else {
        $this->errore = "la cartella esiste ma è impossibile aprirla" ;
      }
    } else {
      $this->errore = "la cartella non esiste " ;
    }
  }

  function close() {
    //Chiudo la lettura della directory.
    closedir($this->handle);
  }

  function PrendiElenco()
  {
    $admin_aziend = checkAdmin();
    global $gTables ;


    while (($file = readdir($this->handle)) !== false) {
    //Se l'elemento trovato è diverso da una directory
    //o dagli elementi . e .. lo visualizzo a schermo
    if((!is_dir($file))&($file!=".")&($file!="..")) {
      $v=explode("_",$file);
      if (count($v)>2) continue ;
      //gaz_dbi_get_single_value($table, $campo, $where)
      if (! gaz_dbi_get_single_value($gTables["tesdoc"],"fattura_elettronica_original_name","tipdoc = 'AFA' AND fattura_elettronica_original_name LIKE '".$file ."'"))
      {
      //$this->fatture['daimportare'][]=trim($file) ;
      $this->fatture[$file]['nome_file']=trim($file) ;
    } else {
      $f=gaz_dbi_get_single_value($gTables["tesdoc"],"fattura_elettronica_original_name","tipdoc = 'AFA' AND fattura_elettronica_original_name LIKE '".$file ."'") ;
      $this->fatture[$file]['idsdi']= $f['id_doc_ritorno'];
      $this->fatture[$file]['ricezione']= $f['datreg'];
      $this->fatture[$file]['fornitore']= $f['clfoco'];
      $this->fatture[$file]['numero']= $f['numfat'];
      $this->fatture[$file]['dat_fat']= $f['datfat'];

      //$this->fatture['giaimportate'][]=trim($file) ;
      $this->fatture[$file]['nome_file']=trim($file) ;
    }
    }
    }
    return $this->fatture ;

  }


}
