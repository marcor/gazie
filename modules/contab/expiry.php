<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$clfoco =  filter_var(intval($_GET['clfoco']),FILTER_SANITIZE_MAGIC_QUOTES);
$tes_exc =  filter_var(substr($_GET['id_tesdoc_ref'],0,15),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$sqlquery= "SELECT ".$gTables['paymov'].".*,".$gTables['rigmoc'].".*,".$gTables['tesmov'].".*,".$gTables['anagra'].".ragso1,".$gTables['anagra'].".ragso2 FROM ".$gTables['paymov']."
            LEFT JOIN ".$gTables['rigmoc']." ON ( ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_rigmoc_doc OR ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_rigmoc_pay ) 
            LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['tesmov'].".id_tes = ".$gTables['rigmoc'].".id_tes
            LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['clfoco'].".codice = ".$gTables['tesmov'].".clfoco  
            LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra  
            WHERE codcon=".$clfoco." AND ".$gTables['paymov'].".id_tesdoc_ref NOT LIKE '$tes_exc' ORDER BY ".$gTables['tesmov'].".datreg DESC, id_tesdoc_ref DESC, id_rig";
$result = gaz_dbi_query($sqlquery);

while($row = gaz_dbi_fetch_array($result)) {
            array_push($return_arr,$row);
}
echo json_encode($return_arr);
?>

