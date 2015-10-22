<?php // order_execution_engine.php

include_once 'dbfunction.php';

	
$dbname="trade_db";
$dbname="db380207220";
$dbhost="localhost";
$dbuser="root";
$dbpass="stock168";


//mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
mysql_connect("localhost", "root", "") or die(mysql_error());
mysql_select_db($dbname) or die (mysql_error());
	
	
$order_queue = "order_queue";
$order_history_queue = "order_history_queue";
$portfolio = "liveportfolio";
$realtime_quote = "realtime_quote";

$tranTable = "tran12004";

$query = "select symbol, trade_type, trade_date, shares, price from $tranTable order by trade_date ";
echo $query, PHP_EOL;


$result = queryMysql($query);	
$count = 0;
$alltrades = array();

while ($data = mysql_fetch_row($result)) {
	$alltrades[$count]['symbol'] = $data[0];
	$alltrades[$count]['trade_type'] = $data[1];
	$alltrades[$count]['trade_date'] = $data[2];
	$alltrades[$count]['shares'] = $data[3];
	$alltrades[$count]['price'] = $data[4];

	$count++;
}		

$startingCapital = 1212327;

for ($x=0; $x<count($alltrades); $x++) {
	if ($alltrades[$x]['trade_type'] == 'SELL') {
		$startingCapital += $alltrades[$x]['shares'] * $alltrades[$x]['price'];

		echo "SELL: ", "trade date: ", $alltrades[$x]['trade_date'], " symbol: ", $alltrades[$x]['symbol'], " shares: ", $alltrades[$x]['shares'], " price: ", $alltrades[$x]['price'], " portfolio value: ", $startingCapital,  PHP_EOL;
		
	} elseif ($alltrades[$x]['trade_type'] == 'BUY') {

		$startingCapital -= $alltrades[$x]['shares'] * $alltrades[$x]['price'];
				
		echo "BUY: ", "trade date: ", $alltrades[$x]['trade_date'], " symbol: ", $alltrades[$x]['symbol'], " shares: ", $alltrades[$x]['shares'], " price: ", $alltrades[$x]['price'], " portfolio value: ", $startingCapital,  PHP_EOL;		
	}
	
}








?>

