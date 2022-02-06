<?php
date_default_timezone_set('America/Atlanta');

require("./config/config.php");
require("classes/Webpage.php");
require("classes/Dbase.php");
require("classes/Auth.php");
//require("classes/Top10.php");
//include("classes/Visitor.php");
require_once("config/config.php");

/*
if (ereg("secure", $PHP_SELF)) $secure = true; else $secure = false;
switch ($secure)
{
case true :	$otherfile = "index.php"; $othername = "Open"; break;
case false:     $otherfile = "index.secure.php"; $othername = "Secure"; break;
}
*/


$page = new Webpage("Het Kookpunt", $Style);
$db = new DataBase($Host,$Name,$User,$Pass);
//$visitor = new Visitor();
if ($secure) $auth = new Auth(EDIT);

$maxlistlength = 130;
$query_tpl = "SELECT gerecht, recepten.receptid as receptid,recepten.gerechtid as gerechtid, 
              naam,ref,ingredient,bereiding,opmerking,keuken,keuken.keukenid as keukenid
              FROM recepten,keuken,recept_keuken,gerecht
              WHERE recepten.receptid = recept_keuken.receptid 
                    AND keuken.keukenid = recept_keuken.keukenid 
                    AND recepten.gerechtid = gerecht.gerechtid
                    AND %where%
              ORDER BY %order%";
              // see line 180: LIMIT %limit%,$maxlistlength";


$alfa = array(a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z);

$db->DBQuery("select distinct(substring(naam,1,1)) as firstletter from recepten order by firstletter"); 
$alfaprint  = array(); 
while ($res = $db->DBResult() ) 
       $alfaprint[] = strtolower($res->firstletter);


$db->DBQuery("select keukenid,keuken from keuken order by keuken"); 
$keukenarray  = array(); 
while ($res = $db->DBResult() ) 
       $keukenarray[$res->keukenid] =stripslashes($res->keuken);


$db->DBQuery("select gerechtid,gerecht from gerecht order by gerecht"); 
$gerecht = array();
while ($res = $db->DBResult() ) 
       $gerecht[$res->gerechtid] = stripslashes($res->gerecht);


$db->DBQuery("select count(receptid) as totaalrecept from recepten");
$res = $db->DBResult();
$totaalrecept = $res->totaalrecept;

$display = intval($_GET['display']);

if ($_POST['orderby']) $order = $_POST['orderby'];
else $order = "naam";
if (!ereg("^(naam|keuken|recepten.gerechtid)$", $order)) $order = "naam";

switch ($order)
{
   case "naam"  : $next = ",keuken"; break;
   case "recepten.gerechtid"  : $next = ",naam"; break;
   default        : $next = ",naam"; brea;
}

if ($search == "nieuw") $qorder = "recepten.receptid desc limit 15"; 
$qorder = $order . $next;

$switchvar = "";
foreach ($_GET as $get_var => $get_val) $switchvar .= "$get_var=$get_val&";

?>

<table width="100%" border="0">
<tr>
   <td width="60%" valign="top"><h1>Het Kookpunt</h1></td>
   <td width="20%" valign="top"><a href="<?=$otherfile?>?<?=$switchvar?>"><?=$othername?></a></td>   
   <!-- <td width="20%" valign="top"><a href="admin/index.php">Admin Panel</a></td> -->
</tr>
</table>
<table width="100%" border="0" align="center">
<tr>
   <td align="center"><fieldset><legend>Alfabet search</legend><h3><span class="letter">
       <?php
       foreach ($alfa as $letter)
       {
               if (in_array($letter, $alfaprint))
               echo "[<a href=\"$PHP_SELF?search=first&first=$letter\">$letter</a>]";
               else echo "[$letter]";
               if (ereg("[ejot]",$letter)) echo "<br>";
       }
       ?>
       
       </span></h3></fieldset>
   <td>
   <form name="search" action="<?="$PHP_SELF"?>" method="POST">
     <fieldset><legend>Search</legend>
       <table width="50%" border="0">

           <tr>
              <td class="tablecell"><input type="submit" name="search" value="zoek naam">
              <td class="tablecell"><input type="text" name="naam" value="">
           <tr>
              <td  class="tablecell"><input type="submit" name="search" value="zoek ingredient">
              <td class="tablecell"><input type="text" name="ingredient" value="">
           <tr>
              <td  class="tablecell"><input type="submit" name="search" value="zoek keuken" width="100">
              <td class="tablecell"><select name="keuken">
                             <!--option value="0">Selecteer</option-->
           <?php
               foreach ($keukenarray as $id => $name)
                       echo "<option value=\"$id\">$name</option>\n";          
           ?>
           </select>
         
           <tr><td valign="top"><input type="submit" name="search" value="zoek type">
           <td valign="top"><select name="gerechttype">
                             <!--option value="0">Selecteer</option-->
           <?php
               foreach ($gerecht as $id => $name)
                       echo "<option value=\"$id\">$name</option>\n";          
           ?>
           </select>
           <tr><td  class="tablecell" colspan="2"><input alt="Laatste 15 nieuwe recepten" type="submit" name="search" value="nieuw op de site">
           </table>
           </fieldset>
       <td  class="tablecell"><?php 
                 if (GOOGLE) 
                     googlead();
                 else 
                     echo "";//GenerateTop($db, 5);
            ?>
       <tr>
         <td  class="tablecell" colspan="2">
             Sorteer op
             <input type="radio" <? if ($order=="naam") echo "checked" ?> name="orderby" value="naam">Naam
             <input type="radio" <? if ($order=="keuken") echo "checked" ?> name="orderby" value="keuken">Keuken
             <input type="radio" <? if ($order=="recepten.gerechtid") echo "checked" ?> name="orderby" value="recepten.gerechtid">Type  
       </form>       
</table>
<?php

if ($_POST['search'])
   $search = $_POST['search'];
else if ($_GET['search'])
   $search = $_GET['search'];
else $search = "";

switch($search)
{
    case "nieuw op de site"     : $searchoption = "ref" ; $searchterm = "'%'"; break;
    case "first"         : $searchoption = "naam"; $searchterm = "'".$_GET['first']."%'"; break; 
    case "zoek naam"     : $searchoption = "naam";
                                if (!ereg(" ", $_POST['naam']))
                                   $searchterm = "'%".$_POST['naam']."%'";
                                else
                                {
                                   $naamlijst = explode(" ", $_POST['naam']);
                                   $searchterm = "'%" . implode("%' and naam like '%",$naamlijst) . "%'";
                                }
				break;		
    case "naam"		 : $searchoption = "naam";
                                if (!ereg(" ", $_GET['naam']))
                                   $searchterm = "'%".$_GET['naam']."%'";
                                else
                                {
                                   $naamlijst = explode(" ", $_GET['naam']);
                                   $searchterm = "'%" . implode("%' and naam like '%",$naamlijst) . "%'";
                                }
				break;		
    case "receptnaam"    : $searchoption = "naam"; $searchterm = "'%". urldecode($_GET['naam']) . "%'"; break;
    case "zoek ingredient"    : $searchoption = "ingredient"; 
                                if (!ereg(" ", $_POST['ingredient']))
                                   $searchterm = "'%".$_POST['ingredient']."%'";
                                else
                                {
                                   $ingredlijst = explode(" ", $_POST['ingredient']);
                                   $searchterm = "'%" . implode("%' and ingredient like '%",$ingredlijst) . "%'";
                                }
				break;		
    case "ingredient"    : $searchoption = "ingredient";
                                if (!ereg(" ", urldecode($_GET['ingredient'])))
                                   $searchterm = "'%".urldecode($_GET['ingredient'])."%'";
                                else
                                {
                                   $ingredlijst = explode(" ", urldecode($_GET['ingredient']));
                                   $searchterm = "'%" . implode("%' and ingredient like '%",$ingredlijst) . "%'";
                                }
				break;    
    case "zoek keuken"   : $searchoption = "keuken.keukenid"; $searchterm = intval($_POST['keuken']); break;
    case "keukenid"      : $searchoption = "keuken.keukenid"; $searchterm = intval($_GET['keuken']); break;
    case "zoek type"     : $searchoption = "recepten.gerechtid"; $searchterm = intval($_POST['gerechttype']); break;
    case "gerechttype"          : $searchoption = "recepten.gerechtid"; $searchterm = intval($_GET['gerechttype']); break;
    default              : $searchoption = "naam"; $searchterm = "'a%'"; break;
}


if (intval($_GET['show']))
{
    $receptid = intval($_GET['show']);
    //$top10 = new Top10($db, $receptid);
    $db->query = ereg_replace("%where%", "  recepten.receptid = $receptid ", $query_tpl);
    $db->query = ereg_replace("%order%", " keuken ", $db->query);
    $db->DBQuery();
    $keukentype="";
    while ($recept = $db->DBResult())
    {
          $keukentype .= ", <a href=\"$PHP_SELF?search=keukenid&keuken=". intval($recept->keukenid)."\">".stripslashes($recept->keuken)."</a>";
          $gerecht = "<a href=\"$PHP_SELF?search=gerechttype&gerechttype=". intval($recept->gerechtid)."\">".stripslashes($recept->gerecht)."</a>";
          $naam = stripslashes($recept->naam);
          if ($secure) $ref = stripslashes($recept->ref);
          $opmerking = stripslashes($recept->opmerking); 
          $ingredient = nl2br(stripslashes($recept->ingredient));
          $bereiding = nl2br(stripslashes($recept->bereiding));
    }      

    $db->query = "SELECT count(viewid) as numberviewers from viewed where receptid = $receptid";
    $db->DBQuery();
    $number = $db->DBResult();
    $viewed = $number->numberviewers; 

    ?>
    <table border="0" width="100%">
    <tr>
       <td class="tablecell" width="40%">Recept
       <td class="tablecell" width="60%"><?=$naam ?>
<?php 
    if ($secure) echo "<tr><td>Vindplaats<td class=\"tablecell\">$ref";
?>
    <tr> 
       <td class="tablecell">Type gerecht
       <td class="tablecell"><?=$gerecht.$keukentype ?>       
    <tr>
        <td class="tablecell">Opmerking(en)
        <td class="tablecell"><?=$opmerking ?>
    <tr>
        <td class="tablecell">Aantal keer bezocht in de afgelopen <?=TIMEAGE ?> dagen
        <td class="tablecell"><?=$viewed ?>        
    
    <tr>
        <td class="tablecell" colspan="2">&nbsp;
    <tr>
       <td class="tablecell"><b>Ingredienten</b><br><?=$ingredient ?>
       <td class="tablecell"><b>Bereiding</b><br><?=$bereiding ?>     
    <tr colspan="2">
       
       <td class="tablecell"><a href="<? echo "$PHP_SELF?" . interpretreturnval() ?>">Back</a>
    </table>
    <?php
}
else 
{
    if (ereg("(keuken)",$search))
       $where = "$searchoption = $searchterm";
    else $where = "$searchoption like $searchterm";

    if ($search == "nieuw op de site") $qorder = "recepten.receptid desc limit 15"; 

    $db->query = ereg_replace("%where%", $where, $query_tpl);
    $db->query = ereg_replace("%order%", $qorder, $db->query);
    $db->query = ereg_replace("%limit%", "$display", $db->query);
    //echo $db->query;

    $db->DBQuery();

//    $numrecept = $db->DBNumRows();
    
    $numrecept = 0;
    


/* display shorter lists and links to next
    $numtag = $numrecept / $maxlistlength ;
    for ($j = 0; $j <= $numtag; $j++)
        echo "<a href=\"$PHP_SELF?search=first&first=". $_GET['first'] . "&display=" . ($j*$maxlistlength)."\">[" . ($j*$maxlistlength+1) . "-" . ($j+1)*$maxlistlength . "]</a>";
*/
    echo "<br>";
    $oldid = -1;
    $firsttime = true;
    echo "<table border=\"0\" width=\"100%\"><tr><td width=\"50%\"><td width=\"20%\"><td width=\"30%\">";
    while ($recept = $db->DBResult() )
    {
        $receptid = intval($recept->receptid);
        $back= makereturnval();
        $naam = stripslashes($recept->naam);
        if ($secure) $ref = stripslashes($recept->ref);
        $gerecht = stripslashes($recept->gerecht);
        $keuken = stripslashes($recept->keuken);
        $br = "<tr><td class=\"tablecell\">";
        $gerechtshow = "<a href=\"$PHP_SELF?search=gerechttype&gerechttype=$recept->gerechtid\">$gerecht</a>";
        $naamshow = "<span class=\"auteur\"><a href=\"$PHP_SELF?show=$receptid&back=$back\">$naam</a></span>";
        $keukenshow = "<a href=\"$PHP_SELF?search=keukenid&keuken=$recept->keukenid\">$keuken</a>";
        $refshow = ($ref) ? "<td class=\"tablecell\">[$ref]" : "";

        $numrecept++;

        if ($firsttime)
        {
             echo $br. $naamshow. " ($gerechtshow)" . $refshow . "<td class=\"tablecell\">". $keukenshow;
        }
        else if ($receptid == $oldid)
        {
             $numrecept--;
             echo ", " . $keukenshow;

        }
        else
        {
             echo $br. $naamshow. " ($gerechtshow)".$refshow."<td class=\"tablecell\">" .$keukenshow;
        }

        $firsttime = false;
        $oldid = $receptid;
    }
    echo "</table>\n\n";
}


if ($totaalrecept == 1) $en = ""; else $en="en";
echo "<p>Gevonden: $numrecept van $totaalrecept recept$en</p>";

$page->Footer();

//if (WEBSTAT) webstat();
//if (GOOGLE) googlead();


function makeReturnVal()
{
//first-receptnaam-ingredient-keukenid-type 
   $first = $_GET['first'];
   if (!$first)  $first = 0;
   $receptnaam = ($_POST['naam'])? urlencode($_POST['naam']):urlencode($_GET['naam']);
   if (!$receptnaam) $receptnaam = 0; 
   $ingredient= ($_POST['ingredient'])? urlencode($_POST['ingredient']):urlencode($_GET['ingredient']);
   if (!$ingredient) $ingredient = 0;
/*
   $keukenid = $_POST['keuken'] ? intval($_POST['keuken']):intval($_GET['keuken']);
   if ($receptnaam or $ingredient) $keukenid = 0;
   $type = $_POST['gerechttype'] ? intval($_POST['gerechttype']):intval($_GET['gerechttype']);
   if ($keukenid or $receptnaam or $ingredient) $type=0;
   return "$first-$receptnaam-$ingredient-$keukenid-$type";
*/
   return "$first,$receptnaam,$ingredient";
}

function interpretReturnVal()
{
   $back = $_GET['back'];
   $return = explode(",", $back);
   foreach ($return as $key => $value)
   {
       switch ($key)
       {
           case "0" : if ($value) $goback = "search=first&first=".urlencode($value); break;
           case "1" : if ($value) $goback = "search=naam&naam=".urlencode($value); break; 
           case "2" : if ($value) $goback = "search=ingredient&ingredient=".urlencode($value);  break;
           /*
           case "3" : if ($value) $goback = "search=keukenid&keuken=".urlencode($value);  break;
           case "4" : if ($value) $goback = "search=gerechttype&gerechttype=".urlencode($value);  break;
           */
           //default  : $goback = urlencode("");break;
        }
    
   }
   return $goback;
}



function googlead()
{
?>
<script type="text/javascript"><!--
google_ad_client = "pub-6292799263121102";
/* 125x125, gemaakt 3-2-09 */
google_ad_slot = "9180053762";
google_ad_width = 125;
google_ad_height = 125;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<?php
}

function webstat()
{
?>
<!-- Begin Nedstat Basic code -->
<!-- Title: Recepten Site -->
<!-- URL: http://www.catalysis.nl/~rob/recept -->
<script language="JavaScript" type="text/javascript"
src="http://m1.nedstatbasic.net/basic.js">
</script>
<script language="JavaScript" type="text/javascript">
<!--
  nedstatbasic("ADgGYg5IzHAgkd5EnrG19DWCDNrA", 0);
  // -->
  </script>
  <noscript>
  <a target="_blank"
  href="http://www.nedstatbasic.net/stats?ADgGYg5IzHAgkd5EnrG19DWCDNrA"><img
  src="http://m1.nedstatbasic.net/n?id=ADgGYg5IzHAgkd5EnrG19DWCDNrA"
  border="0" width="18" height="18"
  alt="Nedstat Basic - Free web site statistics
  Personal homepage website counter"></a><br>
  <a target="_blank" href="http://www.nedstatbasic.net/">Free counter</a>
  </noscript>
  <!-- End Nedstat Basic code -->

<?php
}

function GenerateTop($db,$number)
{
       $toplist = "<strong>Top $number today</strong><ol>";
       
       $now = mktime() - 24*60*60;
       
       $query  = "select count(viewid) as viewedtotal, viewed.receptid as id,naam
                  from viewed,recepten
                  where viewed.receptid = recepten.receptid and viewunixtime > $now
                  group by id
                  order by viewedtotal DESC, naam ASC
                  limit $number";
       //$toplist =  $query;                                                                                                                   
       
       $db->DBQuery($query);
       while ($recept = $db->DBResult())
              $toplist .= "<li><a href=\"$PHP_SELF?show=" . $recept->id . "\">". stripslashes($recept->naam) . "</a></li>";
       
       $toplist .= "</ol>";
       
       return $toplist;
}

function determinate()
{
    $f = rand(1,10);
    if ($f <= 3) $d = 0; 
    else $d = 1;
    return $d;
}

?>
