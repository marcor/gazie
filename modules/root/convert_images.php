<?php

/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2021 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();

$rs_row = gaz_dbi_dyn_query('codice,image', $gTables['artico'], "image NOT LIKE ''");
while ($r = gaz_dbi_fetch_array($rs_row)) {
    $img=imagecreatefromstring($r['image']);
    imagepng($img,$_SERVER['DOCUMENT_ROOT'].'/temp.png');
    $png = addslashes(file_get_contents($_SERVER['DOCUMENT_ROOT']."/temp.png", "r"));
    gaz_dbi_put_row($gTables['artico'],'codice',$r['codice'],'image',$png);
}
$rs_row = gaz_dbi_dyn_query('codice,image', $gTables['catmer'], "image NOT LIKE ''");
while ($r = gaz_dbi_fetch_array($rs_row)) {
    $img=imagecreatefromstring($r['image']);
    imagepng($img,$_SERVER['DOCUMENT_ROOT'].'/temp.png');
    $png = addslashes(file_get_contents($_SERVER['DOCUMENT_ROOT']."/temp.png", "r"));
    gaz_dbi_put_row($gTables['catmer'],'codice',$r['codice'],'image',$png);
}
$rs_row = gaz_dbi_dyn_query('codice,image', $gTables['aziend'], "image NOT LIKE ''");
while ($r = gaz_dbi_fetch_array($rs_row)) {
    $img=imagecreatefromstring($r['image']);
    imagepng($img,$_SERVER['DOCUMENT_ROOT'].'/temp.png');
    $png = addslashes(file_get_contents($_SERVER['DOCUMENT_ROOT']."/temp.png", "r"));
    gaz_dbi_put_row($gTables['aziend'],'codice',$r['codice'],'image',$png);
}
print 'Se durante l\'esecuzione di questo script non si sono verificati errori, dovresti aver convertito i file JPG del logo, degli articoli e delle categorie merceologiche in PNG, clicca  <A HREF="admin.php" > QUI </A> per tornare alla home page';