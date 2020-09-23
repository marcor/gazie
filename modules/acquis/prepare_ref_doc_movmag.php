<?php
function acquis_prepare_ref_doc($tipdoc,$id_rif){
    global $gTables;
    $acc=[];
    switch ($tipdoc){
        default:
        $result = gaz_dbi_dyn_query("*", $gTables['rigdoc']." LEFT JOIN ".$gTables['tesdoc']." ON ".$gTables['rigdoc'].".id_tes = ".$gTables['tesdoc'].".id_tes", 'id_rig='.$id_rif, ' id_rig', 0, 1);    
        $r = gaz_dbi_fetch_array($result);
        $acc['link']="../acquis/admin_docacq.php?Update&id_tes=".$r['id_tes'];
        break;
    }
    return $acc;
}
?>