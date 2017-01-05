<?php
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if (!$isAjax) {
        $user_error = 'Access denied - not an AJAX request...';
        trigger_error($user_error, E_USER_ERROR);
    }

    require("../../library/include/datlib.inc.php");
    $config = new Config;
    $admin_aziend=checkAdmin();
    
    $form['description'] = $_POST['desc'];
    $form['variable'] = $_POST['name'];
    $form['cvalue'] = $_POST['val'];
    $form['show'] = 0;
    
    $config->setValue ( $form['variable'], $form );
?>