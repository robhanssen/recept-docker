<?php
// include the class definition files
require("../classes/Auth.php");
require("../classes/Dbase.php");
require("../classes/Webpage.php");
require("classes/Recept.php");
require("../classes/Top10.php");
require("../config/config.php");

$auth = new Auth(EDIT);

// MAIN PROGRAM
$db = new DataBase($Host, $Name, $User, $Pass);
//$page = new Webpage("Statistieken", $Style, $db, $auth);

$top10 = new Top10($db);

//$page->Footer();
$db->DBClose();
?>
