<?php
require("../../library/include/datlib.inc.php");
$admin_aziend=checkAdmin(9);
if (!isset($_POST['hidden_req'])) { //al primo accesso allo script
    $form['table'] = '';
    $form['hidden_req'] = '';
    $form['ritorno'] = $_SERVER['HTTP_REFERER'];
} else {
    $form['table'] = $_POST['table'];
    $form['hidden_req'] = $_POST['hidden_req'];
    $form['ritorno'] = $_POST['ritorno'];
    if (isset($_POST['Return'])) {
       header("Location:docume_finean.php");
       exit;
    }
}

//  MySQL host name, user name, password, database, and table to edit
$opts["hn"] = $Host;
$opts["un"] = $User;
$opts["pw"] = $Password;
$opts["db"] = $Database;


// Number of records to display on the screen
// Note value of -1 lists all records in a table.
$opts['inc'] = $passo;

// Options you wish to give the users
// A(dd) C(hange) (co)P(y) V(iew) D(elete) F(ilter) I(nitial sort suppressed)
$opts['options'] = 'ACPVDFI';

// Number of lines to display on multiple selection filters
$opts['multiple'] = '4';

// Number of lines to display on multiple selection filters
//$opts['default_sort_columns'] = array('pushId','due','priority','task');

// Navigation style: B - buttons (default), T - text links, G - graphic links
// Buttons position: U - up, D - down (default)
$opts['navigation'] = 'DB';

// Display special page elements
$opts['display'] = array(
    'query' => false,
    'sort'  => true,
    'time'  => false
    );



$select_tab=array();
$tables = gaz_dbi_query ("SHOW TABLES FROM ".$Database." LIKE '".$table_prefix.'_'."%fae_flux'");// ottengo le tabelle in un unico array associativo
$cnt=0;
while ($r = gaz_dbi_fetch_array($tables)) {
        //
        // Mi accerto che la tabella indicata da $r[0] sia una di quelle
        // che si vuole rendere accessibile alla modifica diretta.
        // Diversamente la si ignora e si salta a un nuovo ciclo.
        //
        $lunghezza_prefisso = strlen ($table_prefix."_".$id);
        //
        if (! strncmp ($r[0], $table_prefix."_".$id, $lunghezza_prefisso)) {
            //
            // Si tratta di una tabella dell'azienda $id, ovvero quella
            // attuale: tabella valida.
            //
            ;
        } else if (   ($r[0] == $table_prefix."_"."anagra")
                   || ($r[0] == $table_prefix."_"."aziend")
                   || ($r[0] == $table_prefix."_"."country")
                   || ($r[0] == $table_prefix."_"."currencies")
                   || ($r[0] == $table_prefix."_"."currency_history")
                   || ($r[0] == $table_prefix."_"."languages")
                   || ($r[0] == $table_prefix."_"."municipalities")
                   || ($r[0] == $table_prefix."_"."provinces")
                   || ($r[0] == $table_prefix."_"."regions")) {
            //
            // Si tratta di una tabella generale che si ritiene
            // utile poter modificare direttamente.
            //
            ;
        } else {
          //
          // Non si espongono altre tabelle.
          //
          continue;
        }


       $select_tab[]=$r[0];
       if (!isset($_POST['hidden_req']) && $cnt==0) { // al primo accesso seleziono comunque la prima tabella
           $form['table']=$r[0];
       }
       if ($r[0] == $form['table']){
           $opts["tb"] = $r[0];
           // ---- INIZIO ROUTINE PRESA DA phpMyEditSetup e modificata
           // ---- per generare l'array $fdd anziche' il file php con il nome della tabella
           $tb_desc = @mysql_query("DESCRIBE ".$opts["tb"]);
           $fds     = @mysql_list_fields($opts["db"], $opts["tb"], $link);
           $num_fds = @mysql_num_fields($fds);
           $ts_cnt  = 0;
           $opts['fdd']=array();
           $k_cnt  = true;
           $first  = true;
           for ($k = 0; $k < $num_fds; $k++) {
               $fd = mysql_field_name($fds,$k);
               $fm = mysql_fetch_field($fds,$k);
               if ($first) {
                  $opts["key"] = $fm->name;
                  $opts["sort_field"] = $fm->name;
                  $opts["key_type"] = $fm->type;
                  $first=false ;
               }
               if ($fm->primary_key ==1 && $k_cnt) {
                  $opts["key"] = $fm->name;
                  $opts["sort_field"] = $fm->name;
                  $opts["key_type"] = $fm->type;
                  $k_cnt=false ;
               }
               $fn = strtr($fd, '_-.', '   ');
               //--- GAZIE $fn = preg_replace('/(^| +)id( +|$)/', '\\1ID\\2', $fn); // uppercase IDs
               //--- GAZIE $fn = ucfirst($fn);
               $row = @mysql_fetch_array($tb_desc);
               //--- GAZIE echo_buffer('$opts[\'fdd\'][\''.$fd.'\'] = array('); // )
               //--- GAZIE echo_buffer("  'name'     => '".str_replace('\'','\\\'',$fn)."',");
               $opts['fdd'][$fd]['name']=$fn;
               $auto_increment = strstr($row[5], 'auto_increment') ? 1 : 0;
               if (substr($row[1],0,3) == 'set') {
                  //--- GAZIE echo_buffer("  'select'   => 'M',");
                  $opts['fdd'][$fd]['select']='M';
               } else {
                  //--- GAZIE echo_buffer("  'select'   => 'T',");
                  $opts['fdd'][$fd]['select']='T';
               }
               if ($auto_increment) {
                  //--- GAZIE echo_buffer("  'options'  => 'AVCPDR', // auto increment");
                  $opts['fdd'][$fd]['options']='AVCPDR';
               } else if (@mysql_field_type($fds, $k) == 'timestamp') { // timestamps are read-only
                    if ($ts_cnt > 0) {
                       //--- GAZIE echo_buffer("  'options'  => 'AVCPD',");
                       $opts['fdd'][$fd]['options']='AVCPD';
                    } else { // first timestamp
                       //--- GAZIE echo_buffer("  'options'  => 'AVCPDR', // updated automatically (MySQL feature)");
                       $opts['fdd'][$fd]['options']='AVCPDR';
                    }
               $ts_cnt++;
               }
               //--- GAZIE echo_buffer("  'maxlen'   => ".@mysql_field_len($fds,$k).',');
               $opts['fdd'][$fd]['maxlen']=@mysql_field_len($fds,$k);
               // blobs -> textarea
               if (@mysql_field_type($fds,$k) == 'blob') { // non permetto il trattamento dei campi BLOB
                  /*--- GAZIE   echo_buffer("  'textarea' => array(");
                  echo_buffer("    'rows' => 5,");
                  echo_buffer("    'cols' => 50),");
                  */
                  unset($opts['fdd'][$fd]);
                  continue;
               }
               // SETs and ENUMs get special treatment
               /*--- GAZIE   if ((substr($row[1],0,3) == 'set' || substr($row[1],0,4) == 'enum')
                  && ! (($pos = strpos($row[1], '(')) === false)) {
                  $indent = str_repeat(' ', 18);
                  $outstr = substr($row[1], $pos + 2, -2);
                  $outstr = explode("','", $outstr);
                  $outstr = str_replace("''", "'",  $outstr);
                  $outstr = str_replace('"', '\\"', $outstr);
                  $outstr = implode('",'.PHP_EOL.$indent.'"', $outstr);
                  echo_buffer("  'values'   => array(".PHP_EOL.$indent.'"'.$outstr.'"),');
               }
               */
               // automatic support for Default values
               if ($row[4] != '' && $row[4] != 'NULL') {
                  //--- GAZIE echo_buffer("  'default'  => '".$row[4]."',");
                  $opts['fdd'][$fd]['default']=$row[4];
               } else if ($auto_increment) {
                  //--- GAZIE echo_buffer("  'default'  => '0',");
                  $opts['fdd'][$fd]['default']='0';
               }
               // check for table constraints
               /*--- GAZIE   $outstr = check_constraints($tb, $fd);
               if ($outstr != '') {
                 //--- GAZIE echo_buffer($outstr);
               }*/
               //--- GAZIE echo_buffer("  'sort'     => true");
               $opts['fdd'][$fd]['sort']=true;
               //echo_buffer("  'nowrap'   => false,");
               $opts['fdd'][$fd]['nowrap']=false;
           }

           // ---- FINE ROUTINE PRESA DA phpMyEditSetup e modificata
           // ---- per generare l'array $fdd anziche' il file php con il nome della tabella
       }
       $cnt++;
}




/* Get the user's default language and use it if possible or you can specify
   language particular one you want to use. Available languages are:
   DE EN-US EN FR IT NL PG SK SP */
$opts['language']= $admin_aziend['country'];

require("../../library/include/header.php");
//  and now the all-important call to phpMyEdit
//  warning - beware of case-sensitive operating systems!
require_once '../../modules/gazpme/phpMyEdit.class.php';

$script_transl = HeadMain();
$gForm = new GAzieForm();
echo '<form method="post" name="GAz_sys_form">';
echo "<input type=\"hidden\" value=\"".$form['hidden_req']."\" name=\"hidden_req\" />\n";
echo "<input type=\"hidden\" value=\"".$form['ritorno']."\" name=\"ritorno\" />\n";
echo "<div align=\"center\" class=\"FacetFormHeaderFont\">".$script_transl['title'];
echo "</div>\n";
echo "<div align=\"center\" class=\"FacetFormHeaderRed\">".$script_transl['msg1'];
echo "</div>\n";
echo "<table class=\"Tmiddle\">\n";
echo "\t<td class=\"FacetFieldCaptionTD\">".$script_transl['table']."</td><td class=\"FacetDataTD\">\n";
$gForm->variousSelect('table',array_flip($select_tab),$form['table'],'FacetSelect',true,'table');
echo "\t</td>\n";
echo "</table>\n";
$MyForm = new phpMyEdit($opts);
echo "</form>\n";
echo '<hr size="1" class="pme-hr" />';

?>
</body>
</html>

