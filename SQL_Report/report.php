<?php
/***
	PHP MYSQL EMAIL REPORTER
	Script Written by Julian Young
	SQLREPORTER is Copyrighted to Julian Young 2012 - Not to be resold.
	PHPMailer is Distributed under the Lesser General Public License (LGPL) and is untouched.
	
	Website: http://www.julian-young.com
	
	Version: 1.6 - October 2012
	
	If you have any queries please do not hesitate to email me at info@julian-young.com
***/

include ('config.php');
include ('sqlreporter.php');

//Setup Email Subject
$subject = 'Current Order Queue';

//Color is the color report table, it can be set to be grey, green or blue
$color = 'blue';

//Add a message to the header of the report
$header = '<p><b>Introduction</b>. This is an optional report introduction</p>';

//Add a message to the footer of the report, you can use plain text or html.
$footer = '<p><strong>Note: </strong>This is an optional report footer, ideally used for a report legend and/or signature.</p>';

//The Database Query to Run - Recommend Testing in SQL / PHPMYADMIN / Other tool beforehand to ensure syntax is correct
$query = 'SELECT firstName AS "First Name", lastName AS "Last Name" FROM userinfo ORDER BY lastName DESC';
$query = 'select portfolio_id AS "PID", order_type AS "Order Type", order_date AS "Order Date", symbol AS "Symbol", shares AS "Shares" from order_queue order by 1, 2';
$query = 'select a.portfolio_id AS "PID", b.description AS "Description", b.risk_category as "Risk Category",  order_type AS "Order Type", order_date AS "Order Date", symbol AS "Symbol", shares AS "Shares" from order_queue a, liveportfolio_account b where a.portfolio_id = b.portfolio_id order by PID, order_date';

//Generate the report (Do not change)
$report = generateReport($query, $header, $footer, $color);

//Setup Email Recipient (Who are we sending the report to)
$recipient1 = 'jimmyc815@gmail.com';
//$recipient2 = 'person2@somedomain.com';
//$recipient3 = 'person3@somedomain.com';

//Send the report (if you uncomment out recipients above then ensure you uncomment the corresponding html_email below
html_email($recipient1,$subject,$report);
//html_email($recipient2,$subject,$report); 
//html_email($recipient3,$subject,$report);

?>
