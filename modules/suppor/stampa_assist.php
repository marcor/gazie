<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2017 - Antonio De Vincentiis Montesilvano (PE)
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
$title = "";
require("lang.".$admin_aziend['lang'].".php");
if ( !isset($_GET['id'])) {
    header("Location: report_assist.php");
    exit;
}
require("../../config/templates/report_template.php");

if ( isset($_GET['id']) ){
   $sql = $gTables['assist'].'.id = '.intval($_GET['id']).' ';
} else {
   $sql = $gTables['assist'].'.id > 0 ';
}
$where = $sql;

//$what = $gTables['assist'].".* ";
/*$table = $gTables['assist']." LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice
                              LEFT JOIN ".$gTables['anagra']." ON ".$gTables['anagra'].".id = ".$gTables['clfoco'].".id_anagra ";*/

$result = gaz_dbi_dyn_query($gTables['assist'].".*,
		".$gTables['anagra'].".ragso1, ".$gTables['anagra'].".telefo, ".$gTables['anagra'].".cell, ".$gTables['anagra'].".fax, ".$gTables['clfoco'].".codice ",  $gTables['assist'].
		" LEFT JOIN ".$gTables['clfoco']." ON ".$gTables['assist'].".clfoco = ".$gTables['clfoco'].".codice". 
		" LEFT JOIN ".$gTables['anagra'].' ON '.$gTables['clfoco'].'.id_anagra = '.$gTables['anagra'].'.id',
		$where, "id", $limit, $passo);
		
//$result = gaz_dbi_dyn_query ($what, $table,$where,"clfoco");
//echo " what: ".$what. " table: ".$table. " where: ".$where." <br>";

/*while ($a_row = gaz_dbi_fetch_array($result)) {
	echo $a_row["descri"]."<br>";
}*/

$pdf = new Report_template();
$pdf->setVars($admin_aziend,$title);
$pdf->SetTopMargin(32);
$pdf->SetFooterMargin(20);
$config = new Config;
$pdf->AddPage('P',$config->getValue('page_format'));
$pdf->SetFillColor(hexdec(substr($admin_aziend['colore'],0,2)),hexdec(substr($admin_aziend['colore'],2,2)),hexdec(substr($admin_aziend['colore'],4,2)));

$row = gaz_dbi_fetch_array($result);

$html = "<span style=\"font-family: arial,helvetica,sans-serif; font-size:28px;\">";
$html .= "Nome Tecnico : <b>".$row["tecnico"]."</b><br/>";
$html .= "Cliente : <b>". $row["codice"] ." - ". $row["ragso1"]."</b><br>";
if ( $row["telefo"] ) $html .= "Telefono : <b>".$row["telefo"]."</b><br>";
if ( $row["telefo"] ) $html .= "Cellulare : <b>".$row["cell"]."</b><br>";
if ( $row["telefo"] ) $html .= "Fax : <b>".$row["fax"]."</b><br>";
$html .= "</span>";

$html .= "
	<p>
					<span style=\"font-family: arial,helvetica,sans-serif; font-size:28px;\">Il cliente consegna al centro assistenza il seguente materiale :<br />
					<strong>".$row["oggetto"]."</strong><br />
					<br />
					dichiarando i seguenti difetti, malfunzionamento o lavori da effettuare :<br />
					<strong>".$row["descrizione"]."</strong><br /><br />
					<br />";
if ( $row["info_agg"] ) {
                    $html .= "dichiarando o consegnando anche :<br />
                    <strong>".$row["info_agg"]."</strong><br />";
}
$html .= "</span>
    </p>
				<p style=\"text-align: justify;\">
					<span style=\"font-size:28px;\"><span style=\"font-family: arial,helvetica,sans-serif;\"><strong>Condizioni e termini per la presa in carico e ritiro del prodotto :</strong></span></span><br />
					&nbsp;</p>
				<ol>
					<li style=\"text-align: justify;\">
						<span style=\"font-size:28px;\"><span style=\"font-family: arial,helvetica,sans-serif;\">L&#39;intervento se in garanzia, copre esclusivamente i difetti di conformit&agrave; del prodotto acquistato presso il laboratorio, ai sensi della legge. Non sono coperti da garanziai prodotti che presentino chiari segni di manomissione o guasti causati da un&#39;uso improprio del prodotto o da agenti esterni non riconducibili a vizi e/o difetti di fabbricazione. In tal caso il laboratorio non sar&agrave;, pertanto, tenuto ad effettuare gratuitamente le riparazioni necessarie, ma potr&agrave; effettuarle, su richiesta del cliente a pagamento e secondo il preventivo che verr&agrave; fornito.</span></span><br />
						&nbsp;</li>
					<li style=\"text-align: justify;\">
						<span style=\"font-size:28px;\"><span style=\"font-family: arial,helvetica,sans-serif;\">Il cliente dichiara di essere a conoscenza che l&#39;intervento per la riparazione pu&ograve; comportare l&#39;eventuale perdita totale o parziale di programmi e dati in qualunque modo contenuti o registrati nel prodotto consegnato per la riparazione. Il laboratorio non si assume responsabili&agrave; alcuna riguardo a tale perdita, pertanto &egrave; esclusiva cura del cliente assicurarsi di aver effettuato le copie di sicurezza dei dati. A tale proposito si consiglia di richiedere al laboratorio, che provveder&agrave; a titolo oneroso, per l&#39;effettuazione dei backup di tutti i dati. In ogni caso il cliente &egrave; unico ed esclusivo responsabile di dati, informazioni e programmi contenuti o registrati in qualunque modo nel prodotto consegnato al laboratorio con particolare riferimento alla liceit&ugrave; e legittima titolarit&agrave; degli stessi.</span></span><br />
						&nbsp;</li>
					<li style=\"text-align: justify;\">
						<span style=\"font-size:28px;\"><span style=\"font-family: arial,helvetica,sans-serif;\">Salvo diversi accordi scritti, il cliente &egrave; tenuto a ritirare il prodotto recandosi presso il punto vendita secondo ti tempi indicati dal laboratorio medesimo. Nel caso in cui il cliente non ritiri il prodotto nel termine di 30gg. dalla data di riparazione, il cliente si impegna sin d&#39;ora a corrispondere al laboratorio una somma pari a 5,00 &euro; a titolo di deposito per ogni giorno di permanenza del prodotto presso il laboratorio.</span></span><br />
					</li>
				</ol><table><tr><td align=\"center\">Firma cliente</td><td align=\"center\">Firma tecnico</td></tr></table>
			";

/*<<<EOD
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
EOD;*/

$pdf->writeHTMLCell(0, 20, '', '', $html, 0, 1, 0, true, '', true);
	
$pdf->Output();
?>