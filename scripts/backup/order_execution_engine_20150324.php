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


if($_GET){
	if($_GET['action'] == 'execute'){
			$pid = 1; 
			execute_order($pid);
	} elseif ($_GET['action'] == 'add'){
			$pid = $_GET['pid'];

			$pid = 1; 
			$order_type = $_GET['order_type'];
			$symbol = $_GET['symbol'];
			$shares = $_GET['shares'];

			
			add_order($pid, $order_type, $symbol, $shares);
	} elseif ($_GET['action'] == 'delete'){
			$pid = 1; 
			delete_order($pid, 'BUY', 'AAPL');
	} elseif ($_GET['action'] == 'update'){
			$pid = 1; 
			update_order($pid, 'BUY', 'AAPL', 500);
	} elseif ($_GET['action'] == 'get_order'){
			$pid = 1; 
			get_open_order($pid);
	} elseif ($_GET['action'] == 'get_quote'){
			$pid = 1; 
			get_yahoo_quote("AAPL+IBM+GS+BAC+FB+WYNN+SZYM+DANG+TSLA+JCP+BIDU+CMG+V+LVS");
	} elseif ($_GET['action'] == 'refresh_realtime_quotes'){
			$pid = 1; 
			refresh_realtime_quotes($a);
	} 
}

function add_order ($pid, $order_type, $symbol, $shares) {
	global $order_queue ;
	
	$query = "insert into $order_queue (portfolio_id, order_date, order_type, symbol, shares) values ($pid, now(), '$order_type', '$symbol', $shares) 
			  on duplicate key update shares = shares + $shares, order_date = now() ";
			  
	try {
		$result = queryMysql($query);
	} catch (Exception $e) {
	   echo "Problem Query: $query \n";
 	   echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	

}

function delete_order ($pid, $order_type, $symbol){
	global $order_queue ;

	$query = "delete from $order_queue where portfolio_id = $pid and order_type = '$order_type' and symbol = '$symbol' ";

	try {
		$result = queryMysql($query);
	} catch (Exception $e) {
	   echo "Problem Query: $query \n";
 	   echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	
}


function update_order ($pid, $order_type, $symbol, $shares){
	global $order_queue ;

	$query = "update $order_queue set shares = $shares, order_date = now() where portfolio_id = $pid and order_type = '$order_type'  ";

	try {
		$result = queryMysql($query);
	} catch (Exception $e) {
	   echo "Problem Query: $query \n";
 	   echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	
}

function get_open_order ($pid)
{
	global $order_queue ;

	$query = "select order_type, order_date, symbol, shares from $order_queue where portfolio_id = $pid and execute_date is null order by order_date ";

	try {
		$result = queryMysql($query);
		$i = mysql_num_rows($result);		
		$queue = array();
		
		while ($row = mysql_fetch_assoc($result)) {
			array_push($queue, $row);
		}	
	} catch (Exception $e) {
	   echo "Problem Query: $query \n";
 	   echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	

    $i = mysql_num_rows($result);

	//Return result to jTable
	$jTableResult = array();
	$jTableResult['Result'] = "OK";
	$jTableResult['Records'] = $queue;
	$jTableResult['TotalRecordCount'] = $i;
	return json_encode($jTableResult);
}



function execute_order ($pid) {
	global $order_queue ;
	global $order_history_queue ;
	global $portfolio;


	$json_open_orders = get_open_order($pid);	
	$array = json_decode($json_open_orders, true);
	
	foreach ($array as $key => $jsons) { // This will search in the 2 jsons
		if ($key == 'Records') { // only print when json = RECORDS
		    foreach($jsons as $key2 => $value) {
		         echo "value: ", $value['symbol'], " date: ", $value['order_date'], " type: ", $value['order_type'], " shares: ", $value['shares'] ; 		    
		         
		         $rt_quotes = json_decode(get_yahoo_quote($value['symbol']), true);
		         
		         echo "symbol: ", $rt_quotes[0]['symbol'], " price:", $rt_quotes[0]['last price'], "<br />";
		         
		         if ($value['order_type'] == "BUY") {
			         
		         } elseif ($value['order_type'] == "BUY_OPEN") {
			         $execute_price = $rt_quotes[0]['open'];
			         $execute_date = $rt_quotes[0]['last trade date'];
			         $execute_time = $execute_date." ".$rt_quotes[0]['last trade time'];
			         $shares_executed = $value['shares'];
			         $cash_change = -1 * $value['shares'] * $execute_price;		
					 
		         } elseif ($value['order_type'] == "BUY_CLOSE") {
			         
		         } elseif ($value['order_type'] == "SELL") {
			         
		         } elseif ($value['order_type'] == "SELL_OPEN") {
			         $execute_price = $rt_quotes[0]['open'];
			         $execute_date = $rt_quotes[0]['last trade date'];
			         $execute_time = $execute_date." ".$rt_quotes[0]['last trade time'];
			         $shares_executed = -1*$value['shares'];
			         $cash_change = $value['shares'] * $execute_price;				         
			         
		         } elseif ($value['order_type'] == "SELL_CLOSE") {
			         
		         } else {
			         echo "nothing else \n";
		         }
		         
				// add to portfolio
				$query = "INSERT INTO $portfolio (portfolio_id, symbol, shares, cost_basis)  
						  VALUES (".$pid." ,
						  	     '".$value['symbol']."' , 
						  	      ".$value['shares']." ,
						  	      ".$execute_price.") 
						  ON DUPLICATE KEY UPDATE shares = shares + ".$shares_executed;
							
				//print "query: $query \n";		
				$result = queryMysql($query);
				
				//update cash position
				$query = "UPDATE $portfolio 
						  SET shares = shares + ".$cash_change."
						  WHERE portfolio_id = ".$pid."
						    AND symbol = 'CASH'";
				
				//print "query: $query \n";
									
				$result = queryMysql($query);
				 
		        $query = "INSERT INTO $order_history_queue 
		         					(portfolio_id, order_type, order_date, symbol, shares, execute_date, execute_time, execute_price)
						  VALUES (".$pid." ,
				 		   		  '".$value['order_type']."' ,
				 		   		  '".$value['order_date']."' ,
				 		   		  '".$value['symbol']."' ,
				 		   		   ".$value['shares']." ,
				 		   		  '".$execute_date."' ,
				 		   		  '".$execute_time."' ,
				 		   		   ".$execute_price.") ";
			 
				$result = queryMysql($query);

				//remove from order queue
				$query = "DELETE FROM $order_queue 
						  WHERE portfolio_id = ".$pid." 
						    AND symbol = '".$value['symbol']."' 
						    AND order_type = '".$value['order_type']."' ";
									
				$result = queryMysql($query);
				
				//remove symbol from portfolio if shares = 0
				$query = "DELETE FROM $portfolio
						  WHERE portfolio_id = ".$pid."
						    AND symbol != 'CASH' 
						    AND shares = 0 ";
				//print "query: $query \n";

				$result = queryMysql($query);
		         
			} 
		}
	}	
	
	
}

function get_yahoo_quote($symbol) {
	$result = array();
	$json1 = array();
	$quotes = array();

	$data = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=slk2c6c');
	$data = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=sl1opt1d1hgc1p2l');
	#$data = file_get_contents('http://download.finance.yahoo.com/d/quotes.csv?s='.$symbol.'&e=.csv&f=sl1opt1d1h0g0c1p2');

	$rows = explode("\n", $data);

	for($i = 0; $i < count($rows)-1; $i++)
	{
		$L = explode(',', $rows[$i]);
		

		$quotes["symbol"] = str_replace("/", "", (str_replace("\"", "", $L[0]))) ;//$L[0];
		$quotes["last price"] = $L[1];
		$quotes["open"] = $L[2];
		$quotes["previous close"] = $L[3];
		$quotes["last trade time"] = str_replace("/", "", (str_replace("\"", "", $L[4]))); //$L[4];
		//switch from mon-day-year to year-mon-year format
		$trade_date = (str_replace("\"", "", $L[5]));
		$date = explode("/", $trade_date);
		$new_date = $date[2]."-".$date[0]."-".$date[1];
		//$quotes["last trade date"] = str_replace("/", "-", (str_replace("\"", "", $L[5])));//$L[5];
		$quotes["last trade date"] = $new_date;
		$quotes["high"] = $L[6];
		$quotes["low"] = $L[7];
		$quotes["change"] = $L[8];
		$quotes["change in percent"] = str_replace("/", "", (str_replace("\"", "", $L[9])));//$L[9];
		$quotes['last real'] = $L[10];

		if (preg_match('#[\d]#',$quotes["last price"]) && preg_match('#[\d]#',$quotes["high"]) && preg_match('#[\d]#',$quotes["low"]) && preg_match('#[\d]#',$quotes["open"])){ 	
			array_push($result, $quotes);
		} else {
			echo "NOT VALID: ", $quotes["symbol"], " last trade date: ", $quotes["last trade date"], " last price: ", $quotes["last price"], " high: ", $quotes["high"], " low: ", $quotes["low"], " open: ", $quotes["open"], PHP_EOL;
		}
		$quotes = array();
	}


	print json_encode($result);

	return json_encode($result);
	
}

//refresh quotes in database
function refresh_realtime_quotes($s) {
		global $realtime_quote;
		$allstocks = array();
		
		// if no symbol supplied, update eod price for all stocks in stock_list
		if (!$s) {		
			$query = "SELECT symbol FROM stock_list order by symbol asc ";
			$query = "SELECT name as symbol from trade_db.instrument order by name asc ";
			$result = queryMysql($query);
			
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				//$return = $row['symbol']."+".$return;
				$str = preg_replace('/\s+/', '', $row['symbol']);


				array_push ($allstocks, $str);
			}
			
		} else {
			array_push($allstocks, $s);

		}
		
		$loop_count = 0;				
		$batch_stocks = array();
		$batch_list = "";
		
		for ($x=0; $x<count($allstocks); $x++) {
#		for ($x=0; $x<50; $x++) {

			if ($loop_count < 49) {
				array_push ($batch_stocks, $allstocks[$x]);
				$batch_list .= $allstocks[$x]."+";
				
				$loop_count ++;				
			} else {
				
		        $allquotes = json_decode(get_yahoo_quote($batch_list), true);
		         
/*		         
			         $execute_price = $rt_quotes[0]['open'];
			         $execute_date = $rt_quotes[0]['last trade date'];
			         $execute_time = $execute_date." ".$rt_quotes[0]['last trade time'];
			         $shares_executed = $value['shares'];
			         $cash_change = -1 * $value['shares'] * $execute_price;		
	*/		         
				for ($i=0; $i<count($allquotes); $i++) {
			         echo "symbol: ", $allquotes[$i]['symbol'], " price:", $allquotes[$i]['last price'], " last trade date: ", $allquotes[$i]['last trade date'], " time: ", $allquotes[$i]['last trade time'], " open:", $allquotes[$i]['last price'], " high: ", $allquotes[$i]['high'], " low: ", $allquotes[$i]['low'], " change: ", $allquotes[$i]['change'], " change in percent: ", $allquotes[$i]['change in percent'], " open: ", $allquotes[$i]['open'],   PHP_EOL;
			         

					
				}

				## batch insert into crsi_portfolio_performance afterward
				$sql = array(); 
				foreach( $allquotes as $row ) {
				    #$sql[] = "('".$allquotes[$i]['symbol']."', '".$allquotes[$i]['last trade date']."', ".$allquotes[$i]['change in percent'].", ".$allquotes[$i]['change'].", ".$allquotes[$i]['high'].", ".$allquotes[$i]['low'].", ". $allquotes[$i]['open'].")";
				    $change_in_percent = 1 * $row['change in percent'];
				    $daily_change = 1*$row['change'];
				    $sql[] = "('".$row['symbol']."', ".$row['last price'].", ".$change_in_percent.", ".$daily_change.", ".$row['high'].", ".$row['low'].", ".$row['open'].")";
				
					$query = "INSERT INTO ".$realtime_quote." (symbol, last_trade, change_percent, daily_change, high, low, open, last_trade_date, last_trade_time) VALUES (
							  '".$row['symbol']."',
							  ".$row['last price'].", 
							  ".$change_in_percent.",
							  ".$daily_change.",
							  ".$row['high'].",
							  ".$row['low'].",
							  ".$row['open'].",
							  '".$row['last trade date']."',
							  '".$row['last trade time']."' )
							  on duplicate key update 
							  last_trade = ".$row['last price'].",
							  change_percent = ".$change_in_percent.",
							  daily_change = ".$daily_change.",
							  high = ".$row['high'].",
							  low = ".$row['low'].",
							  open = ".$row['open'].",
							  last_trade_date = '".$row['last trade date']."', 
							  last_trade_time = '".$row['last trade time']."'
							  
							  ";
					//echo $query, PHP_EOL;
					queryMysql($query);
				}
				//var_dump($sql);

				//global $dbname;
				//mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());
				//mysql_connect("localhost", "root", "") or die(mysql_error());
				//mysql_select_db($dbname) or die (mysql_error());

				//queryMysql ("INSERT INTO realtime_quote (symbol) VALUES ('jimmy')");
				
				//queryMysql('INSERT INTO '.$realtime_quote.' (symbol, last_trade, change_percent, daily_change, high, low, open) VALUES '.implode(',', $sql));
				//print 'INSERT INTO realtime_quote (symbol, last_trade, change_percent, daily_change, high, low, open) VALUES '.implode(',', $sql);

			/*
				try {
					queryMysql('INSERT INTO realtime_quote (symbol, last_trade, change_percent, daily_change, high, low, open) VALUES '.implode(',', $sql));
					print 'INSERT INTO realtime_quote (symbol, last_trade, change_percent, daily_change, high, low, open) VALUES '.implode(',', $sql);
				} catch (SQLException $e) {
					echo "Query: $query \n";
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			*/
/*symbol         | varchar(6)  | NO   | PRI | NULL    |       |
| last_trade     | double      | NO   |     | NULL    |       |
| bid            | double      | NO   |     | NULL    |       |
| ask            | double      | NO   |     | NULL    |       |
| change_percent | double      | NO   |     | NULL    |       |
| daily_change   | double      | NO   |     | NULL    |       |
| market_cap     | varchar(10) | NO   |     | NULL    |       |
| high           | double      | YES  |     | NULL    |       |
| low            | double      | YES  |     | NULL    |       |
| open           | double      | YES  |     | NULL    |       |
*/

				$loop_count = 0;
				$batch_stocks = array();
				$batch_list = "";


			}
			
			
		}

/*		$result = queryMysql($query);
		$return;
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$return = $row['symbol']."+".$return;
		}
		
		return $return;
*/

}




?>