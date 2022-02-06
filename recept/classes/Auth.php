<?php
// <$Id: Auth.php,v 1.11 2002/12/12 13:26:39 rob Exp $>

//session_save_path("/home/rob/tmp/session");

if (file_exists("config/config.php")) require_once("config/config.php");
if (file_exists("../config/config.php")) require_once("../config/config.php");

// let's define some constants for the database
define(__DEFAULTHOST__, $Host);
define(__DEFAULTNAME__, $Name);
define(__DEFAULTUSER__,  $User);
define(__DEFAULTPASS__, $Pass);
define(__DEFAULTTABLE__, "users");
// the default permission; since 'permission' is an int(2), all permissions are lower than 100.
define(__DEFAULTPERMISSION__, 100);

if (file_exists("../config/config.php")) require_once("../config/config.php");
if (file_exists("../../config/config.php")) require_once("../../config/config.php");
if (file_exists("../../../config/config.php")) require_once("../../../config/config.php");

define(VIEW,99);
define(TOURPOOL,98);
define(GUIDELINE,20);
define(SEMINAR,15);
define(PUBS,10);
define(EDIT,7);
define(ADVEDIT,4);
define(SUPER,2);
define(ADMIN,1);

define(EXACT, "exact-auth-match");

$PermissionList = array(VIEW => "View Only",
                        TOURPOOL => "Tourpool",
                        GUIDELINE => "SKA Guideline editor",
                        SEMINAR => "Seminar User",
                        PUBS => "Publications",
                        EDIT => "Editor",
                        ADVEDIT => "Advanced Editor",
                        SUPER => "Super User",
                        ADMIN => "Administrator"
                        );


Class Auth
{
    var $authUserid;           // string: the userid, a unique name in the database
    var $authUsername;         // string: the real name of the user
    var $authUserPass;         // string: password
    var $authUserPermission;   // int(2): user permission level
    var $LoggedOn;             // bool  : logged on or not
    var $PageLevel;            // int   : the auth level of the protected page

    var $Host;
    var $Name;
    var $Table;
    var $User;
    var $Pass;

    function Auth($PageLevel)
    {     
        // first do all the database stuff
        // all the $Host,$Name,User,$Pass and $Table vars could be stored in
        // a config file, or set in the function call below
        if (!$this->setDBInfo($Host,$Name,$User,$Pass,$Table)) echo die ("unable to use the database");
        // then start the authentification
        session_start();

        $this->authUserid = $this->setUserid();
        $this->LoggedOn = $this->setLoggedOn();
        $this->authUsername = $this->setUsername();
        if (!$this->LoggedOn) $this->authUserPass = $this->setUserPass();
        $this->authUserPermission = $this->setUserPerm();
        $this->PageLevel = $this->setPageLevel($PageLevel);

        if (!$this->LoggedOn || !$this->isPermitted($this->PageLevel))
        {
            if ($this->getUserid() && ($this->getUserPass() || $this->isLoggedOn()))
                $userinfo = $this->_authenticate();
            else $this->_login();

            if (is_array($userinfo) && $userinfo[1] <= $this->PageLevel)
            {
                $this->authUsername = $userinfo[0];
                $this->authUserPermission = (int)$userinfo[1];
                $this->LoggedOn = TRUE;
                $this->_initSession();
            }
            else
            {
                 $this->LoggedOn = FALSE;
                 $this->_errorMsg();
                 $this->_login();
            }
        }
    }
    

    function setDBInfo($host="",$name="", $user="", $pass="", $table="")       
    {
        $this->Host  = ($host !="")  ? $host  : __DEFAULTHOST__;
        $this->Name  = ($name !="")  ? $name  : __DEFAULTNAME__;
        $this->User  = ($user !="")  ? $user  : __DEFAULTUSER__;
        $this->Pass  = ($pass !="")  ? $pass  : __DEFAULTPASS__;
        $this->Table = ($table!="")  ? $table : __DEFAULTTABLE__;
        $_connect = mysql_pconnect($this->Host,$this->User,$this->Pass);
        $_select  = mysql_select_db($this->Name,$_connect);
        return ($_connect && $_select); 
    }


    function setUserid()
    {
        if (isset($_SESSION['authuseridr'])) $authuserid = $_SESSION['authuseridr'];
        else if (isset($_POST['authuserid'])) $authuserid = $_POST['authuserid'];
        else $authuserid = "";
        if (ereg(" ",$authuserid)) $authuserid = "";
        return $authuserid;
    }

    function getUserid()
    {
        if ($this->authUserid) return $this->authUserid; else return "Unknown"; 
    } 

    function setUsername()
    {
        if (isset($_SESSION['authusernamer'])) $authusername = $_SESSION['authusernamer'];
        else $authusername = "";
        return $authusername;
    }

    function getUsername()
    {
        if ($this->authUsername) return $this->authUsername; else return "Unknown";
    } 
    
    function setUserPass()
    {
        if (isset($_POST['authuserpass'])) $authuserpass = $_POST['authuserpass'];
        else $authuserpass = "";
        return $authuserpass;
    }

    function getUserPass()
    {
        if ($this->authUserPass) return true; else return false;
    }

    function setLoggedOn()
    {
        if (isset($_SESSION['authissetr'])) $authisset = $_SESSION['authissetr'];
        else $authisset = FALSE;
        return $authisset;
    }

    function setUserPerm()
    {
        if (isset($_SESSION['authuserpermr'])) $authuserperm = (int)$_SESSION['authuserpermr'];
        else $authuserperm = __DEFAULTPERMISSION__;
        return $authuserperm;
    }

    function getUserPerm()
    {
       if ($this->authUserPermission) return $this->authUserPermission; else return __DEFAULTPERMISSION__;
    }

    function setPageLevel($PageLevel)
    {
         return $PageLevel;
    }
    
    function getPageLevel()
    {
         if ($this->PageLevel) return $this->PageLevel; else return __DEFAULTPERMISSION__;
    }


    function isLoggedOn()
    {
        if ($this->LoggedOn) return TRUE; else return FALSE;
    }    

    
    function isPermitted($reqlevel,$key="")
    {
        if ($reqlevel && $key && $key == EXACT)
        {
            if ($this->authUserPermission == $reqlevel) $permission = 1; //TRUE;
            else $permission = 0; //FALSE;
        }
        else if ($reqlevel)
        {
            if ($this->authUserPermission <= $reqlevel) $permission = 1; //TRUE;
            else $permission = 0; //FALSE;
        }
        else $permission = 0; //FALSE;
        return $permission;
    }

    function Logout()
    {
    global $Style;
    foreach($_SESSION as $key => $value)
         session_unregister($key);
    session_destroy();
    ?>
    <html>
    <head>
    <title>Log on</title>
    <link rel="stylesheet" href="<?=$Style?>">
    </head>
    <body>
       <h3>You have been logged out</h3>
    </body>
    </html>
    <?
    exit;
    }


    
    function _login()
    {
        global $PHP_SELF, $Style, $_SESSION;
/*        
        session_unregister("authuseridr");
        session_unregister("authusernamer");
        session_unregister("authuserissetr");
        session_unregister("authuserpermr");
*/

        unset($_SESSION['authuseridr']);
        unset($_SESSION['authusernamer']);
        unset($_SESSION['authuserissetr']);
        unset($_SESSION['authuserpermr']);

        session_destroy();
    ?>
    <html>
    <head>
    <title>Log on</title>
    <link rel="stylesheet" href="<?=$Style?>">
    </head>
    <body>
    <FORM METHOD="POST" ACTION="<? echo $PHP_SELF ?>">
          <DIV ALIGN="CENTER"><CENTER>
             <H3>Logon:</H3>
             <TABLE BORDER="1" WIDTH="200" CELLPADDING="2">
               <TR>
                 <TH WIDTH="18%" ALIGN="RIGHT" NOWRAP>ID</TH>
                 <TD WIDTH="82%" NOWRAP>
                    <INPUT TYPE="TEXT" NAME="authuserid" SIZE="8">
                 </TD>
               </TR>
               <TR>
                 <TH WIDTH="18%" ALIGN="RIGHT" NOWRAP>Password</TH>
                 <TD WIDTH="82%" NOWRAP>
                 <INPUT TYPE="PASSWORD" NAME="authuserpass" SIZE="8">
                 </TD>
              </TR>
              <TR>
                 <TD WIDTH="100%" COLSPAN="2" ALIGN="CENTER" NOWRAP>
                 <INPUT TYPE="SUBMIT" VALUE="LOGIN" NAME="Submit">
                 </TD>
              </TR>
              </TABLE>
          </DIV>
    </FORM>
    </body>
    </html>
    <?
    exit;
    }
    
    function _authenticate()
    {
          if ($this->LoggedOn)
              $query = "SELECT username,permission FROM $this->Table WHERE userid = '$this->authUserid' AND permission <= $this->PageLevel";
          else
              $query = "SELECT username,permission,password FROM $this->Table WHERE userid = '$this->authUserid' and password = password('$this->authUserPass') AND permission <= $this->PageLevel";
          $result = mysql_query($query) or die("Query failed");
          if(mysql_num_rows($result) == 0) return 0;
          else
          {
              $query_data = mysql_fetch_row($result);
              return $query_data;
          }
    }
    
    function _errorMsg()
    {
        global $PHP_SELF, $PermissionList;
        $invalid = "<div align=\"center\">Authorization failed.<br>
                    You must enter a valid userid and password combo.<br>
                    Try to logon again</div>";
        $lowpermission = "<div align=\"center\">Authorization failed.<br>
                    You are not authorized to use this program.<br>
                    The mininum required userlevel is ". $PermissionList[$this->PageLevel]."<br>
                    Try to logon again</div>";
                    
        if (!$this->isPermitted($this->PageLevel))
             $error = $lowpermission;
        else $error = $invalid;
        echo $error;
    }

    function _initSession()
    {
/*
        $GLOBALS['authuseridr'] = $this->authUserid;
        $GLOBALS['authusernamer'] = $this->authUsername;
        $GLOBALS['authissetr'] = $this->LoggedOn;
        $GLOBALS['authuserpermr'] = $this->authUserPermission;
*/

        $_SESSION['authuseridr'] = $this->authUserid;
        $_SESSION['authusernamer'] = $this->authUsername;
        $_SESSION['authissetr'] = $this->LoggedOn;
        $_SESSION['authuserpermr'] = $this->authUserPermission;
        
        session_register("authuseridr", "authusernamer","authissetr","authuserpermr");
    }
    
    function _endSession()
    {
/*
        session_unregister("authuseridr");
        session_unregister("authusernamer");
        session_unregister("authuserissetr");
        session_unregister("authuserpermr");
 */  

        unset($_SESSION['authuseridr']);
        unset($_SESSION['authusernamer']);
        unset($_SESSION['authissetr']);
        unset($_SESSION['authuserpermr']);
        
        session_destroy();
    }
}
