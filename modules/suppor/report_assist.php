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
require_once("../../library/include/datlib.inc.php");

$admin_aziend=checkAdmin();
$titolo = 'Assistenza Clienti';
$totale_ore = 0;
$stati = array();

if ( !isset($_GET['include']) ) {
   require_once("../../library/include/header.php");
   $script_transl=HeadMain();  
}

if ( isset($_GET['chstato'] ) ) {
   //$result = gaz_dbi_dyn_query(" DISTINCT ".$gTables['assist'].".stato", $gTables['assist']," tipo='ASS'", "stato asc", "0", "9999");   
   //$rows = gaz_dbi_fetch_all($result);
   $rows = array ("aperto","effettuato","chiuso");
   $found = false;
   for ($t=0; $t<count($rows); $t++ ) {
      if ( $found == true ) {
         $stato = $rows[$t];
         $found = false;
      }
      if ( $rows[$t]==$_GET['prev'] && $t<count($rows)-1 ) $found=true;
      elseif  ( $rows[$t]==$_GET['prev'] && $t==count($rows)-1 ) {
         $stato = $rows[0];
      }
   }
   gaz_dbi_table_update("assist", array ("id", $_GET['chstato']), array("stato" => $stato));
}

if ( isset($_GET['auxil']) ) {
   $auxil = $_GET['auxil'];
   $where = "tipo = 'ASS' and ".$gTables['anagra'].".ragso1 like '%$auxil%'";	
} else {
   $auxil = "";
   $where = "tipo = 'ASS' and ".$gTables['anagra'].".ragso1 like '%%'";	
}
$all	= $where;

if ( isset( $_GET['idinstallazione']) ) {
   $where .= " and idinstallazione=".$_GET['idinstallazione'];
}

if ( isset($_GET['flt_passo']) ) {
	$passo = $_GET['flt_passo'];
} else {
	$passo = 20;
}

if ( isset($_GET['flt_tecnico']) ) {
	$flt_tecnico = $_GET['flt_tecnico'];
	if ( $flt_tecnico!="tutti" ) {
		$where .= " and tecnico = '".$flt_tecnico."'";
	}
} else {
	$flt_tecnico = "tutti";
}
if ( isset($_GET['flt_stato']) ) {
	$flt_stato = $_GET['flt_stato'];
	if ( $flt_stato!="tutti" ) {
		if ( $flt_stato=="nochiusi" ) {
			$where .= " and stato != 'chiuso' and stato != 'contratto' ";
		} else {
			$where .= " and stato = '".$flt_stato."'";
		}
	}
} else {
	$flt_stato = "nochiusi";
	$where .= " and stato != 'chiuso'";
}

if ( isset($_GET['flt_cliente']) ) {
	$flt_cliente = $_GET['flt_cliente'];
} else {
	$flt_cliente = "tutti";
}

if ( $flt_cliente!="tutti" ) {
	$where .= " and ".$gTables['assist'].".clfoco = '".$flt_cliente."'";
}

?>
<div align="center" class="FacetFormHeaderFont">Assistenza Tecnica</div>
	<form method="GET">
	<table class="Tlarge table table-striped table-bordered table-condensed table-responsive">
		<tr>
		<td class="FacetFieldCaptionTD" colspan="4">
			<input type="text" name="auxil" value="<?php if ($auxil != "&all=yes") echo $auxil; ?>" maxlength="15" size="15" tabindex=1 class="FacetInput">
			<input type="submit" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value=1;">
		
		</td>
		<td class="FacetFieldCaptionTD" colspan="2">
		<select name="flt_cliente" onchange="this.form.submit()">
			<?php
			$result = gaz_dbi_dyn_query(" DISTINCT ".$gTables['assist'].".clfoco, ".$gTables['anagra'].".ragso1",	$gTables['assist'].
				" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice".
				" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id"
				,$where, "clfoco", "0", "9999");
			echo "<option value=\"tutti\" ".($flt_cliente=="tutti"?"selected":"").">tutti</option>";
			while ($stati = gaz_dbi_fetch_array($result)) {
					if ( $flt_cliente == $stati["clfoco"] ) $selected = "selected";
					else $selected = "";
					echo "<option value=\"".$stati["clfoco"]."\" ".$selected.">".$stati["ragso1"]."</option>";
			}
			?>
		</select>
		</td>
		<td class="FacetFieldCaptionTD">&nbsp;</td>
        <td class="FacetFieldCaptionTD"><select name="flt_tecnico" onchange="this.form.submit()">
			<?php
			$result = gaz_dbi_dyn_query(" DISTINCT ".$gTables['assist'].".tecnico", $gTables['assist'],"", "tecnico", "0", "9999");
			echo "<option value=\"tutti\" ".($flt_tecnico=="tutti"?"selected":"").">tutti</option>";
			while ($tecnici = gaz_dbi_fetch_array($result)) {
					if ( $flt_tecnico == $tecnici["tecnico"] ) {
                        $selected = "selected"; 
                    } else $selected = "";
					echo "<option value=\"".$tecnici["tecnico"]."\" ".$selected.">".$tecnici["tecnico"]."</option>";
			}
			?>
		</select></td>
		<td class="FacetFieldCaptionTD"><select name="flt_stato" onchange="this.form.submit()">
			<?php
			$result = gaz_dbi_dyn_query(" DISTINCT ".$gTables['assist'].".stato", $gTables['assist']," tipo='ASS' and stato!='chiuso'", "stato", "0", "9999");
			echo "<option value=\"tutti\" ".($flt_stato=="tutti"?"selected":"").">tutti</option>";
			echo "<option value=\"nochiusi\" ".($flt_stato=="nochiusi"?"selected":"").">non chiuso</option>";
         echo "<option value=\"chiuso\" ".($flt_stato=="chiuso"?"selected":"").">chiuso</option>";
			while ($stati = gaz_dbi_fetch_array($result)) {
					
					if ( $flt_stato == $stati["stato"] ) $selected = "selected"; 
					else $selected = "";
					echo "<option value=\"".$stati["stato"]."\" ".$selected.">".$stati["stato"]."</option>";
			}
			?>
		</select></td>
		<td class="FacetFieldCaptionTD" colspan="2">
			<!--<input type="submit" name="all" value="Mostra tutti" onClick="javascript:document.report.all.value=1;">-->
			<a class="btn btn-xs btn-default" href="print_ticket_list.php?auxil=<?php echo $auxil; ?>&flt_cliente=<?php echo $flt_cliente; ?>&flt_stato=<?php echo $flt_stato; ?>&flt_passo=<?php echo $passo; ?>"><i class="glyphicon glyphicon-list"></i>&nbsp;Stampa Lista</a>
		</td>
		</tr>

		<?php 
      if ( isset($_GET['include']) ) {
      $headers_assist = array  (
			"ID" 	=> "codice",
			"Data" 		=> "data",
			"Cliente" 	=> "cliente",
			"Oggetto" 	=> "oggetto",
			"Soluzione" => "soluzione",             
			""          => "",
         "Ore"			=> "ore",
         "Tecnico"       => "tecnico",
			"Stato" 		=> "stato",	
			"Stampa" 	=> ""
			//"Elimina" 	=> ""
		);   
      } else {
		$headers_assist = array  (
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
	" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].".id_anagra = ".$gTables['anagra'].".id",
	$where, $limit, $passo);
$recordnav -> output();

if (!isset($_GET['field']) or ($_GET['field'] == 2) or (empty($_GET['field'])))
   $orderby = "codice desc";

$result = gaz_dbi_dyn_query($gTables['assist'].".*,
		".$gTables['anagra'].".ragso1, ".$gTables['anagra'].".telefo ", $gTables['assist'].
		" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
		" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
		$where, $orderby, $limit, $passo);

while ($a_row = gaz_dbi_fetch_array($result)) {
?>
   <tr class="FacetDataTD">
		<td>
			<a class="btn btn-xs btn-edit" href="admin_assist.php?codice=<?php echo $a_row["codice"]; ?>&Update">
			<?php echo $a_row["codice"]; ?></a>
		</td>
		<td><?php echo $a_row["data"]; ?></td>
		<td><a href="../vendit/report_client.php?auxil=<?php echo $a_row["ragso1"]; ?>&search=Cerca">
		<?php 
			if ( strlen($a_row["ragso1"]) > 20 ) {
				echo substr($a_row["ragso1"],0,20)."..."; 
			} else {
				echo $a_row["ragso1"]; 
			}
		?></a>
		</td>
		<?php
         if ( !isset($_GET['include']) ) {
            echo "<td>".$a_row["telefo"]."</td>";
         }
      ?>
      
		<td><?php echo $a_row["oggetto"]; ?></td>
		<?php
         if ( !isset($_GET['include']) ) {
            echo "<td>". $a_row["descrizione"]. "</td>";
         } else {
            echo "<td colspan='2'>". $a_row["soluzione"]. "</td>";
         }     
      ?>
		<td><?php echo $a_row["ore"]; ?></td>
      <td><?php echo $a_row["tecnico"]; ?></td>
		<td>
         <a href="report_assist.php?chstato=<?php echo $a_row["id"]."&prev=".$a_row["stato"];?>" class="btn btn-xs btn-default">
            <?php echo $a_row["stato"]; ?>
         </a>
      </td>
		<td>
			<a class="btn btn-xs btn-default" href="stampa_assist.php?id=<?php echo $a_row["id"]; ?>&cod=<?php echo $a_row["codice"]; ?>" target="_blank"><i class="glyphicon glyphicon-print"></i></a>
		</td>
		<?php
      if ( !isset($_GET['include']) ) {
      echo "<td>
			<a class=\"btn btn-xs btn-default btn-elimina\" href=\"delete_assist.php?id=".$a_row["id"]."&cod=".$a_row["codice"]."\">
			<i class=\"glyphicon glyphicon-remove\"></i></a>
         </td>";
      }
      ?>
   </tr>
<?php 
	$totale_ore += $a_row["ore"];
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