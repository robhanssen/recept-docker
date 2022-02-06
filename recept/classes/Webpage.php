<?php
// $Revision: 1.9 $
class Webpage
{
    var $title;
    var $stylesheet;
    var $author;
    var $email;
    var $keywords;
    var $description;


    function Webpage($title, $stylesheet="", $author="",$email="",$keywords="",$description="")
    {
        $this->title = $title;
        $this->stylesheet = $stylesheet;
        $this->author = $author;
        $this->email = $email;
        $this->keywords = $keywords;
        $this->description = $description;

        $this->Header();
    }

    function Header()
    {
        ob_start();
        global $PHP_SELF;
        echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">";
        echo "<html><head>";
        if ($this->title)       echo "<title>$this->title</title>";
        ?><link rel="stylesheet" type="text/css" href="/config/basicstyle.css"><?php
        if ($this->stylesheet)  echo "<link rel=\"stylesheet\" href=\"$this->stylesheet\" type=\"text/css\">";
        if ($this->author)      echo "<meta name=\"author\" content=\"$this->author\">";
        if ($this->email)       echo "<meta name=\"email\" content=\"$this->email\">";
        if ($this->keywords)    echo "<meta name=\"keywords\" content=\"$this->keywords\">";
        if ($this->description) echo "<meta name=\"description\" content=\"$this->description\">";
        echo "</head><body>";
    }

    function Footer()
    {
          /*
          global $visitor;
          if ($visitor)
          {
          $numvis = $visitor->getNumVisitors();
          }
          if ($numvis == 1 ) $comment = "is $numvis visitor";
          else $comment = "are $numvis visitors";
           */
          echo "<div style=\"text-align : right; font-size : small\"><h6>Deze website is slechts bedoeld voor persoonlijk gebruik en niet voor commerciele doeleinden<h6></div>";
          echo "<div style=\"text-align : right\"><h5>Currently there no people on this site<br>";
          echo "For suggestions and comments please contact the <a href=\"/recept/mail.php?to=web&subject=Your+website\">webmaster</a><br>";
          echo "<br>Go back to my <a href=\"http://www.peperazzi.nl/\">homepage</a></h5></div>"; 
          echo "</body></html>";
          ob_end_flush();
    }

    function InsertLink($link, $linktext,$linkcomment)
    {
        if (!$linktext) die("invalid link: linktext not specified");

        $ref = "<a href=\"$link\" ";
        if ($linkcomment)
        {
            $linkcomment = htmlspecialchars($linkcomment);
            $ref .= "onmouseover=\"window.status='$linkcomment'; return true\" onmouseout=\"window.status=''; return true\" ";
        }
        $ref .= ">$linktext</a>";
        echo $ref;
    }
}
?>
