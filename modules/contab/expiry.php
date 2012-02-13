<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$clfoco =  filter_var(intval($_GET['clfoco']),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$sqlquery= "SELECT * FROM ".$gTables['paymov']."
            LEFT JOIN ".$gTables['rigmoc']." ON ( ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_docmovcon OR ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_paymovcon ) 
            LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['tesmov'].".id_tes = ".$gTables['rigmoc'].".id_tes
            WHERE codcon=".$clfoco." ORDER BY ".$gTables['tesmov'].".datreg DESC";
$result = gaz_dbi_query($sqlquery);

while($row = gaz_dbi_fetch_array($result)) {
            array_push($return_arr,$row);
}
echo json_encode($return_arr);
?>

