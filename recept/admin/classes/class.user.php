<?php
// <$Id: class.user.php,v 1.6 2002/12/12 13:23:41 rob Exp $>

// define some constants
define(__DEFAULTUSERID__, "");
define(__DEFAULTUSERNAME__, "");
define(__DEFAULTPASS__, "");
define(__DEFAULTPERMISSION__, 100);
// define actions
define(__NOACTION__,0);
define(__NEW__, 1);
define(__ADD__, 2);
define(__EDIT__,3);
define(__UPDATE__,4);
define(__DELETE__,5);
// define some permissions;
define(ADMIN, 1);
define(VIEW, 99);

class AuthClassedEdit
{
    var $userid;
    var $username;
    var $password;
    var $permission;
    var $tblname;

    function AuthClassedEdit($auth)
    {
       $this->tblname = "users";

       $this->userid = $this->setUserid($auth);
       $this->username = $this->setUsername($auth);
       $this->password = $this->setUserPass($auth);
       $this->permission = $this->setUserPerm($auth);

       if ($this->userid != __DEFAULTUSERID__ ||  isset($_GET['action']))
       { 
           $actionarr = $this->getAction();
           if (is_array($actionarr))
           {
              switch($actionarr[0])
              {
                  case __NEW__    : $this->News($auth); break;
                  case __ADD__    : $this->Add($auth); $this->Display($auth); break;
                  case __EDIT__   : $this->Edit($auth); break;
                  case __UPDATE__ : $this->Update($auth); $this->Display($auth); break;
                  case __DELETE__ : $this->Delete($auth); $this->Display($auth); break;
                  default         : $this->Display($auth); break;
               }
           }
           else $this->Display($auth);
        }
        else $this->Display($auth);
    }

    function setUserid($auth)
    {
         if (isset($_POST['authuserid'])) $userid = $_POST['authuserid'];
         else if ($_GET['action'] == "edit" && isset($_GET['edit'])) $userid = $_GET['edit']; 
         else if ($_GET['action'] == "update" && isset($_GET['update'])) $userid = $_GET['update']; 
         else if ($_GET['action'] == "delete" && isset($_GET['delete'])) $userid = $_GET['delete']; 
         else $userid = __DEFAULTUSERID__;
         if (ereg("^[A-Za-z0-9_+-.,]*$", $userid)) return $userid;
         else return __DEFAULTUSERID__;
    }

    function setUsername($auth)
    {
         $username = isset($_POST['authusername']) ? $_POST['authusername'] : __DEFAULTUSERNAME__;
         if (ereg("^[A-Za-z0-9_+-.,\ ]*$", $username)) return $username;         
         else return __DEFAULTUSERNAME__;
    }

    function setUserPass($auth)
    {
         $password = isset($_POST['authpassword']) ? $_POST['authpassword'] : __DEFAULTPASS__;
         return $password;
    }

    function setUserPerm($auth)
    {
         $userperm = isset($_POST['authpermission']) ? intval($_POST['authpermission']) : __DEFAULTPERMISSION__;
         if ($userperm <= 0 || $userperm > __DEFAULTPERMISSION__) $userperm = __DEFAULTPERMISSION__;
         if (!$auth->isPermitted(ADMIN)) $userperm = $auth->getUserPerm();
         return $userperm; 
    }

    function getAction()
    {
        switch($_GET['action'])
        { 
             case "new"    : $action = __NEW__;    $item = __NOACTION__; break;
             case "add"    : $action = __ADD__;    $item = __NOACTION__; break;
             case "edit"   : $action = __EDIT__;   $item = $_GET['edit']; break;
             case "update" : $action = __UPDATE__; $item = $_GET['update']; break;
             case "delete" : $action = __DELETE__; $item = $_GET['delete']; break;
             default       : $item = __NOACTION__; break;
        }
        $retarr = array($action, $item);
        if ($action) return $retarr; else return FALSE;
    }
    
    function News($auth)
    {
        global $PHP_SELF;
        echo "<form method=\"POST\" action=\"$PHP_SELF?action=add\">"; 
        $this->Enter($auth,"new");
    }

    function Add($auth)
    {    
        if ($this->userid != __DEFAULTUSERID__)
        {
            $query = "INSERT into $this->tblname VALUES('$this->userid',
                                                            '$this->username',
                                                            password('$this->password'),
                                                            $this->permission
                                                            );";
             if ($auth->isPermitted(ADMIN))  mysql_query($query);
        }
     }

     function Delete($auth)
     {
         $query = "DELETE from $this->tblname WHERE userid = '$this->userid';";
         if ($auth->isPermitted(ADMIN))  mysql_query($query);
     }

     function Edit($auth)
     {
         global $PHP_SELF;
         $query = "SELECT * from $this->tblname WHERE userid = '$this->userid';";
         $resultset = mysql_query($query);
         $r = mysql_fetch_object($resultset);
            $this->userid = $r->userid;
            $this->username = $r->username;
            $this->password = $r->password;
            $this->permission = $r->permission;
            echo "<form method=\"post\" action=\"$PHP_SELF?action=update&update=$this->userid\">";
            $this->Enter($auth,"edit");
     }

     function Update($auth)
     {
         if (!$this->password)
             $query = "UPDATE $this->tblname SET
                                            username = '$this->username',
                                            permission = $this->permission
                             WHERE userid = '$this->userid';";

         else
         $query = "UPDATE $this->tblname SET
                                    username = '$this->username',
                                    password = password(\"$this->password\"),
                                    permission = $this->permission
                          WHERE userid = '$this->userid';";
         if ($auth->isPermitted(ADMIN)||$auth->getUserid() == $this->userid) $r = mysql_query($query) or die("unable to process query : $query");
     }

     function Enter($auth,$func)
     {
           global $PermissionList;
           ?>
           <table>
                  <? if ($func == "new")
                  {
                      ?>
                  <tr>
                      <td>Userid</td>
                      <td><input name="authuserid" type="text" value="<? echo $this->userid ?>" size="80"></td>
                          <input name="authfunction" type="hidden" value="new">
                  </tr>
                  <?
                  }
                  else if ($func == "edit")
                  {
                  ?>
                  <tr>
                      <td>Userid</td>
                      <td><input name="authuserid" type="hidden" value="<? echo $this->userid ?>"><? echo $this->userid ?></td>
                          <input name="authfunction" type="hidden" value="edit">
                  </tr>
                  <?
                  } 
                  ?>
                  <tr>
                      <td>Username</td>
                      <td><input name="authusername" type="text" value="<? echo $this->username ?>" size="80"></td>
                 </tr>
                  <tr>
                      <td>Password</td>
                      <td><input name="authpassword" type="password" size="80"></td>
                  </tr>
                  <tr>
                      <td>Permission</td>
                      <?php
                      if ($auth->isPermitted(ADMIN))
                      {
                      ?>                         
                        <td>
                            <select name="authpermission">
                            <? foreach ($PermissionList as $val => $label)
                             {
                                 echo "<option value=\"$val\" ";
                                 if ($this->permission == $val) echo "selected";
                                 echo ">$label";
                             }
                          ?>
                            </select></td>
                      <?php
                      } else { 
                      ?>
                        <td><? echo $PermissionList[$auth->getUserPerm()]; ?><input type="hidden" value="<? echo $auth->getUserPerm(); ?>" name="authpermission"></td>
                      <? } ?>
                  </tr>

           </table>
           <input type=submit value="Enter data">
           <input type=reset value="Reset to old values">
           </form>
    <?
    }

    function Display($auth)
    {
        global $PHP_SELF,$PermissionList;
        if (!$auth->isPermitted(ADMIN)) $where = " WHERE userid = '" . $auth->getUserid(). "'"; 
        else $where = "";
        $query = "SELECT * from $this->tblname $where ORDER by permission asc";
        $resultset = mysql_query($query);
        echo "<p><table width=\"100%\">";
        echo "<TR>
              <TD width=\"15%\"><b>Userid</b></TD>
              <TD width=\"15%\"><b>Username</b></TD>
              <td width=\"10%\"><b>Permission Level</b></td>";
              if ($auth->isPermitted(VIEW))
              echo "<TD width=\"5%\"><b>Edit</b></TD>
                    <TD width=\"5%\"><b>Delete</b></TD></TR>";
        while ($result = mysql_fetch_object($resultset))
        {
            echo "<tr>";
            echo "<td>" . $result->userid . "</td>";
            echo "<td>" . $result->username . "</td>";
            echo "<td>" . $PermissionList[$result->permission] . "</td>";
            if ($auth->isPermitted(ADMIN) || $auth->getUserid() == $result->userid)
            {
                echo "<td><a href=\"$PHP_SELF?action=edit&edit=" . $result->userid . "\">Edit</a></td>";
            }
            if ($auth->isPermitted(ADMIN))
            {
                if ($result->permission != ADMIN) echo "<td><a href=\"$PHP_SELF?action=delete&delete=" . $result->userid . "\">Delete</a></td>";
                else echo "<td></td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}


Class UserPage extends Webpage
{
    function Menu($auth)
    {
       global $PHP_SELF;
       echo "<div align=\"right\" class=\"menuhead\">User Database</div>";
       echo "<div align=\"right\" class=\"menuhead\">User: ". $auth->getUsername() ."</div>";
       if ($auth->isPermitted(ADMIN))   echo "[<a href=$PHP_SELF?action=new>New</a>]";
       echo "[<a href=$PHP_SELF>View</a>]<p>";
    }
}

?>
