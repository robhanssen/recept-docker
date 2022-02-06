<?php

require_once("../classes/Auth.php");
require_once("../classes/Dbase.php");
require_once("../classes/Webpage.php");
require("../config/config.php");


$auth = new Auth(EDIT);
$page = new Webpage("Uitgevers", $Style);
$db = new Database($Host,$Name,$User,$Pass);

echo "<div align=\"right\" class=\"menuhead\">Uitgevers<br>User : ". $auth->getUsername() . "</div>";

if (isset($_GET['action']) && $_GET['action'] == "update")
{
    $id = $_POST['uitgeverid'];
    $naam = addslashes(htmlspecialchars($_POST['uitgevernaam']));
    $db->query = "update uitgever set uitgevernaam = '$naam' where uitgeverid = $id";
    if ($auth->isPermitted(ADVEDIT)) 
        $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "delete")
{
    $id = $_GET['delete'];
    $db->query = "delete from uitgever where uitgeverid = $id";
    if ($auth->isPermitted(ADVEDIT)) 
       $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "new")
{
     $naam = addslashes(htmlspecialchars($_POST['uitgevernaam']));
     $db->query = "insert into uitgever (uitgevernaam) values('$naam')";
     if ($auth->isPermitted(EDIT)) 
        $db->DBQuery();
}

$form = "<p><form action=\"$PHP_SELF?action=new\" method=\"POST\">" .
        "<input type=\"text\" name=\"uitgevernaam\" size=\"100\">" .
        "<input type=\"submit\" name=\"action\" value=\"new\"></form>";
echo $form;



$db->query = "select uitgeverid,uitgevernaam from uitgever order by uitgevernaam asc";
$db->DBQuery();
while ($news = $db->DBResult() )
{
    $form = "<p><form action=\"$PHP_SELF?action=update\" method=\"POST\">" .
            "<input type=\"text\" name=\"uitgevernaam\" size=\"100\" value=\"". stripslashes($news->uitgevernaam) ."\">" .
            "<input type=\"hidden\" name=\"uitgeverid\" value=\"$news->uitgeverid\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<input type=\"submit\" value=\"Change\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<a href=\"$PHP_SELF?action=delete&delete=$news->uitgeverid\">Delete</a>";
    $form .= "</form>";
    echo $form;
}

$db->DBClose();
?>