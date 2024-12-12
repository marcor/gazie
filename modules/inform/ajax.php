<?php
/*
 --------------------------------------------------------------------------
  GAzie - MODULO 'VACATION RENTAL'
  Copyright (C) 2022-2023 - Antonio Germani, Massignano (AP)
  (https://www.programmisitiweb.lacasettabio.it)

  --------------------------------------------------------------------------
  --------------------------------------------------------------------------
  GAzie - Gestione Azienda
  Copyright (C) 2004-2022 - Antonio De Vincentiis Montesilvano (PE)
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
// prevent direct access

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (isset ($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'modules/root/admin.php') !== false) {

}elseif (!$isAjax) {
    $user_error = 'Access denied - not an AJAX request...';
    trigger_error($user_error, E_USER_ERROR);
}

if (isset($_GET['type'])) {
	require("../../library/include/datlib.inc.php");
  $admin_aziend = checkAdmin(9);
	switch ($_GET['type']) {
		case "save":
      $src_path="../../data/files";
      $dest_path="../../data/files/backups/backup".date("dmYhis")."/data_files";
      $zip_src_path="../../data/files/backups/backup".date("dmYhis")."/data_files.zip";
      if ( !is_dir( $dest_path ) ) {
          //echo"<br>Creo la nuova direttory di destinazione:",$dest_path;
          mkdir($dest_path , 0777, true);
        }

			$dump = new MySQLDump($link);
			try {@$dump->save('../../data/files/backups/backup'.date("dmYhis").'/' . $Database . '-' . date("YmdHi") . '-v' . GAZIE_VERSION . '.sql.gz');
				gaz_dbi_put_row($gTables['config'], 'variable', 'last_backup', 'cvalue', date('Y-m-d'));
			}
			catch(Exception $e){
			  echo $e->getMessage();
			}
      if (extension_loaded('zip')) {
        // cartelle di file da escludere dal backup
        $exclude = array(
        'backups',
        $admin_aziend['company_id']-1,
        $admin_aziend['company_id']-2,
        $admin_aziend['company_id']-3,
        $admin_aziend['company_id']+1,
        $admin_aziend['company_id']+2,
        $admin_aziend['company_id']+3,
        'tmp',
        ''
        );
        // files da escludere dal backup
        $excludeFiles = array(
        '.htaccess',
        ''
        );

        $dirCopy = new dirCopyClass() ;
        $dirCopy -> dirCopy($exclude, $excludeFiles, $src_path , $dest_path );
        //echo "<br/> Inizio ZIP del backup file";
        if (is_dir($dest_path)) {
          // Initialize archive object
          $zip = new ZipArchive();
          if ($zip->open($zip_src_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)){
            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
              new RecursiveDirectoryIterator($dest_path),
              RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $name => $file){
              // Skip directories (they would be added automatically)
              if (!$file->isDir())    {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($dest_path) + 10);
                //echo"<br>Relative path:",$relativePath," <br> filepath:",$filePath," <br> dest path:",$dest_path;
                //echo "<br>zipping file path:",$filePath,"<br> relative path:",$relativePath;
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
              }
            }
            // Zip archive will be created only after closing object
            $zip->close();
            //echo "<br/> Backup file zippato";

            if ( is_dir( $dest_path ) ) {
              //echo "<br> Avvio rimozione backup files non zippato";
              $it = new RecursiveDirectoryIterator($dest_path, RecursiveDirectoryIterator::SKIP_DOTS);
              $files = new RecursiveIteratorIterator($it,
              RecursiveIteratorIterator::CHILD_FIRST);
              foreach($files as $file) {
                if ($file->isDir()){
                  rmdir($file->getRealPath());
                } else {
                  unlink($file->getRealPath());
                }
              }
              if (rmdir($dest_path)){
                //echo "<br/> unzip backup files removed";
              }
            }

          } else {
            echo "Non riesco ad aprire in scrittura il file zip";die;
          }
        }

      }else{
        echo "Devi attivare la libreria zip di Php";
      }
			break;
	}
}
?>
