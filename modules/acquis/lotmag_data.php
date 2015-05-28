<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$ref =  filter_var(substr($_GET['id_purchase'],0,9),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$sqlquery= "SELECT * FROM ".$gTables['lotmag']."
            LEFT JOIN ".$gTables['rigdoc']." ON ".$gTables['rigdoc'].".id_rig = ".$gTables['lotmag'].".id_purchase 
            LEFT JOIN ".$gTables['movmag']." ON ".$gTables['movmag'].".id_lotmag = ".$gTables['lotmag'].".id
            WHERE id_purchase = ". $ref." ORDER BY ".$gTables['movmag'].".datreg DESC";
$result = gaz_dbi_query($sqlquery);

while($row = gaz_dbi_fetch_array($result)) {
            array_push($return_arr,$row);
}
echo json_encode($return_arr);
?>

