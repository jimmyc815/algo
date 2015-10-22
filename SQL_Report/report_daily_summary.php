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

$today = date("Y-m-d");


//Setup Email Subject
$subject = $today.': Order Placed To Be Executed Next Day';

//Color is the color report table, it can be set to be grey, green or blue
$color = 'blue';
$color2 = 'green';

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
	
	$performance_report = generateReport($performance_query, $performance_header, $footer, $color);

	$sell_header = '<b>Submitted Sell Orders</b>';	
	$sell_query = 'select a.symbol AS "Symbol", a.shares AS "Shares", b.close AS "Today Close", b.daily_change AS "Today Change", b.pct_change AS "Today Pct Change", c.cost_basis AS "Cost Basis", round(b.close-c.cost_basis, 2) AS "Profit" from order_queue a, quotes b, liveportfolio c where a.portfolio_id = '.$pid.' and a.portfolio_id = c.portfolio_id and a.order_type like "%SELL%" and a.symbol=b.symbol and b.symbol = c.symbol and b.trade_date = "'.$today.'"';
	$sell_report = generateReport($sell_query, $sell_header, $footer, $color);

	$buy_header = '<b>Submitted Buy Orders</b>';	
	$buy_query = 'select a.symbol AS "Symbol", a.shares AS "Shares", b.close AS "Today Close", b.daily_change AS "Today Change", b.pct_change AS "Today Pct Change" from order_queue a, quotes b where a.portfolio_id = '.$pid.' and a.order_type like "%BUY%" and a.symbol=b.symbol and b.trade_date = "'.$today.'"';
	$buy_report = generateReport($buy_query, $buy_header, $footer, $color);

	$holding_header = '<b>Current Holdings</b>';	
	$holding_query = 'select a.symbol AS "Symbol", a.shares AS "Shares", a.cost_basis AS "Cost Basis", b.close AS "Current Price", round((b.close-a.cost_basis)*100/b.close, 2) AS "Profit %", b.daily_change as "Daily Change", b.pct_change as "Daily % Change" from liveportfolio a, quotes b where portfolio_id = '.$pid.' and a.symbol = b.symbol and b.trade_date = "'.$today.'" order by 5 asc';
		
	$holding_report = generateReport($holding_query, $holding_header, $footer, $color);

	$history_header = '<p><b>Historical Portfolio Returns</b></p>';
	
	$history_performance_query = 'select a.portfolio_id as "PID", a.trade_date as "Date", b.description AS "Description", b.risk_category as "Risk Category", a.return_dollar as "Dollar Returns", a.return_pct as "Pct Returns", round(a.portfolio_value, 2) as "Portfolio Value" from liveportfolio_hist_performance a, liveportfolio_account b where a.portfolio_id = '.$pid.' and b.status = "A" and a.portfolio_id = b.portfolio_id order by PID, DATE desc';

	#$hist_report = generateReport($history_performance_query, $history_header, $footer, $color);

	$finalReport .= $summary_report.$poerformance_report.$sell_report.$buy_report.$holding_report.$hist_report;

}

	$html_live_monitor = '<p></p><p><b><a href="http://www.lucasmia.com/StockValuation/algo/live_monitor.html">Click Here</a> to see the historical performance for all live portfolios </b></p>';
	
	$finalReport .= $html_live_monitor;


//Setup Email Recipient (Who are we sending the report to)
$recipient1 = 'jimmyc815stern@gmail.com';
//$recipient1 = 'jimmyc815@gmail.com';
$recipient2 = 'jian.tang@gs.com';
//$recipient2 = 'person2@somedomain.com';
//$recipient3 = 'person3@somedomain.com';

//Send the report (if you uncomment out recipients above then ensure you uncomment the corresponding html_email below
html_email($recipient1,$subject,$finalReport);
html_email($recipient2,$subject,$finalReport); 
//html_email($recipient3,$subject,$report);

?>
