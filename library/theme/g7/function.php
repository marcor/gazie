<?php

function bc_get_current_path( $posizione ) {
    $intpos=0;
    $pos="";
    $found=false;
    foreach ( $posizione as $posizione_modulo ) {
        if ( $posizione_modulo == "modules" || $found) {
            $found=true;
            $intpos++;
            $pos .= $posizione_modulo.'/';
        }
    }
    $pos = rtrim($pos,"/");

    if ( strpos($pos, "?") ) {
        $pos = explode ("?", $pos);
        if ( is_array($pos) ) $pos = $pos[0];
    }
    return $pos;
}

function printDash($gTables,$module,$admin_aziend,$transl){
        $pos="";
        $ci_sono_tasti_nel_menu=false;
        $posizione = explode( '/',$_SERVER['REQUEST_URI'] );     
        $pos = bc_get_current_path($posizione);
        $posizione = array_pop( $posizione );
        $res_pos = gaz_dbi_dyn_query("*", $gTables['breadcrumb'], ' file="'.$pos.'" AND exec_mode=0', ' id_bread',0,999);
		if ( gaz_dbi_num_rows($res_pos)>0 ) {             
            $row = gaz_dbi_fetch_array($res_pos);
            echo "<ol class='breadcrumb'>";
            echo "<li>";
            echo "<a href='".$row['link']."'>".$row['titolo']."</a>";
            echo "</li>";
			while ( $row = gaz_dbi_fetch_array($res_pos) ) {
				echo "<li><a href='".$row['link']."'>".$row['titolo']."</a></li>";
			}
			echo "<li><a href='../../modules/root/admin.php'><i class='glyphicon glyphicon-home'></i></a>&nbsp;<a href='../../modules/root/admin_breadcrumb.php?url=".$pos."'><i class='glyphicon glyphicon-cog'></i></a></li>";
			echo "</ol>";
        } else {
		  if ($pos=='modules/root/admin.php') {             
				echo "<li><a href='../../modules/root/admin_dash.php'><i class='glyphicon glyphicon-cog'></i></a></li>";
				echo "</ol>";
		  } else {
            if ( $posizione == "report_received.php" ) $posizione = "report_scontr.php";
			if ( strpos($posizione, "VOG")!==false ) $posizione = "report_broven.php?auxil=VOR";
            $result    = gaz_dbi_dyn_query("*", $gTables['menu_module'] , ' link="'.$posizione.'" ',' id',0,1);
            if ( !gaz_dbi_num_rows($result)>0 ) {
                $posizionex = explode ("?",$posizione );
                $result    = gaz_dbi_dyn_query("*", $gTables['menu_module'] , ' link="'.$posizionex[0].'" ',' id',0,1);	
            }
            $riga = gaz_dbi_fetch_array($result);
            
            if ( $riga["id"]!="" ) {
                // siamo su una pagina di 2 livello nel menu principale
                // mostra il titolo se siamo su una pagina di secondo livello
                $result2 = gaz_dbi_dyn_query("*", $gTables['menu_script'] , ' id_menu='.$riga["id"].' ','id',0);
                
                echo "<ol class=\"breadcrumb\">";
                echo "<li>";
                echo "&nbsp;".stripslashes($transl[$module]["m2"][$riga["translate_key"]][0]);
                echo "</li>";          
                //da fare salvare i moduli più usati tramite la stella
                while ($r = gaz_dbi_fetch_array($result2)) {
                    if ( $admin_aziend["Abilit"]>=$r["accesskey"] )
                        echo '<li><a href="'.$r["link"].'">'.stripslashes ($transl[$module]["m3"][$r["translate_key"]]["1"]).'</a></li>';
                }
                $ci_sono_tasti_nel_menu=true;
            } else { 
                // siamo su una pagina di 3 livello nel menu principale
                $result3    = gaz_dbi_dyn_query("*", $gTables['menu_script'] , ' link="'.$posizione.'"',' id',0,1);
                if ( $r = gaz_dbi_fetch_array($result3) ) {
                    // disegno i bottoni di accesso alle funzioni di questa pagina
                    $posizionex = explode ("?",$posizione );
                    $result4    = gaz_dbi_dyn_query("*", $gTables['menu_script'] , ' link like "%'.$posizionex[0].'%" ',' id',0,99);
                    echo "<ol class=\"breadcrumb\">";
                    echo "<li>";
                    echo "&nbsp;".$transl[$module]["m3"][$r["translate_key"]][0];
                    echo "</li>";                
                    while ($r = gaz_dbi_fetch_array($result4)) {
                        if ( $admin_aziend["Abilit"]>=$r["accesskey"] )
                            echo '<li><a href="'.$r["link"].'">'.stripslashes ($transl[$module]["m3"][$r["translate_key"]]["1"]).'</a></li>';
                    }
                    $ci_sono_tasti_nel_menu=true;
                }
                
            }

            if ( !$ci_sono_tasti_nel_menu ) {
                echo "<ol class=\"breadcrumb\">";
                echo "<li>NoTitle</li>";
            }

            echo "<li><a href=\"../../modules/root/admin.php\"><i class=\"glyphicon glyphicon-home\"></i></a>&nbsp;<a href='../../modules/root/admin_breadcrumb.php?url=".$pos."'><i class='glyphicon glyphicon-cog'></i></a></li>";
            echo "</ol>";
		  }
        }
}
?>