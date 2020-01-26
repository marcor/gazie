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
global $gTables;

function submenu($array) {
	if(!is_array($array)) { return ;}

	$numsub = 0;
	$submenu = '';

	foreach($array as $i => $mnu) {
		if(!is_array($mnu)) {continue;}
		$submnu = '';

		if ($numsub === 0) {
			echo '<ul class="dropdown-menu">';
		}
		
		if (preg_match("/^[A-Za-z0-9!@#$%&()*;:_.'\/\\\\ ]+\.png$/" ,$mnu['icon'])){
			$submnu='<img src="'.$mnu['icon'].'" /> ';
		}
		$submnu = '<a href="'.$mnu['link'].'">'.$submnu.stripslashes($mnu['name']).'</a>';

		if (count($mnu)>5) {	//	Esiste un sotto menu
			echo '<li class="dropdown-submenu">'.$submnu;
			submenu($mnu);
			echo '</li>';
		} else {
			echo '<li>'.$submnu.'</li>';
		}

		$numsub++;
	}

	if ($numsub > 0) {echo '</ul>';}
}

//preparo la query per la seconda barra 
$posizione = explode( '/',$_SERVER['REQUEST_URI'] );
$posizione = array_pop( $posizione );

//cambio la posizione manualmente per far apparire la seconda barra in questi moduli i report sono invertiti
if ( strpos($posizione, "report_received.php" )  !==false ) $posizione = "report_scontr.php";
if ( strpos($posizione, "report_aziend.php" )    !==false ) $posizione = "admin_aziend.php";
if ( strpos($posizione, "report_broven_gio.php" )!==false ) $posizione = "report_broven.php?auxil=VOR";
if ( strpos($posizione, "report_destinazioni.php" )!==false ) $posizione = "report_client.php";

$result    = gaz_dbi_dyn_query("*", $gTables['menu_module'] , ' link="'.$posizione.'" ',' id',0,1);

if ( !gaz_dbi_num_rows($result)>0 ) {
	$posizione = explode ("?",$posizione );
	$result    = gaz_dbi_dyn_query("*", $gTables['menu_module'] , ' link="'.$posizione[0].'" ',' id',0,1);	
}

//aggiungo classe per spaziare in caso di assenza seconda barra
$classe_barra1 = "";
$riga = gaz_dbi_fetch_array($result);

if ( $riga["id"]!="" ) {
	$result2 = gaz_dbi_dyn_query("*", $gTables['menu_script'] , ' id_menu='.$riga["id"].' ','id',0);
	if ( gaz_dbi_num_rows($result2)<=0 ) {
		$classe_barra1 = " nav-mb";
	}
} else { 
	$classe_barra1 = " nav-mb";
}
?>


<nav class="navbar navbar-fixed-top navbar-default nav-boot" role="navigation">
	<!--<div class="navbar-header navbar-right vcenter" >-->
        <a class="navbar-brand pull-right vcenter" href="../../modules/root/admin.php">
			<?php //echo strtoupper( $admin_aziend["ragso1"]); ?>
        	<img src="../../modules/root/view.php?table=aziend&amp;value=<?php echo $admin_aziend["company_id"]; ?>" height="35" alt="Logo" border="0" title="<?php echo $admin_aziend["ragso1"]; ?>" />
	  </a>
    	<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Visualizza Men&ugrave;</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav nav-tabs">
            <?php
            $i = 0;
            foreach ($menuArray as $link) {
                if ( $i==0 ) {
                    echo '		<li class="dropdown">
                                    <a>
                                        <img src="'.$link["icon"].'"/>&nbsp;'.$link['name'].'<span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">';
                } else {
                    echo '		<li class="dropdown-submenu">
                                    <a href="'.$link['link'].'">
                                        <img src="'.$link["icon"].'"/>&nbsp;'.$link['name'].'
                                    </a>';
                }
                submenu($link);
                $i++;
            }
            echo '		</li>
                    </ul>
                  </li>';
    
            $i=0;
            foreach ( $menuArray[0] as $menu ) {
                $icon_lnk=''; $css_class='row-menu';
                if (isset($menu['icon']) && preg_match("/^[A-Za-z0-9!@#$%&()*;:_.'\/\\\\ ]+\.png$/",$menu['icon'])){
                    $icon_lnk='<img src="'.$menu['icon'].'" />';
                    $css_class='icon-menu';
                }
                if ( $i > 4 ) {
                    if ( count($menu)>5 ) {
                        echo '<li class="dropdown"><a class="'.$css_class.'" href="'.$menu['link'].'">'.$icon_lnk.' '.$menu['name'].'<span class="caret"></span></a>';
                    } else {
                        echo '<li><a class="row-menu" href="'.$menu['link'].'">'.$icon_lnk.''.$menu['name'].'</a>';
                    }
                    submenu($menu);
                    $livello3 = $menu;
                }
                $i++;
            }
            echo '</li>';
            ?>
            </ul>
	</div>
</nav>
<?php

if ( $riga["id"]!="" ) {
	$result2 = gaz_dbi_dyn_query("*", $gTables['menu_script'] , ' id_menu='.$riga["id"].' ','id',0);
	if ( gaz_dbi_num_rows($result2)>0 ) {
		if ( is_array( $posizione ) ) $posizione = $posizione[0];
		if (isset($_GET['auxil'])) $auxil = $_GET['auxil'];
		else $auxil = "";
		?>
		<nav class="navbar navbar-default navbar-lower nav-mb" role="navigation">
			<div class="navbar-form navbar-left" role="search">
				<div class="btn-group btn-group-xs">
				<?php
					while ($r = gaz_dbi_fetch_array($result2)) {
						echo '<a href="'.$r["link"].'" class="btn btn-default">'.stripslashes ($transl[$module]["m3"][$r["translate_key"]]["1"]).'</a>';
					}
					if ( file_exists( "function_menu.php" ) ) {
						include "function_menu.php";
					}
				?>
				</div>
			</div>
			
			<!--<div class="navbar-default navbar-right">
				<div class="form-inline">
					<form action="<?php echo $posizione; ?>" method="GET">
						<input type="hidden" name="auxil" value="<?php echo $auxil; ?>">
						<!--<input disabled type="text" class="form-control input-xs" title="La ricerca viene effettuata nei campi ragione sociale 1 e 2, partita iva, codice fiscale e città" type="text" name="ricerca_completa" placeholder="Cerca nel modulo">
						<button disabled type="submit" class="btn btn-xs btn-default">Go!</button>-->
					<!--</form>
				</div>
			</div>-->
		</nav>
		<?php
	}
}
?>