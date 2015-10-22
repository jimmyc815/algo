<?php // dbfunctions.php
//tutorial db
/*
$dbuser="dbo395394486";
$dbpass="goldman1234";
$dbname="db395394486";
$dbhost="db395394486.db.1and1.com";

*/

//stock db
$dbname="db380207220";
$dbhost="db380207220.db.1and1.com";
$dbuser="dbo380207220";
$dbpass="goldman1234";


$dbname="trade_db";
$dbname="db380207220";
$dbhost="localhost";
$dbuser="root";
$dbpass="stock168";


//mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_connect("localhost", "root", "") or die(mysql_error());

mysql_select_db($dbname) or die (mysql_error());

function createTable($name, $query)
{
	if (tableExists($name))
	{
		echo "Table '$name' already exists<br />";
	}
	else
	{
		queryMysql("CREATE TABLE $name($query)");
		echo "Table '$name' created<br />";
	}
}

function tableExists($name)
{
	$result = queryMysql("SHOW TABLES LIKE '$name'");
	return mysql_num_rows($result);
}

function queryMysql($query)
{	
	global $dbname; 
	mysql_select_db($dbname) or die (mysql_error());

	try {
		$result = mysql_query($query) or die(mysql_error());	
	} catch (Exception $e) {
		echo "Query: $query \n";
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	return $result;
}

function destroySession()
{
	$_SESSION=array();
	
	if (session_id() != "" || isset($_COOKIE[session_name()]))
		setcookie(session_name(), '', time()-2592000, '/');
		
	session_destroy();
}

function sanitizeString($var)
{
	$var = strip_tags($var);
	$var = htmlentities($var);
	$var = stripslashes($var);
	return mysql_real_escape_string($var);
}

function showProfile($user)
{
	if (file_exists("$user.jpg"))
		echo "<img src='$user.jpg' border='1' align='left'/>";
	
	$result = queryMysql ("SELECT * FROM rnprofiles WHERE user='$user'");
	
	if (mysql_num_rows($result))
	{
		$row = mysql_fetch_row($result);
		echo stripslashes($row[1]) . "<br clear=left /><br />";
	}
}
?>
