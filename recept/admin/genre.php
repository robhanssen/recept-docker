<?php

require_once("../classes/Dbase.php");
require_once("../classes/Auth.php");
require("../classes/Webpage.php");
require("../config/config.php");


$auth = new Auth(EDIT);
$page = new Webpage("Genre", $Style);
$db = new Database($Host,$Name,$User,$Pass);

echo "<div align=\"right\" class=\"menuhead\">Genre's<br>User : ". $auth->getUsername() . "</div>";

if (isset($_GET['action']) && $_GET['action'] == "update")
{
    $gerneid = $_POST['genreid'];
    $genrenaam = addslashes(htmlspecialchars($_POST['genrenaam']));
    $db->query = "update genre set genrenaam = '$genrenaam' where genreid = $gerneid";
    if ($auth->isPermitted(ADVEDIT)) 
        $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "delete")
{
    $genreid = $_GET['delete'];
    $db->query = "delete from genre where genreid = $genreid";
    if ($auth->isPermitted(ADVEDIT)) 
       $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "new")
{
     $genre = addslashes(htmlspecialchars($_POST['genrenaam']));
     $db->query = "insert into genre (genrenaam) values('$genre')";
     if ($auth->isPermitted(EDIT)) 
        $db->DBQuery();
}

$form = "<p><form action=\"$PHP_SELF?action=new\" method=\"POST\">" .
        "<input type=\"text\" name=\"genrenaam\" size=\"100\">" .
        "<input type=\"submit\" name=\"action\" value=\"new\"></form>";
echo $form;



$db->query = "select genreid,genrenaam from genre order by genrenaam asc";
$db->DBQuery();
while ($news = $db->DBResult() )
{
    $form = "<p><form action=\"$PHP_SELF?action=update\" method=\"POST\">" .
            "<input type=\"text\" name=\"genrenaam\" size=\"100\" value=\"". stripslashes($news->genrenaam) ."\">" .
            "<input type=\"hidden\" name=\"genreid\" value=\"$news->genreid\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<input type=\"submit\" value=\"Change\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<a href=\"$PHP_SELF?action=delete&delete=$news->genreid\">Delete</a>";
    $form .= "</form>";
    echo $form;
}

$db->DBClose();
?>