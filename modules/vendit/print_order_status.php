<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-present - Antonio De Vincentiis Montesilvano (PE)
         (https://www.devincentiis.it)
           <https://gazie.sourceforge.net>
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

if (!isset($_GET['utsini']) || !isset($_GET['utsfin']) ||  !isset($_GET['status']) ) {
  header("Location: docume_vendit.php");
  exit;
} else {
  $status = intval($_GET['status']);
  require("../../config/templates/report_template.php");
  require("lang.".$admin_aziend['lang'].".php");
  $script_transl = $strScript["select_order_status.php"];
  $gazTimeFormatter->setPattern('dd MMMM yyyy');
  $luogo_data = $admin_aziend['citspe'].", lì ".ucwords($gazTimeFormatter->format(new DateTime()));
  $title =['luogo_data'=> $luogo_data,
           'title'=>'Situazione ordini ('.$script_transl['status_value'][$status] .') dal '.date("d-m-Y",$_GET['utsini']).' al '.date("d-m-Y",$_GET['utsfin']),
           'hile'=>[['lun' => 10,'nam'=>'Num.'],
                    ['lun' => 15,'nam'=>'Data'],
                    ['lun' => 80,'nam'=>'Cliente'],
                    ['lun' => 20,'nam'=>'Q.tà ord.'],
                    ['lun' => 20,'nam'=>'Q.tà eva.'],
                    ['lun' => 20,'nam'=>'€ ordine'],
                    ['lun' => 20,'nam'=>'€ evasi'],
                   ]
            ];
  $pdf = new Report_template();
  $pdf->setVars($admin_aziend,$title);
  $pdf->SetTopMargin(40);
  $config = new Config;
  $pdf->SetFont('helvetica','',9);
  $pdf->AddPage();
  $pdf->setRiporti('');
  $pdf->Output();
}
?>
