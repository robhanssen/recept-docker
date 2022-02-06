<?php
// include the class definition files
require("../classes/Auth.php");
require("../classes/Dbase.php");
require("../classes/Webpage.php");
require("classes/Recept.php");
require("../config/config.php");

$auth = new Auth(EDIT);

// MAIN PROGRAM
$db = new DataBase($Host, $Name, $User, $Pass);
$page = new ReceptPage("Receptenlijst", $Style, $db, $auth);
$recept = new Recept();


$db->DBQuery("select count(receptid) as totaalrecept from recepten");
$res = $db->DBResult();
$totaalrecept = intval($res->totaalrecept);
define(RECIPECOUNT, $totaalrecept);

if ($_GET['display'] && ereg("^[A-Za-z]$", $_GET['display']))
   $display = $_GET['display'];
else $display = "a";

$action = $_GET['action'];

    switch($action)
    {
       case "new"  :      echo "<form method=\"post\" action=\"$PHP_SELF?action=add&display=$display\">";
                          $recept->Enter($db);
                          break;
        case "add" :      
                          $recept->Add($db,$auth); 
                          $recept->Display($db,$auth);
                          break;
        case "edit" :     
                          $recept->id = $_GET['edit'];
                          $recept->Edit($db);
                          break;
        case "update" :   
                          $recept->Update($db,$auth);
                          $recept->Display($db,$auth);
                          break;
        case "delete" :   
                          $recept->id = $_GET['delete'];
                          $recept->Delete($db,$auth);
                          $recept->Display($db,$auth);
                          break;
        default  :        
                          $recept->Display($db,$auth);
                          break;
        }

    $db->DBClose();

?>
