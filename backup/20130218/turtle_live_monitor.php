#!/usr/local/bin/php5

<?php

// tables in mysql
// turtle_portfolio_performance
// turtle_portfolio_transaction
// turtle_portfolio


include_once 'dbfunction.php';
include_once 'portfolio_selection.php';

//global $max_risk;
//global $max_num_holdings;
//global $original_investment;
global $portfolioID ;
$portfolioID = 2;

if ($_GET['action'] == 'turtle_live_initial_setup') {
		$portfolio_id = $_GET['portfolio_id'];
		$original_investment = $_GET['original_investment'];
		$max_risk = $_GET['max_risk'];
		$max_num_holdings = $_GET['max_num_holdings'];
		$breakOutSignal = $_GET['breakout_signal'];

		if (!$portfolio_id) $portfolio_id = 2;
		$today_date = date("Y-m-d");  
		if (!$original_investment) $original_investment=200000;
		if (!$max_num_holdings) $max_num_holdings = 15;
		if (!$max_risk) $max_risk=10;
		if (!$breakOutSignal) $breakOutSignal="55_DAY_HIGH";
		
		$query = "INSERT INTO turtle_portfolio_init_setup (portfolio_id, portfolio_start_date, initial_investment, ";
		$query .= "max_risk, max_num_holding, breakout_signal) values (";
		$query .= $portfolio_id.", '".$today_date."', ".$original_investment.", ".$max_risk.", ".$max_num_holdings.", '";
		$query .= $breakOutSignal."') ON DUPLICATE KEY UPDATE ";
		$query .= "portfolio_start_date='".$today_date."', ";
		$query .= "initial_investment=".$original_investment.", ";
		$query .= "max_risk=".$max_risk.", ";
		$query .= "max_num_holding=".$max_num_holdings.", ";
		$query .= "breakout_signal='".$breakOutSignal."' ";
		
		$result = queryMysql($query);
		
} else if($_GET['action'] == 'live_monitor'){ 
		global $original_investment;
		global $breakOutSignal;
		global $ADX_filter;
		global $breakOutSignal;
		global $breakOutOrderBy;
		global $simplePriceHistory;

		$ADX_filter = $_GET['adx_filter'];
		$breakOutOrderBy = $_GET['breakoutOrderBy'];		
		
		$portfolio_id = $_GET['portfolio_id'];			
		if (!$portfolio_id) $portfolio_id = 2;

		$today_date = date("Y-m-d");  

		$query  = "select portfolio_start_date, initial_investment, max_risk, max_num_holding, breakout_signal ";
		$query .= "from turtle_portfolio_init_setup where portfolio_id = ".$portfolio_id; 
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$portfolio_start_date = $data[0];
			$original_investment = $data[1];
			$max_risk = $data[2];
			$max_num_holdings = $data[3];
			$breakOutSignal = $data[4];
		}

		if (!$original_investment) $original_investment=200000;
		if (!$max_num_holdings) $max_num_holdings = 15;
		if (!$max_risk) $max_risk=10;
		if (!$breakOutSignal) $breakOutSignal="55_DAY_HIGH";
	
//		$spyReturn = array();
		$portfolioReturn = array();
		$retArray = array();
		$count = 0;
		$ADX_filter = "Off";	
			
		$dailyBuyList = "turtle_daily_buy_list_live";

		// if end date is not supplied, default to today		
		if (!$today_date) {
			$today_date = date("Y-m-d");  
		}
		
		$valueOrderByPctChange = 1.0001 - ( $_GET['valueOrderByPctChange'] / 100 );
		$valueOrderByRelVol = 1.0001 - ( $_GET['valueOrderByRelVol'] / 100 );
		$valueOrderByVsSpy = 1.0001 - ($_GET['valueOrderByVsSpy'] / 100 );

		$breakOutOrderBy = array();
		//$breakOutOrderBy["pct_change"] = 0.1;
		//$breakOutOrderBy["relative_avg_vol"] = 0.1;
		//$breakOutOrderBy["vsSpyEMA"]=1;
		$breakOutOrderBy["pct_change"] = $valueOrderByPctChange;
		$breakOutOrderBy["relative_avg_vol"] = $valueOrderByRelVol;
		$breakOutOrderBy["vsSpyEMA"]=$valueOrderByVsSpy;

		$minReturn = 100;
		$maxReturn = -100;

		live_turtle_portfolio_sell($today_date, $portfolioID);
		live_turtle_portfolio_pyramid_buy ($today_date, $portfolioID);
		live_turtle_portfolio_buy($today_date, $breakOutSignal, $ADX_filter, '', $portfolioID, $dailyBuyList) ;
		
/*		
			$value = get_realtime_turtle_portfolio_value($portfolioID);
			$preturn = ($value - $original_investment) / $original_investment * 100;
			$dollar_return = $value - $original_investment;
		
			if ($preturn > $maxReturn) {$maxReturn = $preturn;};
			if ($preturn < $minReturn) {$minReturn = $preturn;};
			
			$portfolioReturn[$count]['trade_date'] = $today_date;
			$portfolioReturn[$count]['return'] = $preturn;
			$portfolioReturn[$count]['value'] = $value;
			$portfolioReturn[$count]['maxReturn'] = $maxReturn;
			$portfolioReturn[$count]['minReturn'] = $minReturn;
			$portfolioReturn[$count]['dollar_return'] = $dollar_return;

			$query2 = "insert into turtle_portfolio_performance values (".$portfolioID.", '".$portfolioReturn[$count]['trade_date']."', ".$portfolioReturn[$count]['dollar_return'].", ".$portfolioReturn[$count]['return'].")";
			$result2 = queryMysql($query2);			


			$newDateStr = strtotime($trade_date);
			$newDateStr = $newDateStr * 1000 - 14400000;

			array_push($retArray, array($newDateStr, $preturn));
			
*/			$count ++;
//		}

		echo json_encode($retArray);

} else if ($_GET['action'] == 'end_of_day_processing'){
	global $portfolioID ;
	
	$portfolioID = $_GET['portfolio_id'];

	$today_date = date("Y-m-d");  
	$today_date = "2013-02-15";

	$breakOutSignal = "55_DAY_HIGH";
	$dailyBuyList = "turtle_daily_buy_list_live";
	$rankAndWeightArray = "";

	populateDailyBuyList ($today_date, $breakOutSignal, $rankAndWeightArray, $portfolioID, $dailyBuyList);
	live_turtle_portfolio_update_stop_loss ($portfolioID) ;
	update_daily_portfolio_performance($portfolioID);
	send_end_of_day_email($portfolioID);
	
} else if ($_GET['action'] == 'reset_live_portfolio'){
	global $portfolioID ;

	$portfolioID = $_GET['portfolio_id'];

	reset_portfolio($portfolioID);
	
} elseif ($_GET['action'] == 'testCalculateEndBalance') {
		$transactionList = array();
		$beginBalance = 1000000;
		
		// get unique symbol from transaction table that has sell
		$query = "select symbol, trade_type, trade_date, shares, price from turtle_portfolio_transaction where portfolio_id = 1 order by trade_date asc ";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			if ($data[1] == 'BUY')
			{
				$beginBalance -= ($data[3] * $data[4]);				
			} else {
				$beginBalance += ($data[3] * $data[4]);
			}
		}
		
		print "cash: ";
		print $beginBalance;
		print "<br>";
		
		$query = "select a.symbol, (a.shares * b.last_price ) as value from turtle_portfolio a, detail_quote b where a.symbol = b.symbol and b.symbol != 'CASH'";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$beginBalance += $data[1];
		}		
		
		print "end balance: ";
		print $beginBalance;
		return $beginBalance;
		
		
} elseif ($_GET['action'] == 'test_cross') {
		test_cross_script();

} elseif ($_GET['action'] == 'test_get_current_portfolio_value') {
		$value = get_realtime_turtle_portfolio_value(2);
		print "portfolio value: $value \n";
} else if ($_GET['action'] == 'test_live_sell'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  


	live_turtle_portfolio_sell($today_date, $portfolioID);
} else if ($_GET['action'] == 'test_live_pyramid_buy'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	live_turtle_portfolio_pyramid_buy ($today_date, $portfolioID);
} else if ($_GET['action'] == 'test_live_get_breakout_stock'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	live_get_breakout_stock ('55_DAY_HIGH', "", $portfolioID, $dailyBuyList) ;
} else if ($_GET['action'] == 'test_live_turtle_portfolio_buy'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	live_turtle_portfolio_buy('55_DAY_HIGH', 'Off', '', $portfolioID, $dailyBuyList) ;
} else if ($_GET['action'] == 'test_live_turtle_portfolio_update_stop_loss'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	live_turtle_portfolio_update_stop_loss ($portfolioID) ;
} else if ($_GET['action'] == 'test_update_daily_performance'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	update_daily_portfolio_performance($portfolio_id);

} else if ($_GET['action'] == 'test_eod_email'){
	global $portfolioID ;
	$today_date = date("Y-m-d");  

	$portfolioID = 2;
	
	send_end_of_day_email($portfolioID);

} else if ($_GET['action'] == 'test_email') {
	 $to = "jimmyc815@gmail.com";
	 $subject = "Hi!";
	 $body = "Hi,\n\nHow are you?";
	 if (mail($to, $subject, $body)) {
	   echo("<p>Message successfully sent!</p>");
	  } else {
	   echo("<p>Message delivery failed...</p>");
	  }	
	
}


function live_turtle_portfolio_sell ($date, $portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
				
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		
		$sell_price = 0;

		$query  = "select a.symbol, b.last_price, daily_change, percent_change, stop_loss, stop_buy, shares, risk, risk_pct, last_trade_date, last_trade_time ";
		$query .= "from turtle_portfolio a, detail_quote b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.last_price < a.stop_loss ";
		//$query .= "and b.trade_date = '".$date."'";
		$result = queryMysql($query);
//print $query;
		$ph = array();
		$i = 0;

		while ($data = mysql_fetch_row($result)) {
			$ph[$i]['symbol'] = str_replace("\"", "",$data[0]);
			$ph[$i]['last_price'] = str_replace("\"", "",$data[1]);
			$ph[$i]['daily_change'] = str_replace("\"", "",$data[2]);
			$ph[$i]['percent_change'] = str_replace("\"", "",$data[3]);
			$ph[$i]['stop_loss'] = str_replace("\"", "",$data[4]);
			$ph[$i]['stop_buy'] = str_replace("\"", "",$data[5]);
			$ph[$i]['shares'] = str_replace("\"", "",$data[6]);
			$ph[$i]['risk'] = str_replace("\"", "",$data[7]);
			$ph[$i]['risk_pct'] = str_replace("\"", "",$data[8]);
			$ph[$i]['last_trade_date'] = str_replace("\"", "",$data[9]);
			$ph[$i]['last_trade_time'] = str_replace("\"", "",$data[10]);

			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# determine sell price. If price of day high is < stop loss, sell price will be price of day high
			//if ($ph[$i]['high'] < $ph[$x]['stop_loss']) {
			//	$sell_price = $ph[$x]['high'];
			//} else {
			//	$sell_price = $ph[$x]['stop_loss'];
			//}
			$sell_price = $ph[$x]['last_price'];

			$stock_sales = $ph[$x]['shares'] * $sell_price;

			
			$update_portfolio_query = "update turtle_portfolio set shares = shares + ".$stock_sales." where portfolio_id = ".$portfolioID." and symbol ='CASH'";
			$result = queryMysql($update_portfolio_query);
			$delete_stock_query = "delete from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = '".$ph[$x]['symbol']."'";
			$result = queryMysql($delete_stock_query);
			
			$insert_transaction_history = "insert into turtle_portfolio_transaction values (".$portfolioID.", '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";

			$result = queryMysql($insert_transaction_history);
			
			$email_subject = $ph[$x]['last_trade_date']." ".$ph[$x]['last_trade_time']. " SELL ".$ph[$x]['symbol']." ".$ph[$x]['shares']." shares at $".$sell_price;			
			$email_body = "";			
			sendemail ($email_subject, $email_body);
			
		}		  

}  

function live_turtle_portfolio_pyramid_buy ($date, $portfolioID) { 
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		$risk_factor = 1 / $max_num_holdings;
		
		if (!$portfolioID) {
			$portfolioID = 1;
		}
	
		$my_sql = "select a.symbol, a.shares, b.last_price, b.last_trade_date, b.last_trade_time, a.stop_loss, a.stop_buy, a.cost_basis, a.risk, a.risk_pct from turtle_portfolio a, detail_quote b where a.symbol = b.symbol  and b.last_price > a.stop_buy and a.portfolio_id = ".$portfolioID." order by b.percent_change desc";


		$result = queryMysql($my_sql);

		while ($data = mysql_fetch_row($result)) {
			$this_symbol = $data[0];
			$this_shares = $data[1];
			$this_last_price = $data[2];
			$this_trade_date = $data[3];
			$this_trade_time = $data[4];
			$this_stop_loss = $data[5];
			$this_stop_buy = $data[6];
			$this_cost_basis = $data[7];
			$this_risk = $data[8];
			$this_risk_pct = $data[9];

			### get portfolio value
			$portfolio_value = get_realtime_turtle_portfolio_value($portfolioID);

			### get current available cash		
			$query = "select shares from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = 'CASH'";
			$result2 = queryMysql($query);
			while ($data2 = mysql_fetch_row($result2)) {
				$cash = $data2[0];
			}					
			$risk_value = $portfolio_value * $risk_factor / 100;

			### get yesterday's ATR
			$query = "select ATR from price_history where symbol = '".$this_symbol."' and trade_date_id = (select max(trade_date_id) from price_history where symbol = '".$this_symbol."')";

			$result3 = queryMysql($query);
			while ($data3 = mysql_fetch_row($result3)) {
				$this_ATR = $data3[0];
			}		
			$num_shares = floor($risk_value /(2*$this_ATR));

			## purchase price would be the last update price
			$purchase_value = $num_shares * $this_last_price;
			
			$current_risk = get_current_risk($portfolioID);

			if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
		
				$stop_loss = $this_stop_buy - (2*$this_ATR);
				$stop_buy = $this_stop_buy + $this_ATR;
				
				$avg_cost_basis = (($this_shares * $this_cost_basis) + $purchase_value) / ($this_shares + $num_shares) ;
				$total_shares = $this_shares + $num_shares;
				
				$risk_dollar = $this_shares * (2 * $this_ATR) + $this_risk;
				$risk_pct = ($risk_dollar / $portfolio_value) * 100;
				
				$query3 = "update turtle_portfolio set shares = ".$total_shares.", cost_basis = ".$avg_cost_basis.", stop_loss = ".$stop_loss.", stop_buy=".$stop_buy.", risk=".$risk_dollar.", risk_pct=".$risk_pct." where symbol = '".$this_symbol."' and portfolio_id = ".$portfolioID;
				$result3 = queryMysql($query3);

				// insert into transaction history table
				$query4  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct) ";
				$query4 .= "values (".$portfolioID.", '".$this_symbol."', ";
				$query4 .= "'BUY', ";
				$query4 .= "'".$date."', ";
				$query4 .= $num_shares.", ";
				$query4 .= $this_last_price.", ";
				$query4 .= $risk_dollar.", ";
				$query4 .= $risk_pct." )";
				$result4 = queryMysql($query4);

				$cash_left = $cash - $purchase_value;
				// update cash position
				$query5  = "update turtle_portfolio set shares = ".$cash_left." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
				$result5 = queryMysql($query5);
				
				$email_subject = $this_trade_date." ".$this_trade_time. " Pyramid Buy ".$this_symbol." ".$this_shares." shares at $".$this_last_price;
				$email_body = "";
				sendemail ($email_subject, $email_body);
				
			}				
				
		}

}

function live_turtle_portfolio_buy($date, $breakOutSignal, $ADX_filter, $breakOutOrderBy, $portfolioID, $dailyBuyList) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		if (!$portfolioID)
		{
			$portfolioID = 2;
		}
		if (!$dailyBuyList)
		{
			$dailyBuyList = "turtle_daily_buy_list_live";
		}
		
		//$breakOutSignal = "55_DAY_HIGH";

		### get list of breakout stocks
		//$breakOutStockArray = array();

		$breakOutStockArray = live_get_breakout_stock ($breakOutSignal, $breakOutOrderBy, $portfolioID, $dailyBuyList);
	
		// set how many breakout stocks to buy
		//$len_array = count($breakOutStockArray);	
		$len_array = count($breakOutStockArray);
        $risk_factor = 1 / $max_num_holdings;
	
        $portfolio_value = 0;
		$current_pos = 0;
		$next_buy_point = 100000;
		$stop_loss = 0;
		$num_shares = 0;
		$current_N = 0;
		$risk_value = 0;
		$purchase_value = 0;
		$sim_start_day = 55;
        $pyramid_mode = 0;
 
		
		$stop_loss = 0;
		
//		$ADX_filter = "Off";
		$current_trade_date_id = 0;
		
		$workingArray = $breakOutStockArray;
		
		$adxArray = "";
		$adxCount = 1;

		// check if ADX check if turned on
		if ($ADX_filter == "On"){
			for ($x=1; $x < $len_array; $x++) {
				$current_trade_date_id = getTradeDateID($workingArray[$x]['symbol'], $date);

				$returnADX = calculate_ADX ($workingArray[$x]['symbol'], $current_trade_date_id, 14);

				if (($returnADX["plusDI14"] > $returnADX["negDI14"]) && ($returnADX["ADX"] < 30)) {
				//if ($returnADX["plusDI14"] < $returnADX["negDI14"])  {
					//array_delete ($breakOutStockArray[$x]);
					//unset ($breakOutStockArray[$x]);
					if (($returnADX["changeADX"]) > 5) {
						$adxArray[$adxCount] = $workingArray[$x];
						$adxCount ++;
					}
				}
			}
		
			$breakOutStockArray = $adxArray;
		}

		$len_array = count($breakOutStockArray);
		
		for ($x=1; $x < $len_array; $x++) {
				### get portfolio value
				
				$portfolio_value = get_realtime_turtle_portfolio_value($portfolioID);
				### get current available cash		
				$query = "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
				$result = queryMysql($query);
				while ($data = mysql_fetch_row($result)) {
					$cash = $data[0];
				}		
				$risk_value = $portfolio_value * $risk_factor / 100;
				### get yesterday's ATR
				$query = "select ATR from price_history where symbol = '".$breakOutStockArray[$x]['symbol']."' and trade_date_id = (select max(trade_date_id) from price_history where symbol = '".$breakOutStockArray[$x]['symbol']."')";

				$result3 = queryMysql($query);
				while ($data3 = mysql_fetch_row($result3)) {
					$this_ATR = $data3[0];
				}		
				
				$current_N = $this_ATR;
							
				if ($current_N > 0) {
					$num_shares = floor($risk_value /(2*$current_N));
				}

/*				$purchase_value = $num_shares * $breakOutStockArray[$x][$breakOutSignal];
				$stop_loss = $breakOutStockArray[$x][$breakOutSignal] - (2*$current_N);
				$stop_buy = $breakOutStockArray[$x][$breakOutSignal] + $current_N;
*/
				$purchase_value = $num_shares * $breakOutStockArray[$x]['purchase_price'];
				$stop_loss = $breakOutStockArray[$x]['purchase_price'] - (2*$current_N);
	
				$stop_buy = $breakOutStockArray[$x]['purchase_price'] + $current_N;

				$current_risk = get_current_risk($portfolioID);

				if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
					$cash = $cash - $purchase_value;

					$risk_dollar = $num_shares * (2 * $current_N);
					$risk_pct = ($risk_dollar / $portfolio_value) * 100;

					// insert into turtle_portfolio
					$my_sql  = "insert into turtle_portfolio (portfolio_id, symbol, last_price, shares, cost_basis, stop_loss, stop_buy, risk, risk_pct) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= $breakOutStockArray[$x]['last_price'].", ";
					$my_sql .= $num_shares.", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $stop_loss.", ";
					$my_sql .= $stop_buy.", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";
					$result = queryMysql($my_sql);
					// insert into transaction history table
					$my_sql  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= "'BUY', ";
					$my_sql .= "'".$date."', ";
					$my_sql .= $num_shares.", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";
					$result = queryMysql($my_sql);

					// update cash position
					$my_sql  = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
					$result = queryMysql($my_sql);

					$email_subject = $breakOutStockArray[$x]['last_trade_date']." ".$breakOutStockArray[$x]['last_trade_price']." Buy ".$breakOutStockArray[$x]['symbol']." ".$num_shares." shares at $".$breakOutStockArray[$x]['purchase_price'];
					$email_body = "";
					sendemail ($email_subject, $email_body);

				}
				$pyramid_mode ++;
		}


		//populateDailyBuyList ($date, $breakOutSignal, $rankAndWeightArray, $portfolioID, $dailyBuyList);

} 

function live_turtle_portfolio_update_stop_loss ($portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		if (!$today_date) {
			$today_date = date("Y-m-d");  
		}
		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.trade_date = '".$today_date."'";
		$result = queryMysql($query);

		$ph = array();
		$i = 0;

		while ($data = mysql_fetch_row($result)) {
			$ph[$i]['symbol'] = str_replace("\"", "",$data[0]);
			$ph[$i]['close'] = str_replace("\"", "",$data[1]);
			$ph[$i]['low'] = str_replace("\"", "",$data[2]);
			$ph[$i]['daily_change'] = str_replace("\"", "",$data[3]);
			$ph[$i]['pct_change'] = str_replace("\"", "",$data[4]);
			$ph[$i]['ATR'] = str_replace("\"", "",$data[5]);
			$ph[$i]['55_DAY_HIGH'] = str_replace("\"", "",$data[6]);
			$ph[$i]['20_DAY_HIGH'] = str_replace("\"", "",$data[7]);
			$ph[$i]['20_DAY_LOW'] = str_replace("\"", "",$data[8]);
			$ph[$i]['10_DAY_LOW'] = str_replace("\"", "",$data[9]);
			$ph[$i]['50_MA'] = str_replace("\"", "",$data[10]);
			$ph[$i]['200_MA'] = str_replace("\"", "",$data[11]);
			$ph[$i]['stop_loss'] = str_replace("\"", "",$data[12]);
			$ph[$i]['stop_buy'] = str_replace("\"", "",$data[13]);
			$ph[$i]['shares'] = str_replace("\"", "",$data[14]);

			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# if stop loss is less than (close price - 2N), make (close - 2N) the new stop loss
			$new_stop_loss = $ph[$x]['close'] - (2*$ph[$x]['ATR']);


			if ($ph[$x]['stop_loss'] < ($ph[$x]['close'] - 2*$ph[$x]['ATR']))
			{
				$new_stop_loss = $ph[$x]['close'] - (2*$ph[$x]['ATR']);
				
				$update_sql = "update turtle_portfolio set stop_loss = ".$new_stop_loss." where symbol = '".$ph[$x]['symbol']."' and portfolio_id = ".$portfolioID;
				$result = queryMysql($update_sql);
		
			}
		
		}		  


}


function get_realtime_turtle_portfolio_value($portfolioID) {
	global $portfolioID;
	
	$my_sql  = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
	
	$result = queryMysql($my_sql);
	while ($data = mysql_fetch_row($result)) {
		$value += ($data[0] * 1 / 1);
	}

	return $value;
}

function update_daily_portfolio_performance($portfolioID) {
	global $portfolioID;
	global $original_investment;
	global $today_date;
	
	$value = get_realtime_turtle_portfolio_value($portfolioID);
	$preturn = ($value - $original_investment) / $original_investment * 100;
	$dollar_return = $value - $original_investment;
	
	$query2 = "insert into turtle_portfolio_performance values (".$portfolioID.", '".$today_date."', ".$dollar_return.", ".$preturn.")";

	$result2 = queryMysql($query2);			


//	$newDateStr = strtotime($trade_date);
//	$newDateStr = $newDateStr * 1000 - 14400000;

}

// get breakout stock with weighted critiria
// put stocks in the daily buy list
function live_get_breakout_stock ($movingAvg, $rankAndWeightArray, $portfolioID, $dailyBuyList) {
		if (!$portfolioID) {
				$portfolioID = 1;
		}
		if (!$dailyBuyList) {
				$dailyBuyList = "turtle_daily_buy_list";
		}	

		$masterRankByResult = array();
		$rankResult = array();
		
	
		//foreach ($rankAndWeightArray as $rankBy => $rankWeight)
		//{		
			/* types of query
			1: pct_change
			2: relative_avg_vol
			3: vsSpyEMA
			
			query stock where today's high is greater than yesterda's moving average
			provided that yesterday's 50 MA is greater than yesterday's 200 MA
			use table simple_price_history to compare against full price_history with trade_date_id - 1
			*/
			$query  = "select d.rank, a.symbol, a.last_trade_date, a.last_price, a.percent_change, d.buy_price ";
			$query .= "from detail_quote a, turtle_daily_buy_list d ";
			$query .= " where a.symbol = d.symbol ";
			$query .= " and a.last_price > d.buy_price ";
			//$query .= " and a.trade_date = d.trade_date ";
			//$query .= " and d.rank < 5 ";
			$query .= " and d.symbol not in (select symbol from turtle_portfolio where portfolio_id = ".$portfolioID.") ";
			$query .= " order by d.rank asc";
			$query = stripslashes($query);

			$result = queryMysql($query);

			$tmpArray = array();
			$tmpRankArray = array();
	
			$i = 0;
	  	  	while ($row = mysql_fetch_assoc($result)) {
	    		foreach ($row as $key => $value) {
	   	         	$tmpArray[$i][$key] = $value;
	   	         	
	   	         	if ($key == "symbol")
	   	         	{
		   	         	$tmpRankArray[$value] = $i;		   	    
	   	         	}
	            }
	            
	            // set would-be purchase price of the stock
	            // if high > stop buy price 
	            // 		AND stop buy price > low, then purchase price = stop buy price
	            //		AND stop buy price < open, the purchase price = opening pric

/*	            if ($tmpArray[$i]['buy_price'] > $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['close'];
	            } elseif ($tmpArray[$i]['buy_price'] < $tmpArray[$i]['open']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['close'];
	            } elseif ($tmpArray[$i]['buy_price'] < $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['close'];
	            }

	            if (!$tmpArray[$i]['purchase_price']){
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['close'];
		            
	            }
*/	            
	            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['last_price'];
	        	$i++;
	        }
		
	        $masterRankByResult[$rankBy] = $tmpArray;
	        $rankResult[$rankBy] = $tmpRankArray;
		

return $tmpArray;
		//}
		
		$combineRank = array();

		foreach ($rankResult as $rankBy => $resultArray)
		{
			foreach ($resultArray as $symbol => $value) 
			{
				$combineRank[$symbol] += $value * $rankAndWeightArray[$rankBy];
			}
		}

		asort($combineRank);
		
		$i = 0;

		foreach ($combineRank as $key => $value)
		{
			$keyPos = $tmpRankArray[$key];
			$finalRet[$i] = $tmpArray[$keyPos];
			$i++;
		}

		//populate daily buy list
		//populateDailyBuyList ($today_date, $movingAvg, $rankAndWeightArray);
print $finalRet ;
print "\n";
	return $finalRet;
}

function send_end_of_day_email($portfolioID)
{
	global $portfolioID;
	global $today_date;
	
	$count = 0;
	if (!$today_date) {
		$today_date = date("Y-m-d");  
	}

	$email_subject = $today_date." Portfolio Cumulative Return: ";
	$email_body = "<html><body>";
	
	$query = "select portfolio_start_date, initial_investment from turtle_portfolio_init_setup where portfolio_id = ".$portfolioID;
	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$portfolio_start_date = $data[0];
		$initial_investment = $data[1];
	}	

	$email_body .= "<table id='initial_investment' border=1>";
	$email_body .= "<tr><td>Portfolio Start Date</td><td>Initial Investment</td></tr>";
	$email_body .= "<tr><td>".$portfolio_start_date."</td><td>".$initial_investment."</td></tr>";
	$email_body .= "</table>";
	
	
	$query = "select trade_date, return_dollar, return_pct from turtle_portfolio_performance where portfolio_id = ".$portfolioID." order by trade_date desc";
	$result = queryMysql($query);
	
	$email_body .= "<br>Past Performance<br>";
	$email_body .= "<table id='emailbody' border=1>";
	$email_body .= "<tr><td>Trade Date</td><td>Return in Dollar</td><td>Return Percentage</td></tr>";
	while ($data = mysql_fetch_row($result)) {
			if ($count == 0) {
				$email_subject .= $data[2]."%";
			}
			$email_body .= "<tr><td>".$data[0]."</td><td>".$data[1]."</td><td>".$data[2]."</td></tr>";
			
			$count++;
	}	
	$email_body .= "</table>";
	
	
	$email_body .= "</body></html>";
	$email_body .= get_current_holding_in_email_format($portfolioID);
	
	sendemail ($email_subject, $email_body);
}

function get_current_holding_in_email_format($pid) {
//	global $portfolioID;
	
	$body  = "<br>Current Portfolio Holding <br><table id='holding' border=1>";
	$body .= "<tr><td>Symbol</td><td>Last Price</td><td>Shares></td><td>Cost Basis</td><td>Stop Loss</td><td>Stop Buy</td><td>Risk</td><td>Risk Pct</td></tr>";
	
	$query  = "select symbol, last_price, shares, cost_basis, stop_loss, stop_buy, risk, risk_pct ";
	$query .= "from turtle_portfolio where portfolio_id = ".$pid." order by risk_pct desc";
 	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$body .= "<tr>";
		$body .= "<td>".$data[0]."</td>";
		$body .= "<td>".$data[1]."</td>";
		$body .= "<td>".$data[2]."</td>";
		$body .= "<td>".$data[3]."</td>";
		$body .= "<td>".$data[4]."</td>";
		$body .= "<td>".$data[5]."</td>";
		$body .= "<td>".$data[6]."</td>";
		$body .= "<td>".$data[7]."</td>";
		$body .= "</tr>";

	}
	
	return $body ;
}

function sendemail ($subject, $body) {
	$headers = "From: Turtle Engine\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	 $to = "jimmyc815stern@gmail.com";

	 if (mail($to, $subject, $body, $headers)) {
	   echo("<p>Message successfully sent!</p>");
	  } else {
	   echo("<p>Message delivery failed...</p>");
	 }	
	
}

function reset_live_portfolio($pid) {
		global $original_investment;

		$cash = $original_investment;
		if (!$pid)
		{
			$pid = 2;
		}
		
		$query = "delete from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);
			
		$query = "delete from turtle_portfolio_transaction where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);	
	
}
