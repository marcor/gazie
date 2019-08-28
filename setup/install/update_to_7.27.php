<?php 
if (isset($_SESSION['table_prefix'])) {
    $table_prefix = substr($_SESSION['table_prefix'], 0, 12);
} elseif (isset($_GET['tp'])) {
    $table_prefix = filter_var(substr($_GET['tp'], 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
} else {
    $table_prefix = filter_var(substr($table_prefix, 0, 12), FILTER_SANITIZE_MAGIC_QUOTES);
}

// al momento inserisco solo i due widget principali su tutti gli utenti, ma poi dovrò mettere anche scadenzario e lotti 
$data=array(array('exec_mode'=>2, 'file'=>'dash_company_widget.php','position_order'=>1),
			array('exec_mode'=>2, 'file'=>'dash_user_widget.php','position_order'=>2),
			array('exec_mode'=>2, 'file'=>'dash_customer_schedule.php','position_order'=>3),
			array('exec_mode'=>2, 'file'=>'dash_supplier_schedule.php','position_order'=>4)
			);
$get_users=gaz_dbi_dyn_query("*", $table_prefix . "_admin","1");
$gTables['breadcrumb']=$table_prefix . "_breadcrumb";
while($rus=gaz_dbi_fetch_array($get_users)){
	foreach($data as $v){
		$v['adminid']=$rus["user_name"];
		gaz_dbi_table_insert('breadcrumb',$v);
	}
	echo "<p>Ho creato una <b>dashboard personalizzabile</b> per l'utente <b>".$v['adminid']."</b></p>";
}
?>
