<?php
// include the class definition files
require("../classes/Auth.php");
require("../classes/Dbase.php");
require("../classes/Webpage.php");
require("classes/Recept.php");
require("../config/config.php");
require("../classes/Visitor.php");

$auth = new Auth(EDIT);

// MAIN PROGRAM
$db = new DataBase($Host, $Name, $User, $Pass);
//$page = new Webpage("Visitors", $Style, $db, $auth);


echo "<h1>Bezoekers</h1>";
$vis = new Visitor();
$vis->getVisitors();

//$page->Footer();
$db->DBClose();
?>
