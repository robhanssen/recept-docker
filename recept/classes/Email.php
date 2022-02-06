<?php
// $Revision: 1.5 $
// valid recipients for email
// format  alias => array( email-address , real-name )
$ValidRecipients = array(
                    "web" => array("joyce@peperazzi.nl", "Joyce")
                        );

Class EMail
{
    var $sendername;
    var $senderemail;
    var $recipient;
    var $subject;
    var $message;
    
    
    function EMail($sendername,$senderemail,$recipient,$subject,$message)
    {
        $_sender = $this->setSender($sendername,$senderemail);
        $this->sendername = $_sender['name'];
        $this->senderemail = $_sender['email'];
        
        $this->recipient = $this->setRecipient($recipient);
        $this->subject = $this->setSubject($subject);
        $this->message = $this->setMessage($message);

        if ($this->senderemail && $this->subject && $this->message)
           $this->_sendMail();
        else $this->Enter();
    }
    
    
    function setSender($sendername, $senderemail)
    {
          $_sender = array();
          if ($sendername) $_sender['name'] = $sendername; else $_sender['name'] = "";
          if ($this->_validEmail($senderemail)) $_sender['email'] = $senderemail; else  $_sender['email'] = "";
          return $_sender;
    }
    
    function setRecipient($recipient)
    {
          if ($this->_validEmail($recipient)) return $recipient; else return "";
    }
    
    function setSubject($subject)
    {
        return htmlspecialchars($subject);
    }
    
    function setMessage($message)
    {
        return htmlspecialchars($message);
    }
        
        
    function Enter()
    {
    global $PHP_SELF, $ValidRecipients;
    ?>
    <h2>Send mail to <?php echo $ValidRecipients[$_GET['to']][1] ?></h2>

    <form method="POST" enctype="multipart/form-data" action="<?=$PHP_SELF?>?action=mail&to=<?=$_GET['to']?>">
          <table border="0" width="50%">
          <tr>
             <td width="50%">Name</td>
             <td width="50%"><input type="text" value="<?=$this->sendername ?>" name="sendername" size="30"></td>
          </tr>
          <tr>
             <td width="50%">E-mail<sup style="color :red">*<sup></td>
             <td width="50%"><input type="text" name="senderemail" value="<?=$this->senderemail ?>" size="30"></td>
          </tr>
          <tr>
              <td width="50%">Subject<sup style="color :red">*<sup></td>
              <td width="50%"><input type="text" value="<?=$this->subject ?>" name="subject" size="30"></td>
          </tr>
          </table>
         <p><textarea rows="15" name="message" cols="70"><?=$this->message ?></textarea><br>
         <br>
         <input type="submit" value="Submit" name="Submit"><input type="reset" value="Reset" name="Reset"></p>
    <h5><sup style="color :red">*</sup>: Required fields</h5>
    </form> 
    <?php
    }
    
    function _sendMail()
    {
        if (mail($this->recipient, $this->subject, $this->message, "From: $this->sendername <$this->senderemail>"))
           echo "The following mail has been sent:
                 <p>Subject: $this->subject
                 <br>Message:<p>$this->message
                 <p>Thank you for your interest in our site
                 <br>Return to the <a href=\"/~rob/recept/index.php\">homepage</a>";
        else echo "There was an error sending your mail";
    }
    
    function _validEmail($email)
    {
        if (eregi("^[0-9a-z_]([_\.-]?[0-9a-z])*@[0-9a-z][0-9a-z\.-]*\.[a-z]{2,4}\.?$", $email, $check))
        {
            if ( getmxrr(substr(strstr($check[0], '@'), 1), $validate_email_temp) )
            {
                return TRUE;
            }
            // THIS WILL CATCH DNSs THAT ARE NOT MX.
            if(checkdnsrr(substr(strstr($check[0], '@'), 1),"ANY"))
            {
                return TRUE;
            }
        }
        return FALSE;
    }

}
