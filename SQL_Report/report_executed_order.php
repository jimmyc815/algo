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
include_once ('../scripts/dbfunction.php');
include_once ('../scripts/connors_strat_live.php');

$all_active_pid = get_all_active_portfolio_id();
var_dump($all_active_pid);


$today = date("Y-m-d");


//Setup Email Subject
$subject = $today.': Executed Orders';

//Color is the color report table, it can be set to be grey, green or blue
$color = 'blue';


foreach ($all_active_pid as $pid) {
	
	$current_value = live_get_portfolio_value($today, $pid);
	
	
	//Add a message to the header of the report
	$summary_header = '<p><b>Summary for Portfolio ID: '.$pid.'</b></p><b>Account Setting</b>';
	
	//Add a message to the footer of the report, you can use plain text or html.
	//$footer = '<p><strong>Note: </strong>This is an optional report footer, ideally used for a report legend and/or signature.</p>';
	$footer = ''; 
	
	$summary_query = 'select description AS "Description", risk_category AS "Risk Category", start_date AS "Portfolio Start Date", starting_capital AS "Starting Capital" from liveportfolio_account where portfolio_id = '.$pid;

	
	$summary_report = generateReport($summary_query, $summary_header, $footer, $color);

	$performance_header = '<b>Current Performance</b>';	
	$performance_query = 'select liveportfolio_value('.$pid.') AS "Current Value", (liveportfolio_value('.$pid.') - starting_capital) AS "Current Dollar Return", round((liveportfolio_value('.$pid.') - starting_capital)*100/starting_capital, 2) AS "Current Pct Return" from liveportfolio_account where portfolio_id = '.$pid;
	
	$poerformance_report = generateReport($performance_query, $performance_header, $footer, $color);
	
	$sell_header = '<b>Executed Sell Orders</b>';	
	$sell_query = 'select a.execute_time as "Executed Time", order_type AS "Order Type", symbol AS "Symbol", shares AS "Shares", a.execute_price as "Executed Price" , liveportfolio_get_purchase_price('.$pid.', symbol) AS "Purchase Price", round((a.execute_price-liveportfolio_get_purchase_price('.$pid.', symbol))*100/liveportfolio_get_purchase_price('.$pid.', symbol), 2) AS "PnL %"  from order_history_queue a, liveportfolio_account b where a.portfolio_id = b.portfolio_id and b.status = "A" and a.portfolio_id = '.$pid.' and a.order_type like "%SELL%" and a.execute_date = "'.$today.'" order by a.execute_time';
	
	$sell_report = generateReport($sell_query, $sell_header, $footer, $color);

	$buy_header = '<b>Executed Buy Orders</b>';	
	$buy_query = 'select a.execute_time as "Executed Time", order_type AS "Order Type", symbol AS "Symbol", shares AS "Shares", a.execute_price as "Executed Price" from order_history_queue a, liveportfolio_account b where a.portfolio_id = b.portfolio_id and b.status = "A" and a.portfolio_id = '.$pid.' and a.order_type like "%BUY%" and a.execute_date = "'.$today.'" order by a.execute_time';
	
	$buy_report = generateReport($buy_query, $buy_header, $footer, $color);

	$holding_header = '<b>Current Holdings</b>';	
	$holding_query = 'select a.symbol AS "Symbol", a.shares AS "Shares", a.cost_basis AS "Cost Basis", b.close AS "Current Price", round((b.close-a.cost_basis)*100/b.close, 2) AS "Profit %", b.daily_change as "Daily Change", b.pct_change as "Daily % Change" from liveportfolio a, quotes b where portfolio_id = '.$pid.' and a.symbol = b.symbol and b.trade_date = "'.$today.'" order by 5 asc';
		
	$holding_report = generateReport($holding_query, $holding_header, $footer, $color);

	
	//The Database Query to Run - Recommend Testing in SQL / PHPMYADMIN / Other tool beforehand to ensure syntax is correct
	//$query = 'SELECT firstName AS "First Name", lastName AS "Last Name" FROM userinfo ORDER BY lastName DESC';
	//$query = 'select portfolio_id AS "PID", order_type AS "Order Type", order_date AS "Order Date", symbol AS "Symbol", shares AS "Shares" from order_queue order by 1, 2';
	//$query = 'select a.portfolio_id AS "PID", b.description AS "Description", b.risk_category as "Risk Category",  order_type AS "Order Type", order_date AS "Order Date", symbol AS "Symbol", shares AS "Shares", a.execute_time as "Executed Time", a.execute_price as "Executed Price" from order_history_queue a, liveportfolio_account b where a.portfolio_id = b.portfolio_id and b.status = "A" and a.execute_date = "'.$today.'" order by PID, a.execute_time';
	
	
	//Generate the report (Do not change)
	$report = generateReport($query, $header, $footer, $color);

	$final_report .= $summary_report.$poerformance_report.$sell_report.$buy_report.$holding_report;
}


//Setup Email Recipient (Who are we sending the report to)
$recipient1 = 'jimmyc815stern@gmail.com';
$recipient2 = 'jian.tang@gs.com';
//$recipient3 = 'person3@somedomain.com';

//$recipient1 = 'jimmyc815@gmail.com';

//Send the report (if you uncomment out recipients above then ensure you uncomment the corresponding html_email below
html_email($recipient1,$subject,$final_report);
html_email($recipient2,$subject,$final_report); 
//html_email($recipient3,$subject,$report);

?>
