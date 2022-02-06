<?php

require_once("../classes/Dbase.php");
require_once("../classes/Auth.php");
require("../classes/Webpage.php");
require("../config/config.php");


$auth = new Auth(EDIT);
$page = new Webpage("Keuken", $Style);
$db = new Database($Host,$Name,$User,$Pass);

echo "<div align=\"right\" class=\"menuhead\">Keuken<br>User : ". $auth->getUsername() . "</div>";

if (isset($_GET['action']) && $_GET['action'] == "update")
{
    $keukenid = $_POST['keukenid'];
    $keuken = addslashes(htmlspecialchars($_POST['keuken']));
    $db->query = "update keuken set keuken = '$keuken' where keukenid = $keukenid";
    if ($auth->isPermitted(ADVEDIT)) 
        $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "delete")
{
    $keukenid = $_GET['delete'];
    $db->query = "delete from keuken where keukenid = $keukenid";
    if ($auth->isPermitted(ADVEDIT)) 
       $db->DBQuery();
}
else if (isset($_GET['action']) && $_GET['action'] == "new")
{
     $keuken = addslashes(htmlspecialchars($_POST['keuken']));
     $db->query = "insert into keuken (keuken) values('$keuken')";
     if ($auth->isPermitted(EDIT)) 
        $db->DBQuery();
}

$form = "<p><form action=\"$PHP_SELF?action=new\" method=\"POST\">" .
        "<input type=\"text\" name=\"keuken\" size=\"100\">" .
        "<input type=\"submit\" name=\"action\" value=\"new\"></form>";
echo $form;



$db->query = "select keukenid,keuken from keuken order by keuken asc";
$db->DBQuery();
while ($news = $db->DBResult() )
{
    $form = "<p><form action=\"$PHP_SELF?action=update\" method=\"POST\">" .
            "<input type=\"text\" name=\"keuken\" size=\"100\" value=\"". stripslashes($news->keuken) ."\">" .
            "<input type=\"hidden\" name=\"keukenid\" value=\"$news->keukenid\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<input type=\"submit\" value=\"Change\">";
    if ($auth->isPermitted(ADVEDIT)) 
        $form .= "<a href=\"$PHP_SELF?action=delete&delete=$news->keukenid\">Delete</a>";
    $form .= "</form>";
    echo $form;
}

$db->DBClose();
?>