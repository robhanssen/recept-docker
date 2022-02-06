<?php

class DataBase
{
    var $dbhost;
    var $dbname;
    var $dbuser;
    var $dbpass;
    var $linkid;
    var $query;
    var $queryresult;
    var $queryobject;
    var $numrows;

    function DataBase($host, $name, $user, $passwd)
    {
        $this->dbhost = $host;
        $this->dbname = $name;
        $this->dbuser = $user;
        $this->dbpass = $passwd;
        $this->DBConnect();
    }

    function DBConnect()
    {
	//die($this->dbhost. $this->dbuser. $this->dbpass. $this->dbname);
	$this->linkid = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);

    }

    function DBQuery($query="")
    {

        if ($query) $this->query = $query;
        $this->queryresult = mysqli_query($this->linkid, $this->query) or die("QUERY Failed!".$this->query);
        //if (!empty($this->queryresult) $this->numrows = mysql_num_rows($this->queryresult);
    }

    function DBResult()
    {
        $this->queryobject = mysqli_fetch_object($this->queryresult);
        return $this->queryobject;
    }

    function DBClose()
    {
        mysqli_close($this->linkid);
    }
    
    function DBNumRows()
    {
        $this->numrows = mysqli_num_rows($this->queryresult);
        return $this->numrows;
    }
   
    function Getid()
    {
       return mysqli_insert_id();
    }
    
    function DBFetchRow()
    {
            return mysqli_fetch_row($this->queryresult);
    }
    
}


?>
