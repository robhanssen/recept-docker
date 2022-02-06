<?php
class Recept
{
    var $id;
    var $naam;
    var $gerechtid;
    var $gerecht;
    var $ingredient;
    var $bereiding;
    var $ref;
    var $opmerking;
    var $keukenidarray;

    var $tblname;

    function Recept()
    {
        $this->id = intval($_POST['receptid']);
        $this->naam = addslashes(htmlspecialchars($_POST['naam']));
        if (!$this->naam) $this->naam = "Naam onbekend";
        $this->ingredient = addslashes(htmlspecialchars($_POST['ingredient']));
        $this->bereiding = addslashes(htmlspecialchars($_POST['bereiding']));
        $this->opmerking = addslashes(htmlspecialchars($_POST['opmerking']));
        $this->ref = addslashes(htmlspecialchars($_POST['ref']));                
        $this->gerechtid = intval($_POST['gerechtid']);
        if ($this->gerechtid == 0) $this->gerechtid = 1;
        $this->keukenidarray = $_POST['keukenidarray'];
        $this->tblname = "recepten";
    }

    function Add($db, $auth)
    {
         $db->query = "INSERT into $this->tblname (naam, gerechtid, ingredient, bereiding, ref, opmerking) 
                       values ('$this->naam', $this->gerechtid, '$this->ingredient', '$this->bereiding', '$this->ref', '$this->opmerking')";
         if ($auth->isPermitted(EDIT)) $db->DBQuery();
         $this->id = $db->Getid();
         foreach ($this->keukenidarray as $keukenid)
         {
             $db->query = "insert into recept_keuken (receptid, keukenid) values ($this->id, $keukenid)";
             $db->DBQuery();
         }
     }

     function Delete($db,$auth)
     {
         $db->query = "DELETE from $this->tblname WHERE receptid = $this->id;";
         if ($auth->isPermitted(ADVEDIT)) $db->DBQuery();
         $db->query = "DELETE from recept_keuken WHERE receptid = $this->id";
         if ($auth->isPermitted(ADVEDIT)) $db->DBQuery();         
     }

     function Edit($db)
     {
         global $PHP_SELF, $display;
         $db->query = "SELECT recepten.receptid as receptid,recepten.gerechtid as gerechtid,naam,bereiding,ingredient,ref,opmerking,keuken,keuken.keukenid 
                       FROM $this->tblname,keuken,recept_keuken,gerecht 
                       WHERE 
                           recepten.receptid = recept_keuken.receptid
                           AND keuken.keukenid = recept_keuken.keukenid
                           AND recepten.gerechtid = gerecht.gerechtid
                           AND recepten.receptid = $this->id
                       ORDER by keuken";

         $db->DBQuery();
         while ($res = $db->DBResult())
         {
            $this->naam = stripslashes($res->naam);
            $this->gerechtid = $res->gerechtid;
            $this->bereiding = stripslashes($res->bereiding);
            $this->ingredient = stripslashes($res->ingredient);
            $this->ref = stripslashes($res->ref);
            $this->opmerking = stripslashes($res->opmerking);         
            $this->keukenidarray[] = $res->keukenid;
         }
         echo "<form method=\"post\" action=\"$PHP_SELF?display=$display&action=update&update=$this->id#$this->id\">";

         $this->Enter($db);
     }

     function Update($db,$auth)
     {
 
         $db->query = "UPDATE $this->tblname SET
                                 naam = '$this->naam',
                                 gerechtid = $this->gerechtid,
                                 bereiding = '$this->bereiding',
                                 ref = '$this->ref',
                                 ingredient = '$this->ingredient',
                                 opmerking = '$this->opmerking'
                           WHERE receptid = $this->id";

         if ($auth->isPermitted(EDIT)) $db->DBQuery();
         
         $db->query = "DELETE from recept_keuken WHERE receptid = $this->id";
         $db->DBQuery();
         foreach ($this->keukenidarray as $keukenid)
         {
             $db->query = "INSERT into recept_keuken (receptid, keukenid) values ($this->id, $keukenid)";
             $db->DBQuery();
         }
     }

     function Enter($db)
     {
        global $PHP_SELF;
        $keuken  = array();
        $gerechttype = array();

        $db->DBQuery("select keukenid,keuken from keuken order by keuken");
        while ($res = $db->DBResult() )
              $keuken[$res->keukenid] = stripslashes($res->keuken);

        $db->DBQuery("select gerechtid,gerecht from gerecht order by gerecht");
        while ($res = $db->DBResult() )
              $gerechttype[$res->gerechtid] = stripslashes($res->gerecht);

           ?>
       <p><table>
           <tr>
                 <td>Naam</td>
                 <td><input name="naam" type="text" value="<? echo $this->naam ?>" size="80"></td>
            </tr>
            <tr>
                 <td>Referentie</td>
                 <td><input name="ref" type="text" value="<? echo $this->ref ?>" size="80"></td>
            </tr>
            <tr>
                 <td>Type</td>
                 <td>
                 <?php
                 
                 $i = 1;
                 echo "<table border=\"0\" width=\"100%\"><tr>";
                 foreach ($gerechttype as $id => $naam)
                 {
                      echo "<td><input type=\"radio\" name=\"gerechtid\" value=\"$id\" ";
                      if ($id == $this->gerechtid) echo " checked >$naam\n "; else echo " >$naam\n ";
                      if ($i % 3 == 0) echo "</tr><tr>";
                      $i++;
                 }   
                 echo "</table>";
            ?>
            </tr>
            <tr><td>Keuken</td><td>
                 <?php
                 $i = 1;
                 echo "<table border=\"0\" width=\"100%\"><tr>";
                 foreach ($keuken as $id => $naam)
                 {
                      echo "<td><input type=\"checkbox\" name=\"keukenidarray[]\" value=\"$id\" ";
                      if (is_array($this->keukenidarray) && in_array($id, $this->keukenidarray)) echo "checked >$naam"; else echo " >$naam";
                      if ($i % 5 == 0) echo "</tr><tr>";
                      $i++;
                 }
                 echo "</table>";
                 ?>
            </td></tr>
            <tr>
                 <td>Ingredi&euml;nten
                 <td><textarea rows="8" cols="80" name="ingredient"><?=$this->ingredient?></textarea>
            <tr>
                 <td>Bereiding
                 <td><textarea rows="8" cols="80" name="bereiding"><?=$this->bereiding?></textarea>
            </tr>

            <tr>
                 <td>Commentaar</td>
                 <td><textarea rows="3" cols="80" name="opmerking"><?=$this->opmerking?></textarea>
            </tr>
            <input type="hidden" name="receptid" value="<? echo $this->id ?>">
    </table>
    <?php if ($this->id) $btname="Aanpassen"; else $btname="Toevoegen"; ?>
    <div align="left"><input type=submit value="<?=$btname?>"></div>
    <div align="right"><input type=reset></div>
    </form>
    <?
    }

    function Display($db,$auth)
    {
        global $PHP_SELF, $display;
       
        $db->query = "SELECT receptid,naam FROM recepten
                      WHERE naam like '$display%'
                      ORDER by naam";
        $db->DBQuery();

        echo "<p><table border=\"1\" width=\"100%\">";
        echo "<tr>";
        echo "<td width=\"30%\">" . "Naam" . "</td>";
        if ($auth->isPermitted(EDIT))
            echo "<td width=\"5%\">" . "Edit" . "</td>";
        if ($auth->isPermitted(ADVEDIT))
            echo "<td width=\"5%\">" . "Delete" . "</td>";
        echo "</tr>";

        while ($result = $db->DBResult())
        {
                   $id = $result->receptid;
                   echo "<tr>";
                   echo "<td><a name=\"$id\">" . stripslashes($result->naam) . "</a></td>";
                   if ($auth->isPermitted(EDIT))
                   echo "<td><a href=\"$PHP_SELF?action=edit&edit=$id&display=$display\">Edit</a></td>";
                   if ($auth->isPermitted(ADVEDIT))
                   echo "<td><a href=\"$PHP_SELF?action=delete&delete=$id&display=$display\">Delete</a></td>";
                   echo "</tr>";
        }
        echo "</table>";
    }
}


class ReceptPage extends Webpage
{
    function ReceptPage($title, $style, $db, $auth, $author="", $email="", $keywords="",$description="")
    {
       global $PHP_SELF;
       $alfa = array(a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z);
       Webpage::Webpage($title, $style, $author, $email, $keywords,$description);
       echo "<div align=\"right\" class=\"menuhead\">ReceptenLijst<br>User : ". $auth->getUsername() . "<br>" . $this->recipecount($db). " recipes</div>";

       if ($auth->isPermitted(EDIT))
           echo "<p><a href=\"$PHP_SELF?action=new\">Add new</a> | <a href=\"$PHP_SELF\">View</a><p>";         
       foreach ($alfa as $letter)
       {
           echo "<a href=\"$PHP_SELF?display=$letter\">[". ucfirst($letter) . "]</a>";
       }
       
    }

    function recipecount($db)
    {
       $db->DBQuery("select count(receptid) as totaalrecept from recepten");
       $res = $db->DBResult();
       $totaalrecept = intval($res->totaalrecept);
       return $totaalrecept;
    }
}

?>
