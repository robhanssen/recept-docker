<?php
include "../config/config.php";
include "../classes/Auth.php";
include "../classes/Dbase.php";


$menu = array (
                "user.php" => array("Users","User administration module",VIEW),
                "recept.php"  => array("Receptenlijst","Recepten toevoegen, bewerken en verwijderen",EDIT),
                "keuken.php" => array("Keuken","Keuken types",EDIT),
                "stats.php" => array("Statistics", "Statistieken over de recepten", VIEW),
                "visitors.php" => array("Visitors", "Bezoekers op dit moment",EDIT),
                "$PHP_SELF?target=logout" => array("Log out","log out of the system",VIEW),
                "../index.php" => array("Home","Terug naar de receptenpagina",VIEW)
                );
                
function displayframe()
{
    global $PHP_SELF;
    ?>
<html>
<head>
<title>Recepten Administratie</title>
</head>
<frameset cols="188,*" border="0">
    <frame name="admenu" target="admain" src="<? echo "$PHP_SELF?target=menu" ?>" scrolling="no" noresize>
    <frame name="admain" scrolling="auto" src="<? echo "$PHP_SELF?target=main" ?>" target="_self">
    <noframes>
        <body>
          <p>This page uses frames, but your browser doesn't support them.</p>
        </body>
    </noframes>
</frameset>
</html>
<?php
}



function displaymenu($Auth, $menu)
{
    global $PHP_SELF,$Style;
    $style = $Style;
    echo "<html>
            <head>
                <title>Menu</title>
                <link rel=\"stylesheet\" href=\"/style.css\">
                <link rel=\"stylesheet\" href=\"$style\">
             </head>
             <body>";

    foreach ($menu as $link => $text)
    { 
      if ($Auth->isPermitted($text[2]))
      {
          if ($text[0]!= "Home") echo "<p><a href=\"$link\" target=\"admain\">$text[0]</a></p>"; 
          else echo "<p><a href=\"$link\" target=\"_top\">$text[0]</a></p>";
      }
    }
    echo "</body></html>";
}


function displaymain($Auth,$menu)
{
    global $PHP_SELF, $Style, $PermissionList;
?>
<html>
<head>
<title>SKA Admininstration Panel (SKAAP)</title>
<link rel="stylesheet" href="/style.css">
<link rel="stylesheet" href="<? echo $Style ?>">
</head>
        <body>
          <p>Welcome <? echo $Auth->authUsername ." (permission level:" . $PermissionList[$Auth->authUserPermission] . ")" ?></p>
          <p>These pages show the administrator Apps.</p>

          <ul>
          <? foreach ($menu as $link => $text)
          {
              echo "<li style=\"color : black\">" . ucfirst($text[0]) . " : " . $text[1];
          }
          ?>
          </ul>
          <i>Version history</i>
          <ul>
          <li>Jan. 25, 2006: Added visitors statistics module</li>
          <li>Aug. 15, 2005: Added statistics modules</li>
          <li>Jan. 05, 2005: Two versions: one open without reference to recipe, one secure with reference</li>
          <li>Aug. 16, 2004: Implemented 'Back' button to go back to previous search</li>
          <li>Aug. 14, 2004: Upgrade 'ingredient search option' to include multiple ingredients separated by a space</li>
          <li>Nov. 23, 2004: Initial version</li>
          </ul>
       

        </body>
</html>

<?php
}


$auth = new Auth(VIEW);
// main program start and authentification vs. database
   // if you get to here, you are authorized to use the database
   $target = $_GET['target'];
   switch ($target)
   {
       case "menu" : displaymenu($auth,$menu); break;
       case "main" : displaymain($auth,$menu); break;
       case "logout" : $auth->logout(); break;
       default     : displayframe(); break;
   }
?>
