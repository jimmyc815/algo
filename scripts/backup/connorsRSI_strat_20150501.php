<?php
// trade both buy sell with next day open price
// tables in mysql
// turtle_portfolio_performance
// turtle_portfolio_transaction
// turtle_portfolio

date_default_timezone_set('America/New_York');
error_reporting(E_ALL & ~E_NOTICE);

$showOutput = false;
#$time_start = microtime(true);
#print "time start: $time_start \n";

include_once 'dbfunction.php';
include_once 'trend_setup.php';
//include_once 'turtle_share_module.php';

$max_risk = 20;
$max_num_holdings = 3;
$risk_factor = 1 / $max_num_holdings;
$original_investment = 1000000;

$stop_loss_multiplier = 2;

$commission=8;

$tranHistArray=array();

$dbname="db380207220";
$dbhost="localhost";
$dbuser="root";
$dbpass=NULL;

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

#$time_end = microtime(true);
#$time = $time_end - $time_start;
#print "total time used: $time \n";

#$_GET['action'] ='simulate_range_trade';
#$_GET['start_date']='2009-8-2';
#$_GET['end_date']='2009-8-10';

#$_GET['breakoutSignal']='55_DAY_HIGH';
$_GET['adx_filter']='On';
#$_GET['breakoutOrderBy']='pct_change';
#$_GET['valueOrderByPctChange']=100;
#$_GET['valueOrderByRelVol']=0;
#$_GET['valueOrderByVsSpy']=20;

//global $ADX_filter;

//	date_default_timezone_set('America/Los_Angeles');

if($_GET){
	if($_GET['action'] == 'rankStock'){ 
		$symbol = $_GET['symbol'];
		$stop_loss_type = $_GET['stop_loss'];
		$buy_signal = $_GET['buy_signal'];

		$query = "select * from turtle_s2_system where symbol = '".$symbol."'";
        $query .= " and stop_loss_type = '$stop_loss_type' and buy_type = '$buy_signal'"; 


		$query= stripslashes($query);
		$result = queryMysql($query);

		$json_result = array();
		$i = 0;
		
  	  	while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	         	$ret[$i][$key] = $value;
            }
        	//$ret[i] = $rowRet;
        	$i++;
        }


//	  $ret2["JSON"] = "success";
//	  $ret2["data"] = $ret;
//		$json_result = mysql_resultTo2DAssocArray_JGrid($result);
		
//		echo json_encode($json_result);
		echo json_encode($ret);
		
		
	} elseif($_GET['action'] == 'getMovingAvg'){ 
		$symbol = $_GET['symbol'];
		$begin_date = $_GET['begin_date'];
		$end_date = $_GET['end_date'];
		$movingAvg = $_GET['mv'];

		$query  = "select trade_date, ".$movingAvg." as MA from price_history where symbol = '".$symbol."'";
		$query .= "and trade_date between STR_TO_DATE('".$begin_date."', '%m/%d/%Y') ";
		$query .= "and STR_TO_DATE('".$end_date."', '%m/%d/%Y') ";
		$query .= "and ".$movingAvg." > 0 "; //make sure we don't include non valid moving avg
		$query .= "order by trade_date_id ASC ";

		$query= stripslashes($query);
		$result = queryMysql($query);

		$json_result = array();
		$i = 0;
		
  	  	while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	         	$ret[$i][$key] = $value;
            }
        	//$ret[i] = $rowRet;
        	$i++;
        }

		echo json_encode($ret);		
		
	} elseif($_GET['action'] == 'testPopulateDailyBuyList'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];
		$crsiThreshold = $_GET['crsi'];
		$pctLimitBelow = $_GET['limit'];

		
		$portfolioID = 1;
		$dailyBuyList = "crsi_daily_buy_list";

		$trade_date_id = getTradeDateID($symbol, $date);

		populateDailyBuyList ($date, $crsiLimit, $symbol, $portfolioID, $dailyBuyList, $pctLimitBelow);

		//echo json_encode($trade_date_id);		
		
		
	} elseif($_GET['action'] == 'testGetBreakoutStock'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		$testArray = array();
		//$testArray["pct_change"] = 0.3;
		//$testArray["relative_avg_vol"] = 0.4;
		$testArray["daily_change"] = 0.3;
		//$testArray[2] = "c";
		
		$breakOutList = getBreakoutStock6 ($date, "20_DAY_HIGH", $testArray, 1, "turtle_daily_buy_list");

//		$breakOutList = getBreakoutStock5 ($date, "55_DAY_HIGH", $testArray, 1, "turtle_daily_buy_list");
//		$breakOutList = getBreakoutStock4 ($date, "55_DAY_HIGH", $testArray);

		//$breakOutList = getBreakoutStock2 ($date, "55_DAY_HIGH", "relative_avg_vol");

		echo json_encode($breakOutList);		
		
		
	} 	elseif($_GET['action'] == 'testGetBuyList'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];
		$crsiThreshold = $_GET['crsi'];
		$pctLimitBelow = $_GET['limit'];

		$testArray = array();
		//$testArray["pct_change"] = 0.3;
		//$testArray["relative_avg_vol"] = 0.4;
		$testArray["daily_change"] = 0.3;
		//$testArray[2] = "c";
		
		$breakOutList = getBuyList($date, $crsiThreshold, $testArray, 2, "crsi_daily_buy_list", $pctLimitBelow);

//		$breakOutList = getBreakoutStock5 ($date, "55_DAY_HIGH", $testArray, 1, "turtle_daily_buy_list");
//		$breakOutList = getBreakoutStock4 ($date, "55_DAY_HIGH", $testArray);

		//$breakOutList = getBreakoutStock2 ($date, "55_DAY_HIGH", "relative_avg_vol");

		echo json_encode($breakOutList);		
		
		
	} elseif($_GET['action'] == 'test_turtle_portfolio_buy'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		reset_portfolio(1);
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

					// update cash position
					$my_sql  = "delete from turtle_portfolio where symbol != 'CASH'";
					$result = queryMysql($my_sql);
					
		turtle_portfolio_buy($date);
		
		//echo json_encode($returnADX);		
		
		
	} elseif($_GET['action'] == 'test_crsi_buy'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		reset_portfolio(1);
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		// update cash position
		//$my_sql  = "delete from turtle_portfolio where symbol != 'CASH'";
		//$result = queryMysql($my_sql);
					
		crsi_buy($date);
		
		//echo json_encode($returnADX);		
		
		
	} elseif($_GET['action'] == 'getPortfolioHolding'){
		$query = $_GET['txtInputQuery'];
		//$query = "select * from turtle_portfolio";
		$query= stripslashes($query);
		$result = queryMysql($query);
		$ret = array();

	    $i = 0;
		while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	        	 $ret[$i][$key] = $value;

            }
        //$ret[i] = $rowRet;
        	$i++;
        }

        $ret2["dataset"] = $ret;


		echo json_encode($ret2);
		
		//exit;	 
	} elseif($_GET['action'] == 'getCurrentTurtlePortfolio'){
		$query = $_GET['txtInputQuery'];

		$query= stripslashes($query);
		$result = queryMysql($query);

		$sightings = array();

		$sightings = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($sightings);
		
		exit;	 
	} elseif ($_GET['action'] == 'calculateEndBalance') {
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
		
		
	} elseif ($_GET['action'] == 'calculatePostSimulationKeyStats') { // calcute max min loss and other keystats after a simulation run
		$keyStatsArray = array();
		
		// get max and min return during the simulation run
		$query = "select max(return_pct) as max_return, min(return_pct) as min_return from turtle_portfolio_performance where portfolio_id = 1";
		$result = queryMysql($query);
		
		
		while ($data = mysql_fetch_row($result)) {
			//$max_return = $data[0];
			//$min_return = $data[1];
			$keyStatsArray[0]['max_portfolio_return'] = $data[0];
			$keyStatsArray[0]['min_portfolio_return'] = $data[1];

		}
		
		
		$query = "select max(profit_loss), min(profit_loss), max(holding_days), min(holding_days), max(r_multiple), min(r_multiple), avg(r_multiple), avg(holding_days) from turtle_transaction_pandl";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$keyStatsArray[0]['max_tran_return'] = $data[0];
			$keyStatsArray[0]['min_tran_return'] = $data[1];
			$keyStatsArray[0]['max_tran_holding_days'] = $data[2];
			$keyStatsArray[0]['min_tran_holding_days'] = $data[3];
			$keyStatsArray[0]['max_tran_r_multiple'] = $data[4];
			$keyStatsArray[0]['min_tran_r_multiple'] = $data[5];
			$keyStatsArray[0]['avg_tran_r_multiple'] = $data[6];
			$keyStatsArray[0]['avg_tran_holding_days'] = $data[7];
		}

		$query = "select max(profit_loss), min(profit_loss), max(holding_days), min(holding_days), max(r_multiple), min(r_multiple), avg(r_multiple), avg(holding_days) from turtle_transaction_pandl where profit_loss > 0";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {	
			$keyStatsArray[0]['max_pos_tran_return'] = $data[0];
			$keyStatsArray[0]['min_pos_tran_return'] = $data[1];
			$keyStatsArray[0]['max_pos_tran_holding_days'] = $data[2];
			$keyStatsArray[0]['min_pos_tran_holding_days'] = $data[3];
			$keyStatsArray[0]['max_pos_tran_r_multiple'] = $data[4];
			$keyStatsArray[0]['min_pos_tran_r_multiple'] = $data[5];
			$keyStatsArray[0]['avg_pos_tran_r_multiple'] = $data[6];
			$keyStatsArray[0]['avg_pos_tran_holding_days'] = $data[7];
		}

		$query = "select max(profit_loss), min(profit_loss), max(holding_days), min(holding_days), max(r_multiple), min(r_multiple), avg(r_multiple), avg(holding_days) from turtle_transaction_pandl where profit_loss < 0";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$keyStatsArray[0]['max_neg_tran_return'] = $data[0];
			$keyStatsArray[0]['min_neg_tran_return'] = $data[1];
			$keyStatsArray[0]['max_neg_tran_holding_days'] = $data[2];
			$keyStatsArray[0]['min_neg_tran_holding_days'] = $data[3];
			$keyStatsArray[0]['max_neg_tran_r_multiple'] = $data[4];
			$keyStatsArray[0]['min_neg_tran_r_multiple'] = $data[5];
			$keyStatsArray[0]['avg_neg_tran_r_multiple'] = $data[6];
			$keyStatsArray[0]['avg_neg_tran_holding_days'] = $data[7];
		}

		// Probablity calcutaion
		$query = "select count(*) as tran_count from turtle_transaction_pandl";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$keyStatsArray[0]['tran_count'] = $data[0];
		}
		
		
		$query = "select count(*) as total_pos_tran from turtle_transaction_pandl where profit_loss > 0";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$keyStatsArray[0]['total_pos_tran'] = $data[0];
		}

		$query = "select count(*) as total_neg_tran from turtle_transaction_pandl where profit_loss < 0";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$keyStatsArray[0]['total_neg_tran'] = $data[0];
		}
		
		$keyStatsArray[0]['pos_tran_probability'] = round(($keyStatsArray[0]['total_pos_tran']/$keyStatsArray[0]['tran_count'])*100, 2);
		$keyStatsArray[0]['neg_tran_probability'] = round(($keyStatsArray[0]['total_neg_tran']/$keyStatsArray[0]['tran_count'])*100, 2);
		

		echo json_encode($keyStatsArray);

		
	
	} elseif ($_GET['action'] == 'getRealTimeQuote') {
		$symbol = $_GET['symbol'];
	
		$quote = get_yahoo_rt_quote ($symbol);	
	
		echo json_encode($quote);
	}  elseif($_GET['action'] == 'test_turtle_portfolio_sell'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		$portfolioID = 1;
		turtle_portfolio_sell ($date, $portfolioID);
		
	}  elseif($_GET['action'] == 'test_crsi_sell'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		$portfolioID = 1;
		crsi_sell ($date, $portfolioID);
		
	}  elseif($_GET['action'] == 'test_turtle_portfolio_update_stop_loss'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
			
		turtle_portfolio_update_stop_loss($date); 
			
	}  elseif($_GET['action'] == 'test_turtle_portfolio_buy'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
			
		turtle_portfolio_buy($date);
			
	}  elseif($_GET['action'] == 'test_turtle_portfolio_pyramid_buy'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		
		turtle_portfolio_pyramid_buy ($date);
	}  elseif($_GET['action'] == 'get_current_portfolio_value'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		
		$value = get_real_time_turtle_portfolio_value();
		
		$json1 = array();
		$json1[0]['pvalue'] = $value ;
		
		
		echo json_encode($json1);
	}  elseif($_GET['action'] == 'get_current_portfolio_return'){ 
		global $original_investment;
		
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		
		
		$preturn = get_real_time_turtle_portfolio_return($original_investment);
		
		$json1 = array();
		$json1[0]['preturn'] = $preturn ;
		
		
		echo json_encode($json1);
	}   elseif($_GET['action'] == 'get_historical_portfolio_value'){ 
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		$portfolioID = 1;
		$value = get_historical_turtle_portfolio_value($date, $portfolioID);
		
		$json1 = array();
		$json1[0]['pvalue'] = $value ;
		
		
		echo json_encode($json1);
	}  elseif($_GET['action'] == 'get_historical_portfolio_return'){ 
		global $original_investment;
		
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		
		$portfolioID = 1;
		$preturn = get_historical_turtle_portfolio_value($date, $portfolioID);
		
		$preturn = ($preturn - $original_investment) / $original_investment * 100;


		$json1 = array();
		$json1[0]['preturn'] = $preturn ;
		
		
		echo json_encode($json1);
	}   elseif($_GET['action'] == 'simulate_1_day_trade'){ 
		$date = $_GET['date'];
		turtle_portfolio_sell ($date);		
		turtle_portfolio_pyramid_buy ($date);
		turtle_portfolio_buy($date);
		turtle_portfolio_update_stop_loss($date); 

	}  elseif($_GET['action'] == 'get_valid_trade_dates'){ 
		$start_date = $_GET['start_date'];
		$dateArray = array();
		$count = 0;

		$query = "select trade_date from price_history where symbol = 'AAPL' and trade_date >= '".$start_date."' order by trade_date desc";

		$result = queryMysql($query);

		while ($data = mysql_fetch_row($result)) {
			$dateArray[$count]['trade_date'] = $data[0];
			$count ++;

		}
		
		echo json_encode($dateArray);
		

		
	}  elseif ($_GET['action'] == 'getTransactionPandL') {
		$portfolioID = $_GET['portfolio_id'];
		
		if (!$portfolioID) {$portfolioID = 1;}

		// get all transactions p and l
		$query= "select symbol, holding_days, profit_loss, r_multiple from transactions1 where portfolio_id = ".$portfolioID." order by profit_loss desc";
		$query= "select xid, symbol, round(PnL*100/(buy_price * buy_shares), 2) as PnL, buy_date, buy_shares, buy_price, sell_date, sell_shares, sell_price from transactions1 where portfolio_id = ".$portfolioID." order by sell_date desc limit 100";

		
		
		$result = queryMysql($query);
		$ret = array();
		$ret2 = array();

	    $i = 0;
		while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	        	 $ret[$i][$key] = $value;
            }
        	$i++;
        }

        $ret2["dataset"] = $ret;


		echo json_encode($ret2);


	} elseif($_GET['action'] == 'simulate_range_trade'){ 
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
				
//		$spyReturn = array();
		$portfolioReturn = array();
		$retArray = array();
		$count = 0;
		$ADX_filter = "Off";		
		//$portfolioID = 1;
		$portfolioID = $_GET['portfolio_id'];
		
		if ($_GET['cash']) {$original_investment = $_GET['cash'];}

		$portfolio_table = "portfolio".$portfolioID;
		
		
		if (!$portfolioID) {
			$portfolioID = 1;
		}
		reset_portfolio($portfolioID);
		
		$dailyBuyList = "crsi_daily_buy_list";
		
		$dailyBuyList = "crsi_daily_buy_list".$portfolioID;
		

		### drop recreate daily buy list table
		$query = "drop table if exists ".$dailyBuyList;
		$result = queryMysql($query);

		$create_sql  = "CREATE TABLE $dailyBuyList ( ";
		$create_sql .= "portfolio_id int(11) DEFAULT NULL, ";
		$create_sql .= "rank int(11) NOT NULL DEFAULT '0', ";
		$create_sql .= "trade_date date DEFAULT NULL, ";
		$create_sql .= "symbol varchar(6) COLLATE latin1_german2_ci DEFAULT NULL, ";
		$create_sql .= "buy_price double DEFAULT NULL, ";
		$create_sql .= "UNIQUE KEY uniq_idx (portfolio_id,symbol), ";
		$create_sql .= "KEY pid_idex (portfolio_id)  ";
		$create_sql .= ") ENGINE=MEMORY "; 
		$result = queryMysql($create_sql);

		$tranHistory = "turtle_portfolio_transaction".$portfolioID;


		
		prepare_transactions_table ($portfolioID, $tranHistory, $portfolio_table);		
		
		$breakOutSignal = $_GET['breakoutSignal'];

		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}
		
		$ADX_filter = $_GET['adx_filter'];
		$breakOutSignal = $_GET['breakoutSignal'];
		$breakOutOrderBy = $_GET['breakoutOrderBy'];
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
		
		#print "enter crsi: $enterCRSI enterRange: $enterRange  enterLimit: $enterLimit  exitCRSI: $exitCRSI   order by: $orderBy <br />\n";

		#if (!$stopLoss) {
		#	$stopLoss = -20;
		#}
		if (!$orderBy) {
			$orderBy = "crsi desc";
		}



		//create temporary table to store daily pricing for comparisons		
		$simplePriceHistory = "simple_price_history";
		#$query = "drop table if exists ".$simplePriceHistory;
		#$result = queryMysql($query);
		#$query  = "create table ".$simplePriceHistory." select symbol, trade_date, trade_date_id, open, high, low, close, daily_change, pct_change,   ";
		#$query .= "55_DAY_HIGH, 20_DAY_HIGH, vsSpyRank, crsi from quotes_memory_memory where trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		#$result = queryMysql($query);


		$query = "select trade_date from quotes_memory where symbol = 'AAPL' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."' order by trade_date";
#print "$query <br />";
		$result = queryMysql($query);

		$dateArray = array();
		$dateCount = 0;

		while ($data = mysql_fetch_row($result)) {
			$dateArray[$dateCount]['trade_date'] = $data[0];
			$dateCount ++;
			#print "trade date in array: $data[0] <br />";
		}

		$count = 0;

		$minReturn = 100;
		$maxReturn = -100;

		for ($x = 0; $x < $dateCount-1; $x++){
		#while ($data = mysql_fetch_row($result)) {
		#	$trade_date = $data[0];
			$trade_date = $dateArray[$x]['trade_date'];
			$next_trade_date = 	$dateArray[$x+1]['trade_date'];

if ($showOutput) {
	print "trade date: $trade_date <br />";
}
			crsi_sell ($trade_date, $portfolioID, $tranHistory, $exitCRSI, $stopLoss, $next_trade_date);
			crsi_buy($trade_date, $breakOutSignal, $ADX_filter, $breakOutOrderBy, $portfolioID, $dailyBuyList, $enterCRSI, $enterRange, $enterLimit, $tranHistory, $orderBy, $next_trade_date);

			$value = get_historical_turtle_portfolio_value($trade_date, $portfolioID);
			$preturn = ($value - $original_investment) / $original_investment * 100;
			$dollar_return = $value - $original_investment;
		
			if ($preturn > $maxReturn) {$maxReturn = $preturn;};
			if ($preturn < $minReturn) {$minReturn = $preturn;};
			
			$portfolioReturn[$count]['trade_date'] = $trade_date;
			$portfolioReturn[$count]['return'] = $preturn;
			$portfolioReturn[$count]['value'] = $value;
			$portfolioReturn[$count]['maxReturn'] = $maxReturn;
			$portfolioReturn[$count]['minReturn'] = $minReturn;
			$portfolioReturn[$count]['dollar_return'] = $dollar_return;
		
			#$query2  = "insert into crsi_portfolio_performance (portfolio_id, trade_date, return_dollar, return_pct, portfolio_value, day_return, day_return_pct) ";
			#$query2 .= " values (".$portfolioID.", '".$portfolioReturn[$count]['trade_date']."', ".$portfolioReturn[$count]['dollar_return'].", ".$portfolioReturn[$count]['return'].", ".$portfolioReturn[$count]['value'].", null, null)";

			#if ($showOutput) {
			#	print "insert performance: $query2 <br />";
			#}
			#$result2 = queryMysql($query2);			

			$newDateStr = strtotime($trade_date);
			$newDateStr = $newDateStr * 1000 - 14400000;

			array_push($retArray, array($newDateStr, $preturn));
			
			$count ++;
		}
		
		## batch insert into crsi_portfolio_performance afterward
		$sql = array(); 
		foreach( $portfolioReturn as $row ) {
		    $sql[] = '('.$portfolioID.", '".mysql_real_escape_string($row['trade_date'])."', ".$row['dollar_return'].", ".$row['return'].", ".$row['value'].")";
		}
		try {
			mysql_query('INSERT INTO crsi_portfolio_performance (portfolio_id, trade_date, return_dollar, return_pct, portfolio_value) VALUES '.implode(',', $sql));
		} catch (Exception $e) {
			echo "Query: $query \n";
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
		foreach( $tranHistArray as $th ) {
			try {
				queryMysql($th);
			} catch (Exception $e) {
				echo "Query: $th \n";
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}
		
		
//print strftime('%c');
//print "number of dates: ";
//print $count;
//print "<BR> p return: $preturn ";
//print "<BR> Max return: $maxReturn ";
//print "<BR> Min return: $minReturn ";

//	$spyReturn = historical_stock_return("SPY", $start_date);
//print "array: ";
//print $retArray[1];
		//	echo json_encode($portfolioReturn);
		
		// update turtle_portfolio_performance table
/*		for ($z = 0; $z < $count; $z++)
		{
				$query = "insert into turtle_portfolio_performance values (1, '".$portfolioReturn[$z]['trade_date']."', ".$portfolioReturn[$z]['dollar_return'].", ".$portfolioReturn[$z]['return'].")";
			$result = queryMysql($query);

//print "<BR>query: ";
//print $query;
		//	$p=$i;
		}
*/

		mysql_close();
		echo json_encode($retArray);
		

	} elseif($_GET['action'] == 'reset_portfolio'){ 
		$cash = 1000000;
		$cash = $_GET['cash'];
		$pid = $_GET['portfolio_id'];
		print "cash: $cash \n";
		reset_portfolio($pid);
	}  elseif($_GET['action'] == 'get_num_of_trade_days'){ 		
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}

		$query = "select count(*) from quotes_memory where symbol = 'AAPL' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		$result = queryMysql($query);

		while ($data = mysql_fetch_row($result)) {
			$num_trade_days = $data[0];
		}
		
		echo json_encode( $num_trade_days);
	}  elseif($_GET['action'] == 'get_historical_stock_return'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
				
			$retArray = array();
			$stockRetArray = array();	

			$stockRetArray = historical_stock_return($symbol, $start_date, $end_date);
	
			$arrayLen = count($stockRetArray);
			
			for ($x = 0; $x < $arrayLen; $x++)
			{
				/*print "<BR>date: $stockRetArray[$x]  close: $stockRetArray[$x]['trade_date'] " ;
				print $stockRetArray[$x]['trade_date'];	
				print $stockRetArray[$x]['close'];
				print $stockRetArray[$x]['return'];
				print "<BR>";	
				*/
				$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;
				
				$preturn = $stockRetArray[$x]['return'] * 1 / 1;
				//print "return: $preturn";

				//array_push($retArray, array($newDateStr, $preturn));
				//array_push($retArray, array($newDateStr, $preturn, $stockRetArray[$x]['trade_date'], $stockRetArray[$x]['start_date'], $stockRetArray[$x]['start_price'], $stockRetArray[$x]['close']));
				//array_push($retArray, {$newDateStr, $preturn});
				array_push($retArray, array($newDateStr, $preturn));
			}			
	
			echo json_encode($retArray);
		
	}  elseif($_GET['action'] == 'get_stock_transaction_record'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$pid = $_GET['portfolio_id'];

			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$retArray = array();
			$stockRetArray = array();	
	
			$stockRetArray = stock_transaction_record($symbol, $start_date, $end_date, $pid);
	
			$arrayLen = count($stockRetArray);
			
			for ($x = 0; $x < $arrayLen; $x++)
			{
				$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;
				
				$price = $stockRetArray[$x]['price'] * 1 / 1;
				$title = $stockRetArray[$x]['trade_type'];
				$text = $stockRetArray[$x]['trade_type']." ".$stockRetArray[$x]['shares']." shares at ".$price;

				$eachRow = array();
				$eachRow['x'] = $newDateStr;
				$eachRow['y'] = $price;
				$eachRow['title'] = $title;
				$eachRow['text'] = $text;
				array_push($retArray, $eachRow);
			}			
	
			echo json_encode($retArray);
	} elseif ($_GET['action'] == 'get_swing_points'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$pid = $_GET['portfolio_id'];

			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = calculate_swing_points($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} elseif ($_GET['action'] == 'get_swing_points_and_trends'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$pid = $_GET['portfolio_id'];

			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = chart_swing_points($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} elseif ($_GET['action'] == 'get_trends'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$pid = $_GET['portfolio_id'];

			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = chart_trends($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} elseif ($_GET['action'] == 'test_historical_stock_price'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$e_date = $_GET['end_date'];
			$pid = $_GET['portfolio_id'];

			if (!$e_date) {
				$e_date = date("Y-m-d");  
			}
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = historical_stock_price($symbol, $start_date, $e_date, $pid);
			
			echo json_encode($swingArray);
	} elseif($_GET['action'] == 'jtableList'){ 
		if ($_REQUEST['jtSorting']) {
			$orderBy = " ORDER BY ".$_REQUEST['jtSorting'];
		}
		
		if ($_REQUEST['jtStartIndex'] > -1){
			$limit = " LIMIT ".$_REQUEST['jtStartIndex'];
		}
		
		if ($_REQUEST['jtPageSize']){
			$pageSize = ",".$_REQUEST['jtPageSize'];
		}
		
		
		#$query = $_GET['txtInputQuery']." ORDER BY ". $_REQUEST['jtSorting']. " LIMIT ". $_REQUEST['jtStartIndex'] . "," . $_REQUEST['jtPageSize'];

		$query = $_GET['txtInputQuery'].$orderBy.$limit.$pageSize;
		$query= stripslashes($query);
		
		#echo "$query ", PHP_EOL;
		
		$result = mysql_query($query);

		$ret = array();

	    $i = 0;
		while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	        	 $ret[$i][$key] = $value;

            }
        	$i++;
        }
          
        ## get total count
		$query = $_GET['txtInputQuery'];
		$query= stripslashes($query);
		$result = mysql_query($query);
        $i = mysql_num_rows($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $ret;
		$jTableResult['TotalRecordCount'] = $i;
		print json_encode($jTableResult);
	}	elseif($_GET['action'] == 'jtableList2'){ 
		if ($_REQUEST['jtSorting']) {
			$orderBy = " ORDER BY ".$_REQUEST['jtSorting'];
		}
		
		if ($_REQUEST['jtStartIndex'] > -1){
			$limit = " LIMIT ".$_REQUEST['jtStartIndex'];
		}
		
		if ($_REQUEST['jtPageSize']){
			$pageSize = ",".$_REQUEST['jtPageSize'];
		}
		
		
		#$query = $_GET['txtInputQuery']." ORDER BY ". $_REQUEST['jtSorting']. " LIMIT ". $_REQUEST['jtStartIndex'] . "," . $_REQUEST['jtPageSize'];

		$query = $_GET['txtInputQuery'].$orderBy.$limit.$pageSize;
		$query= stripslashes($query);
		
		#echo "$query ", PHP_EOL;
		
		$result = mysql_query($query);

		$ret = array();

	    $i = 0;
		while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	        	 $ret[$i][$key] = $value;

            }
        	$i++;
        }
          
        ## get total count
		$query = $_GET['txtInputQuery'];
		$query= stripslashes($query);
		$result = mysql_query($query);
        $i = mysql_num_rows($result);

		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $ret;
		#$jTableResult['TotalRecordCount'] = $i;
		print json_encode($jTableResult);
	} elseif($_GET['action'] == 'export_to_csv'){ 
	    $query = $_GET['txtInputQuery'];

		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
				
		// fetch the data
		mysql_connect('localhost', 'username', 'password');
		mysql_select_db('database');
		//$rows = mysql_query('SELECT symbol, trade_date, buy_price from crsi_daily_buy_list1');
		$rows = mysql_query($query);

		
		$columns = array();
		for($i = 0; $i < mysql_num_fields($rows); $i++) {
		    $field_info = mysql_fetch_field($rows, $i);
		    //echo "<th>{$field_info->name}</th>";
		    array_push ($columns, $field_info->name );
		}
		
		fputcsv($output, $columns);
				
		// loop over the rows, outputting them
		while ($row = mysql_fetch_assoc($rows)) fputcsv($output, $row);	
	}
	
	
}	

function historical_stock_return($symbol, $start_date, $end_date) {
		$perfArray = array();
		$count = 0;

		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}
		// get close price for symbol on starting date
		$query  = "select close from quotes_memory where symbol = '".$symbol."' and trade_date = ";
		$query .= "(select min(trade_date) from quotes_memory where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."')";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$startPrice = $data[0];
		}
		
		// get performance of stock for each trade date compared to price on starting day
		$query  = "select trade_date, close, ((close - ".$startPrice.")/".$startPrice." * 100), 50_MA, 200_MA, 55_DAY_HIGH, 20_DAY_LOW, 20_DAY_HIGH ";
		$query .= "from quotes_memory where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."' order by trade_date";		

		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$perfArray[$count]['trade_date'] = $data[0];
			$perfArray[$count]['close'] = $data[1];
			$perfArray[$count]['return'] = $data[2];
			$perfArray[$count]['50_MA'] = $data[3];
			$perfArray[$count]['200_MA'] = $data[4];
			$perfArray[$count]['55_DAY_HIGH'] = $data[5];
			$perfArray[$count]['20_DAY_LOW'] = $data[6];
			$perfArray[$count]['20_DAY_HIGH'] = $data[7];

			//$perfArray[$count]['start_date'] = $start_date;
			//$perfArray[$count]['start_price'] = $startPrice;


			$count ++;	
		}
		
		return $perfArray;

}

function historical_stock_price($symbol, $start_date, $end_date) {
		$perfArray = array();
		$count = 0;
		
		// get close price for symbol on starting date
		$query  = "select close from quotes_memory where symbol = '".$symbol."' and trade_date = ";
		$query .= "(select min(trade_date) from quotes_memory where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."')";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$startPrice = $data[0];
		}
		
		// get performance of stock for each trade date compared to price on starting day
		$query  = "select trade_date, close, ((close - ".$startPrice.")/".$startPrice." * 100), open, high, low, volume, avg_volume, relative_avg_vol, ATR ";
		$query .= "from quotes_memory where symbol = '".$symbol."' and trade_date > '".$start_date."' and trade_date <= '".$end_date."'";		
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$perfArray[$count]['trade_date'] = $data[0];
			$perfArray[$count]['close'] = $data[1];
			$perfArray[$count]['return'] = $data[2];
			$perfArray[$count]['open'] = $data[3];
			$perfArray[$count]['high'] = $data[4];
			$perfArray[$count]['low'] = $data[5];
			$perfArray[$count]['volume'] = $data[6];
			$perfArray[$count]['avg_volume'] = $data[7];
			$perfArray[$count]['relative_avg_vol'] = $data[8];
			$perfArray[$count]['ATR'] = $data[9];


			$count ++;	
		}
		
		return $perfArray;
}

function stock_transaction_record ($symbol, $start_date, $end_date, $pid) {
		global $portfolio_table;
		
		$stockArray = array();
		$count = 0;
		
		// get close price for symbol on starting date
		$query  = "select trade_type, trade_date, shares, price, risk, risk_pct from turtle_portfolio_transaction where symbol = '".$symbol."' and portfolio_id =   ".$pid;
		$result = queryMysql($query);

		while ($data = mysql_fetch_row($result)) {
			$stockArray[$count]['trade_type'] = $data[0];
			$stockArray[$count]['trade_date'] = $data[1];
			$stockArray[$count]['shares'] = $data[2];
			$stockArray[$count]['price'] = $data[3];
			$stockArray[$count]['risk'] = $data[4];
			$stockArray[$count]['risk_pct'] = $data[5];

			$count ++;	
		}
		
		return $stockArray;
}


function crsi_sell ($date, $portfolioID, $tranHistory, $exitCRSI, $stopLoss, $next_date) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_multiplier;
		global $showOutput;
		global $commission;
		global $tranHistArray;
		global $connection;
		global $portfolio_table;
		
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		
		#$previous_trade_date = getPreviousDate($date);
		
		$sell_price = 0;

		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares, risk, risk_pct, high, vsSpyRank, crsi ";
		$query .= "from $portfolio_table a, quotes_memory b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.crsi > $exitCRSI ";
		$query .= "and b.trade_date = '".$date."'";
		
		if ($stopLoss) {
			$query .= "union
						select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, 
							stop_buy, shares, risk, risk_pct, high, vsSpyRank, crsi 
						from $portfolio_table a, quotes_memory b
						where a.symbol = b.symbol 
						and a.portfolio_id = $portfolioID
						and a.symbol != 'CASH' 
						and ((b.adj_close - a.cost_basis) * 100 / a.cost_basis ) < $stopLoss			
						and b.trade_date = '$date'				
			";
		}
		

		
		#print "$query <br />\n\n";
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
		
/*		
		$spcall = "call SP_get_above_crsi('$date', $portfolioID, $exitCRSI)";
		$resulti = "";
		//run the store proc
		$resulti = mysqli_query($connection, $spcall) or die("Query fail: " . mysqli_error($connection));
		
		//loop the result set
		$ph = array();
		$i = 0;
		
		while ($data = mysqli_fetch_array($resulti)){
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

#		    echo $data[0] . " - " . + $data[1]; 
		}
		
		while($connection->more_results())
		{
		    $connection->next_result();
		    if($res = $connection->store_result()) // added closing bracket
		    {
		        $res->free(); 
		    }
		}				
*/

		$total_sale = 0;
		
		$delete_query = "delete from $portfolio_table where portfolio_id = ".$portfolioID." and symbol in (NULL ";
		
		for ($x=0; $x < $i; $x++) {			
			$sell_price = $ph[$x]['close'];
			$sell_price = getStockPrice($next_date, $ph[$x]['symbol'], 'open');
			#$sell_price = getStockPrice($date, $ph[$x]['symbol'], 'close');

			#$sell_price = $ph[$x]['open'];

			# calculate sales proceed for stock, stock to be sold at stop_loss price 
			//$stock_sales = $ph[$x]['shares'] * $ph[$x]['stop_loss'];
			$stock_sales = $ph[$x]['shares'] * $sell_price - $commission;
			$total_sale += $stock_sales;
			
			$delete_query .= ",'".$ph[$x]['symbol']."'";

			$insert_transaction_history = "insert into $tranHistory values (".$portfolioID.", '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].", null, null, null, ".$ph[$x]['vsSpyRank'].")";

			array_push($tranHistArray, $insert_transaction_history);

		}	
		
		$delete_query .= ")";	 
		$result = queryMysql($delete_query);
 
		
		$update_portfolio_query = "update $portfolio_table set shares = shares + ".$total_sale." where portfolio_id = ".$portfolioID." and symbol ='CASH'";
		$result = queryMysql($update_portfolio_query);
		
}  	
	

function crsi_buy ($date, $breakOutSignal, $ADX_filter, $breakOutOrderBy, $portfolioID, $dailyBuyList, $enterCRSI, $enterRange, $enterLimit, $tranHistory, $orderBy, $next_date) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_multiplier;
		global $portfolio_tbl;
		global $showOutput;
		global $commission;
		global $tranHistArray;
		global $portfolio_table;
		
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		if (!$dailyBuyList)
		{
			$dailyBuyList = "crsi_daily_buy_list";
		}
		
		$breakOutStockArray = getBuyList ($date, $enterCRSI, $enterRange, $portfolioID, $dailyBuyList, $enterLimit, $orderBy); 

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

		$portfolio_value = get_historical_turtle_portfolio_value($date, $portfolioID);

		$query = "select shares from $portfolio_table where symbol = 'CASH' and portfolio_id = ".$portfolioID;
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$cash = $data[0];
		}		

		$current_risk = get_portfolio_risk($date, $portfolioID);

#print "current risk in the beginning: $current_risk \n";
		$buy_array = array();
		
		


		for ($x=1; $x < $len_array; $x++) {
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
				
/*				$purchase_value = $num_shares * $breakOutStockArray[$x][$breakOutSignal];
				$stop_loss = $breakOutStockArray[$x][$breakOutSignal] - (2*$current_N);
				$stop_buy = $breakOutStockArray[$x][$breakOutSignal] + $current_N;
*/
#print "date: $date	portfolio value: $portfolio_value	cash: $cash	risk value: $risk_value	current N: $current_N	symbol: ".$breakOutStockArray[$x]['symbol']."\n";

				## find purchase price 
				## if target buy price > open (gap down at open, past buy price)
				##	set purchase price = (open + low ) / 2
				## if open > target buy price > close (regular price action
				##  set purchase price = target buy price
				
	            if ($breakOutStockArray[$x]['buy_price'] > $breakOutStockArray[$x]['open']) {
		            $breakOutStockArray[$x]['purchase_price'] = getStockPrice($date, $breakOutStockArray[$x]['symbol'], 'close');

		            $s = $breakOutStockArray[$x]['symbol'];
		            $p = $breakOutStockArray[$x]['purchase_price'] ;
		            $b = $breakOutStockArray[$x]['buy_price'];
					#print "buy price > open: $s buy price = $b purchase price = $p <br />";
	            } /*elseif (($breakOutStockArray[$x]['buy_price'] > $breakOutStockArray[$x]['close']) && ($breakOutStockArray[$x]['buy_price'] < $breakOutStockArray[$x]['open']) ) {
		            $breakOutStockArray[$x]['purchase_price'] = $breakOutStockArray[$x]['buy_price'];
		            $s = $breakOutStockArray[$x]['symbol'];
		            $p = $breakOutStockArray[$x]['purchase_price'] ;
		            $b = $breakOutStockArray[$x]['buy_price'];
					print "buy open > price > close : $s buy price = $b purchase price = $p <br />";
	            } */
	            else {
					$breakOutStockArray[$x]['purchase_price'] = getStockPrice($date, $breakOutStockArray[$x]['symbol'], 'close');
		            		            
		            $s = $breakOutStockArray[$x]['symbol'];
		            $p = $breakOutStockArray[$x]['purchase_price'] ;
		            $b = $breakOutStockArray[$x]['buy_price'];					
		            $h = $breakOutStockArray[$x]['high'];					
		            $l = $breakOutStockArray[$x]['low'];					
		            $o = $breakOutStockArray[$x]['open'];					
		            $c = $breakOutStockArray[$x]['close'];					
		            #print "regular price: $o high $h low $l  close $c : $s buy price = $b purchase price = $p <br />";

		            
		            
	            }	
	            
	            ## set purchase price to next day open price
	            $breakOutStockArray[$x]['purchase_price'] = getStockPrice($next_date, $breakOutStockArray[$x]['symbol'], 'open');

                if (!$breakOutStockArray[$x]['purchase_price'] ) {break;}


	            $h = $breakOutStockArray[$x]['high'];					
	            $l = $breakOutStockArray[$x]['low'];					
	            $o = $breakOutStockArray[$x]['open'];					
	            $c = $breakOutStockArray[$x]['close'];					

	            $p = $breakOutStockArray[$x]['purchase_price'] ;
	            #print "trade date: $date symbol $s close: $c open: $o purchase price = $p <br /> \n";


				#$breakOutStockArray[$x]['purchase_price'] = $breakOutStockArray[$x]['buy_price'];
		
				$purchase_value = $num_shares * $breakOutStockArray[$x]['purchase_price'] + $commission;
				$stop_loss = $breakOutStockArray[$x]['purchase_price'] - ($stop_loss_multiplier*$current_N);
				$stop_buy = $breakOutStockArray[$x]['purchase_price'] + $current_N;

				#$current_risk = get_current_risk($portfolioID);
				#$current_risk = get_portfolio_risk($date, $portfolioID);
#print "date: $date symbol: ".$breakOutStockArray[$x]['symbol']." current risk :$current_risk \n";
				
				if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
					$cash = $cash - $purchase_value;
					$risk_dollar = $num_shares * ($stop_loss_multiplier * $current_N);
					$risk_pct = ($risk_dollar / $portfolio_value) * 100;

					// insert into turtle_portfolio
					$my_sql  = "insert into $portfolio_table (portfolio_id, symbol, last_price, shares, cost_basis, stop_loss, stop_buy, risk, risk_pct) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= $breakOutStockArray[$x]['adj_close'].", ";
					$my_sql .= $num_shares.", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $stop_loss.", ";
					$my_sql .= $stop_buy.", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";

					array_push ($buy_array, $my_sql);
					
					$current_risk += $risk_pct;
					#print "current risk after adding risk dollar: current risk: $current_risk	risk pct added: $risk_pct max risk: $max_risk \n";
#$showOutput = true;
if ($showOutput) {
	print "num: $x sql: $my_sql <br />";
	$p = $breakOutStockArray[$x]['purchase_price'];
	print "cash: $cash purchase price: $purchase_value num shares: $num_shares purchase value: $p current n: $current_N<br />";
	
}
					#$result = queryMysql($my_sql);

					// trigger on transaction table
					/*
DELIMITER $$
CREATE TRIGGER ins_buy AFTER INSERT ON turtle_portfolio_transaction 
	FOR EACH ROW 
		BEGIN
			DECLARE xid int;
			IF (NEW.trade_type = 'BUY')
			THEN
				insert IGNORE into open_buy_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price) values (NEW.portfolio_id, NEW.symbol, NEW.trade_type, NEW.trade_date, NEW.shares, NEW.price);
			ELSE
				insert IGNORE into transactions (portfolio_id, symbol, sell_date, sell_shares, sell_price) values (NEW.portfolio_id, NEW.symbol, NEW.trade_date, NEW.shares, NEW.price);
				SET xid = select 1 from transactions;
				update transactions, open_buy_transaction set transactions.buy_date =  open_buy_transaction.trade_date, transactions.buy_shares = open_buy_transaction.shares, transactions.buy_price=open_buy_transaction.price where transactions.symbol = open_buy_transaction and transactions.xid = xid  ;
				
				delete from open_buy_transaction where symbol = NEW.symbol
			END IF;
		END;
$$
DELIMITER ;

						
						*/

					// insert into transaction history table
					$my_sql  = "insert into $tranHistory (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct, vsSpyRank) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= "'BUY', ";
					$my_sql .= "'".$date."', ";
					$my_sql .= $num_shares.", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct.", ";
					$my_sql .= $breakOutStockArray[$x]['vsSpyRank']." )";
					
					array_push($tranHistArray, $my_sql);
if ($showOutput) {
	print "$my_sql <br />";	
}
					#try {
					#	$result = queryMysql($my_sql);
					#} catch (Exception $e) {
					#   echo "query: $my_sql \n";
				 	#   echo 'Caught exception: ',  $e->getMessage(), "\n";
					#}
					
					// update cash position
					#$my_sql  = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
					#$result = queryMysql($my_sql);
				
				} /*elseif ($current_risk > $max_risk ) {
					$s = $breakOutStockArray[$x]['symbol'];					
				} */ else {
					#print "trade date: $date ran out of money </ br> \n";
					break;
				} 
				
				
				$pyramid_mode ++;
		}

		$my_sql  = "update $portfolio_table set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
		$result = queryMysql($my_sql);
		
		foreach( $buy_array as $buy ) {
			try {
				queryMysql($buy);
			} catch (Exception $e) {
				echo "Query: $buy \n";
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
		}



			populateDailyBuyList ($date, $enterCRSI, $enterRange, $portfolioID, $dailyBuyList, $enterLimit);

} 


// get breakout stock with weighted critiria
// put stocks in the daily buy list
function getBuyList ($today_date, $crsiThreshold, $enterRange, $portfolioID, $dailyBuyList, $pctLimitBelow, $orderBy) {
		global $simplePriceHistory;		
		global $portfolio_table;

		if (!$portfolioID) {
				$portfolioID = 1;
		}
		if (!$dailyBuyList) {
				$dailyBuyList = "crsi_daily_buy_list";
		}	

		$masterRankByResult = array();
		$rankResult = array();
		
		#$previous_trade_date = getPreviousDate($today_date);

		$query  = "select a.symbol, a.trade_date, a.open, a.high, a.low, a.close, a.pct_change, ";
		$query .= "a.ATR, a.55_DAY_HIGH, a.20_DAY_HIGH, a.50_MA, a.200_MA, a.vsSpyRank, a.crsi, c.buy_price, a.adj_close ";
		#$query .= "from price_history a, $dailyBuyList c ";
		$query .= "from quotes_memory a, $dailyBuyList c ";
		$query .= " where a.trade_date = '".$today_date."'";
		$query .= " and c.portfolio_id = ".$portfolioID;
		$query .= " and a.symbol = c.symbol ";
#		$query .= " and a.low  < c.buy_price";
		$query .= " and a.low * (a.adj_close / a.close)  < c.buy_price";
		$query .= " and a.symbol not in (select symbol from $portfolio_table where portfolio_id = ".$portfolioID.") ";
		$query .= " and a.ATR > 0 ";
		#$query .= " order by a.crsi asc";
		$query .= " order by $orderBy";

#print " $query <br />\n\n";
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
	           
	            if ($tmpArray[$i]['buy_price'] > $tmpArray[$i]['open']) {
		            $tmpArray[$i]['purchase_price'] = ($tmpArray[$i]['open'] + $tmpArray[$i]['low'])/2;
	            } elseif (($tmpArray[$i]['buy_price'] > $tmpArray[$i]['close']) && ($tmpArray[$i]['buy_price'] < $tmpArray[$i]['open']) ) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['buy_price'];
	            }		            
	            
	            if (!$tmpArray[$i]['purchase_price']){
		            $tmpArray[$i]['purchase_price'] = ($tmpArray[$i]['high'] + $tmpArray[$i]['low'])/2;
		            
	            }
	            
	        	$i++;
	        }
	       
	        $rankResult[] = $tmpRankArray;
		
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

		$finalRet = "";

		foreach ($combineRank as $key => $value)
		{
			$keyPos = $tmpRankArray[$key];
			$finalRet[$i] = $tmpArray[$keyPos];
			$i++;
		}

	return $finalRet;
}

## generate daily buy list base on if stock price breaches crsi threshold and set limit buy price

function populateDailyBuyList ($today_date, $crsiThreshold, $enterRange, $portfolioID, $dailyBuyList, $pctLimitBelow) {
			global $showOutput;
			global $portfolio_table;
			
			if (!$portfolioID) {
				$portfolioID = 1;
			}
			if (!$dailyBuyList) {
				$dailyBuyList = "crsi_daily_buy_list";
			}
			if (!$crsiThreshold) {
				$crsiThreshold = 20;
			}
			if (!$pctLimitBelow) {
				$pctLimitBelow = 2;
			}			
			
			## only keep buy list for 1 day
			##$query  = "delete from $dailyBuyList where portfolio_id = $portfolioID ";
			##$query .= "and exists (select 1 from price_history where trade_date = '$today_date' and crsi > 50 and price_history.symbol = $dailyBuyList.symbol  )";
			##$query = stripslashes($query);
			##$result = queryMysql($query);
			
			
			//clear daily buy list
			#$query = "delete from ".$dailyBuyList." where portfolio_id = ".$portfolioID;
			#$result = queryMysql($query);
			// reset identity column
			#$query = "alter table ".$dailyBuyList." auto_increment = 1";
			#$result = queryMysql($query);
			$query = "insert into ".$dailyBuyList." (portfolio_id, trade_date, symbol, buy_price) ";
			$query .= "select ".$portfolioID.", a.trade_date, a.symbol, a.adj_close*(100-".$pctLimitBelow.")/100";
			#$query .= " from price_history a, stock_list b ";
			$query .= " from quotes_memory a, stock_list b ";
			$query .= "where a.trade_date = '".$today_date."' ";
			$query .= "and a.symbol = b.symbol ";
			$query .= "and a.symbol not in (select symbol from $dailyBuyList where portfolio_id = $portfolioID )";
			$query .= " and a.crsi < $crsiThreshold ";
			$query .= " and a.crsi > 0 ";
			$query .= " and (a.close-a.low)*100/(a.high-a.close) < $enterRange ";
			$query .= " order by a.crsi asc";
			$query = stripslashes($query);
#print "$query <br />\n\n";
			$result = queryMysql($query);

			$query  = "delete from $dailyBuyList where portfolio_id = $portfolioID ";
			#$query .= "and exists (select 1 from price_history where trade_date = '$today_date' and crsi > 50 and price_history.symbol = $dailyBuyList.symbol  )";
			$query .= "and exists (select 1 from quotes_memory where trade_date = '$today_date' and crsi > 50 and quotes_memory.symbol = $dailyBuyList.symbol  )";

			$query = stripslashes($query);
			$result = queryMysql($query);
#print "$query <br />";
	
			$query  = "delete from $dailyBuyList where portfolio_id = $portfolioID ";
			$query .= "and exists (select 1 from $portfolio_table where portfolio_id = $portfolioID and $portfolio_table.symbol = $dailyBuyList.symbol )";
			$query = stripslashes($query);
			$result = queryMysql($query);
#print "$query <br />";
}

function updateDailyBuyRank ($startDate, $endDate, $movingAvg, $rankAndWeightArray, $portfolioID) {
			if (!$portfolioID) {
				$portfolioID = 1;
			}
			
			$tmpDailyBuyList = "turtle_daily_buy_list_tmp";
			$dailyRankTable = "turtle_daily_vs_spy_ranking";
			
			//select all trade_dates between start and end date
			#$query = "select trade_date from price_history where symbol='AAPL' and trade_date between '".$startDate."' ";
			$query = "select trade_date from quotes_memory where symbol='AAPL' and trade_date between '".$startDate."' ";
			
			$query .= "and '".$endDate."' ";
			
			$result = queryMysql($query);

			$count = 0;
			while ($tmp_data = mysql_fetch_row($result)) {
				$trade_date_array[$count] = $tmp_data[0];
				$count ++;

			}
			
			
			for ($x=0; $x<$count; $x++)
			{
				//clear daily buy list
				$query = "delete from ".$tmpDailyBuyList." where portfolio_id = ".$portfolioID;
				$result = queryMysql($query);
				// reset identity column
				$query = "alter table ".$tmpDailyBuyList." auto_increment = 1";
				$result = queryMysql($query);
				
				$query = "insert into ".$tmpDailyBuyList." (portfolio_id, trade_date, symbol, buy_price) ";
				$query .= "select ".$portfolioID.", a.trade_date, a.symbol, a.".$movingAvg;
				#$query .= " from price_history a, stock_list b ";
				$query .= " from quotes_memory a, stock_list b ";
				$query .= "where a.trade_date = '".$trade_date_array[$x]."' ";
				$query .= "and a.symbol = b.symbol ";
				$query .= " and a.".$movingAvg." > 0 ";
				$query .= " order by vsSpyEMA desc";
	//print "$query \n";
				$query = stripslashes($query);
				$result = queryMysql($query);

				print $trade_date_array[$x];
				print "\n";

				
				#$query  = "update price_history a, ".$tmpDailyBuyList." b set a.vsSpyRank = b.rank ";
				$query  = "update quotes_memory a, ".$tmpDailyBuyList." b set a.vsSpyRank = b.rank ";

				$query .= "where a.symbol = b.symbol and a.trade_date = b.trade_date ";
		
				$result = queryMysql($query);			
			}
}



function get_real_time_turtle_portfolio_value() {
	global $portfolio_table;
	
	$my_sql = "select sum(a.shares * b.last_price) from $portfolio_table a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	
	$result = queryMysql($my_sql);
	
	while ($data = mysql_fetch_row($result)) {
		$value = $data[0];
	}
	
	return $value;
}

function get_historical_turtle_portfolio_value($trade_date, $portfolioID) {
	global $portfolio_table;

	//$my_sql = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	##$my_sql  = "select sum(a.shares * b.close) from turtle_portfolio a, price_history b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' and ";
	
	$my_sql  = "select sum(a.shares * b.close) from $portfolio_table a, quotes_memory b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' and ";
	$my_sql .= "b.trade_date = '".$trade_date."' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from $portfolio_table where symbol = 'CASH' and portfolio_id = ".$portfolioID;

	## use adj_close
	$my_sql  = "select sum(a.shares * b.adj_close) from $portfolio_table a, quotes_memory b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' and ";
	$my_sql .= "b.trade_date = '".$trade_date."' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from $portfolio_table where symbol = 'CASH' and portfolio_id = ".$portfolioID;


	$result = queryMysql($my_sql);
	$value=0;
	
	while ($data = mysql_fetch_row($result)) {
		$value += $data[0];
	}
	
	return $value;
}
 
function get_real_time_turtle_portfolio_return($original_cash) {
	
	$current_value = get_real_time_turtle_portfolio_value();
	
	$preturn = ($current_value - $original_cash) / $original_cash * 100;
	
	return $preturn;
	
}

function reset_portfolio($pid) {
		global $original_investment;
		global $portfolio_table;
		
		$cash = $original_investment;
		if (!$pid)
		{
			$pid = 1;
		}
		$portfolio_table = "portfolio".$pid;

		$query = "delete from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);
			
		$query = "delete from turtle_portfolio_transaction where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "insert into turtle_portfolio (portfolio_id, symbol, shares) value (".$pid.", 'CASH', ".$cash.") on duplicate key update shares = ".$cash;
		$result = queryMysql($query);				
		
		$drop_sql = "drop table if exists ".$portfolio_table;
		$result = queryMysql($drop_sql);
		
		$create_sql = "CREATE TABLE $portfolio_table (
			  portfolio_id int(11) DEFAULT NULL,
			  symbol varchar(10) DEFAULT NULL,
			  last_price double DEFAULT NULL,
			  shares int(11) DEFAULT NULL,
			  cost_basis decimal(6,2) DEFAULT NULL,
			  overall_return decimal(5,2) DEFAULT NULL,
			  stop_loss double DEFAULT NULL,
			  stop_buy double DEFAULT NULL,
			  risk double DEFAULT NULL,
			  risk_pct decimal(5,2) DEFAULT NULL,
			  profit_loss decimal(10,2) DEFAULT NULL,
			  PRIMARY KEY (portfolio_id, symbol)
	
			) ENGINE=MEMORY";
			
		$result = queryMysql($create_sql);		

		$query = "insert into $portfolio_table (portfolio_id, symbol, shares) value (".$pid.", 'CASH', ".$cash.") on duplicate key update shares = ".$cash;
		$result = queryMysql($query);				

		$query = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);	

		$query = "delete from turtle_portfolio_performance where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "delete from crsi_daily_buy_list where portfolio_id = ".$pid;
		$result = queryMysql($query);

		$query = "delete from crsi_portfolio_performance where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		#$query = "ALTER TABLE crsi_portfolio_performance AUTO_INCREMENT = 1 ";
		#$result = queryMysql($query);
				
		$query = "delete from turtle_transaction_pandl where portfolio_id = ".$pid;
		$result = queryMysql($query);

		$query = "delete from transactions where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		#$query = "ALTER TABLE transactions AUTO_INCREMENT = 1 ";
		#$result = queryMysql($query);

		$query = "delete from open_buy_transaction where portfolio_id = ".$pid;
		$result = queryMysql($query);
		


	
}
 
function get_current_risk($portfolioID) {
	$p_value =get_real_time_turtle_portfolio_value($portfolioID);
	
	$tmp_sql = "select sum(risk) from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$portfolioID;
	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$r_value = $tmp_data[0];
	}
	
	$current_risk = ($r_value / $p_value) * 100;
	
	return $current_risk;
	
}

function get_portfolio_risk($trade_date, $portfolioID) {
	#$p_value =get_real_time_turtle_portfolio_value($portfolioID);
	$p_value =get_historical_turtle_portfolio_value($trade_date, $portfolioID);

	
	$tmp_sql = "select sum(risk) from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$portfolioID;
	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$r_value = $tmp_data[0];
	}
	
	$current_risk = ($r_value / $p_value) * 100;
	
	return $current_risk;
	
}


function calculate_ADX ($symbol, $trade_date_id, $smooth_constant) {
		// Idea of ADX
		// ADX > 30 = Strong trend, ADX < 20 = weak trend (take profit sooner)
		
		
		/* 
			General Idea
		 1. don't use ADX when ADX is below both DM+ and DM- line
		 2. As long as ADX is rising, any level of ADX above 15 is a trend
		 3. Greater increase in the ADX, stronger the trend
		 4. Decrease in ADX means trend is weakening
		 5. When ADX is rising, indicators such as overbought/oversold will not work. Oscillators work only when ADX is falling
		 
		 Specific:
		 1. Long
		 		- When DI+ > DI - AND high of previous day is penetrated
		 		
		 	Short
		 		- When DI- > DI+ and low of previous day is penetrated
		 2. Enter trade when ADX increase by more than 4 points in 2 days
		 3. Enter trade when ADX reach highest value of last 10 days
	
		 */
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		//$symbol = $_GET['symbol'];
		$trade_date_id_30_day_prior = $trade_date_id - 100;
		if ($smooth_constant <= 0) {$smooth_constant = 14;}

		#$query  = "select trade_date, high, low, close, TR, ATR from price_history where symbol = '".$symbol."' ";
		$query  = "select trade_date, high, low, close, TR, ATR from quotes_memory where symbol = '".$symbol."' ";

		$query .= "and trade_date_id between ".$trade_date_id_30_day_prior." and ".$trade_date_id;
		$query .= " order by trade_date_id DESC ";


		$query= stripslashes($query);
		$result = queryMysql($query);

		$json_result = array();
		$i = 0;
		
  	  	while ($row = mysql_fetch_assoc($result)) {
    		$rowRet = array();
    		foreach ($row as $key => $value) {
   	         	$ret[$i][$key] = $value;
            }
            
        	//$ret[i] = $rowRet;
        	$i++;
        }


        // +DM = Today's High - Yesterday's High (when price moves upward)
        // -DM = Yesterday's Low - Today's Low (when price moves downward)
		for ($x = 0; $x < $i-1; $x++)
		{
			 // current high > prior high
			 $upMove = $ret[$x]["high"] - $ret[$x+1]["high"];
			 // prior low < current low
			 $downMove = $ret[$x+1]["low"] - $ret[$x]["low"];

//			 if (abs($upMove) > abs($downMove) && ($upMove > 0)) {
			 if ($upMove > $downMove && $upMove > 0) {
				 $ret[$x]["plusDM"] = $upMove;
			 } else {	
				 $ret[$x]["plusDM"] = 0;
			 }
//			 if (abs($upMove) < abs($downMove) && ($downMove > 0)) {
			 if ($upMove < $downMove && $downMove > 0) {
				 $ret[$x]["negDM"] = $downMove;
			 } else {
				 $ret[$x]["negDM"] = 0 ;
			 }
			
		}
		
		$arrayLen = count($ret);

		for ($x=$arrayLen-15; $x >= 0; $x--)
		{
			$ret[$x]["plusDM14"] = ($ret[$x+1]["plusDM14"] - ($ret[$x+1]["plusDM14"] / 14)) + $ret[$x]["plusDM"];
			$ret[$x]["negDM14"] = ($ret[$x+1]["negDM14"] - ($ret[$x+1]["negDM14"] / 14)) + $ret[$x]["negDM"];
			$ret[$x]["TR14"] = ($ret[$x+1]["TR14"] - ($ret[$x+1]["TR14"] / 14)) + $ret[$x]["TR"];

			$ret[$x]["plusDI14"] = ($ret[$x]["plusDM14"] / $ret[$x]["TR14"]) * 100;
			$ret[$x]["negDI14"] = ($ret[$x]["negDM14"] / $ret[$x]["TR14"]) * 100;
		
			// calculate ADX			
			// Calculate the absolute DI Difference (+DI14 - -DI14) 	
			$ret[$x]["DIDiff"] = abs($ret[$x]["plusDI14"] - $ret[$x]["negDI14"]);
			// Calculate DX = DI Difference divided by the sum of +DI14 and -DI14
			if (($ret[$x]["plusDI14"] + $ret[$x]["negDI14"]) > 0 ) {
				$ret[$x]["DX"] = ($ret[$x]["DIDiff"]  / ($ret[$x]["plusDI14"] + $ret[$x]["negDI14"])) * 100;			
			} else {
				$ret[$x]["DX"] = 0;
			}
		}

		//Calculate ADX = the exponential moving average of DX			
		for ($x=$arrayLen-29; $x >= 0; $x--)
		{
			$ret[$x]["ADX"] = ($ret[$x]["DX"]+ ($ret[$x+1]["ADX"])*13)/14;
			
			// calculate daily difference between ADX in %
			if ($ret[$x+1]["ADX"] > 0) {
				$ret[$x]["changeADX"] = (($ret[$x]["ADX"] - $ret[$x+1]["ADX"])/$ret[$x+1]["ADX"]) * 100;			
			} else {
				$ret[$x]["changeADX"] = 0;
			}
								
		}

		//echo json_encode($ret);		
		return $ret[0];
}
 
function mysql_resultTo2DAssocArray_JGrid ( $result) {
    $i=0;
    $ret = array();
    $ret2 = array();
	$ret3 = array();
    $colN = array();
    $colM = array();
    $ret4=array();
   
	$j = 0;
	while ($j < mysql_num_fields($result)) {
    	$meta = mysql_fetch_field($result, $j);
    	if (!$meta) {
    	}
    
    	array_push ($colN, $meta->name);
    	if ($meta->type == "string") {
    		$colM[$j]["edittype"] = "text";
    		if ($meta->name == "symbol") {
	    		$colM[$j]["width"] = 50;	
    		}
    	} elseif ($meta->type == "int" ) {
    		$colM[$j]["editype"] = "integer";
    		$colM[$j]["width"] = 100;
    	} else {
    		$colM[$j]["editype"] = $meta->type;
        	$colM[$j]["width"] = 100;
    	}
    	
   		$colM[$j]["name"] = $meta -> name;
    
    	$j++;
    
	}

    while ($row = mysql_fetch_assoc($result)) {
    	$rowRet = array();
    	foreach ($row as $key => $value) {
   	         //$rowRet[] = $value;
   	         $ret[$i][$key] = $value;

            }
        //$ret[i] = $rowRet;
        $i++;
        }

	  $ret2["dataset"] = $ret;
	  $ret2["page"] = 1;
	  $ret2["records"] = mysql_num_rows($result);
	  $ret2["rows"] = 50;
	  $ret2["sidx"] = null;
	  $ret2["sord"] = "asc";
	  $ret2["total"] = mysql_num_rows($result);

	  $ret3["JSON"] = "success";
	  $ret3["colModel"] = $colM;
	  $ret3["colNames"] = $colN;
	  $ret3["gridModel"] = $ret2;

	return ($ret3);
}

function prepare_transactions_table ($pid, $tranHist) {

	$open_buy_table = "open_buy_transaction".$pid;
	$tran_table = "transactions".$pid;
	
	$drop_sql = "drop table if exists $open_buy_table";
	$result = queryMysql($drop_sql);
	
	$drop_sql = "drop table if exists $tran_table";
	$result = queryMysql($drop_sql);

	$drop_sql = "drop table if exists ".$tranHist;
	$result = queryMysql($drop_sql);



	$create_sql = "
		CREATE TABLE $tranHist (
		  portfolio_id int(11) NOT NULL,
		  symbol varchar(6) COLLATE latin1_german2_ci NOT NULL,
		  trade_type varchar(10) COLLATE latin1_german2_ci NOT NULL,
		  trade_date date NOT NULL,
		  shares double NOT NULL,
		  price double NOT NULL,
		  risk double DEFAULT NULL,
		  risk_pct decimal(5,2) DEFAULT NULL,
		  trade_time varchar(8) COLLATE latin1_german2_ci DEFAULT NULL,
		  gain_loss double(5,2) DEFAULT NULL,
		  holding_period int(11) DEFAULT NULL,
		  vsSpyRank int(11) DEFAULT NULL
		) ENGINE=MEMORY";
		
	$result = queryMysql($create_sql);

	$create_sql = "CREATE TABLE $open_buy_table (
		  portfolio_id int(11) NOT NULL,
		  symbol varchar(6) COLLATE latin1_german2_ci NOT NULL,
		  trade_type varchar(10) COLLATE latin1_german2_ci NOT NULL,
		  trade_date date NOT NULL,
		  shares double NOT NULL,
		  price double NOT NULL,
		  PRIMARY KEY (portfolio_id, symbol, trade_date )

		) ENGINE=MEMORY";

	$result = queryMysql($create_sql);
	
	$create_sql = "CREATE TABLE $tran_table (
		  xid int(11) NOT NULL AUTO_INCREMENT,
		  portfolio_id int(11) DEFAULT NULL,
		  symbol varchar(10) COLLATE latin1_german2_ci DEFAULT NULL,
		  buy_date date DEFAULT NULL,
		  buy_shares int(11) DEFAULT NULL,
		  buy_price double DEFAULT NULL,
		  sell_date date DEFAULT NULL,
		  sell_shares int(11) DEFAULT NULL,
		  sell_price double DEFAULT NULL,
		  PnL double DEFAULT NULL,
		  PRIMARY KEY (xid)

		) ENGINE=MEMORY";

	$result = queryMysql($create_sql);

	$triggerName = "ins_buy_tran".$pid;
	$trigger = "CREATE TRIGGER $triggerName AFTER INSERT ON $tranHist 
			FOR EACH ROW 
				BEGIN
					DECLARE xid int;
					DECLARE buy_date date;
					DECLARE buy_share int;
					DECLARE buy_price double;
					DECLARE PnL double;
					IF (NEW.trade_type = 'BUY')
					THEN
						insert IGNORE into $open_buy_table (portfolio_id, symbol, trade_type, trade_date, shares, price) 
						values (NEW.portfolio_id, NEW.symbol, NEW.trade_type, NEW.trade_date, NEW.shares, NEW.price);
					ELSE
						set @buy_date:=(select trade_date from $open_buy_table where symbol = NEW.symbol and portfolio_id = NEW.portfolio_id);
						set @buy_share:=(select shares from $open_buy_table where symbol = NEW.symbol and portfolio_id = NEW.portfolio_id);
						set @buy_price:=(select price from $open_buy_table where symbol = NEW.symbol and portfolio_id = NEW.portfolio_id);
						set @PnL:=(select (NEW.shares * NEW.price) - (@buy_share * @buy_price));
						insert IGNORE into $tran_table (portfolio_id, symbol, buy_date, buy_shares, buy_price, sell_date, sell_shares, sell_price, PnL) 
						values (NEW.portfolio_id, NEW.symbol, @buy_date, @buy_share, @buy_price, NEW.trade_date, NEW.shares, NEW.price, @PnL);
						delete from $open_buy_table where symbol = NEW.symbol;
					END IF;
				END ";
				
	$result = queryMysql($trigger);


	
}

function get_yahoo_rt_quote ($symbol) {
	$result = array();
	$json1 = array();
	$json2 = array();
    $colN = array();
    $colM = array();	
    
    $colN =array('symbol', 'last_trade', 'price', 'pct_change', 'change');
	$colM =array (
				array(
					"edittype" => "text",
					"name" => "	symbol",
					"width" => 50
				),
/*				array(
					"edittype" => "text",
					"name" => "name"
				),
*/				array(
					"edittype" => "date",
					"name" => "last_trade"
				),
				array(
					"edittype" => "real",
					"name" => "price",
					"width" => 100
				),
				array(
					"edittype" => "real",
					"name" => "pct_change",
					"width" => 100
				),
				array(
					"edittype" => "real",
					"name" => "change",
					"width" => 100
				)					
			);


	$data = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=slk2c6cohgm3m4r5');

	$rows = explode("\n", $data);

	for($i = 0; $i < count($rows)-1; $i++)
	{
		$temp = explode(',', $rows[$i]);
		$sym = $temp[0];
	//	$name = $temp[1];
		$price_temp = explode(' - ', $temp[1]);
		$last_trade = $price_temp[0];
		$price = $price_temp[1];
		$pct_temp = explode(' - ', $temp[2]);
		$percent = $pct_temp[1];
		$change = $temp[3];
	//	$pc = $temp[4];
	//	$open = $temp[5];
	//	$day_high = $temp[6];
	//	$day_low = $temp[7];
	//	$50_ma = $temp[8];
	//	$200_ma = $temp[9];
	//	$peg = $temp[10];
		
		
		$json1[$i]['symbol'] = str_replace("\"", "", $sym); ;
		//$json1[$i]['name'] = str_replace("\"", "", $name);
		$json1[$i]['last_trade'] = str_replace("\"", "",$last_trade);
		
		$json1[$i]['price'] = str_replace("<b>", "", str_replace("/", "", (str_replace("\"", "", $price))));
		//$json1[$i]['price'] = $price;

		$json1[$i]['pct_change'] = str_replace("/", "", (str_replace("\"", "", $percent)));
		$json1[$i]['change'] = str_replace("/", "", (str_replace("\"", "", $change)));
		

		array_push($result, $temp);
 
		//echo "$sym	$name	$price	$percent	$change	$pc <br />";
		//echo "count is " , count($rows);
	}
	  $ret2["dataset"] = $json1;
	  $ret2["page"] = 1;
	  $ret2["records"] = count($rows)-1;
	  $ret2["rows"] = 50;
	  $ret2["sidx"] = null;
	  $ret2["sord"] = "asc";
	  $ret2["total"] = count($rows)-1;

	  $ret3["JSON"] = "success";
	  $ret3["colModel"] = $colM;
	  $ret3["colNames"] = $colN;
	  $ret3["gridModel"] = $ret2;

	return $json1;

}
 
function getRealREQUEST($input) {
    $vars = array();

    //$input    = $_SERVER['REDIRECT_QUERY_STRING'];

    if(!empty($input)){
        $pairs    = explode("&", $input);
        foreach ($pairs     as $pair) {
            $nv                = explode("=", $pair);

            $name            = urldecode($nv[0]);
            $nameSanitize    = preg_replace('/([^\[]*)\[.*$/','$1',$name);

            $nameMatched    = str_replace('.','_',$nameSanitize);
            $nameMatched    = str_replace(' ','_',$nameMatched);

            $vars[$nameSanitize]    = $_REQUEST[$nameMatched];
        }
    }

    $input    = file_get_contents("php://input");
    if(!empty($input)){
        $pairs    = explode("&", $input);
        foreach ($pairs as $pair) {
            $nv                = explode("=", $pair);

            $name            = urldecode($nv[0]);
            $nameSanitize    = preg_replace('/([^\[]*)\[.*$/','$1',$name);

            $nameMatched    = str_replace('.','_',$nameSanitize);
            $nameMatched    = str_replace(' ','_',$nameMatched);

            $vars[$nameSanitize]    = $_REQUEST[$nameMatched];
        }
    }

    return $vars;
}

function dateDiff($start, $end) {
	$start_ts = strtotime($start);
	$end_ts = strtotime($end);
	$diff = $end_ts - $start_ts;
	return round($diff / 86400);
}

function getTradeDateID($symbol, $trade_date) {
	$query  = "select trade_date_id from price_history where symbol = '".$symbol."' and trade_date = '".$trade_date."'";

	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$trade_date_id = $tmp_data[0];
	}

	return $trade_date_id;
}

function getPreviousDate($trade_date) {	
	#$query  = "select trade_date from price_history where symbol = 'AAPL' and trade_date_id = ";
	#$query .= "(select trade_date_id - 1 from price_history where symbol = 'AAPL' and trade_date = '".$trade_date."') ";

	$query  = "select max(trade_date) as trade_date from quotes_memory where symbol = 'AAPL' and trade_date < '".$trade_date."' ";


	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$previous_date = $tmp_data[0];
	}

	return $previous_date;
}

function getStockPrice($trade_date, $symbol, $time ) {	
	$adj_close = true;

	if ($adj_close) {
		$query  = "select $time * (adj_close / close) from quotes_memory where symbol = '$symbol' and trade_date = '$trade_date' ";	
	} else {
		$query  = "select $time from quotes_memory where symbol = '$symbol' and trade_date = '$trade_date' ";	
	}

	try {
		$query= stripslashes($query);
		$result = queryMysql($query);

		while ($tmp_data = mysql_fetch_row($result)) {
			$price = $tmp_data[0];
			
		}
		
	    ## if price is not available, get the last available closing price
	    if (!$price) {
	            if ($adj_close) {
	                    $query  = "select close * (adj_close / close) from quotes_memory where symbol = '$symbol' and trade_date = (select max(trade_date) from quotes_memory where symbol = '$symbol') ";
	            } else {
	                    $query  = "select close from quotes_memory where symbol = '$symbol' and trade_date = (select max(trade_date) from quotes_memory where symbol = '$symbol') ";
	            }
		
	            try {
	                    $query= stripslashes($query);
	                    $result = queryMysql($query);
	
	                    while ($tmp_data = mysql_fetch_row($result)) {
	                            $price = $tmp_data[0];
	                    }
	            } catch (Exception $e) {
	                            echo "query: $query \n";
	                            echo 'Caught exception: ',  $e->getMessage(), "\n";
	            }
	    }
	
			
	} catch (Exception $e) {
	   echo "query: $query \n";
 	   echo 'Caught exception: ',  $e->getMessage(), "\n";
	}	

	return $price;
}



?>
