<?php
function orderman_prepare_ref_doc($tipdoc,$id_rif){
    global $gTables;
    $acc=[];
    switch ($tipdoc){
        default:
        $acc['link']=($r)?"../orderman/admin_orderman.php?Update&codice=".$id_rif:'';
        break;
    }
    return $acc;
}
?>