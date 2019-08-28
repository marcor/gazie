<div class="panel panel-default panel-user" >
    <p>
        <?php echo ucfirst($msg) . " " . $admin_aziend['user_firstname'] . ' (ip=' . $admin_aziend['last_ip'] . ')'; ?>
    </p>
    <p>
    <div class="img-containter">
        <a href="../config/admin_utente.php?user_name=<?php echo $admin_aziend['user_name']; ?>&Update">
            <img class="img-circle usr-picture" src="view.php?table=admin&field=user_name&value=<?php echo $admin_aziend['user_name'] ?>" alt="<?php echo $admin_aziend['user_lastname'] . ' ' . $admin_aziend['user_firstname']; ?>" style="max-width: 100%;" title="<?php echo $script_transl['change_usr']; ?>" >
        </a>
    </div>
    </p>
    <p>
        <?php echo $script_transl['access'] . $admin_aziend['Access'] . $script_transl['pass'] . gaz_format_date($admin_aziend['datpas']) ?> 
    </p>
    <div>
		<a class="btn btn-primary" href="../config/print_privacy_regol.php" class="button"> <?php echo $script_transl['user_regol'];?></a> 
	</div>
</div>