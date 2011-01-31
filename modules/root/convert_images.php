<?php
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