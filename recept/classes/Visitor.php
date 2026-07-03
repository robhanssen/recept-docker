<?php

//include_once("./config/config.php");

define('INTERVAL', 5*60);
define('ONEDAY', 24*60*60);

Class Visitor
{
    var $Time;
    var $NumVisitors;
    var $DBLink;

    public function __construct()
    {
        $this->Time = time();
        $this->DBConnect();
        $this->CountVisitors();
    }

    public function DBConnect()
    {
        global $Host,$User,$Name,$Pass;
        $this->DBLink = new mysqli($Host, $User, $Pass, $Name);
        if ($this->DBLink->connect_error) {
            die("Cannot connect to $Host");
        }
    }
    
    public function DBClose()
    {
       $this->DBLink->close();
    }


    function getDateTime($timestamp = 0)
    {
       if (!$timestamp) $timestamp = $this->Time; 
       $datetime = date("YmdHis", $timestamp);
       return $datetime;
    }


    function CountVisitors()
    {
        $vis5 = $this->Time - INTERVAL;    
        $query = "SELECT count(distinct(viewip)) as numvis FROM viewed WHERE viewunixtime > $vis5";
        $rs = $this->DBLink->query($query) or die("Unable to perform query: $query");
        $r = $rs->fetch_object();
        $this->NumVisitors = $r->numvis;
    }

    function getNumVisitors()
    {
       return $this->NumVisitors;
    }

    function getVisitors()
    {
        if (isset($_GET['order'])) $order = $_GET['order'];
        switch($order)
        {
            case "time" : $orderby = viewunixtime; break;
            case "number" : $orderby = visnr; break;
            default	: $orderby = viewunixtime; break;
        }
        
        $vis5 = $this->Time - INTERVAL;           
        $vis24 = $this->Time - ONEDAY;
        
        
        if (isset($_GET['address']) || isset($_GET['receptid'])) 
        {
             if (isset($_GET['address'])) { $ipaddress = urldecode($_GET['address']); $search = "viewip = '$ipaddress'";}
             else if (isset($_GET['receptid'])) { $receptid = intval($_GET['receptid']); $search = "viewed.receptid = $receptid"; }
        $query = "SELECT viewip,viewed.viewid,viewed.receptid as id,naam,viewunixtime 
                  FROM viewed,recepten 
                  WHERE viewed.receptid=recepten.receptid and $search
                  ORDER BY viewunixtime DESC"; 

        echo "<table border=\"1\"><tr><td><b>Visitor</b><td><b>IP address</b><td><b>Visit Date</b><td><b><a href=\"?order=time\">Time</a></b><td><b><a href=\"?order=number\">Recept</a></b>";  
        $rs = $this->DBLink->query($query) or die("Unable to perform query: $query");
        while ($r = $rs->fetch_object())
        {
           $datetime = $r->viewunixtime;
           $date = date("d/m/Y", $datetime);
           $h = date("H:i:s", $datetime);
           $name = gethostbyaddr($r->viewip);
           if ($datetime < $vis5)
               echo "<tr><td>" . $this->constructlink($r->viewip,$name) . "<td>".$r->viewip."<td>$date</td><td>$h</td><td>" . $this->constructlink2($r->id,$r->naam) ."</td>";
           else echo "<tr><td><b>" . $this->constructlink($r->viewip,$name) . "</b><td>".$r->viewip."<td>$date<td>$h</td><td>" . $this->constructlink2($r->id,$r->naam) ."</td>";
         }
         echo "</table>";
         }       
       
       
       
        else {
        $vis5 = $this->Time - INTERVAL;           
        $vis24 = $this->Time - ONEDAY;
        
        $query = "SELECT viewip,count(viewid) as visnr,viewunixtime 
                  FROM viewed 
                  WHERE viewunixtime > $vis24
                  GROUP BY viewip 
                  ORDER BY $orderby DESC";
                
        echo "<table border=\"1\"><tr><td><b>Visitor</b><td><b>IP address</b><td><b>Visit Date</b><td><b><a href=\"?order=time\">Time</a></b><td><b><a href=\"?order=number\"># of visits</a></b>";  
        $rs = $this->DBLink->query($query) or die("Unable to perform query: $query");
        while ($r = $rs->fetch_object())
        {
           $datetime = $r->viewunixtime;
           $date = date("d/m/Y", $datetime);
           $h = date("H:i:s", $datetime);
           $name = gethostbyaddr($r->viewip);
           if (!preg_match('/(crawl|bot|search)/i', $name))
           {
              if ($datetime < $vis5)
                echo "<tr><td>" . $this->constructlink($r->viewip,$name) . "<td>".$r->viewip."<td>$date</td><td>$h</td><td>" . $r->visnr ."</td>";
              else echo "<tr><td><b>" . $this->constructlink($r->viewip,$name) . "</b><td>".$r->viewip."<td>$date<td>$h</td><td>" . $r->visnr ."</td>";
           }
         }
         echo "</table>";
         }
    }

    function constructlink($ipaddress, $name)
    {
         //$name = gethostbyaddr($ipaddress);
         $number = urlencode($ipaddress);
         $link = "<a href=\"?address=$number\">$name</a>";
         return $link;
    }

    function constructlink2($id, $naam)
    {
         $link = "<a href=\"?receptid=$id\">$naam</a>";
         return $link;
    }


}

?>