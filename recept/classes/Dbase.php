<?php

class DataBase
{
    public $dbhost;
    public $dbname;
    public $dbuser;
    public $dbpass;
    public $linkid;
    public $query;
    public $queryresult;
    public $queryobject;
    public $numrows;

    public function __construct($host, $name, $user, $passwd)
    {
        $this->dbhost = $host;
        $this->dbname = $name;
        $this->dbuser = $user;
        $this->dbpass = $passwd;
        $this->DBConnect();
    }

    public function DBConnect()
    {
        $this->linkid = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if ($this->linkid->connect_error) {
            throw new RuntimeException('Database connection failed: ' . $this->linkid->connect_error);
        }
    }

    public function DBQuery($query = "")
    {
        if ($query) {
            $this->query = $query;
        }

        $this->queryresult = mysqli_query($this->linkid, $this->query);
        if ($this->queryresult === false) {
            throw new RuntimeException('QUERY Failed! ' . $this->query . ' :: ' . mysqli_error($this->linkid));
        }
    }

    public function DBResult()
    {
        $this->queryobject = mysqli_fetch_object($this->queryresult);
        return $this->queryobject;
    }

    public function DBClose()
    {
        mysqli_close($this->linkid);
    }

    public function DBNumRows()
    {
        $this->numrows = mysqli_num_rows($this->queryresult);
        return $this->numrows;
    }

    public function Getid()
    {
        return mysqli_insert_id($this->linkid);
    }

    public function DBFetchRow()
    {
        return mysqli_fetch_row($this->queryresult);
    }
}
?>
