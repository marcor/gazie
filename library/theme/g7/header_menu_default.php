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
global $gTables;

function submenu($menu_data) {
    if (!is_array($menu_data)) {
        return;
    }
    $numsub = 0;
    $submenu = '';
    foreach ($menu_data as $i => $mnu) {
        if (!is_array($mnu)) {
            continue;
        }
        $submnu = '';
        if ($numsub === 0) {
            echo "\n\t\t\t\t\t\t\t" . '<ul class="dropdown-menu">' . "\n";
        }
        if (preg_match("/^[A-Za-z0-9!@#$%&()*;:_.'\/\\\\ ]+\.png$/", $mnu['icon'])) {
            $submnu = '<img src="' . $mnu['icon'] . '" /> ';
        }
        $submnu = '<a href="' . $mnu['link'] . '">' . $submnu . stripslashes($mnu['name']);
        if (count($mnu) > 5) { //	Esiste un sotto menu
            echo "\t\t\t\t\t\t\t" . '<li>' . $submnu . "<span class=\"caret\"></span></a>";
            submenu($mnu);
            echo "\t\t\t\t\t\t\t</li>\n";
        } else {
            echo "\t\t\t\t\t\t\t<li>" . $submnu . "</a></li>\n";
        }
        $numsub++;
        if ($numsub == 0) {
            echo "\t\t\t\t\t\t\t</ul>\n";
        }
    }
    if ($numsub > 0) {
        echo "\t\t\t\t\t\t\t</ul>\n";
    }
}
?>

<!-- Navbar static top per menu multilivello responsive -->
<div class="navbar navbar-default" role="navigation">
    <div id="l-wrapper" class="navbar-header company-color">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a  href="../../modules/root/admin.php"> <?php echo strtoupper($admin_aziend["ragso1"]); ?>
        </a>
    </div>
    <div class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
            <?php
            // stampo la prima voce della barra del menù con il dropdown dei moduli 
            $i = 0;
            foreach ($menuArray as $menu_modules_val) {
                if ($i == 0) { // sul modulo attivo non permetto i submenu in quanto verrano messi sulla barra orizzontale 
                    echo "\t\t\t\t<li>" . '<a class="dropdown-toggle" data-toggle="dropdown"><img src="' . $menu_modules_val["icon"] . '"/>&nbsp;' . $menu_modules_val['name'] . '<span class="caret"></span></a>';
                    echo "\n\t\t\t\t\t" . '<ul class="dropdown-menu">' . "\n";
                } else {
                    echo "\t\t\t\t\t";
                    echo '<li><a href="' . $menu_modules_val['link'] . '"><img src="' . $menu_modules_val["icon"] . '"/>&nbsp;' . $menu_modules_val['name'] . "<span class=\"caret\"></span></a>\n";
                    submenu($menu_modules_val);
                    echo "\t\t\t\t\t</li>\n";
                }
                $i++;
            }
            // fine stampa prima voce menu
            ?>
        </ul>
        </li>
        <?php
        $i = 0;
        foreach ($menuArray[0] as $menu) {
            // stampo nella barra del menù il dropdown del modulo 
            $icon_lnk = '';
            if (isset($menu['icon']) && preg_match("/^[A-Za-z0-9!@#$%&()*;:_.'\/\\\\ ]+\.png$/", $menu['icon'])) {
                $icon_lnk = '<img src="' . $menu['icon'] . '" />';
            }
            if ($i > 4) { // perché ci sono 5 indici prima dei dati veri e propri
                if (count($menu) > 5) { // Esiste un sotto menu
                    echo "\t\t\t" . '<li class="dropdown">'
                    . '<a href="' . $menu['link'] . '">' . $icon_lnk . ' ' . $menu['name'] . '<span class="caret"></span></a>';
                } else {
                    echo "\t\t\t" . '<li><a class="dropdown" href="' . $menu['link'] . '">' . $icon_lnk . '' . $menu['name'] . '</a>';
                }
                submenu($menu);
                echo "\t\t\t\t\t</li>\n";
                $livello3 = $menu;
            }
            $i++;
        }
        ?>
        <li id="user-position">
			<div>
                <img src="../root/view.php?table=admin&field=user_name&value=<?php echo $admin_aziend["user_name"] ?>" height="30" title="<?php echo $admin_aziend['user_lastname'] . ' ' . $admin_aziend['user_firstname']; ?>" >				
			</div>
        </li>
        </ul>
    </div>
</div><!-- chiude navbar -->
