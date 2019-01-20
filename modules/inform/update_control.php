<?php
/*
 --------------------------------------------------------------------------
                            GAzie - Gestione Azienda
    Copyright (C) 2004-2019 - Antonio De Vincentiis Montesilvano (PE)
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
$admin_aziend=checkAdmin(9);

// Qui viene tenuto dagli sviluppatori la lista dei siti che hanno messo a disposizione il file di check della propria versione
$tutor[1] = array('zone'=>'Abruzzo','city'=>'Montesilvano (PE)','sms'=>'+393383121161','web'=>'http://www.devincentiis.it','check'=>'http://www.devincentiis.it/file_ver');
// fine lista
$configurazione = gaz_dbi_get_row($gTables['config'],'variable','update_url');
// se si ha un sito "personalizzato" per il download diverso da quello ufficiale su Sourceforge: modifico quello di default
$URI_files = gaz_dbi_get_row($gTables['config'],'variable','update_URI_files');
if (!empty($URI_files['cvalue'])){ $update_URI_files = $URI_files['cvalue']; }
require("../../library/include/header.php");


if (isset($_POST['check'])){// se viene richiesta una modifica della fonte di check
    foreach ($_POST['check'] as $key => $value){
         if ($key != 'disabled'){
             //modifico il valore della configurazione sul DB
             gaz_dbi_put_row($gTables['config'], 'variable','update_url', "cvalue", $tutor[$key]['check']);
         } else {
             gaz_dbi_put_row($gTables['config'], 'variable','update_url', "cvalue", '' );
         }
    }
    $configurazione = gaz_dbi_get_row($gTables['config'],'variable','update_url');
}

function tutor_list($tutor,$configurazione,$script_transl)
{
    echo "<form method=\"POST\"><table class=\"Tlarge table table-striped table-bordered table-condensed table-responsive\">\n";
    echo "<tr><th class=\"FacetFieldCaptionTD\">".$script_transl['zone']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['city']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['sms']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['web']."</th>
              <th class=\"FacetFieldCaptionTD\">".$script_transl['choice']."</th></tr>\n";
    foreach ($tutor as $key => $value){
            echo "<tr><td>".$value['zone']."</td>\n";
            echo "<td>".$value['city']."</td>\n";
            echo "<td>".$value['sms']."</td>\n";
            echo "<td align=\"center\"><a href=\"".$value['web']."\" target=\"_NEW\">".$value['web']."</a></td>\n";
            if (!empty($value['check']) and $configurazione['cvalue'] == $value['check']) {
               echo "<td class=\"FacetDataTD\" align=\"right\"><input disabled style=\"color:red;\" type=\"submit\" value=\"".$script_transl['check_value'][1]."\" name=\"check[$key]\" title=\"".$script_transl['check_title_value'][1]."\" /></td></tr>\n";
            } else {
               echo "<td align=\"right\"><input type=\"submit\" value=\"".$script_transl['check_value'][0]."\" name=\"check[$key]\" title=\"".$script_transl['check_title_value'][0]."\" /></td></tr>\n";
            }
    }
    echo "<tr><td colspan=\"5\" class=\"FacetDataTD\" align=\"right\"><input type=\"submit\" value=\"".$script_transl['all_disabling'][0]."\" name=\"check[disabled]\" title=\"".$script_transl['all_disabling'][1]."\" /></td></tr>\n";
    echo "</table></form>";
}

$script_transl=HeadMain();
?>
<div align="center" class="FacetFormHeaderFont"><?php echo $script_transl['title']; ?></div>
<br />
<?php
if ($configurazione['cvalue']) {
   $remote_id = file_get_contents($configurazione['cvalue']);
   if (preg_match("/^([0-9]{1,2}).([0-9]{1,2})/",$remote_id,$regs)){
      // versione locale presa da gconfig.php
      $pz_local = explode(".", GAZIE_VERSION);
      $pz_remote = explode(".", $remote_id);
      $local = $pz_local[0] * 100 + $pz_local[1];
      $remote = $regs[1]*100 + $regs[2];
      if ($remote <= $local) {
         $newversion = false;
      } else {
         $newversion = true;
      }
      if ($newversion) {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['new_ver1'].$regs[1]. $regs[2].$script_transl['new_ver2'].": <a href=\"".$update_URI_files."\" target=\"_blank\">".$update_URI_files."</div>";
      } else {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['is_align']."(".$remote_id.")</div>";
        tutor_list($tutor,$configurazione,$script_transl);
      }
   } else {
        echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['no_conn']."<br />".$configurazione['cvalue']."</div>";
        tutor_list($tutor,$configurazione,$script_transl);
   }
} else {
    echo "<div class=\"FacetDataTDred\" align=\"center\">".$script_transl['disabled'].": </div>";
    tutor_list($tutor,$configurazione,$script_transl);
}
?>
<?php
function getMaximumFileUploadSize()  
{  
    return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));  
}  

/**
* This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
* 
* @param string $sSize
* @return integer The value in bytes
*/
function convertPHPSizeToBytes($sSize)
{
    //
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix,array('P','T','G','M','K'))){
        return (int)$sSize;  
    } 
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P':
            $iValue *= 1024;
            // Fallthrough intended
        case 'T':
            $iValue *= 1024;
            // Fallthrough intended
        case 'G':
            $iValue *= 1024;
            // Fallthrough intended
        case 'M':
            $iValue *= 1024;
            // Fallthrough intended
        case 'K':
            $iValue *= 1024;
            break;
    }
    return (int)$iValue;
}

// Copy folder 
function copyFolder($src, $dst, $create=FALSE) { 
    if ($src === $dst )
	    return FALSE;
    $dir = opendir($src); 
    if ( !is_dir($dst) ) {
	if ( $create )
		@mkdir($dst); 
	else
	   return FALSE;
    }
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
		if ( is_dir($src . '/' . $file) ) {
                  copyFolder($src . '/' . $file, $dst . '/' . $file,true); 
            } 
		else {
                  copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir); 
    return true;
} 

// Delete all directory
function deleteDirectory($dirname){
	if (file_exists($dirname) && is_file($dirname)) {
		unlink($dirname);
	} elseif (is_dir($dirname)){
		$handle = opendir($dirname);
		while (false !== ($file = readdir($handle))) {
		   if ( $file != '.' && $file != ".." ) {
			if(is_file($dirname.'/'.$file)){
				unlink($dirname.'/'.$file);
			} else  {
				deleteDirectory($dirname.'/'.$file);
			}
		   }
		}
		$handle = closedir($handle);
		rmdir($dirname);
	}
}

if ( getMaximumFileUploadSize() < 36*1024*1024 )
	echo "Cambia la configurazione php.ini per upload > 100M ";

// Verifica upload file gazie
if ( isset($_FILES['file'])) {
	// Verifica dimensioni > 30 MB
	$errors = [];
	$path_local = realpath('../..');
	$directory = [
			$path_local."/admin.php", 
			$path_local."/composer.json", 
			$path_local."/composer.lock.php", 
			$path_local."/config.php", 
			$path_local."/doc", 
			$path_local."/.htaccess", 
			$path_local."/INDEX.html", 
			$path_local."/index.php", 
			$path_local."/js", 
			$path_local."/language", 
			$path_local."/library", 
			$path_local."/modules", 
			$path_local."/README.html", 
			$path_local."/setup", 
			$path_local."/SETUP.html", 
		];
	$perms = substr(sprintf('%o', fileperms($path_local)), -4); 
	if ( $perms !== '0755' && $perms !== '0777' ) {
		$errors[] = "Non hai permessi di scrittura in '$path_local' impostali a 0755 con proprietario www-data";
	}
	if ( $_FILES['file']['size'] < 30*1024*1024 )
		$errors[] = "Errore dimensioni files in upload";
	if ( $_FILES['file']['type'] !== 'application/zip' )
		$errors[] = "Il file non è uno zip di gazie";
	if ( $_FILES['file']['error'] !== 0 )
		$errors[] = "Errore nell'upload del file";

	if ( empty( $errors ) ) {
		$tmp_zip = $_FILES['file']['tmp_name'];
		$zip_name = $_FILES['file']['name'];
		// Unzippo tutti i files
		$zip = new ZipArchive;
		$res = $zip->open($tmp_zip);
		if ($res === TRUE) {
		  if ( !is_dir($path_local.'/tmp') )
			  mkdir($path_local.'/tmp');
		  $zip->extractTo($path_local.'/tmp');
		  $zip->close();
		  if ( !is_dir($path_local.'/tmp/gazie') ) {
			deleteDirectory($path_local.'/tmp');
			echo "Sembra non essere uno zip gazie";
		  } else {
			foreach ( $directory as $d ) {
				deleteDirectory( $d );
			}
//			@mkdir($path_local."/tmp/g");
			$m = copyFolder($path_local.'/tmp/gazie',$path_local);
			if ( $m ) {
				chmod('../../library/tcpdf/cache',0777);
		  		echo 'Esci e rientra nella nuova versione! <a href="../../modules/root/logout.php">Logout</a>';
			} else {
			   echo "Errore nello spostamento dei file";
			}
		        deleteDirectory("$path_local/tmp");
		  }
		} else {
  		    echo "Errore in apertura zip file $tmp_zip";
		}
	        @unlink($tmp_zip);	
	} else {
		foreach ( $errors as $error ) {
			echo $error;
		}
	}

}
?>
<br><br><br>
<div class="container text-center">
<form enctype="multipart/form-data" method="post" class="text-center">
  <input id="file" type="file" name="file" >  
  <button type="submit" class="btn btn-primary " id="save" name="save"><i class="icon-ok icon-white"></i> Upload</button>
</form>
</div>

<?php
require("../../library/include/footer.php");
?>
