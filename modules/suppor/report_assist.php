<?php
 /*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2018 - Antonio De Vincentiis Montesilvano (PE)
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

$orderby = "data asc";
$where 	= "tipo = 'ASS' ";

if ( !isset($_GET['include']) ) {
   require_once("../../library/include/header.php");
   $script_transl=HeadMain();  
}

if ( isset($_GET['chstato'] ) ) {
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
   gaz_dbi_table_update("assist", array ("id", $_GET['chstato'])
           , array("stato" => $stato));
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
    $passo = 50;
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
    $where .= " and stato!='chiuso' ";
    //$where .= " ";
}

gaz_flt_var_assign('codice', 'i');
gaz_flt_var_assign('data', 'd');
gaz_flt_var_assign('clfoco', 'v');
gaz_flt_var_assign('telefo', 'v');
gaz_flt_var_assign('oggetto', 'v');
gaz_flt_var_assign('descrizione', "v");
gaz_flt_var_assign('tecnico', "v");
gaz_flt_var_assign('stato', "v");

if ( isset($_GET['flt_cliente']) ) {
    $flt_cliente = $_GET['clfoco'];
} else {
    $flt_cliente = "tutti";
}

if ( $flt_cliente!="tutti" ) {
	$where .= " and ".$gTables['assist'].".clfoco = '".$flt_cliente."'";
}

?>
<div align="center" class="FacetFormHeaderFont"></div>
    <form method="GET">
      <div class="box-body table-responsive">
	<table class="Tlarge table table-striped table-bordered table-condensed">
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
                <a class="btn btn-sm btn-default" href="print_ticket_list.php?auxil=<?php echo $auxil; ?>&flt_cliente=<?php echo $flt_cliente; ?>&flt_stato=<?php echo $flt_stato; ?>&flt_passo=<?php echo $passo; ?>"><i class="glyphicon glyphicon-list"></i>&nbsp;Stampa Lista</a>
            </td>
            <td class="FacetFieldCaptionTD">
                <input type="submit" class="btn btn-sm btn-default" name="search" value="Cerca" tabindex="1" onClick="javascript:document.report.all.value = 1;">
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
   $orderby = $gTables['assist'].".codice desc";

$result = gaz_dbi_dyn_query($gTables['assist'].".*,
		".$gTables['anagra'].".ragso1, ".$gTables['anagra'].".telefo ", $gTables['assist'].
		" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
		" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
		$where, $orderby, $limit, $passo);

while ($a_row = gaz_dbi_fetch_array($result)) {
?>
   <tr class="FacetDataTD">
		<td>
			<a class="btn btn-xs btn-default btn-100" href="admin_assist.php?codice=<?php echo $a_row["codice"]; ?>&Update">
			<i class="glyphicon glyphicon-edit"></i><?php echo $a_row["codice"]; ?></a>
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
                    <?php
                    $filtro = "";
                    if ( isset($_GET["flt_cliente"]) ) {
                        $filtro = "&flt_cliente=".$_GET["flt_cliente"];
                    }?>
         <a href="report_assist.php?chstato=<?php echo $a_row["id"]."&prev=".$a_row["stato"].$filtro."&clfoco=".$flt_cliente; ?>" class="btn btn-xs btn-edit">
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
</div>
<?php
if ( !isset($_GET['include']) ) {
    echo "</form>";
    require("../../library/include/footer.php");
}
?>