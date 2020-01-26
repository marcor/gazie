<?php
/*
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2020 - Antonio De Vincentiis Montesilvano (PE)
  (http://www.devincentiis.it)
  <http://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation, 51 Franklin Street,
  Fifth Floor Boston, MA 02110-1335 USA Stati Uniti.
  --------------------------------------------------------------------------
 */
require_once('../../library/include/datlib.inc.php');
$admin_aziend = checkAdmin();

$titolo = 'Assistenza Clienti';
$totale_ore = 0;
$stati = array();

$orderby = "data asc";

if ( !isset( $_GET['idinstallazione']) ) { //se non viene visualizzato all'interno dello script installazioni
	require_once('../../library/include/header.php');
	$script_transl = HeadMain();
}

if ( isset($_GET['chstato'] ) ) {
	$rows = array ('aperto','effettuato','chiuso');
	$found = false;
	for ($t=0; $t<count($rows); $t++ ) {
		if ( $found == true ) {
			$stato = $rows[$t];
			$found = false;
		}
		if ( $rows[$t]==$_GET['prev'] && $t<count($rows)-1 ) $found=true;
		elseif ( $rows[$t]==$_GET['prev'] && $t==count($rows)-1 ) {
			$stato = $rows[0];
		}
	}
	gaz_dbi_table_update("assist", array ("id", $_GET['chstato']), array("stato" => $stato));
	header ();
} else {
	if ( !isset($_GET['stato']) ) $_GET['stato'] = 'nochiusi';
}

if ( !isset( $_GET['clfoco'] )) $_GET['clfoco'] = 'All';
if ( !isset( $_GET['oggetto'] )) $_GET['oggetto'] = '';

$where = "tipo = 'ASS' and ".$gTables['anagra'].".ragso1 like '%%'";
$all	= $where;

if ( isset( $_GET['idinstallazione']) ) {
	$where .= " and idinstallazione=".$_GET['idinstallazione'];
}

if ( isset($_GET['flt_passo']) ) {
	$passo = $_GET['flt_passo'];
} else {
	$passo = 50;
}

if ( !isset($_GET['tecnico']) ) $_GET['tecnico']='All';
/*if ( isset($_GET['tecnico']) ) {
	$flt_tecnico = $_GET['tecnico'];
	if ( $flt_tecnico!='All' ) {
		$where .= " and tecnico = '".$flt_tecnico."'";
	}
} else {
	$flt_tecnico = 'All';
}*/

/*if ( isset($_GET['flt_stato']) ) {
	$flt_stato = $_GET['flt_stato'];
	if ( $flt_stato!='tutti' ) {
		if ( $flt_stato=='nochiusi' ) {
			$where .= " and ".$gTables['assist'].".stato != 'chiuso' and stato != 'contratto' ";
		} else {
			$where .= " and ".$gTables['assist'].".stato = '".$flt_stato."'";
		}
	}
} else {
	$flt_stato = 'nochiusi';
	$where .= " and ".$gTables['assist'].".stato!='chiuso' ";
	//$where .= " ";
}*/

if ( isset($_GET['stato']) ) {
	$flt_stato = $_GET['stato'];
}
/*if ( $flt_stato!='All' ) {
	if ( $flt_stato=='nochiusi' ) {
		$where .= " and ".$gTables['assist'].".stato!='chiuso' and ".$gTables['assist'].".stato!='contratto' ";
	} else {
		$where .= " and ".$gTables['assist'].".stato='".$flt_stato."'";
	}
} else {
	$flt_stato = 'nochiusi';
	$where .= " and ".$gTables['assist'].".stato!='chiuso' ";
}*/

if ( !isset( $_GET['idinstallazione']) ) {
	gaz_flt_var_assign('codice', 'i', $gTables['assist']);
	gaz_flt_var_assign('data', 'd', $gTables['assist']);
	gaz_flt_var_assign('clfoco', 'v', $gTables['assist']);
	gaz_flt_var_assign('telefo', 'v');
	gaz_flt_var_assign('oggetto', 'v');
	gaz_flt_var_assign('descrizione', 'v');
	gaz_flt_var_assign('tecnico', 'v');
	gaz_flt_var_assign('stato', 'v', $gTables['assist']);
}

/*if ( isset($_GET['codice'])) {
	$where .= $gTables['assist'].".codice=".$flt_codice;
}
if ( isset($_GET['flt_cliente']) ) {
	$flt_cliente = $_GET['clfoco'];
} else {
	$flt_cliente = 'tutti';
}

if ( $flt_cliente!='tutti' ) {
	$where .= " and ".$gTables['assist'].".clfoco = '".$flt_cliente."'";
}*/

//d($GLOBALS, $_SERVER);
//d($_GET);

$result = gaz_dbi_dyn_query($gTables['assist'].".*,
		".$gTables['anagra'].".ragso1, ".$gTables['anagra'].".telefo ", $gTables['assist'].
		" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
		" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
		$where, $orderby, $limit, $passo);

if (!isset( $_GET['idinstallazione']) || count($result) > 0) {
?>
<div align="center" class="FacetFormHeaderFont"></div>
	<?php
	if ( !isset( $_GET['idinstallazione']) ) {
	?>
	<form method="GET">
	<?php
	} else {
	?>
	<center>Interventi SPOT</center>
	<?php
	}
	?>
	<div class="box-body table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
		<?php
		if ( !isset( $_GET['idinstallazione']) ) {
		?>
		<tr>
			<td class="FacetFieldCaptionTD" colspan="1">
				<?php gaz_flt_disp_int("codice", "Numero"); ?>
			</td>
			<td class="FacetFieldCaptionTD" colspan="1">
				<?php gaz_flt_disp_select("data", "YEAR(data) as data", $gTables["assist"], "9999", $orderby); ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php gaz_flt_disp_select("clfoco", $gTables['anagra'] . ".ragso1," . $gTables["assist"] . ".clfoco", $gTables['assist'] . " LEFT JOIN " . $gTables['clfoco'] . " ON " . $gTables['assist'] . ".clfoco = " . $gTables['clfoco'] . ".codice LEFT JOIN " . $gTables['anagra'] . " ON " . $gTables['clfoco'] . ".id_anagra = " . $gTables['anagra'] . ".id", $all." and stato='aperto' or stato='effettuato' ", "ragso1", "ragso1"); ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php gaz_flt_disp_int("telefo", "Telefono"); ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php gaz_flt_disp_int("oggetto", "Oggetto"); ?>
			</td>
			<td class="FacetFieldCaptionTD" colspan="1">
				<?php gaz_flt_disp_int("descrizione", "Descrizione"); ?>
			</td>
			<td class="FacetFieldCaptionTD" colspan="2">
				<?php gaz_flt_disp_select("tecnico", "tecnico", $gTables["assist"], "1=1", "tecnico"); ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<?php gaz_flt_disp_select("stato", "stato", $gTables["assist"], "tipo='ASS'", "stato"); ?>
			</td>
			<td class="FacetFieldCaptionTD">
				<a class="btn btn-sm btn-default" href="print_ticket_list.php?auxil=<?php echo $auxil; ?>&clfoco=<?php echo $_GET["clfoco"]; ?>&flt_stato=<?php echo $flt_stato; ?>&oggetto=<?php echo $_GET['oggetto']; ?>&flt_passo=<?php echo $passo; ?>"><i class="glyphicon glyphicon-list"></i>&nbsp;Stampa Lista</a>
			</td>
			<td class="FacetFieldCaptionTD">
				<input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
			</td>
		</tr>
		<?php
		}
		?>

		<?php
		if ( isset( $_GET['idinstallazione']) ) {
		$headers_assist = array(
			"ID"=>"",
			"Data"=>"",
			"Cliente"=>"",
			"Oggetto"=>"",
			"Soluzione"=>"",
			""=>"",
			"Ore"=>"",
			"Tecnico"=>"",
			"Stato"=>"",
			"Stampa"=>""
		);
		} else {
		$headers_assist = array(
			"ID" 	=> "codice",
			"Data" 		=> "data",
			"Cliente" 	=> "cliente",
			"Telefono" 	=> "telefono",
			"Oggetto" 	=> "oggetto",
			"Descrizione" => "descrizione",
			"Ore"			=> "ore",
			"Tecnico"       => "tecnico",
			"Stato" 		=> "stato",	
			"Stampa" 	=> "",
			"Elimina" 	=> ""
		);
		}

$linkHeaders = new linkHeaders($headers_assist);
$linkHeaders -> output();
$recordnav = new recordnav($gTables['assist'].
	" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
	" LEFT JOIN ".$gTables['anagra']." ON ".$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id",
	$where, $limit, $passo);
$recordnav -> output();

if (!isset($_GET['field']) or ($_GET['field'] == 2) or (empty($_GET['field'])))
	$orderby = $gTables['assist'].".codice desc";

while ($a_row = gaz_dbi_fetch_array($result)) {
?>
	<tr class="FacetDataTD">
		<td>
			<a class="btn btn-xs btn-default btn-100" href="admin_assist.php?codice=<?php echo $a_row['codice']; ?>&Update">
			<i class="glyphicon glyphicon-edit"></i><?php echo $a_row['codice']; ?></a>
		</td>
		<td>
			<?php echo $a_row['data']; ?>
		</td>
		<td>
			<a href="../vendit/report_client.php?nome=<?php echo $a_row['ragso1']; ?>">
			<?php 
				if ( strlen($a_row['ragso1']) > 20 ) {
					echo substr($a_row['ragso1'],0,20).'...'; 
				} else {
					echo $a_row['ragso1']; 
				}
			?></a>
		</td>
		<?php
			if ( !isset( $_GET['idinstallazione']) ) {
				echo "<td>".$a_row['telefo']."</td>";
			}
		?>
		<td>
			<?php echo $a_row['oggetto']; ?>
		</td>
		<?php
			if ( !isset( $_GET['idinstallazione']) ) {
				echo "<td>". $a_row['descrizione']. "</td>";
			} else {
				echo "<td colspan='2'>". $a_row['soluzione']. "</td>";
			}
		?>
		<td>
			<?php echo $a_row['ore']; ?>
		</td>
		<td>
			<?php echo $a_row['tecnico']; ?>
		</td>
		<td>
			<?php
				$filtro = '';
				if ( isset($_GET['clfoco']) ) $filtro .= '&clfoco='.$_GET['clfoco'];
				if ( isset($_GET['codice']) ) $filtro .= '&codice='.$_GET['codice'];
				if ( isset($_GET['stato']) ) $filtro .= '&stato='.$flt_stato;
				if ( isset($_GET['data']) ) $filtro .= '&data='.$_GET['data'];
			?>
			<a href="report_assist.php?chstato=<?php echo $a_row['id']."&prev=".$a_row['stato'].$filtro; ?>" class="btn btn-xs btn-edit">
				<?php echo $a_row['stato']; ?>
			</a>
		</td>
		<td>
			<a class="btn btn-xs btn-default" href="stampa_assist.php?id=<?php echo $a_row['id']; ?>&cod=<?php echo $a_row['codice']; ?>" target="_blank"><i class="glyphicon glyphicon-print"></i></a>
		</td>
<?php
	if ( !isset( $_GET['idinstallazione']) ) {
		echo "<td>
			<a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_assist.php?id=".$a_row['id']."&cod=".$a_row['codice']."\">
			<i class=\"glyphicon glyphicon-remove\"></i></a>
		</td>";
	}
?>
	</tr>
<?php 
	$totale_ore += $a_row['ore'];
} 

$passi = array(20, 50, 100, 10000 );
?>
<tr>
	<td class="FacetFieldCaptionTD" colspan="8" align="right">Totale Ore : 
		<?php echo floatval($totale_ore); ?>
	</td>
	<td class="FacetFieldCaptionTD" colspan="3" align="right">Totale Euro : 
		<?php echo floatval($totale_ore * 42); ?>
	</td>
</tr>
<tr>
	<td class="FacetFieldCaptionTD" align="center" colspan="11">Numero elementi : 
		<select name="flt_passo" onchange="this.form.submit()">		
		<?php
		foreach ( $passi as $val ) {
			if ( $val == $passo ) $selected = ' selected';
			else $selected = '';
			echo "<option value='".$val."'".$selected.">".$val."</option>";
		}
		?>
		</select>
	</td>
</tr>
</table>
</div>
<?php
}

if ( !isset( $_GET['idinstallazione']) ) { //se non viene visualizzato all'interno dello script installazioni
	echo '</form>';
	require('../../library/include/footer.php');
}
?>
