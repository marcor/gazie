<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$tesdoc_ref =  filter_var(substr($_GET['id_tesdoc_ref'],0,15),FILTER_SANITIZE_MAGIC_QUOTES);
$id_exc =  filter_var(intval($_GET['id_exc']),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$sqlquery= "SELECT * FROM ".$gTables['paymov']."
            LEFT JOIN ".$gTables['rigmoc']." ON ( ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_rigmoc_doc OR ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_rigmoc_pay ) 
            LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['tesmov'].".id_tes = ".$gTables['rigmoc'].".id_tes
            WHERE id_tesdoc_ref='".$tesdoc_ref."' AND ".$gTables['rigmoc'].".id_rig <> $id_exc ORDER BY ".$gTables['tesmov'].".datreg DESC, id_tesdoc_ref DESC, id_rig";
$result = gaz_dbi_query($sqlquery);

while($row = gaz_dbi_fetch_array($result)) {
            array_push($return_arr,$row);
}
echo json_encode($return_arr);
?>

