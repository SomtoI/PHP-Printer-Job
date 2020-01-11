<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

define('DOMAIN_FQDN', 'providusbank');
define('LDAP_SERVER', '10.10.1.17');

if (isset($_POST['submit']))
{	

	$username = $_POST['username'];
    $password = $_POST['password'];
	
	//$baseDN = "dc=providus,dc=com";
    //$ldapRDN = 'uid=$username,OU = People, DC=providusbank,DC=com';
	  // Validate
    if (empty($_POST['username']) || empty($_POST['password'])) {
        if (empty($_POST['username'])) { $err = "Username field was empty."; }
        if (empty($_POST['password'])) { $err = "Password field was empty."; }
        return false;
    }
	
    $user = strip_tags($_POST['username']).'@'.DOMAIN_FQDN;
    //$pass = stripslashes($_POST['password']);

    $conn = ldap_connect("ldap://". LDAP_SERVER);
	$dn = DOMAIN_FQDN."\\";
    if (!$conn)
        $err = 'Could not connect to LDAP server';

    else
    {
        //define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);

        //ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        //ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($conn, $dn.$username, $pass);
		
		if ($bind == false)
			$err = 'Did NOT Bind';
        //This is where it fails. Can't figure it out

        elseif ($bind)
        {
			
			
			session_start();

            $_SESSION['login_user'] = $user;
			header('location: demo.php');
			exit();
			@ldap_close($conn);
        }
    }

    // session OK, redirect to home page

    if (!isset($err)) $err = 'Unable to login: '. ldap_error($conn);

    ldap_close($conn);
}
?>
<!DOCTYPE html><head><title>Login</title>
<link href='//fonts.googleapis.com/css?family=Bungee+Hairline|Bungee+Inline|Lobster' rel='stylesheet'>
</head>
<style>

.errmsg { color: red; }
#loginbox { font-family: Lobster;  font-size: 18px;}
h2{font-family: Bungee Inline;font-size: 24px;}
.button {
    background-color: #ECB62F;
    border: none;
	border-radius: 4px;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
}

.button:hover {
		background-color: #34424B;
	}
</style>
<body>
<div align="center"><img id="imghdr" src="/providus/logo.png" height="170" alt="Providus Bank Logo" /><br><br><h2 >Login</h2><br><br>

<div style="margin:10px 0;"></div>
<div title="Login" style="width:400px" id="loginbox">
    <div style="padding:10px 0 10px 60px">
    <form action="" id="login" method="post">
        <table><?php if (isset($err)) echo '<tr><td colspan="2" class="errmsg">'. $err .'</td></tr>'; ?>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" style="border: 1px solid #ccc;" autocomplete="off"/></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" style="border: 1px solid #ccc;" autocomplete="off"/></td>
            </tr>
        </table>
        <input class="button" type="submit" name="submit" value="Login" />
    </form>
    </div>
</div>
</div>
</body></html>
