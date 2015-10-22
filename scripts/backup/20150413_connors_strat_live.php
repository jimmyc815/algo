<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL & ~E_NOTICE);

include_once('connorsRSI_strat_13.php');
include_once('order_execution_engine.php');

$liveportfolio = "liveportfolio";
$liveportfolio_account = "liveportfolio_account";
$liveportfolio_trade_hist = "liveportfolio_trade_hist";
$liveportfolio_trade_pnl_hist = "liveportfolio_trade_pnl_hist";
$liveportfolio_hist_performance = "liveportfolio_hist_performance";

$quote_table = "quotes";

$live_daily_buy_list = "live_daily_buy_list";

if($_GET){
	if($_GET['action'] == 'initiate_live_portfolio'){ 
			global $original_investment;
			global $breakOutSignal;
			global $ADX_filter;
			global $breakOutSignal;
			global $breakOutOrderBy;
			global $simplePriceHistory;
			global $showOutput;
			global $risk_factor;
			global $max_risk;
			global $stop_loss_multiplier;
			global $tranHistArray;
			global $portfolio_table;
					
			$portfolioReturn = array();
			$retArray = array();
			$count = 0;
			$ADX_filter = "Off";		
			
			$portfolioID = $_GET['portfolio_id'];		
			if ($_GET['cash']) {$original_investment = $_GET['cash'];}
			$portfolio_table = "portfolio".$portfolioID;		
			if (!$portfolioID) {
				$portfolioID = 1;
			}
			
			$breakOutSignal = $_GET['breakoutSignal'];
			
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
					
			$breakOutOrderBy = array();
		
			$enterCRSI = $_GET['enterCRSI'];
			$enterRange = $_GET['enterRange'];
			$enterLimit = $_GET['enterLimit'];
			$exitCRSI = $_GET['exitCRSI'];
			$stopLoss = $_GET['stopLoss'];
			
			$orderBy = $_GET['orderBy'];
			$commission = $_GET['commission'];
	
			## risk factor
			if ($_GET['maxRisk']){
				$max_risk = $_GET['maxRisk'];
			}
	
			if ($_GET['riskFactor']){
				$risk_factor = $_GET['riskFactor'];
			}		
	
			if ($_GET['riskSD']){
				$stop_loss_multiplier = $_GET['riskSD'];
			}		
			
			if (!$orderBy) {
				$orderBy = "crsi desc";
			}		

			initiate_live_portfolio($portfolioID, $start_date, $end_date, $enterCRSI, $enterRange, $enterLimit, $exitCRSI, $commission, $max_risk, $risk_factor, $stop_loss_multiplier, $orderBy );


	} else if($_GET['action'] == 'copy_sim_to_live_portfolio'){ 
			$portfolioID = $_GET['portfolio_id'];		

			copy_sim_to_live_portfolio($portfolioID);
	} else if($_GET['action'] == 'live_crsi_sell'){ 
			$portfolioID = $_GET['portfolio_id'];		
			
			$today = $_GET['today'];
			
			if (empty($today)) {
				$today = date("Y-m-d");
			}

			$account = live_get_account_setting($portfolioID);
				
			live_crsi_sell ($today, $portfolioID, $account['exit_crsi'], "");

	} else if($_GET['action'] == 'live_populateDailyBuyList'){ 
			$portfolioID = $_GET['portfolio_id'];
			$today = $_GET['today'];
			
			if (empty($today)) {
				$today = date("Y-m-d");
			}

			$account = live_get_account_setting($portfolioID);
			
			#live_populateDailyBuyList ($today, $crsiThreshold, $enterRange, $portfolioID, $pctLimitBelow);
			live_populateDailyBuyList ($today, $account['enter_crsi'], $account['enter_range'], $portfolioID, $account['pct_limit_below']);
			
	} else if($_GET['action'] == 'live_getBuyList'){ 
			$portfolioID = $_GET['portfolio_id'];		

			$today = "2015-03-26";

			$orderBy = "crsi desc";
			$buy_list = live_getBuyList ($today, $portfolioID, $orderBy);
	} else if($_GET['action'] == 'live_get_portfolio_risk'){ 
			$portfolioID = $_GET['portfolio_id'];		

			$today = "2015-03-26";

			$orderBy = "crsi desc";
			live_get_portfolio_risk ($today, $portfolioID);
	} else if($_GET['action'] == 'live_crsi_buy'){ 
			$portfolioID = $_GET['portfolio_id'];		

			$today = $_GET['today'];
			
			if (empty($today)) {
				$today = date("Y-m-d");
			}

			$account = live_get_account_setting($portfolioID);
			
		
            live_crsi_buy ($today, $portfolioID, $account['enter_crsi'], $account['enter_range'], $account['pct_limit_below'], $account['order_by']);


	} else if($_GET['action'] == 'test_live_get_account_setting'){ 
			$portfolioID = $_GET['portfolio_id'];		
			
		
            live_get_account_setting($portfolioID);
	} 

	
#function live_populateDailyBuyList ($today_date, $crsiThreshold, $enterRange, $portfolioID, $dailyBuyList, $pctLimitBelow) {
#function live_getBuyList ($today_date, $crsiThreshold, $enterRange, $portfolioID, $dailyBuyList, $pctLimitBelow, $orderBy) {


}

function initiate_live_portfolio($portfolioID, $start_date, $end_date, $enterCRSI, $enterRange, $enterLimit, $exitCRSI, $commission, $max_risk, $risk_factor, $stop_loss_multiplier, $orderBy) {
		global $original_investment;
		global $breakOutSignal;
		global $ADX_filter;
		global $breakOutSignal;
		global $breakOutOrderBy;
		global $simplePriceHistory;
		global $showOutput;
		global $risk_factor;
		global $max_risk;
		global $stop_loss_multiplier;
		global $tranHistArray;
		global $portfolio_table;
		global $liveportfolio;
		global $liveportfolio_account;
		global $liveportfolio_trade;
		global $liveportfolio_complete_trade;


		reset_live_portfolio($portfolioID);

		$simResult = simulate_range_trade($portfolioID, $start_date, $end_date, $enterCRSI, $enterRange, $enterLimit, $exitCRSI, $commission, $max_risk, $risk_factor, $stop_loss_multiplier, $orderBy );	

		copy_sim_to_live_portfolio($portfolioID);
		
		print $simResult;
		
}

function reset_live_portfolio($pid) {
	global $liveportfolio;
	global $liveportfolio_account;
	global $liveportfolio_trade_hist;
	global $liveportfolio_trade_pnl_hist;
	global $liveportfolio_hist_performance;

	$query = "delete from $liveportfolio where portfolio_id = $pid ";
	$result = queryMysql($query);	
	
	#$query = "delete from $liveportfolio_account where portfolio_id = $pid ";
	#$result = queryMysql($query);

	$query = "delete from $liveportfolio_trade_hist where portfolio_id = $pid ";
	$result = queryMysql($query);

	$query = "delete from $liveportfolio_trade_pnl_hist where portfolio_id = $pid ";
	$result = queryMysql($query);

	$query = "delete from $liveportfolio_hist_performance where portfolio_id = $pid ";
	$result = queryMysql($query);	
}

function copy_sim_to_live_portfolio($pid) {
	global $liveportfolio;
	global $liveportfolio_account;
	global $liveportfolio_trade_hist;
	global $liveportfolio_trade_pnl_hist;
	global $liveportfolio_hist_performance;
	global $live_daily_buy_list;
	global $dbname;

	$sim_portfolio = "portfolio".$pid;	;
	$sim_trade_hist = "turtle_portfolio_transaction".$pid;
	$sim_trade_pnl_hist = "transactions".$pid;
	$sim_portfolio_hist_performance = "crsi_portfolio_performance";
	$sim_daily_buy_list = "crsi_daily_buy_list".$pid;
	
	$query = "use $dbname";
	$result = queryMysql($query);	
	
	## copy portfolio holding
	$query = "insert into $dbname.$liveportfolio (portfolio_id, symbol, last_price, shares, cost_basis ) 
			  select $pid, symbol, last_price, shares, cost_basis from $dbname.$sim_portfolio where portfolio_id = $pid ";
	
	$result = queryMysql($query);	
		
	## copy trade hist
	$query = "insert into $liveportfolio_trade_hist (portfolio_id, symbol, trade_type, trade_date, shares, price, risk)
			  select $pid, symbol, trade_type, trade_date, shares, price, risk from $sim_trade_hist where portfolio_id = $pid";

	$result = queryMysql($query);	
	
	## copy trade pnl hist
	$query = "insert into $liveportfolio_trade_pnl_hist (xid, portfolio_id, symbol, buy_date, buy_shares, buy_price, sell_date, sell_shares, sell_price, PnL)
			  select xid, $pid,  symbol, buy_date, buy_shares, buy_price, sell_date, sell_shares, sell_price, PnL from $sim_trade_pnl_hist where portfolio_id = $pid ";
	#echo "query: $query ", PHP_EOL;

	$result = queryMysql($query);	

	## copy portfolio performance
	$query = "insert into $liveportfolio_hist_performance (portfolio_id, trade_date, return_dollar, return_pct, portfolio_value)
			  select $pid, trade_date, return_dollar, return_pct, portfolio_value from $sim_portfolio_hist_performance where portfolio_id = $pid ";
	#echo "query: $query ", PHP_EOL;

	$result = queryMysql($query);	

	## copy daily buy list
	$query = "insert into $live_daily_buy_list (portfolio_id, rank, trade_date, symbol, buy_price)
			  select $pid, rank, trade_date, symbol, buy_price from $sim_daily_buy_list where portfolio_id = $pid ";
	#echo "query: $query ", PHP_EOL;

	$result = queryMysql($query);	

}

function live_crsi_sell ($date, $portfolioID, $exitCRSI, $stopLoss) {

	global $max_num_holdings;
	global $max_risk;
	global $risk_factor;
	global $stop_loss_multiplier;
	global $showOutput;
	global $commission;
	global $tranHistArray;
	global $connection;
	global $portfolio_table;
		
	global $liveportfolio;
	
	$quote_table = "quotes";
		
		
		$sell_price = 0;

		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares, risk, risk_pct, high, vsSpyRank, crsi ";
		$query .= "from $liveportfolio a, $quote_table b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.crsi > $exitCRSI ";
		$query .= "and b.trade_date = '".$date."'";
		
		if ($stopLoss) {
			$query .= "union
						select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, 
							stop_buy, shares, risk, risk_pct, high, vsSpyRank, crsi 
						from $portfolio_table a, $quote_table b
						where a.symbol = b.symbol 
						and a.portfolio_id = $portfolioID
						and a.symbol != 'CASH' 
						and ((b.adj_close - a.cost_basis) * 100 / a.cost_basis ) < $stopLoss			
						and b.trade_date = '$date'				
			";
		}
		

		
		#echo "$query ", PHP_EOL;
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
			$ph[$i]['risk'] = str_replace("\"", "",$data[15]);
			$ph[$i]['risk_pct'] = str_replace("\"", "",$data[16]);
			$ph[$i]['high'] = str_replace("\"", "",$data[17]);
			$ph[$i]['vsSpyRank'] = str_replace("\"", "",$data[18]);
			$ph[$i]['crsi'] = str_replace("\"", "",$data[19]);
			$ph[$i]['open'] = str_replace("\"", "",$data[20]);



			$i++;						
		}

		$order_type = "SELL_OPEN";
		
		for ($x=0; $x < $i; $x++) {					
			$num_shares = $ph[$x]['shares'];
			$symbol = $ph[$x]['symbol'];
		
			
			echo "date: $today symbol: $symbol num shares: $num_shares ", PHP_EOL;
			add_order($portfolioID, $order_type, $symbol, $num_shares);
		}

				
}  	

## generate daily buy list base on if stock price breaches crsi threshold and set limit buy price
function live_populateDailyBuyList ($today_date, $crsiThreshold, $enterRange, $portfolioID, $pctLimitBelow) {
			global $showOutput;
			global $portfolio_table;
			global $live_daily_buy_list;
			global $quote_table;
			global $liveportfolio;
			
			$query = "insert into ".$live_daily_buy_list." (portfolio_id, trade_date, symbol, buy_price) ";
			$query .= "select ".$portfolioID.", a.trade_date, a.symbol, a.adj_close*(100-".$pctLimitBelow.")/100";
			$query .= " from $quote_table a, stock_list b ";
			$query .= "where a.trade_date = '".$today_date."' ";
			$query .= "and a.symbol = b.symbol ";
			$query .= "and a.symbol not in (select symbol from $live_daily_buy_list where portfolio_id = $portfolioID )";
			$query .= " and a.crsi < $crsiThreshold ";
			$query .= " and a.crsi > 0 ";
			$query .= " and (a.close-a.low)*100/(a.high-a.close) < $enterRange ";
			$query .= " order by a.crsi asc";
			$query = stripslashes($query);
			
			$result = queryMysql($query);

			$query  = "delete from $live_daily_buy_list where portfolio_id = $portfolioID ";
			$query .= "and exists (select 1 from $quote_table where trade_date = '$today_date' and crsi > 50 and $quote_table.symbol = $live_daily_buy_list.symbol  )";

			$query = stripslashes($query);
			$result = queryMysql($query);
			#echo $query , PHP_EOL;
	
			$query  = "delete from $live_daily_buy_list where portfolio_id = $portfolioID ";
			$query .= "and exists (select 1 from $liveportfolio where portfolio_id = $portfolioID and $liveportfolio.symbol = $live_daily_buy_list.symbol )";
			$query = stripslashes($query);
			$result = queryMysql($query);
			#echo $query , PHP_EOL;
}

// put stocks in the daily buy list
function live_getBuyList ($today_date, $portfolioID, $orderBy) {
		global $simplePriceHistory;		
		global $live_daily_buy_list;
		global $quote_table;
		global $liveportfolio;
		

		$masterRankByResult = array();
		$rankResult = array();
		

		$query  = "select a.symbol, a.trade_date, a.open, a.high, a.low, a.close, a.pct_change, ";
		$query .= "a.ATR, a.55_DAY_HIGH, a.20_DAY_HIGH, a.50_MA, a.200_MA, a.vsSpyRank, a.crsi, c.buy_price, a.adj_close ";
		$query .= "from $quote_table a, $live_daily_buy_list c ";
		$query .= " where a.trade_date = '".$today_date."'";
		$query .= " and c.portfolio_id = ".$portfolioID;
		$query .= " and a.symbol = c.symbol ";
		$query .= " and a.low < c.buy_price";
		$query .= " and a.symbol not in (select symbol from $liveportfolio where portfolio_id = ".$portfolioID.") ";
		$query .= " and a.ATR > 0 ";
		$query .= " order by $orderBy";

		#echo $query, PHP_EOL;
		$query = stripslashes($query);

		$result = queryMysql($query);
	
			$tmpArray = array();
			$tmpRankArray = array();
	
			$i = 0;
			
	  	  	while ($row = mysql_fetch_assoc($result)) {
	    		foreach ($row as $key => $value) {
	   	         	$tmpArray[$i][$key] = $value;
	            }
	                      
	        	$i++;
	        }


	return $tmpArray;

}

function live_get_portfolio_value($trade_date, $portfolioID) {
	global $quote_table;
	global $liveportfolio;
	
	$p_value = 0;
	
	$tmp_sql = "SELECT sum(b.close * a.shares) from $liveportfolio a, $quote_table b 
				WHERE a.portfolio_id = $portfolioID 
				AND a.symbol=b.symbol
				AND b.trade_date = '$trade_date'
				AND a.symbol != 'CASH'";

	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$p_value = $tmp_data[0];
	}
	return $p_value;
}


function live_get_portfolio_risk($trade_date, $portfolioID) {
	global $quote_table;
	global $liveportfolio;
		
	$previous_date = getPreviousDate($trade_date);
	
	$p_value = live_get_portfolio_value($trade_date, $portfolioID);

	$tmp_sql = "SELECT sum(b.ATR * a.shares) from $liveportfolio a, $quote_table b 
				WHERE a.portfolio_id = $portfolioID 
				AND a.symbol=b.symbol
				AND b.trade_date = '$previous_date'
				AND a.symbol != 'CASH'";
	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$r_value = $tmp_data[0];
	}

	$current_risk = ($r_value / $p_value) * 100;
	
	return $current_risk;
}


function live_crsi_buy ($date, $portfolioID, $enterCRSI, $enterRange, $enterLimit, $orderBy) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_multiplier;
		global $portfolio_tbl;
		global $showOutput;
		global $commission;
		global $tranHistArray;
		global $portfolio_table;
		global $live_daily_buy_list;
		global $quote_table;
		global $liveportfolio;
		
echo "date: ", $date, " id: ", $portfolioID, " order by: ", $orderBy, PHP_EOL;
				
		$breakOutStockArray = live_getBuyList ($date, $portfolioID, $orderBy); 

		#var_dump ($breakOutStockArray);

		$len_array = count($breakOutStockArray);
        //$risk_factor = 1 / $max_num_holdings;
	
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
		
		$current_trade_date_id = 0;
		
		$workingArray = $breakOutStockArray;
		
		$adxArray = "";
		$adxCount = 1;


		$len_array = count($breakOutStockArray);
		$portfolio_value = live_get_portfolio_value($date, $portfolioID);

		$query = "select shares from $liveportfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$cash = $data[0];
		}		

		$buyingpower = 0;

		
		
		$pendingcash = 0;
			
		$query = "select sum(a.shares * b.close) from $order_queue a, $quote_table b where portfolio_id = $portfolioID and a.symbol = b.symbol and a.order_type like '%SELL%'";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$pendingcash = $data[0];
		}		
			

		$buyingpower = $cash + $pendingcash;


		#$current_risk = get_portfolio_risk($date, $portfolioID);
		$current_risk = live_get_portfolio_risk($date, $portfolioID);

		$buy_array = array();
		

		for ($x=0; $x < $len_array; $x++) {
				### get portfolio value
				#$portfolio_value = get_historical_turtle_portfolio_value($date, $portfolioID);
				### get current available cash		
				#$query = "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
				#$result = queryMysql($query);
				#while ($data = mysql_fetch_row($result)) {
				#	$cash = $data[0];
				#}		
				
				
				$risk_value = $portfolio_value * $risk_factor / 100;
				$current_N = $breakOutStockArray[$x]['ATR'];
				if ($current_N > 0) {
					$num_shares = floor($risk_value /($stop_loss_multiplier*$current_N));
				}
				
				$purchase_price = $breakOutStockArray[$x]['close'];
						
				$purchase_value = $num_shares * $purchase_price + $commission;

				$stop_loss = $purchase_price - ($stop_loss_multiplier*$current_N);
				$stop_buy = $purchase_price + $current_N;

				echo "buying power: ", $buyingpower, "cash: ", $cash, " purchase value: ", $purchase_value, " current risk: ",  $current_risk, " max risk: $max_risk ", " current N: ", $current_N, " num shares: ", $num_shares, PHP_EOL;
				#if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
				if (($buyingpower > $purchase_value) && ($current_risk < $max_risk)) {

					
					#$cash = $cash - $purchase_value;
					$buyingpower = $buyingpower - $purchase_value;
					
					$risk_dollar = $num_shares * ($stop_loss_multiplier * $current_N);
					$risk_pct = ($risk_dollar / $portfolio_value) * 100;
					$current_risk += $risk_pct;
					
					$order_type = "BUY_OPEN";
					$symbol = $breakOutStockArray[$x]['symbol'];
					#echo "date: $today symbol: $symbol num shares: $num_shares ", PHP_EOL;
					add_order($portfolioID, $order_type, $symbol, $num_shares);
				
				} else {
					#echo "trade date: $date ran out of money ", PHP_EOL;
					break;
				} 
								
		}
} 

function live_get_account_setting($portfolioID) {
	global $quote_table;
	global $liveportfolio;
	global $liveportfolio_account;
	
	$account = array();
	
	$tmp_sql = "SELECT  description, 
						risk_category, 
						start_date, 
						enter_crsi, 
						enter_range, 
						pct_limit_below, 
						exit_crsi, 
						order_by, 
						max_risk, 
						risk_factor, 
						risk_sd, 
						create_date
				FROM $liveportfolio_account
				WHERE portfolio_id  = $portfolioID";

	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$account['description'] = $tmp_data[0];
		$account['risk_category'] = $tmp_data[1];
		$account['start_date'] = $tmp_data[2];
		$account['enter_crsi'] = $tmp_data[3];
		$account['enter_range'] = $tmp_data[4];
		$account['pct_limit_below'] = $tmp_data[5];
		$account['exit_crsi'] = $tmp_data[6];
		$account['order_by'] = $tmp_data[7];
		$account['max_risk'] = $tmp_data[8];
		$account['risk_factor'] = $tmp_data[9];
		$account['risk_sd'] = $tmp_data[10];
		$account['create_date'] = $tmp_data[11];


	}
	
	if (empty($account['description'])) {
		echo "No such portfolio id, fatal error, exit now...", PHP_EOL;
	}
	
	return $account;
}


?>