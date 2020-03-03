 <?php
//Credit: m_zolfo
// prevent direct access

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}
require("../../library/include/datlib.inc.php");
$admin_aziend = checkAdmin();

if(isset($_POST["fn"])&& isset($_POST["filename"])) // ho i dati di base
// scrittura db
if ($_POST["fn"]=='save'){ // upsert
	$ier=gaz_dbi_get_row($gTables['company_data'], 'var', filter_var($_POST['filename'], FILTER_SANITIZE_STRING));
	if ($ier){
		gaz_dbi_put_row($gTables['company_data'], 'var', filter_var($_POST['filename'], FILTER_SANITIZE_STRING),'data',filter_var($_POST['value'], FILTER_SANITIZE_STRING));
	}else{
		gaz_dbi_table_insert('company_data', array('description'=>'Valori settaggio IERincludeExcludeRows', 'var'=>filter_var($_POST['filename'], FILTER_SANITIZE_STRING),'data'=>filter_var($_POST['value'], FILTER_SANITIZE_STRING)));		
	}
	// lettura db
} else {
	echo gaz_dbi_get_row($gTables['company_data'], 'var', filter_var($_POST['filename'], FILTER_SANITIZE_STRING))['data'];
}
?>
