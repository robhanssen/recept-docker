<?php
// $Revision: 1.2 $
include "./config/config.php";
include "./classes/Webpage.php";
include "./classes/Email.php";

$page = new Webpage("Send mail", $Style);
                    
if (!isset($_GET['to']) || !$ValidRecipients[$_GET['to']])
    echo "<h3>Error: no valid recipient was entered!</h3>.
          <h4>Return to the <a href=\"main.php\">homepage</a>";
else
{
    $recipient = $ValidRecipients[$_GET['to']][0];
    $mesg = new EMail($sendername,$senderemail,$recipient,$subject,$message);
}
$page->Footer();
?>
