<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$return_arr = array();
$term =  filter_var(substr($_GET['term'],0,20),FILTER_SANITIZE_MAGIC_QUOTES);
if(strlen($term) >2) {
        $result = gaz_dbi_dyn_query("UPPER(".$gTables['municipalities'].".name) AS citspe,".$gTables['municipalities'].".postal_code AS capspe,".$gTables['provinces'].".abbreviation AS prospe, ".$gTables['regions'].".name AS region, ".$gTables['country'].".name AS nation, ".$gTables['country'].".iso AS country",
                                    $gTables['municipalities']." LEFT JOIN ".$gTables['provinces']." ON ".$gTables['municipalities'].".id_province = ".$gTables['provinces'].".id
                                     LEFT JOIN ".$gTables['regions']." ON ".$gTables['provinces'].".id_region = ".$gTables['regions'].".id
                                     LEFT JOIN ".$gTables['country']." ON ".$gTables['regions'].".iso_country = ".$gTables['country'].".iso",
                                    $gTables['municipalities'].".name LIKE '%".$term."%'",$gTables['municipalities'].".name ASC");
        while($row = gaz_dbi_fetch_array($result)) {
            $r['id']=$row['capspe'];
            $r['label']=$row['capspe'].' '.$row['citspe'].' ('.$row['prospe'].') '.$row['region'].'  '.$row['nation'];
            $r['value']=$row['citspe'];
            $r['prospe']=$row['prospe'];
            $r['capspe']=$row['capspe'];
            $r['country']=$row['country'];
            array_push($return_arr,$r);
        }
        echo json_encode($return_arr);
} else {
  return;
}
?>

