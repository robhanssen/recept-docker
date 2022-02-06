<?php
// $Revision: 1.4 $
include "../config/config.php";
include "../classes/Auth.php";
include "../classes/Webpage.php";
require_once("classes/class.user.php");
// include the class definition files



// MAIN PROGRAM
$Page = new UserPage("User Database", $Style);
$auth = new Auth(VIEW);
$Page->Menu($auth);
$user = new AuthClassedEdit($auth);
$Page->Footer();
?> 