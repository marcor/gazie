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

namespace GAzie;

use \ZipArchive;

class Upgrade {

	private $errors;

	private $directories;

	private $path_local;

	public function __construct() {
		$this->errors = [];
		$this->path_local = Config::factory()->getPathRoot();
		$this->directories = Config::factory()->getDirectories();
	}

	private function getMaximumFileUploadSize() {  
		return min($this->convertPHPSizeToBytes(ini_get('post_max_size')), $this->convertPHPSizeToBytes(ini_get('upload_max_filesize')));  
	}  

	private function getPathLocal() {
		return $this->path_local;
	}

	/**
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
	 * 
	 * @param string $sSize
	 * @return integer The value in bytes
	 */
	private function convertPHPSizeToBytes($sSize) {
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
	private function copyFolder($src, $dst, $create=FALSE) { 
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
                  			$this->copyFolder($src . '/' . $file, $dst . '/' . $file,true); 
            			} else {
                  			@copy($src . '/' . $file,$dst . '/' . $file); 
            			} 
        		} 
    		} 
    		closedir($dir); 
    		return true;
	} 

	// Delete all directory
	private function deleteDirectory($dirname){
		if (file_exists($dirname) && is_file($dirname)) {
			unlink($dirname);
		} elseif (is_dir($dirname)) {
			$handle = opendir($dirname);
			while (false !== ($file = readdir($handle))) {
		   		if ( $file != '.' && $file != ".." ) {
					if(is_file($dirname.'/'.$file)){
						unlink($dirname.'/'.$file);
					} else  {
						$this->deleteDirectory($dirname.'/'.$file);
					}
		   		}
			}
			$handle = closedir($handle);
			rmdir($dirname);
		}
	}

	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Upgrade da uno zip file passatogli come ($_FILES['file'])
	 *
	 */
	public function zip(array $zip_file) {
		$success = FALSE;
		$errors = [];
		$path_local = $this->getPathLocal();

		if ( $this->getMaximumFileUploadSize() < 36*1024*1024 ) {
			$errors[] = "Cambia la configurazione php.ini per upload > 100M ";
			return FALSE;
		}
		// Verifica upload file gazie
		if ( ! isset($zip_file['size']))
			return FALSE;
			
		// Verifica dimensioni > 30 MB
		$directory = $this->directories;
		
		$perms = substr(sprintf('%o', fileperms($path_local)), -4); 
		if ( $perms !== '0755' && $perms !== '0777' ) {
			$errors[] = "Non hai permessi di scrittura in '$path_local' impostali a 0755 con proprietario www-data";
		}

		$zip_mime = [	
			'application/x-compressed',
			'application/x-zip-compressed',
			'application/zip',
			'multipart/x-zip',
		];


		if ( $zip_file['size'] < 30*1024*1024 )
			$errors[] = "Errore dimensioni files in upload";
		if ( ! in_array($zip_file['type'],$zip_mime )  )
			$errors[] = "Il file non è uno zip di gazie";
		if ( $zip_file['error'] !== 0 )
			$errors[] = "Errore nell'upload del file";

		if ( empty( $errors ) ) {
			$tmp_zip = $zip_file['tmp_name'];
			$zip_name = $zip_file['name'];
			// Unzippo tutti i files
			$zip = new ZipArchive;
			$res = $zip->open($tmp_zip);
			if ($res === TRUE) {
				if ( !is_dir($path_local.'/tmp') )
					  mkdir($path_local.'/tmp');
				$zip->extractTo($path_local.'/tmp');
				$zip->close();
				if ( !is_dir($path_local.'/tmp/gazie') ) {
					$this->deleteDirectory($path_local.'/tmp');
					$errors[] = "Sembra non essere uno zip gazie";
					} else {
						foreach ( $directory as $d ) {
							$this->deleteDirectory( $d );
						}
		//				@mkdir($path_local."/tmp/g");
						$m = $this->copyFolder($path_local.'/tmp/gazie',$path_local);
						if ( $m ) {
							if ( chmod('../../library/tcpdf/cache',0777) ) 
								$success =  TRUE;
							else
								$errors[] = "Non ho potuto dare i permessi 0777 alla cartella 'library/tcpdf/cache'";
						} else {
						   	$errors[] = "Errore nello spostamento dei file";
						}
						$this->deleteDirectory("$path_local/tmp");
					}
			} else {
				$errors[] = "Errore in apertura zip file $tmp_zip";
			}
			@unlink($tmp_zip);
			return $success;
		
		}

		$this->errors = $errors;	
		return FALSE;

	}
}

?>

