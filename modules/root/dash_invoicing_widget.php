<div class="panel panel-info col-md-12" >
    <div class="box-header company-color">
        <div class="box-title"><b>Situazione della fatturazione</b>
		<a class="pull-right dialog_grid" id_bread="<?php echo $grr['id_bread']; ?>" style="cursor:pointer;"><i class="glyphicon glyphicon-cog"></i></a></div>
	</div>
    <div class="box-body">
        <table id="invoicing" class="table table-bordered table-striped dataTable" 
		<thead>
                <tr>
                    <th class="text-center bg-danger">Tipo di segnalazione</th>
                    <th class="text-center bg-danger">numero</th>
                    <th class="text-center bg-danger">Operazione consiglata</th>
                </tr>
            </thead>
            <tbody>
    <!-- per adesso lo faccio collassare in caso di small device anche se si potrebbe fare uno switch in verticale -->
    <?php
    $rs_fatacq=gaz_dbi_dyn_query( "COUNT(DISTINCT CONCAT(YEAR(datreg), protoc)) AS cnt", $gTables['tesdoc'], "id_con = 0 AND tipdoc LIKE 'AF_'", 'datreg');
	$fatacq = gaz_dbi_fetch_array($rs_fatacq);
	if ($fatacq['cnt']>0){
?>
                <tr>
                    <td><b class="text-info">Fatture d'acquisto da contabilizzare</b></td>
                    <td><b><?php echo $fatacq['cnt']; ?></b></td>
                    <td><a class="btn btn-info">Contabilizza fatture di acquisto<i class="glyphicon glyphicon-export"></i></a></td>
                </tr>
<?php
    }
    $rs_fatven=gaz_dbi_dyn_query( "COUNT(DISTINCT CONCAT(YEAR(datreg), protoc)) AS cnt", $gTables['tesdoc'], "id_con = 0 AND tipdoc LIKE 'F%'", 'datreg');
	$fatven = gaz_dbi_fetch_array($rs_fatven);
	if ($fatven['cnt']>0){
?>
                <tr>
                    <td><b class="text-success">Fatture di vendita da contabilizzare</b></td>
                    <td><b><?php echo $fatven['cnt']; ?></b></td>
                    <td><a class="btn btn-success">Contabilizza fatture di vendita<i class="glyphicon glyphicon-export"></i></a></td>
                </tr>
<?php
    }
    $rs_ddtven=gaz_dbi_dyn_query( "COUNT(*) AS cnt", $gTables['tesdoc'], "protoc = 0 AND ( tipdoc = 'DDT' OR tipdoc ='CMR' OR (tipdoc = 'DDV' AND datemi <  DATE_SUB(NOW(),INTERVAL 1 YEAR) ) )", 'id_tes');
	$ddtven = gaz_dbi_fetch_array($rs_ddtven);
	if ($ddtven['cnt']>0){
?>
                <tr>
                    <td><b class="text-warning">D.d.T. di vendita da fatturare</b></td>
                    <td><b><?php echo $ddtven['cnt']; ?></b></td>
                    <td><a class="btn btn-warning" href="../vendit/emissi_fatdif.php">Genera fatture differite <i class="glyphicon glyphicon-export"></i></a></td>
                </tr>
<?php
    }
    $rs_geneff=gaz_dbi_dyn_query( "COUNT(*) AS cnt", $gTables['tesdoc']." AS tesdoc LEFT JOIN ". $gTables['pagame'] . ' AS pay ON tesdoc.pagame=pay.codice', "(tippag = 'B' OR tippag = 'T' OR tippag = 'V' OR tippag = 'I') AND geneff = '' AND tipdoc LIKE 'FA_'", 'id_tes');
	$geneff = gaz_dbi_fetch_array($rs_geneff);
	if ($geneff['cnt']>0){
?>
                <tr>
                    <td><b class="text-danger">Fatture che devono generare effetti</b></td>
                    <td><b><?php echo $geneff['cnt']; ?></b></td>
                    <td><a class="btn btn-danger">Genera effetti da fatture<i class="glyphicon glyphicon-export"></i></a></td>
                </tr>
<?php
    }
    ?>
            </tbody>
        </table>
	</div>
</div>