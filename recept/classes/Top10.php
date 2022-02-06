<?php
date_default_timezone_set('America/Atlanta');
define (TODAY, date("Ymd"));
define (WEEKAGO, date('Ymd', strtotime('-'. 7 .' days')));
define (TIMEAGE, 28);
define (TIMELIMIT, date('Ymd', strtotime('-'. TIMEAGE .' days')));
define (CUTOFF, 50);

class Top10
{

    var $idnr;
    var $db;
    var $viewdate;
    var $viewtime;
    var $viewip;
    var $viewunixtime;
    
    function Top10($db, $idnr="")
    {
        $this->db = $this->setDBase($db);
        if (!$this->db) exit("No database was specified");
        
        $this->idnr = $this->setIdnr($idnr);
        $this->viewdate = $this->setViewdate();
        $this->viewtime = $this->setViewtime();
        $this->viewip = $this->setViewIP();
        $this->viewunixtime = mktime();
        
        if (!$this->idnr) $this->_Display(); 
        else
             {
                  $this->_DeleteOldEntries();
                  $this->_AddEntry();
             }    
    }
    
    function setDBase($db)
    {
        if (is_object($db))
           $retval = $db;
        else $retval = 0;
        return $retval;    
    }
    
    function setIdnr($idnr)
    {
        $this->idnr = intval($idnr);
        if ($idnr > 0 && $this->_EntryExist($idnr)) 
           $retval = $idnr;
        else $retval = 0;   
        return $retval;
    }
    
    function setViewdate()
    {
        if ($_POST['viewdate'])
        {
            $retval = $_POST['viewdate'];
	    if ($retval == 'week' or $retval == 'all') {}
            else $retval = intval($_POST['viewdate']);
        }
        else if ($_GET['viewdate'])
            $retval = intval($_GET['viewdate']);
        else $retval = 0;
        //if ($retval == 1) $retval = 0;
        return $retval;
    }
    
    function setViewtime()
    {
        if (intval($_GET['viewtime']))
           $retval = intval($_GET['viewtime']);
        else $retval = "";
        return $retval;   
    }
    
    function setViewIP()
    {
        if ($_SERVER['REMOTE_ADDR']) $viewip = $_SERVER['REMOTE_ADDR'];
        //
        // code from http://be.php.net/preg_match
        //
        $num="(\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5])";
        if (!preg_match("/^$num\\.$num\\.$num\\.$num$/", $viewip)) $viewip = "0.0.0.0";
        return $viewip; 
    }
    
    
    function _Display()
    {
    global $_SERVER;
    // displays the top10 of viewed recipes
       $keukentop10 = $this->GenerateKeukenTop10();
       $keukenlijst = $this->GenerateKeuken();
       $receptenlijst = $this->GenerateTop10();
       $gerechtenlijst = $this->GenerateGerecht();
       $hitlist = $this->GenerateHitlist();
       $timelist = $this->GenerateTimelist();       
             
       $timehtml = $keukenhtml = $keuken10html = $hithtml = $gerechthtml = $recepthtml = "<table border=\"0\" width=\"100%\">";
       
       foreach ($keukenlijst as $keuken)
       {
              $keukenhtml .= "<tr><td width=\"20%\">" . $keuken->keukencount . "</td><td>" . $keuken->keuken . "</td>";
       }     
       $keukenhtml .= "</table>";
       
       foreach ($keukentop10 as $keuken)
       {
              $keuken10html .= "<tr><td width=\"20%\">" . $keuken->viewedtotal . "</td><td>" . $keuken->keuken . "</td>";
       }     
       $keuken10html .= "</table>";

       foreach ($gerechtenlijst as $gerecht)
       {
              $gerechthtml .= "<tr><td width=\"20%\">" . $gerecht->gerechtcount . "</td><td>" . $gerecht->gerecht . "</td>";
       }
       $gerechthtml .= "</table>";
       
       $count = 0;                            
       foreach ($receptenlijst as $recept)
       {
              $count++;
              $recepthtml .= "<tr><td width=\"10%\">" . $recept->viewedtotal . "</td><td><a href=\"visitors.php?receptid=". $recept->id ."\">" . stripslashes($recept->naam) . "</a></td>";
       }
       $recepthtml .= "</table>";
       
       foreach ($timelist as $hit)
       {
              $link = "<a href=\"" . $_SERVER['PHP_SELF'] . "?viewdate=" . $this->viewdate . "&viewtime=". $hit->viewtime . "\">";
              $timehtml .= "<tr><td width=\"50%\">$link" . $hit->viewtime . "</a></td><td>" . $hit->viewedtotal . "</td></tr>";
       }
       $timehtml .= "</table>";
     
       $totalhits = $hits = 0;
       foreach ($hitlist as $hit)
       {
              $hits++;
              $totalhits += $hit->viewedtotal;
              $hithtml .= "<tr><td width=\"50%\">" . $this->_Printdate($hit->viewdate) . "</td><td>" . $hit->viewedtotal . "</td></tr>";
       }     
       $hithtml .= "<tr><td colspan=\"2\"><hr></td></tr><tr><td colspan=\"2\">$totalhits hit(s) in $hits day(s)</table>";       
       
                                         
?>
<table border="0" width="100%">
     <tr>
        <td width="25%" valign="top">
          <p><strong>Hitlist by date</strong></p>        
             <?=$hithtml ?>          
          <p><strong>Gerechttypes</strong></p>
             <?=$gerechthtml ?>          
          <p><strong>Keukentypes</strong></p>
             <?=$keukenhtml ?>
        <td width="25%" valign="top">
          <p><strong>Hitlist by time</strong></p>
             <?=$timehtml ?>
	  <p><strong>Keukentypes top 10</strong></p>
             <?=$keuken10html ?>
          </td>           
        <td width="50%" valign="top">
          <p><strong>Recepten Top <?=$count ?>
          <?php 
                $viewdate = intval($this->viewdate);
                if ($viewdate) echo "<br>" . $this->_Printdate($viewdate);
                if ($this->viewtime) echo " " . $this->viewtime . ":00-" . $this->viewtime . ":59";
          ?>
          </strong></p>
             <?=$recepthtml ?></td>
     </tr>   
</table>

<?php           
    }


    function GenerateKeuken()
    {
       $query  = "select count(recept_keuken.keukenid) as keukencount,recept_keuken.keukenid, keuken 
                  from keuken,recept_keuken 
                  where keuken.keukenid = recept_keuken.keukenid
                  group by keukenid 
                  order by keukencount DESC";

       $this->db->DBQuery($query);
       while ($keuken = $this->db->DBResult()) $keukenlijst[] = $keuken;                  
       return $keukenlijst;
    }

    function GenerateKeukenTop10()
    {
        $query = "select count(viewid) as viewedtotal, recept_keuken.keukenid as id, keuken  
                  from viewed, keuken, recept_keuken 
                  where viewed.receptid = recept_keuken.receptid 
                        and recept_keuken.keukenid = keuken.keukenid 
                  group by id 
                  order by viewedtotal desc,keuken asc";
        $this->db->DBQuery($query);
       while ($keuken = $this->db->DBResult()) $keukenlijst[] = $keuken;                  
       return $keukenlijst;     
    }


     function GenerateHitlist()
     {
       $query  = "select count(viewid) as viewedtotal,viewdate
                  from viewed
                  group by viewdate
                  order by viewdate DESC";
       $this->db->DBQuery($query);
       while ($hit = $this->db->DBResult()) 
              $hitlist[] = $hit;                  
       return $hitlist;                
     }
    
     function GenerateTimelist()
     {
       $query = "select distinct(viewdate) as vd from viewed order by viewdate DESC";
       $this->db->DBQuery($query);
       
       $datelist = "<option value=\"all\">All results</option>";
       $datelist .= "<option value=\"week\">Last week</option>";
       while ($datehit = $this->db->DBResult())
       {
            if ($this->viewdate == $datehit->vd)  
               $datelist .= "<option selected value=\"" . $datehit->vd . "\">" . $this->_Printdate($datehit->vd) . "</option>"; 
            else
               $datelist .= "<option value=\"" . $datehit->vd . "\">" . $this->_Printdate($datehit->vd) . "</option>";                
       }
       echo $PHP_SELF;              
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <select name="viewdate">
    <?php
                echo $datelist;
    ?>      
    </select>
    <input type="submit" name="Select date" value="Select datum">
</form>
<?php
      //echo "a".$this->viewdate."a";
      switch($this->viewdate)
      {
          case "all"	:  $where = ""; break;
          case "week"   :  $where = "WHERE viewdate > " . WEEKAGO; break;
          default	:  $where = "WHERE viewdate = " . $this->viewdate; break;
      }

       //if ($this->viewdate) $where = "WHERE viewdate = " . $this->viewdate;
       //else $where = "";     
       $query  = "select count(viewid) as viewedtotal,viewtime
                  from viewed
                  %where%
                  group by viewtime
                  order by viewtime ASC";
       $query = ereg_replace("%where%", $where, $query);
       //echo $query;
       $this->db->DBQuery($query);
       while ($hit = $this->db->DBResult()) 
              $hitlist[] = $hit;                  
       return $hitlist;                
     } 

     function GenerateTop10()
     {
       $query  = "select count(viewid) as viewedtotal, viewed.receptid as id,naam 
                  from viewed,recepten 
                  where viewed.receptid = recepten.receptid %where%
                  group by id 
                  order by viewedtotal DESC, naam ASC
                  limit " . CUTOFF;     
       
       $viewdate = intval($this->viewdate);  
       
       switch ($this->viewdate)
       {
           case "all"   : $where = ""; break;
           case "week"	: $where = "and viewdate > ". WEEKAGO ; break;
           default	: $where = "and viewdate = " . $this->viewdate; break;
       }
       
       switch ($this->viewtime)
       {
           case ""	: $where .= ""; break;
           default	: $where .= " and viewtime = " . $this->viewtime ; break;
       }    

       $query = ereg_replace("%where%", $where, $query);
       //echo $query;
       $this->db->DBQuery($query);
       while ($recept = $this->db->DBResult()) 
                $receptenlijst[] = $recept;                  
       return $receptenlijst;           
     }


     function GenerateGerecht()
     {
       $query  = "select count(recepten.gerechtid) as gerechtcount, gerecht.gerecht 
                  from gerecht,recepten 
                  where recepten.gerechtid = gerecht.gerechtid  
                  group by (recepten.gerechtid) 
                  order by gerechtcount desc";     
     
       $this->db->DBQuery($query);
       while ($gerecht = $this->db->DBResult()) $gerechtenlijst[] = $gerecht;
       return $gerechtenlijst;
     }

    
    function _EntryExist()
    {
    // checks if entry exists in the 'recepten' database
       $query = "SELECT receptid FROM recepten WHERE receptid = " . $this->idnr;
       $this->db->DBQuery($query);
       if ($this->db->DBNumRows()) $returnval =  true;
          else $returnval = false;
       return $returnval;
       
    }
    
    function _AddEntry()
    {
    // add recipe entry to the 'viewed' database
       $time = date("H");
       $query = "INSERT INTO viewed(viewtime, viewdate, viewip, viewunixtime,  receptid) 
                 VALUES ($time,".TODAY . " ,\"" . $this->viewip . "\" ," . $this->viewunixtime . " ," . $this->idnr .")";
       $this->db->DBQuery($query);
    }
    
    function _DeleteOldEntries()
    {
    // deletes all entries that are older than the prescribed period
    $query = "DELETE FROM viewed WHERE viewdate < " . TIMELIMIT;
    $this->db->DBQuery($query);    
    }    
    
    function _Printdate($date)
    {
      $months = array(0, "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
      $year = intval(substr($date,0,4));
      $monthnr = intval(substr($date, 4,2));
      $month = $months[$monthnr];
      $day = intval(substr($date, 6, 2));
      $dow = date("w", strtotime("$day-$month-$year"));
      if ($dow == 0 || $dow == 6) $retval = "<em>$month $day</em>";
      else $retval = "$month $day";
      return $retval;
    }  
}