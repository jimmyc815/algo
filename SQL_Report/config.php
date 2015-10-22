<?php

/* ===== OPTIONAL FIELDS, RECOMMEND LEAVING ERRORS ON ===== */
ini_set('display_errors','1');
error_reporting(E_ALL); 
define('MY_CHARSET', "ISO-8859-1"); //You do not need to change this, some may prefer to use UTF-8 however
/* ======================================================== */

/* ===== CHANGE THESE TO SUIT ===== */
define('DB_SERVER', "localhost"); //database server, normally "localhost"
define('DB_USER', "root"); ////database login name, enter the username
define('DB_PASS', ""); //database login password, enter the password
define('DB_DATABASE', "db380207220"); //enter the name of the database you want to connect to
define('FROM_EMAIL', "jimmyc815stern@gmail.com"); //Define your standard from address for emails, can be no-reply@yourdomain.com for example
define('FROM_NAME', "Engine Report"); //Define the "from name" in the email

define('TEST_MODE', false); //Test Mode, set to false when you want the reports to send emails. Set to true when testing.
/* =============================== */

?>
