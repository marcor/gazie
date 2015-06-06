<form style="display: inline;" method="POST"  action="ruburl.php">
<?php
if ( isset($_POST["id"]) ) $id = $_POST["id"];
if ( isset($_GET["id"]) ) $id = $_GET["id"];

$result = gaz_dbi_dyn_query('*',$gTables['company_config'], "var=\"ruburl\"", "val", 0, 999);
?>
	&nbsp;&nbsp;<select style="display: inline;" name="id" onchange="this.form.submit();">
	<?php 
	while ($row = gaz_dbi_fetch_array($result)) {
		if ( $row["id"] == $id ) {
			$default = "selected";
		} else {
			$default = "";
		}
		$corrente = $row["description"];
		echo "<option value=\"".$row["id"]."\" ".$default." >".$corrente."</option>";
	}
	?>
</select>
</form>