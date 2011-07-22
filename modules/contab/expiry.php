<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$term =  filter_var(intval($_GET['term']),FILTER_SANITIZE_MAGIC_QUOTES);
$return_arr = array();
$sqlquery= "SELECT * FROM ".$gTables['paymov']."
            LEFT JOIN ".$gTables['rigmoc']." ON ".$gTables['rigmoc'].".id_rig = ".$gTables['paymov'].".id_rigmoc 
            LEFT JOIN ".$gTables['tesmov']." ON ".$gTables['tesmov'].".id_tes = ".$gTables['rigmoc'].".id_tes
            WHERE codcon=".$term." ORDER BY ".$gTables['tesmov'].".datreg";
$result = gaz_dbi_query($sqlquery);

while($row = gaz_dbi_fetch_array($result)) {
            $r['id']=$row['numdoc'];
            $r['label']=$row['expiry'];
            $r['value']=$row['amount'];
            array_push($return_arr,$r);
}
echo json_encode($return_arr);
?>

