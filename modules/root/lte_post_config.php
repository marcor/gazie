<?php
    require("../../library/include/datlib.inc.php");
    $config = new Config;
    $admin_aziend=checkAdmin();
    
    $form['description'] = $_POST['desc'];
    $form['variable'] = $_POST['name'];
    $form['cvalue'] = $_POST['val'];
    $form['show'] = 0;
    
    $config->setValue ( $form['variable'], $form );
?>