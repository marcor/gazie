<?php
 /*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2014 - Antonio De Vincentiis Montesilvano (PE)
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
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin();
$titolo = 'Assistenze periodiche';

require("../../library/include/header.php");
$script_transl=HeadMain();

$orderby = "data asc";
$where 	= "tipo = 'ASP' ";
$all 	= $where;

if ( isset($_GET["q"]) && $_GET["q"]=="avv" ) gaz_dbi_table_update("assist", $_GET["codice"], array("stato" => "avvisato"));
if ( isset($_GET["q"]) && $_GET["q"]=="eff" ) gaz_dbi_table_update("assist", $_GET["codice"], array("stato" => "effettuato"));
if ( isset($_GET["q"]) && $_GET["q"]=="chi" ) {
   $result = gaz_dbi_dyn_query( $gTables['assist'].".*", $gTables['assist'], " codice='".$_GET['codice']."'" );
   $value = gaz_dbi_fetch_array($result);
   $rs_ultima_ass = gaz_dbi_dyn_query("codice", $gTables['assist'],"1","codice desc");
	$ultimo_documento = gaz_dbi_fetch_array($rs_ultima_ass);
	if ($ultimo_documento) {      
		$value['codice'] = $ultimo_documento['codice'] + 1;
	}
   $value["stato"]="aperto";
   $value["data"]=date( "Y-m-d", strtotime( "+1 years", strtotime($value["data"]) ));
   gaz_dbi_table_insert("assist", $value);
   gaz_dbi_table_update("assist", $_GET["codice"], array("stato" => "chiuso"));
}

if ( !isset($_GET["all"]) ) {
   $where .= " and stato != 'chiuso'";
   $where .= "and data>'".date("Y-m-d", strtotime("-1 month"))."' and data<'".date("Y-m-d", strtotime("+2 month"))."'";
}

?>
<div align="center" class="FacetFormHeaderFont">Assistenze Periodiche</div>
	<form method="GET">
	<!-- riga filtro -->
	<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
		<tr>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
        <td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			&nbsp;
		</td>
		<td class="FacetFieldCaptionTD">
			<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">
		</td>
		</tr>

		<?php 
		//riga ordinamento colonne
		$headers_assist = array  (
			"ID" 	=> "codice",
			"Data" 		=> "data",
			"Cliente" 	=> "cliente",
			"Telefono" 	=> "telefono",
			"Oggetto" 	=> "oggetto",
			"Descrizione" => "descrizione",
         "Tecnico"   => "tecnico",
			"Stato" 		=> "stato",	
			"Stampa" 	=> "",
			"Elimina" 	=> ""
		);
		$linkHeaders = new linkHeaders($headers_assist);
		$linkHeaders -> output();

		$recordnav = new recordnav($gTables['assist'].
				" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
				" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id",
			$where, $limit, $passo);
		$recordnav -> output();

$result = gaz_dbi_dyn_query( $gTables['assist'].".*,
				".$gTables['anagra'].".ragso1, 
				".$gTables['anagra'].".telefo ",
				$gTables['assist'].
					" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
					" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
				$where, $orderby, $limit, $passo);

$month = array(1=>"Gennaio", 2=>"Febbraio", 3=>"Marzo", 4=>"Aprile", 5=>"Maggio", 6=>">Giugno", 7=>"Luglio", 8=>"Agosto", 9=>"Settembre", 10=>"Ottobre", 11=>"Novembre", 12=>"Dicembre");

while ($a_row = gaz_dbi_fetch_array($result)) {
?>
   <tr class="FacetDataTD">
		<td>
			<a class="btn btn-xs btn-edit" href="admin_period.php?codice=<?php echo $a_row["codice"]; ?>&Update">
			<?php echo $a_row["codice"]; ?></a>
		</td>
		<td><?php echo date("d",strtotime($a_row["data"]))." ".$month[date("n",strtotime($a_row["data"]))]." ".date("Y",strtotime($a_row["data"])); ?></td>
		<td><a href="../vendit/report_client.php?auxil=<?php echo $a_row["ragso1"]; ?>&search=Cerca">
		<?php 
			if ( strlen($a_row["ragso1"]) > 20 ) {
				echo substr($a_row["ragso1"],0,20)."..."; 
			} else {
				echo $a_row["ragso1"]; 
			}
		?></a>
		</td>
		<td><?php echo $a_row["telefo"]; ?></td>
		<td><?php echo $a_row["oggetto"]; ?></td>
		<td><?php 
            $length = strlen($a_row["descrizione"]);
            $descri = substr($a_row["descrizione"], 0, 80);
            echo $descri."..."; ?></td>
      <td><?php echo $a_row["tecnico"]; ?></td>
		<td>
			<?php 
				if ( $a_row["stato"]=="aperto" ) echo '<a class="btn btn-xs btn-edit" href="report_period.php?codice='.$a_row["codice"].'&q=avv">Avvisa</a>';
				if ( $a_row["stato"]=="avvisato" ) echo '<a class="btn btn-xs btn-fatt" href="report_period.php?codice='.$a_row["codice"].'&q=eff">Effettua</a>';
            if ( $a_row["stato"]=="effettuato" ) echo '<a class="btn btn-xs btn-riba" href="report_period.php?codice='.$a_row["codice"].'&q=chi">Chiudi</a>';
            if ( $a_row["stato"]=="chiuso" ) echo '<btn class="btn btn-xs btn-cont">Chiuso</btn>';
				//echo "<pre>";
				//print_r ( get_defined_vars() );
				//echo "</pre>";
			?>
		</td>
		<td>
			<a class="btn btn-xs btn-default" href="stampa_period.php?id=<?php echo $a_row["id"]; ?>&cod=<?php echo $a_row["codice"]; ?>&stato=<?php echo $a_row["stato"]; ?>" target="_blank"><i class="glyphicon glyphicon-print"></i></a>
		</td>
		<td>
			<a class="btn btn-xs btn-default btn-elimina" href="delete_assist.php?id=<?php echo $a_row["id"]; ?>&cod=<?php echo $a_row["codice"]; ?>">
			<i class="glyphicon glyphicon-remove"></i></a>
		</td>
   </tr>
<?php 
	//$totale_ore += $a_row["ore"];
} 

$passi = array(20, 50, 100, 10000 );
?>
<!-- riga riepilogo tabella -->
<tr>
	<td class="FacetFieldCaptionTD" colspan="8" align="right">Totale Ore : 
		<?php //echo floatval($totale_ore); ?>
	</td>
	<td class="FacetFieldCaptionTD" colspan="3" align="right">Totale Euro : 
		<?php //echo floatval($totale_ore * 42); ?>
	</td>
</tr>
<tr>
	<td class="FacetFieldCaptionTD" align="center" colspan="11">Numero elementi : 
		<select name="flt_passo" onchange="this.form.submit()">		
		<?php
		foreach ( $passi as $val ) {
			if ( $val == $passo ) $selected = " selected";
			else $selected = "";
			echo "<option value='".$val."'".$selected.">".$val."</option>";
		}
		?>
		</select>
	</td>
</tr>
</table>
</form>
</div><!-- chiude div container role main --></body>
</html>