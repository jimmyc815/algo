#!/usr/local/bin/php5

<?php

// tables in mysql
// turtle_portfolio_performance
// turtle_portfolio_transaction
// turtle_portfolio


include_once 'dbfunction.php';
include_once 'trend_setup.php';
//include_once 'turtle_share_module.php';


$max_risk = 10;
$max_num_holdings = 5;
$risk_factor = 1 / $max_num_holdings;
$original_investment = 1000000;

$stop_loss_muptiplier = 3;

global $sw_trading_scan;
global $sw_trading_queue;
global $sw_trading_lot;

$sw_trading_scan = "sw_trading_scan";
$sw_trading_queue = "sw_trading_queue";
$sw_trading_lot = "sw_trading_lot";

//global $ADX_filter;

//	date_default_timezone_set('America/Los_Angeles');

if($_GET){
	if($_GET['action'] == 'test_populate_sw_trading_queue'){ 
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		$time_frame = "ST";
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}

		populate_sw_trading_queue($start_date, $time_frame,  $portfolio_id);
		
	} elseif($_GET['action'] == 'test_populate_sw_trading_scan'){ 
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		$portfolio_id = $_GET['portfolio_id'];
		$time_frame = $_GET['time_frame'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}

		populate_sw_trading_scan($end_date, $time_frame);
		
	} elseif($_GET['action'] == 'test_populate_sw_break_out_indicator'){ 
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		$portfolio_id = $_GET['portfolio_id'];
		$time_frame = $_GET['time_frame'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}

		populate_sw_break_out_indicator($start_date, $time_frame, $portfolio_id);
		
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
		$movingAvg = $_GET['movingAvg'];
		
		$portfolioID = 2;
		$dailyBuyList = "turtle_daily_buy_list_live";

		$trade_date_id = getTradeDateID($symbol, $date);

		populateDailyBuyList ($date, $movingAvg, $symbol, $portfolioID, $dailyBuyList);

		//echo json_encode($trade_date_id);		
		
		
	} elseif($_GET['action'] == 'testUpdateDailyRank'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$startDate = $_GET['startDate'];
		$endDate = $_GET['endDate'];
		$movingAvg = $_GET['movingAvg'];
		
		$endDate = date("Y-m-d");  
		
		
		$portfolioID = 2;


		updateDailyBuyRank ($startDate, $endDate, $movingAvg, "", $portfolio_id);

		
		
	}elseif($_GET['action'] == 'testGetTradeDate'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		
		$trade_date_id = getTradeDateID($symbol, $date);

		echo json_encode($trade_date_id);		
		
		
	} 
	elseif($_GET['action'] == 'testGetBreakoutStock'){ 
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
		
		
	} elseif($_GET['action'] == 'testADX2'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		
		$trade_date_id = getTradeDateID($symbol, $date);

		//$trade_date_id = $_GET['trade_date_id'];
		$trade_date_id_30_day_prior = $trade_date_id - 100;

		$returnADX = calculate_ADX ($symbol, $trade_date_id, 14);
		
		echo json_encode($returnADX);		
		
		
	} elseif($_GET['action'] == 'testADX'){ 
		// http://ngureco.hubpages.com/hub/How-to-Buy-Shares-Calculating-Average-Directional-Movement-Using-Excel-ADX-Formula
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		
/*		$trade_date_id = getTradeDateID($symbol, $date);

		//$trade_date_id = $_GET['trade_date_id'];
		$trade_date_id_30_day_prior = $trade_date_id - 100;

		$returnADX = calculate_ADX ($symbol, $trade_date_id, 14);
		
		echo json_encode($returnADX);		
		
*/		
		### get list of breakout stocks
		//$breakOutStockArray = array();

		$breakOutStockArray = getBreakoutStock ($date);

//		echo json_decode($breakOutStockArray);	
		
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
		
		$ADX_filter = "On";
		$current_trade_date_id = 0;
		
		$workingArray = $breakOutStockArray;
		$tmpArray = "";
		$breakOutCount = 1;
		
print "original leng: ";
print $len_array;
		// check if ADX check if turned on
		if ($ADX_filter == "On"){
			for ($x=1; $x < $len_array; $x++) {
				$symbol = $workingArray[$x]['symbol'];
				$current_trade_date_id = getTradeDateID($workingArray[$x]['symbol'], $date);
				
				print "symbol: ";
				print $workingArray[$x]['symbol'];
				print " ";

				$returnADX = calculate_ADX ($workingArray[$x]['symbol'], $current_trade_date_id, 14);

				print " plus DI 14: ";
				print $returnADX["plusDI14"];
				print " neg DI 14: ";
				print $returnADX["negDI14"];
				print " ADX: ";
				print $returnADX["ADX"];
				if (($returnADX["plusDI14"] > $returnADX["negDI14"]) && ($returnADX["ADX"] > 25) ) {
//					array_delete ($breakOutStockArray[$x]);
					print "Meet Requirement :";
					print $x;
					print " ";

					//array_diff($breakOutStockArray, $breakOutStockArray[$x]['symol']);
					//unset ($breakOutStockArray[$x]);
					
					$tmpArray[$breakOutCount] = $symbol;
					$breakOutCount ++;
				} else {
					print $x;
					print " ";
					print "Fail ";
					
				}
			}
		}
		
		//echo json_decode($breakOutStockArray);	
	//	print $tmpArray[6]['symbol'];
		print " length: ";
		print count($tmpArray);

		
	} elseif($_GET['action'] == 'getADX'){ 
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
		$symbol = $_GET['symbol'];
		$trade_date_id = $_GET['trade_date_id'];
		$trade_date_id_30_day_prior = $trade_date_id - 100;

		

		$query  = "select trade_date, high, low, close, TR, ATR from price_history where symbol = '".$symbol."' ";
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
				$ret[$x]["changeADX"] = ($ret[$x]["ADX"] - $ret[$x+1]["ADX"])/$ret[$x+1]["ADX"] * 100;			
			} else {
				$ret[$x]["changeADX"] = 0;
			}
								
		}

		echo json_encode($ret);		
		
	} elseif($_GET['action'] == 'getDynamicSQLResult'){
		$query = $_GET['txtInputQuery'];

		$query= stripslashes($query);
		$result = queryMysql($query);

		$sightings = array();

		$sightings = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($sightings);
		
		exit;	 
	} elseif($_GET['action'] == 'getPortfolioHolding'){
		$query = $_GET['txtInputQuery'];
//		$query = "select * from turtle_portfolio";

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
	} elseif ($_GET['action'] == 'getBreakoutStock') {
		$date = $_GET['date'];
		$movingAvg = $_GET['movingAvg'];
	
		$query  = "select symbol, trade_date, high, low, close, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 50_MA, 200_MA from price_history ";
		$query .= "where trade_date = '".$date."'";
		$query .= " and close > ".$movingAvg;
		$query .= " and 50_MA > 200_MA";
		$query .= " and symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
		$query .= " order by pct_change desc";

		$query = stripslashes($query);
		$result = queryMysql($query);

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
        
        
        #convertto assos array
// 		$grid_array = array();
// 		$grid_array = mysql_resultTo2DAssocArray_JGrid($result);
	
//		echo json_encode($grid_array);
		
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
		
		
	} elseif ($_GET['action'] == 'getTransactionPandL') {
		$portfolioID = $_GET['portfolio_id'];
		
		if (!$portfolioID) {$portfolioID = 1;}

		// get all transactions p and l
		$query= "select symbol, holding_days, profit_loss, r_multiple from turtle_transaction_pandl where portfolio_id = ".$portfolioID." order by profit_loss desc";
		
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
		

		
	}  elseif($_GET['action'] == 'simulate_sw_range_trade'){ 
		global $original_investment;
		global $breakOutSignal;
		global $ADX_filter;
		global $breakOutSignal;
		global $breakOutOrderBy;
		global $simplePriceHistory;
				
//		$spyReturn = array();
		$portfolioReturn = array();
		$retArray = array();
		$count = 0;
		$ADX_filter = "Off";		
		$portfolioID = 5;
		reset_portfolio($portfolioID);
		
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}
		$time_frame = $_GET['time_frame'];
		if (!$time_frame) {
			$time_frame = "ST";
		}
/* 
	create purchase queue base on preset critria
		- define anchor buy and sell zone within the past 60 days
			- Highest SPH and lowest SPL
				- determine the zone base on +- ATR
			- Most recent SPH and SPL
				- determine the znoe base on +- ATR
			- Determine other anchor points
				- determine the zone base on +- ATR
			- Find which SPH zone has the most SPH
			- Find which SPL zone has the most SPL
			- Determine current trend for short term, intermedia term, long term
			
			
	check if today price is above threshold on purchase queue
	purchase queue includes % of allotment already purchase
	



*/		
		$sw_trading_scan = "sw_trading_scan";
		$sw_trading_queue = "sw_trading_queue";

		$breakOutSignal = $_GET['breakoutSignal'];
		//$breakOutSignal = "20_DAY_HIGH";


		
		//create temporary table to store daily pricing for comparisons		
		$simplePriceHistory = "simple_price_history";
		$query = "drop table if exists ".$simplePriceHistory;
		$result = queryMysql($query);
		$query  = "create table ".$simplePriceHistory." select symbol, trade_date, trade_date_id, open, high, low, close, daily_change, pct_change,   ";
		$query .= "55_DAY_HIGH, 20_DAY_HIGH, vsSpyRank from price_history where trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		$result = queryMysql($query);


		$query = "select trade_date from price_history where symbol = 'AAPL' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		$result = queryMysql($query);

		$minReturn = 100;
		$maxReturn = -100;
		
		// reset all swing points table
		reset_sw_tables();

		while ($data = mysql_fetch_row($result)) {
			$trade_date = $data[0];
			$start_date = getPrevious60Days($trade_date);

print "trade date: $trade_date  	start_date: $start_date \n";


			//turtle_portfolio_sell ($trade_date, $portfolioID);		
			//turtle_portfolio_pyramid_buy ($trade_date, $portfolioID);
			//turtle_portfolio_buy($trade_date, $breakOutSignal, $ADX_filter, $breakOutOrderBy, $portfolioID, $dailyBuyList);
			//turtle_portfolio_update_stop_loss($trade_date, $portfolioID); 
			//turtle_portfolio_update_stop_loss_with_spl ($trade_date, $portfolioID);
			
			//clear_swing_point_table($tiime_frame);
			//populate_swing_points_for_all_stocks ($symbol, $trade_date, $start_date, $time_frame);
			//populate_sw_trading_scan($trade_date, $time_frame);
		    populate_sw_trading_queue($trade_date, $time_frame,  $portfolio_id);
			
			
		
/*			$value = get_historical_turtle_portfolio_value($trade_date, $portfolioID);
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

			$query2 = "insert into turtle_portfolio_performance values (".$portfolioID.", '".$portfolioReturn[$count]['trade_date']."', ".$portfolioReturn[$count]['dollar_return'].", ".$portfolioReturn[$count]['return'].", ".$portfolioReturn[$count]['value'].", null, null)";
			$result2 = queryMysql($query2);			

			
			$newDateStr = strtotime($trade_date);
			$newDateStr = $newDateStr * 1000 - 14400000;

			array_push($retArray, array($newDateStr, $preturn));
			
*/
			$count ++;
		}

		mysql_close();
		echo json_encode($retArray);

	} elseif($_GET['action'] == 'reset_portfolio'){ 
		$cash = $_GET['cash'];
		$pid = $_GET['portfolio_id'];
		
		$query = "delete from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);
			
		$query = "delete from turtle_portfolio_transaction where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "delete from turtle_transaction_pandl where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "delete from turtle_portfolio_performance where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);
		
	}  elseif($_GET['action'] == 'get_close_price'){ 		
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];

		$priceArray = array();

		
		$query = "select close from price_history where symbol = '".$symbol."' and trade_date = '".$date."'";
		$result = queryMysql($query);

		while ($data = mysql_fetch_row($result)) {
			$priceArray[0]['price'] = $data[0];
		}
		
		echo json_encode($priceArray);
	}   elseif($_GET['action'] == 'get_num_of_trade_days'){ 		
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
		// if end date is not supplied, default to today		
		if (!$end_date) {
			$end_date = date("Y-m-d");  
		}

		$query = "select count(*) from price_history where symbol = 'AAPL' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
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
		
	}  elseif($_GET['action'] == 'get_stock_price_history'){ 
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
				
				$pclose = $stockRetArray[$x]['close'] * 1 / 1;
				//print "return: $preturn";

				//array_push($retArray, array($newDateStr, $preturn));
				array_push($retArray, array($newDateStr, $pclose));
				//array_push($retArray, {$newDateStr, $preturn});
			}			
	
			echo json_encode($retArray);
		
	} elseif($_GET['action'] == 'get_stock_price_history_ohlc'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
		
			$retArray = array();
			$stockRetArray = array();	
	
			$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);
	
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
				
				$pclose = $stockRetArray[$x]['close'] * 1 / 1;
				$popen = $stockRetArray[$x]['open'] * 1 / 1;
				$phigh = $stockRetArray[$x]['high'] * 1 / 1;
				$plow = $stockRetArray[$x]['low'] * 1 / 1;
				$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;

				//print "return: $preturn";

				//array_push($retArray, array($newDateStr, $preturn));
				array_push($retArray, array($newDateStr, $popen, $phigh, $plow, $pclose, $pvolume));
				//array_push($retArray, {$newDateStr, $preturn});
			}			
	
			echo json_encode($retArray);
		
	} elseif($_GET['action'] == 'get_stock_ranking_history'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
		
			$retArray = array();
			$stockRetArray = array();	
	
			//$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);
			$query = "select trade_date, vsSpyRank from price_history where symbol = '".$symbol."' and trade_date > ";
			$query .= "'".$start_date."' ";
			
			$result = queryMysql($query);

			$count = 0;
			while ($tmp_data = mysql_fetch_row($result)) {
				//$stockRetArray[$count]['trade_date'] = $tmp_data[0];

				$newDateStr = strtotime($tmp_data[0]);
				$newDateStr = $newDateStr * 1000 - 14400000;

				//$stockRetArray[$count]['vsSpyRank'] = $tmp_data[1];
				
				array_push($retArray, array($newDateStr, ($tmp_data[1])*1/1));

				$count ++;

			}			
	
			echo json_encode($retArray);
		
	} elseif($_GET['action'] == 'get_stock_50_MA'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			
			$retArray = array();
			$stockRetArray = array();	
	
			$stockRetArray = historical_stock_return($symbol, $start_date, $end_date);
	
			$arrayLen = count($stockRetArray);
			
			for ($x = 0; $x < $arrayLen; $x++)
			{
				$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;
				
				$ma_50 = $stockRetArray[$x]['50_MA'] * 1 / 1;
				array_push($retArray, array($newDateStr, $ma_50));
			}			
	
			echo json_encode($retArray);
		
	} elseif($_GET['action'] == 'get_stock_200_MA'){ 
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
				$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;
				
				$ma_200 = $stockRetArray[$x]['200_MA'] * 1 / 1;
				array_push($retArray, array($newDateStr, $ma_200));
			}			
	
			echo json_encode($retArray);
		
	} elseif($_GET['action'] == 'get_stock_transaction_record'){ 
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
			$time_frame = $_GET['time_frame'];

			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = chart_swing_points($symbol, $start_date, $end_date, $time_frame, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} elseif ($_GET['action'] == 'get_test_transactions'){ 
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$time_frame = $_GET['time_frame'];
			$gain_or_loss = $_GET['gain_or_loss'];


			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			if (!$pid) {
				$pid = 1;
			}
			
			$swingArray = array();	
	
			$swingArray = chart_test_transaction($symbol, $gain_or_loss, $start_date, $end_date, $time_frame, $pid);
			
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
	} else if ($_GET['action'] == 'daily_populate_swing_points') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$start_date = $_GET['start_date'];
			$time_frame = $_GET['time_frame'];

	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}
			
			if (!$time_frame) {
				$time_frame = "ST";  
			}			
						
			$spArray = populate_swing_points ($symbol, $current_date, $start_date, $time_frame);
			
	} else if ($_GET['action'] == 'daily_populate_stock_trend') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$start_date = $_GET['start_date'];
			$time_frame = $_GET['time_frame'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}
			if (!$time_frame) {
				$time_frame = "ST";  
			}			
						
			$spArray = populate_stock_trend ($symbol, $current_date, $start_date, $time_frame);
	} else if ($_GET['action'] == 'daily_clean_up_swing_point_table') {
			$time_frame = $_GET['time_frame'];

			// clear swing point table first to avoid duplicate rows
			$query = "delete from sw_swing_points where time_frame = '".$time_frame."'";
			$result = queryMysql($query);
	} else if ($_GET['action'] == 'daily_clean_up_stock_trend_table') {
			$time_frame = $_GET['time_frame'];

			// clear swing point table first to avoid duplicate rows
			$query = "delete from sw_stock_trend where time_frame = '".$time_frame."'";
			$result = queryMysql($query);
	} else if ($_GET['action'] == 'daily_populate_max_min_swing_points') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$start_date = $_GET['start_date'];
			$time_frame = $_GET['time_frame'];

	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}
			
			if (!$time_frame) {
				$time_frame = "ST";  
			}			
						
			$spArray = populate_swing_points ($symbol, $current_date, $start_date, $time_frame);
			
	} else if ($_GET['action'] == 'daily_populate_sw_trading_scan') {
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$portfolio_id = $_GET['portfolio_id'];
			$time_frame = $_GET['time_frame'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
	
			populate_sw_trading_scan($end_date, $time_frame);	
			
	} else if ($_GET['action'] == 'test_trade_theory') {
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$portfolio_id = $_GET['portfolio_id'];
			$time_frame = $_GET['time_frame'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
	
			//trade_theory_2_sw_lows($symbol);	
			trade_theory_calculate_probabilities_days_2nd_SPL_happen ($symbol);
			//trade_theory_calculate_probabilities_SPL_follow_SPL ($symbol);
			//trade_theory_calculate_probabilities_swing_point_follow_SPL ($symbol);
			
			
	} else if ($_GET['action'] == 'test_trade_theory_2') {
			$symbol = $_GET['symbol'];
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];
			$portfolio_id = $_GET['portfolio_id'];
			$time_frame = $_GET['time_frame'];
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
	
			//trade_theory_2_sw_lows($symbol);	
			//trade_theory_calculate_probabilities_days_2nd_SPL_happen ($symbol);
			//trade_theory_calculate_probabilities_SPL_follow_SPL ($symbol);
			trade_theory_calculate_probabilities_swing_point_follow_SPL ($symbol);
			
			
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
		$query  = "select close from price_history where symbol = '".$symbol."' and trade_date = ";
		$query .= "(select min(trade_date) from price_history where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."')";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$startPrice = $data[0];
		}
		
		// get performance of stock for each trade date compared to price on starting day
		$query  = "select trade_date, close, ((close - ".$startPrice.")/".$startPrice." * 100), 50_MA, 200_MA, 55_DAY_HIGH, 20_DAY_LOW, 20_DAY_HIGH ";
		$query .= "from price_history where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";		

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
		$query  = "select close from price_history where symbol = '".$symbol."' and trade_date = ";
		$query .= "(select min(trade_date) from price_history where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."')";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$startPrice = $data[0];
		}

// check if symbol is traded during that time period by comparing value of start price		
if ($startPrice)
{
		// get performance of stock for each trade date compared to price on starting day
		$query  = "select trade_date, close, ((close - ".$startPrice.")/".$startPrice." * 100), open, high, low, volume, avg_volume, relative_avg_vol, ATR ";
		$query .= "from price_history where symbol = '".$symbol."' and trade_date > '".$start_date."' and trade_date <= '".$end_date."'";		
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
}
		
		return $perfArray;

}


function turtle_portfolio_sell ($date, $portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		
		$sell_price = 0;

		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares, risk, risk_pct, high, vsSpyRank ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.low < a.stop_loss ";
		$query .= "and b.trade_date = '".$date."'";
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


			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# determine sell price. If price of day high is < stop loss, sell price will be price of day high
			if ($ph[$x]['high'] < $ph[$x]['stop_loss']) {
				$sell_price = $ph[$x]['high'];
			} else {
				$sell_price = $ph[$x]['stop_loss'];
			}
//print "sell price";
//print $sell_price;
			# calculate sales proceed for stock, stock to be sold at stop_loss price 
			//$stock_sales = $ph[$x]['shares'] * $ph[$x]['stop_loss'];
			$stock_sales = $ph[$x]['shares'] * $sell_price;

			
			$update_portfolio_query = "update turtle_portfolio set shares = shares + ".$stock_sales." where portfolio_id = ".$portfolioID." and symbol ='CASH'";
			$result = queryMysql($update_portfolio_query);
			$delete_stock_query = "delete from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = '".$ph[$x]['symbol']."'";
			$result = queryMysql($delete_stock_query);
			
			//$insert_transaction_history = "insert into turtle_portfolio_transaction values (1, '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$ph[$x]['stop_loss'].", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";
			$insert_transaction_history = "insert into turtle_portfolio_transaction values (".$portfolioID.", '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].", null, null, null, ".$ph[$x]['vsSpyRank'].")";

			$result = queryMysql($insert_transaction_history);
			
		}		  

}  
	
//incorprating logic where is vsSpyRank falls below a threshold, automatically sell even though stop loss has not hit
function turtle_portfolio_sell_2 ($date, $portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		global $simplePriceHistory;
		
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		
		$sell_price = 0;

		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares, risk, risk_pct, high, vsSpyRank ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.low < a.stop_loss ";
		$query .= "and b.trade_date = '".$date."'";
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


			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# determine sell price. If price of day high is < stop loss, sell price will be price of day high
			if ($ph[$x]['high'] < $ph[$x]['stop_loss']) {
				$sell_price = $ph[$x]['high'];
			} else {
				$sell_price = $ph[$x]['stop_loss'];
			}
//print "sell price";
//print $sell_price;
			# calculate sales proceed for stock, stock to be sold at stop_loss price 
			//$stock_sales = $ph[$x]['shares'] * $ph[$x]['stop_loss'];
			$stock_sales = $ph[$x]['shares'] * $sell_price;

			
			$update_portfolio_query = "update turtle_portfolio set shares = shares + ".$stock_sales." where portfolio_id = ".$portfolioID." and symbol ='CASH'";
			$result = queryMysql($update_portfolio_query);
			$delete_stock_query = "delete from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = '".$ph[$x]['symbol']."'";
			$result = queryMysql($delete_stock_query);
			
			//$insert_transaction_history = "insert into turtle_portfolio_transaction values (1, '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$ph[$x]['stop_loss'].", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";
			$insert_transaction_history = "insert into turtle_portfolio_transaction values (".$portfolioID.", '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].", null, null, null, ".$ph[$x]['vsSpyRank'].")";

			$result = queryMysql($insert_transaction_history);
			
		}		  



		// check if vsSpyRank falls under threshold
		$sell_price = 0;

		$previous_trade_date = getPreviousDate($date);

		$query  = "select a.symbol, b.close, b.low, b.daily_change, b.pct_change, b.ATR, b.55_DAY_HIGH, b.20_DAY_HIGH, b.20_DAY_LOW, b.10_DAY_LOW, b.50_MA, b.200_MA, a.stop_loss, a.stop_buy, a.shares, a.risk, a.risk_pct, b.high, c.vsSpyRank, b.open ";
		$query .= "from turtle_portfolio a, price_history b, $simplePriceHistory c ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.trade_date = '".$date."'";
		$query .= "and b.symbol = c.symbol ";
		$query .= "and c.trade_date = '".$previous_trade_date."' ";
		//$query .= "and c.vsSpyRank > 350 ";
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
			$ph[$i]['open'] = str_replace("\"", "",$data[19]);



			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# determine sell price. If price of day high is < stop loss, sell price will be price of day high
			if ($ph[$x]['high'] < $ph[$x]['stop_loss']) {
				$sell_price = $ph[$x]['high'];
			} else {
				$sell_price = $ph[$x]['stop_loss'];
			}
			
			$sell_price = $ph[$x]['open'];
//print "sell price";
//print $sell_price;
			# calculate sales proceed for stock, stock to be sold at stop_loss price 
			//$stock_sales = $ph[$x]['shares'] * $ph[$x]['stop_loss'];
			$stock_sales = $ph[$x]['shares'] * $sell_price;

			
			$update_portfolio_query = "update turtle_portfolio set shares = shares + ".$stock_sales." where portfolio_id = ".$portfolioID." and symbol ='CASH'";
			$result = queryMysql($update_portfolio_query);
			$delete_stock_query = "delete from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = '".$ph[$x]['symbol']."'";
			$result = queryMysql($delete_stock_query);
			
			//$insert_transaction_history = "insert into turtle_portfolio_transaction values (1, '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$ph[$x]['stop_loss'].", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";
			$insert_transaction_history = "insert into turtle_portfolio_transaction values (".$portfolioID.", '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].", null, null, null, ".$ph[$x]['vsSpyRank'].")";

			$result = queryMysql($insert_transaction_history);
			
		}		  


}  
	
	
function turtle_portfolio_update_stop_loss ($date, $portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.trade_date = '".$date."'";
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
			$new_stop_loss = $ph[$x]['close'] - ($stop_loss_muptiplier*$ph[$x]['ATR']);


			if ($ph[$x]['stop_loss'] < ($ph[$x]['close'] - $stop_loss_muptiplier*$ph[$x]['ATR']))
			{
				$new_stop_loss = $ph[$x]['close'] - ($stop_loss_muptiplier*$ph[$x]['ATR']);
				
				$update_sql = "update turtle_portfolio set stop_loss = ".$new_stop_loss." where symbol = '".$ph[$x]['symbol']."' and portfolio_id = ".$portfolioID;
				$result = queryMysql($update_sql);
			}
		
		}		  


	}
	
function turtle_portfolio_update_stop_loss_with_spl ($date, $portfolioID) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		
		$splArray = array();
		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = ".$portfolioID." ";
		$query .= "and a.symbol != 'CASH' ";
		$query .= "and b.trade_date = '".$date."'";
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
			//$new_stop_loss = $ph[$x]['close'] - ($stop_loss_muptiplier*$ph[$x]['ATR']);
			$splArray = get_last_spl ($ph[$x]['symbol'], $date);
			$new_stop_loss = $splArray[1] - (0.25*$ph[$x]['ATR']);

			if ($ph[$x]['stop_loss'] < $new_stop_loss )
			{
				//$new_stop_loss = $ph[$x]['close'] - ($stop_loss_muptiplier*$ph[$x]['ATR']);
				
				$update_sql = "update turtle_portfolio set stop_loss = ".$new_stop_loss." where symbol = '".$ph[$x]['symbol']."' and portfolio_id = ".$portfolioID;
				$result = queryMysql($update_sql);
			}
		
		}		  


	}	

function turtle_portfolio_buy($date, $breakOutSignal, $ADX_filter, $breakOutOrderBy, $portfolioID, $dailyBuyList) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		
		if (!$portfolioID)
		{
			$portfolioID = 1;
		}
		if (!$dailyBuyList)
		{
			$dailyBuyList = "turtle_daily_buy_list";
		}
		
		//$breakOutSignal = "55_DAY_HIGH";

		### get list of breakout stocks
		//$breakOutStockArray = array();

//		$breakOutStockArray = getBreakoutStock5 ($date, $breakOutSignal, $breakOutOrderBy, $portfolioID, $dailyBuyList);
		$breakOutStockArray = getBreakoutStock6 ($date, $breakOutSignal, $breakOutOrderBy, $portfolioID, $dailyBuyList);

//		echo json_decode($breakOutStockArray);	
		
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
				
				$portfolio_value = get_historical_turtle_portfolio_value($date, $portfolioID);

				### get current available cash		
				$query = "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
				$result = queryMysql($query);
				while ($data = mysql_fetch_row($result)) {
					$cash = $data[0];
				}		
				$risk_value = $portfolio_value * $risk_factor / 100;
				$current_N = $breakOutStockArray[$x]['ATR'];
				if ($current_N > 0) {
					$num_shares = floor($risk_value /($stop_loss_muptiplier*$current_N));
				}
/*				$purchase_value = $num_shares * $breakOutStockArray[$x][$breakOutSignal];
				$stop_loss = $breakOutStockArray[$x][$breakOutSignal] - (2*$current_N);
				$stop_buy = $breakOutStockArray[$x][$breakOutSignal] + $current_N;
*/
				$purchase_value = $num_shares * $breakOutStockArray[$x]['purchase_price'];
				$stop_loss = $breakOutStockArray[$x]['purchase_price'] - ($stop_loss_muptiplier*$current_N);
				$stop_buy = $breakOutStockArray[$x]['purchase_price'] + $current_N;

				$current_risk = get_current_risk($portfolioID);

				if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
					$cash = $cash - $purchase_value;
					$risk_dollar = $num_shares * ($stop_loss_muptiplier * $current_N);
					$risk_pct = ($risk_dollar / $portfolio_value) * 100;

					// insert into turtle_portfolio
					$my_sql  = "insert into turtle_portfolio (portfolio_id, symbol, last_price, shares, cost_basis, stop_loss, stop_buy, risk, risk_pct) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= $breakOutStockArray[$x]['close'].", ";
					$my_sql .= $num_shares.", ";
//					$my_sql .= $breakOutStockArray[$x][$breakOutSignal].", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $stop_loss.", ";
					$my_sql .= $stop_buy.", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";


					$result = queryMysql($my_sql);

					// insert into transaction history table
					$my_sql  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct, vsSpyRank) ";
					$my_sql .= "values (".$portfolioID.", '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= "'BUY', ";
					$my_sql .= "'".$date."', ";
					$my_sql .= $num_shares.", ";
//					$my_sql .= $breakOutStockArray[$x][$breakOutSignal].", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct.", ";
					$my_sql .= $breakOutStockArray[$x]['vsSpyRank']." )";
	
//print "tranx: $my_sql";				
					$result = queryMysql($my_sql);

					// update cash position
					$my_sql  = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
					$result = queryMysql($my_sql);
//print "update: $my_sql";

				}
				
				$pyramid_mode ++;
		}
		
//		populateDailyBuyList ($date, $breakOutSignal, $rankAndWeightArray, $portfolioID, $dailyBuyList);

} 


function turtle_portfolio_pyramid_buy ($date, $portfolioID) { 
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		global $stop_loss_muptiplier;
		
		$risk_factor = 1 / $max_num_holdings;
		
		if (!$portfolioID) {
			$portfolioID = 1;
		}
	
		$my_sql = "select a.symbol, a.shares, c.high, c.trade_date, a.stop_loss, a.stop_buy, c.ATR, a.cost_basis, a.risk, a.risk_pct, c.low, c.open, c.vsSpyRank from turtle_portfolio a, price_history c where a.symbol = c.symbol  and c.trade_date = '".$date."' and c.high > a.stop_buy and a.portfolio_id = ".$portfolioID." order by c.pct_change desc";


/*if ($date == "2012-12-05")
{
	print "pyramid buy: ";
	print $my_sql;
	print "<br>";
}
*/
		$result = queryMysql($my_sql);

		while ($data = mysql_fetch_row($result)) {
			$this_symbol = $data[0];
			$this_shares = $data[1];
			$this_today_high = $data[2];
			$this_trade_date = $data[3];
			$this_stop_loss = $data[4];
			$this_stop_buy = $data[5];
			$this_ATR = $data[6];
			$this_cost_basis = $data[7];
			$this_risk = $data[8];
			$this_risk_pct = $data[9];
			$this_today_low = $data[10];
			$this_today_open = $data[11];
			$this_vsSpyRank = $data[12];


			### get portfolio value
			$portfolio_value = get_historical_turtle_portfolio_value($date, $portfolioID);

			### get current available cash		
			$query = "select shares from turtle_portfolio where portfolio_id = ".$portfolioID." and symbol = 'CASH'";
			$result2 = queryMysql($query);
			while ($data2 = mysql_fetch_row($result2)) {
				$cash = $data2[0];
			}					

			$risk_value = $portfolio_value * $risk_factor / 100;

			$num_shares = floor($risk_value /($stop_loss_muptiplier*$this_ATR));


            // set would-be purchase price of the stock
            // if high > moving avg 
            // 		AND moving avg > low, then purchase price = moving avg
            //		AND moving avg < open, the purchase price = opening price
	        $purchase_price = $this_stop_buy;

            if ($this_stop_buy > $this_today_low) {
	            $purchase_price = $this_stop_buy;
            } elseif ($this_stop_buy < $this_today_open) {
	            $purchase_price = $this_today_open;
            } elseif ($this_stop_buy < $this_today_low) {
	            $purchase_price = $this_today_low;
            }

            $this_stop_buy = $purchase_price;
//			$purchase_value = $num_shares * $this_stop_buy;
			$purchase_value = $num_shares * $purchase_price;

/*print "this stop buy: $this_stop_buy \n";
print "purchase price: $purchase_price \n";
print "purchase value: $purchase_value \n";
print "cash: $cash \n";
print "currnet risk: $current_risk \n";
print "max risk: $max_risk \n";
print "--------- \n";
*/
			$current_risk = get_current_risk($portfolioID);

			if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
		
				$stop_loss = $this_stop_buy - ($stop_loss_muptiplier*$this_ATR);
				$stop_buy = $this_stop_buy + $this_ATR;
				
				$avg_cost_basis = (($this_shares * $this_cost_basis) + $purchase_value) / ($this_shares + $num_shares) ;
				$total_shares = $this_shares + $num_shares;
				
				$risk_dollar = $this_shares * ($stop_loss_muptiplier * $this_ATR) + $this_risk;
				$risk_pct = ($risk_dollar / $portfolio_value) * 100;
				
				$query3 = "update turtle_portfolio set shares = ".$total_shares.", cost_basis = ".$avg_cost_basis.", stop_loss = ".$stop_loss.", stop_buy=".$stop_buy.", risk=".$risk_dollar.", risk_pct=".$risk_pct." where symbol = '".$this_symbol."' and portfolio_id = ".$portfolioID;
				$result3 = queryMysql($query3);
//print "query 3: $query3 ";

				// insert into transaction history table
				$query4  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct, vsSpyRank) ";
				$query4 .= "values (".$portfolioID.", '".$this_symbol."', ";
				$query4 .= "'BUY', ";
				$query4 .= "'".$date."', ";
				$query4 .= $num_shares.", ";
				$query4 .= $this_stop_buy.", ";
				$query4 .= $risk_dollar.", ";
				$query4 .= $risk_pct.", ";
				$query4 .= $this_vsSpyRank." )";
				
				$result4 = queryMysql($query4);

				$cash_left = $cash - $purchase_value;
//print "cash left: $cash_left \n";
				// update cash position
				$query5  = "update turtle_portfolio set shares = ".$cash_left." where symbol = 'CASH' and portfolio_id = ".$portfolioID;
				$result5 = queryMysql($query5);
				
			}				
				
		}

}



function get_real_time_turtle_portfolio_value() {
	$my_sql = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	
	$result = queryMysql($my_sql);
	
	while ($data = mysql_fetch_row($result)) {
		$value = $data[0];
	}
	
	return $value;
}

function get_historical_turtle_portfolio_value($trade_date, $portfolioID) {
	//$my_sql = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	$my_sql  = "select sum(a.shares * b.close) from turtle_portfolio a, price_history b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' and ";
	$my_sql .= "b.trade_date = '".$trade_date."' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
		
	$result = queryMysql($my_sql);
	
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

		$cash = $original_investment;
		if (!$pid)
		{
			$pid = 1;
		}
		
		$query = "delete from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);
			
		$query = "delete from turtle_portfolio_transaction where portfolio_id = ".$pid;
		$result = queryMysql($query);
	
		$query = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH' and portfolio_id = ".$pid;
		$result = queryMysql($query);	

		$query = "delete from turtle_portfolio_performance where portfolio_id = ".$pid;
		$result = queryMysql($query);
		
		$query = "delete from turtle_transaction_pandl where portfolio_id = ".$pid;
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
	$query  = "select trade_date from price_history where symbol = 'AAPL' and trade_date_id = ";
	$query .= "(select trade_date_id - 1 from price_history where symbol = 'AAPL' and trade_date = '".$trade_date."') ";

	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$previous_date = $tmp_data[0];
	}

	return $previous_date;
}

function reset_sw_tables (){
	global $sw_trading_queue;
	global $sw_trading_scan;
	
	$query = "delete from ".$sw_trading_queue;
	$result = queryMysql($query);

	$query = "delete from ".$sw_trading_scan;
	$result = queryMysql($query);
	
	
}

// populate sw_break_out_indicator IF price of stock is greater than sw_trading_scan
// verify if the stock does not yet exist in the trading queu with the same type of trade_type
// when first insert it will have buy type = 'limit_buy' because we will be waiting for stock to pull back to top of the buy point
// insert_date will be the date this row is inserted into TQ
function populate_sw_break_out_indicator ($today_date, $time_frame, $portfolioID) {
	if (!$portfolioID) {
		$portfolioID = 1;
	}
	if (!$sw_trading_scan) {
		$sw_trading_scan = "sw_trading_scan";
	}
	
	if (!$sw_trading_queue) {
		$sw_trading_queue = "sw_trading_queue";
	}

	if (!$sw_break_out_indicator) {
		$sw_break_out_indicator = "sw_break_out_indicator";
	}			
	
	
	$query  = "insert into ".$sw_break_out_indicator." (symbol, insert_date, signal_type, signal_date, target_price, trading_lot_id) ";
	$query .= "select a.symbol, trade_date, 'MAX_SPH', b.buy_signal_date, b.buy_price, 1 from price_history a, ".$sw_trading_scan." b where ";
	$query .= "a.symbol=b.symbol and ";
	$query .= "(not exists (select distinct c.symbol from ".$sw_break_out_indicator." c where c.symbol=b.symbol and c.signal_type = 'MAX_SPH')) and ";
	$query .= "a.trade_date = '".$today_date."' and ";
	$query .= "b.time_frame = '".$time_frame."' and ";
	$query .= "a.high >= b.buy_price ";
	$query .= "ON DUPLICATE KEY update target_price=b.buy_price, signal_date=b.buy_signal_date  ";

	$query = stripslashes($query);
	
	$result = queryMysql($query);
}

// basing on sw break out indicator and swing points, determine how stock should be traded
// if MAX_SPH = last SPH
function populate_sw_trading_plan ($today_date, $time_frame, $portfolioID) {
	if (!$portfolioID) {
		$portfolioID = 1;
	}
	if (!$sw_trading_scan) {
		$sw_trading_scan = "sw_trading_scan";
	}
	
	if (!$sw_trading_queue) {
		$sw_trading_queue = "sw_trading_queue";
	}

	if (!$sw_break_out_indicator) {
		$sw_break_out_indicator = "sw_break_out_indicator";
	}			
	
	if (!$sw_trading_plan) {
		$sw_trading_plan = "sw_trading_plan";
	}


	$query = "drop table if exists temp_last_sph_table";
	$result = queryMysql($query);	
	$query = "drop table if exists temp_stock_list_sph_is_max";
	$result = queryMysql($query);	

	
	$query  = "create temporary table temp_last_sph_table as (select symbol, max(trade_date) from sw_swing_points where type = 'SPH' group by symbol)";
	$result = queryMysql($query);
	
	$query = "create temporary table temp_stock_last_sph_is_max as (select a.* from sw_break_out_indicator a, temp_last_sph_table b where a.symbol = b.symbol)";
	$result = queryMysql($query);
	
	

	$query .= "select trade_date, price, trend, trend_strength from ".$stock_trend_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and trade_date = ";
	$query .= "(select max(trade_date) from ".$stock_trend_table." where symbol='".$symbol."' and time_frame = '".$time_frame."' )";
/*
	$query  = "insert into ".$sw_trading_plan." (symbol, insert_date, trading_type, target_price, trading_lot_id) ";
	$query .= "select a.symbol, trade_date, 'MAX_SPH', b.buy_price, 1 from price_history a, ".$sw_trading_scan." b where ";
	$query .= "a.symbol=b.symbol and ";
	$query .= "(not exists (select distinct c.symbol from ".$sw_break_out_indicator." c where c.symbol=b.symbol and c.trading_type = 'MAX_SPH')) and ";
	$query .= "a.trade_date = '".$today_date."' and ";
	$query .= "b.time_frame = '".$time_frame."' and ";
	$query .= "a.high >= b.buy_price ";
	$query .= "ON DUPLICATE KEY update target_price=b.buy_price  ";

	$query = stripslashes($query);
	
	$result = queryMysql($query);
	
*/
}


function clear_swing_point_table ($time_frame){
	// clear swing point table first to avoid duplicate rows
	$query = "delete from sw_swing_points where time_frame = '".$time_frame."'";
	$result = queryMysql($query);	
	
}

function populate_swing_points_for_all_stocks ($current_date, $trade_date, $start_date, $time_frame) {
	
	//select all stock symboles
	$query = "select distinct a.symbol from stock_list a, price_history b where a.symbol = b.symbol ";
//	$query .= "and a.symbol = 'AAPL'";
	$result = queryMysql($query);

	$count = 0;
	while ($tmp_data = mysql_fetch_row($result)) {
		//$stockRetArray[$count]['trade_date'] = $tmp_data[0];
			populate_swing_points($tmp_data[0], $trade_date, $start_date, $time_frame);

	}		
	
	
	
}

function trade_theory_2_sw_lows($symbol) {
	$totalReturn = 0;
	$totalTransaction = 0;
	$stockList = Array();

	//$symbol = "SPY";
	
	$query = "select distinct symbol from stock_list where index_group = 'SP500'";
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmp['symbol'] = $tmp_data[0];		
		array_push ($stockList, $tmp);
	}		
	
	$numStock = count($stockList);

	
for ($i=300; $i<$numStock-100; $i++) {	
	$resultArray = Array();
	$tmpArray = Array();
	$tmp = Array();

	$symbol = $stockList[$i]['symbol'];
//	$symbol = "CREE";

	print "\nworking on number: $i symbol: $symbol \n";

	$query = "select a.symbol, a.close, a.trade_date, b.type, b.price from price_history a, sw_swing_points b where a.symbol = b.symbol and a.trade_date = b.trade_date and a.symbol = '".$symbol."' and b.type in ('SPL', 'SPH') order by 1, 3";
	$query = stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['symbol'] = $tmp_data[0];
		$tmpArray['close'] = $tmp_data[1];
		$tmpArray['trade_date'] = $tmp_data[2];
		$tmpArray['sw_type'] = $tmp_data[3];
		$tmpArray['sw_price'] = $tmp_data[4];
		
		array_push ($resultArray, $tmpArray);

	}	
	
	$resultLen = count($resultArray);
	$splCount = 0;
	$purchasePrice = 0;

	for ($x=2;$x < $resultLen; $x++) {


		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" ) {
//		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPL") {

			$splCount ++;
			
			if ($splCount == 1) {
				$y = $x;
				$purchasePrice = $resultArray[$y]['close'];
				print "purchase on ";
				print $resultArray[$y]['trade_date'];
				print " price: ";
				print $resultArray[$y]['close'];

			}
		}
		
		if ($resultArray[$x]['sw_type'] == "SPH" && $purchasePrice > 0) {
			$z = $x;
			
			$soldPrice = $resultArray[$z]['close'];
			
			$return = ($soldPrice - $purchasePrice) * 100 / $purchasePrice ;
			
			$totalReturn += $return;

			
			print " sold on ";
			print $resultArray[$z]['trade_date'];
			print " price: ";
			print $resultArray[$z]['close'];
			
			print " period return: $return total return: $totalReturn \n";
		

/*			$query  = "insert into test_trade_theory_result (theory_id, symbol, purchase_date, purchase_price, sell_date, sell_price, trade_return) ";
			$query .= "values (1, '".$symbol."', '".$resultArray[$y]['trade_date']."', ".$purchasePrice.", '".$resultArray[$z]['trade_date']."', ".$soldPrice.", ".$return.")";
			//print "query: $query \n";
			$result = queryMysql($query);	
			
*/	
			$purchasePrice = 0;
			$splCount = 0;		


			$totalTransaction ++;
		}
		
		

		
		
		
		
	}
}

		print "\ntotal transaction: $totalTransaction \n";
		print "avg return per transaction: ";
		$avgReturn = $totalReturn / $totalTransaction;
		print $avgReturn;
		print "\n";
}

function trade_theory_calculate_probabilities_days_2nd_SPL_happen($symbol) {
	$resultArray = Array();
	$tmpArray = Array();
	$stockList = Array();
	$tmp = Array();
	$totalReturn = 0;
	$totalTransaction = 0;
	$min_spl_array = Array();
	$stop_loss_price = 0;
	$time_frame = 'ST';
	//$symbol = "SPY";
	$splOnDay6 = 0;
	$splOnDay7 = 0;
	$splOnDay8 = 0;
	$splOnDay9 = 0;
	$splOnDay10 = 0;
	$splOnDay11 = 0;
	$splOnDay12 = 0;
	$splOnDay13Out = 0;
	
	$sphOnDay6 = 0;
	$sphOnDay7 = 0;
	$sphOnDay8 = 0;
	$sphOnDay9 = 0;
	$sphOnDay10 = 0;
	$sphOnDay11 = 0;
	$sphOnDay12 = 0;
	$sphOnDay13Out = 0;
	
	
	$query = "select distinct symbol from stock_list where index_group = 'SP500' order by 1";
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmp['symbol'] = $tmp_data[0];		
		array_push ($stockList, $tmp);
	}		
	
	$numStock = count($stockList);

	
for ($i=0; $i<$numStock-495; $i++) {	
	$symbol = $stockList[$i]['symbol'];
//	$symbol = "CREE";

	print "working on number: $i symbol: $symbol \n";

	$query = "select a.symbol, a.close, a.trade_date, b.type, b.price from price_history a, sw_swing_points b where a.symbol = b.symbol and a.trade_date = b.trade_date and a.symbol = '".$symbol."' and b.type in ('SPL', 'SPH') ";
	$query = stripslashes($query);

	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['symbol'] = $tmp_data[0];
		$tmpArray['close'] = $tmp_data[1];
		$tmpArray['trade_date'] = $tmp_data[2];
		$tmpArray['sw_type'] = $tmp_data[3];
		$tmpArray['sw_price'] = $tmp_data[4];
		
		array_push ($resultArray, $tmpArray);

	}	
	
	$resultLen = count($resultArray);
	$splCount = 0;
	$purchasePrice = 0;


	for ($x=2;$x < $resultLen; $x++) {
	
		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" ) {
//		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPL") {

			$splCount ++;
			
			if ($splCount == 1) {
				$y = $x;
				$purchasePrice = $resultArray[$y]['close'];
				$min_spl_array = get_min_spl_from_table($symbol, $time_frame, $resultArray[$y]['trade_date']);
				$stop_loss_price = $min_spl_array['price'] - $min_spl_array['ATR'];
				
				$secondSPLDate = $resultArray[$x]['trade_date'];
				$firstSPLDate = $resultArray[$x-1]['trade_date'];
				
				$query = "select count(trade_date) from price_history where symbol = 'AAPL' and trade_date >= '".$firstSPLDate."' and trade_date < '".$secondSPLDate."'";
				$result = queryMysql($query);
				while ($tmp_data = mysql_fetch_row($result)) {
					$dateDiff = $tmp_data[0];
				}	
				
				if ($dateDiff == 6) {$splOnDay6 ++;}
				else if ($dateDiff == 7) {$splOnDay7 ++;}
				else if ($dateDiff == 8) {$splOnDay8 ++;}				
				else if ($dateDiff == 9) {$splOnDay9 ++;}				
				else if ($dateDiff == 10) {$splOnDay10 ++;}				
				else if ($dateDiff == 11) {$splOnDay11 ++;}				
				else if ($dateDiff == 12) {$splOnDay12 ++;}		
				else if ($dateDiff > 12) {$splOnDay13Out ++;}				
						
/*				print "purchase on ";
				print $resultArray[$y]['trade_date'];
				print " price: ";
				print $resultArray[$y]['close'];
				print " date diff: ";
				print $dateDiff;
*/
			}
		}
		
/*		if ($resultArray[$x]['sw_type'] == "SPH" && $purchasePrice > 0) {
			$z = $x;
			
			$soldPrice = $resultArray[$z]['close'];
			
			$return = ($soldPrice - $purchasePrice) * 100 / $purchasePrice ;
			
			$totalReturn += $return;
		
			$purchasePrice = 0;
			$splCount = 0;		

			
			print " sold on ";
			print $resultArray[$z]['trade_date'];
			print " price: ";
			print $resultArray[$z]['close'];
			
			print " period return: $return total return: $totalReturn \n";
			
			$totalTransaction ++;
		}
*/		

	if ($resultArray[$x]['sw_type'] == "SPH" && $purchasePrice > 0) {
			$z = $x;
			
			$soldPrice = $resultArray[$z]['close'];
			
			$return = ($soldPrice - $purchasePrice) * 100 / $purchasePrice ;
			
			$totalReturn += $return;
		
			$purchasePrice = 0;
			$splCount = 0;		

			
/*			print " sold on ";
			print $resultArray[$z]['trade_date'];
			print " price: ";
			print $resultArray[$z]['close'];
			
			print " period return: $return total return: $totalReturn \n";
*/			


			$secondSPHDate = $resultArray[$x]['trade_date'];
			$firstSPHDate = $resultArray[$x-1]['trade_date'];
				
			$query = "select count(trade_date) from price_history where symbol = 'AAPL' and trade_date >= '".$firstSPLDate."' and trade_date < '".$secondSPLDate."'";
				$result = queryMysql($query);
				while ($tmp_data = mysql_fetch_row($result)) {
					$dateDiff = $tmp_data[0];
				}	
				
				if ($dateDiff == 6) {$sphOnDay6 ++;}
				else if ($dateDiff == 7) {$sphOnDay7 ++;}
				else if ($dateDiff == 8) {$sphOnDay8 ++;}				
				else if ($dateDiff == 9) {$sphOnDay9 ++;}				
				else if ($dateDiff == 10) {$sphOnDay10 ++;}				
				else if ($dateDiff == 11) {$sphOnDay11 ++;}				
				else if ($dateDiff == 12) {$sphOnDay12 ++;}		
				else if ($dateDiff > 12) {$sphOnDay13Out ++;}	


			$totalTransaction ++;
		}
		

		
		
		
		
	}
}

		print "\ntotal transaction: $totalTransaction \n";
		print "avg return per transaction: ";
		$avgReturn = $totalReturn / $totalTransaction;
		print $avgReturn;
		print "\n";
		
		print "SPL: \n";
		$splOnDay6Pct = $splOnDay6 *100  / $totalTransaction;
		$splOnDay7Pct = $splOnDay7 *100/ $totalTransaction;		
		$splOnDay8Pct = $splOnDay8 *100/ $totalTransaction;		
		$splOnDay9Pct = $splOnDay9 *100/ $totalTransaction;		
		$splOnDay10Pct = $splOnDay10 *100/ $totalTransaction;		
		$splOnDay11Pct = $splOnDay11 *100/ $totalTransaction;	
		$splOnDay12Pct = $splOnDay12 *100/ $totalTransaction;		
		$splOnDay13Pct = $splOnDay13Out *100/ $totalTransaction;		
	
		print "Second SPL on Day 6: $splOnDay6 Percent: $splOnDay6Pct \n";
		print "Second SPL on Day 7: $splOnDay7 Percent: $splOnDay7Pct \n";
		print "Second SPL on Day 8: $splOnDay8 Percent: $splOnDay8Pct \n";
		print "Second SPL on Day 9: $splOnDay9 Percent: $splOnDay9Pct \n";
		print "Second SPL on Day 10: $splOnDay10 Percent: $splOnDay10Pct \n";
		print "Second SPL on Day 11: $splOnDay11 Percent: $splOnDay11Pct \n";
		print "Second SPL on Day 12: $splOnDay12 Percent: $splOnDay12Pct \n";
		print "Second SPL on Day 13 and Out: $splOnDay13Out Percent: $splOnDay13Pct \n";

		print "SPH: \n";
		$sphOnDay6Pct = $sphOnDay6 *100  / $totalTransaction;
		$sphOnDay7Pct = $sphOnDay7 *100/ $totalTransaction;		
		$sphOnDay8Pct = $sphOnDay8 *100/ $totalTransaction;		
		$sphOnDay9Pct = $sphOnDay9 *100/ $totalTransaction;		
		$sphOnDay10Pct = $sphOnDay10 *100/ $totalTransaction;		
		$sphOnDay11Pct = $sphOnDay11 *100/ $totalTransaction;	
		$sphOnDay12Pct = $sphOnDay12 *100/ $totalTransaction;		
		$sphOnDay13Pct = $sphOnDay13Out *100/ $totalTransaction;		
	
		print "Second SPH on Day 6: $sphOnDay6 Percent: $sphOnDay6Pct \n";
		print "Second SPH on Day 7: $sphOnDay7 Percent: $sphOnDay7Pct \n";
		print "Second SPH on Day 8: $sphOnDay8 Percent: $sphOnDay8Pct \n";
		print "Second SPH on Day 9: $sphOnDay9 Percent: $sphOnDay9Pct \n";
		print "Second SPH on Day 10: $sphOnDay10 Percent: $sphOnDay10Pct \n";
		print "Second SPH on Day 11: $sphOnDay11 Percent: $sphOnDay11Pct \n";
		print "Second SPH on Day 12: $sphOnDay12 Percent: $sphOnDay12Pct \n";
		print "Second SPH on Day 13 and Out: $sphOnDay13Out Percent: $sphOnDay13Pct \n";

}

function trade_theory_calculate_probabilities_swing_point_follow_SPL($symbol) {
	$resultArray = Array();
	$tmpArray = Array();
	$stockList = Array();
	$tmp = Array();
	$totalReturn = 0;
	$totalTransaction = 0;
	$min_spl_array = Array();
	$stop_loss_price = 0;
	$time_frame = 'ST';
	//$symbol = "SPY";
	$splOnDay6 = 0;
	$splOnDay7 = 0;
	$splOnDay8 = 0;
	$splOnDay9 = 0;
	$splOnDay10 = 0;
	$splOnDay11 = 0;
	$splOnDay12 = 0;
	$splOnDay13Out = 0;
	
	$printOutput = 0;
	
	
	$query = "select distinct symbol from stock_list where index_group = 'SP500' order by 1";
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmp['symbol'] = $tmp_data[0];		
		array_push ($stockList, $tmp);
	}		
	
	$numStock = count($stockList);

	$SPH_SPH_SPL_SPL = 0;
	$SPH_SPH_SPL_SPH = 0;
	$SPL_SPH_SPL_SPL = 0;
	$SPL_SPH_SPL_SPH = 0;
	
	$SPH_SPL_SPECIAL_SPH = 0;
	$SPH_SPL_SPECIAL_SPL = 0;
	
for ($i=0; $i<$numStock-495; $i++) {	
	$symbol = $stockList[$i]['symbol'];
//	$symbol = "CREE";

	print "working on number: $i symbol: $symbol \n";

	$query = "select a.symbol, a.close, a.trade_date, b.type, b.price from price_history a, sw_swing_points b where a.symbol = b.symbol and a.trade_date = b.trade_date and a.symbol = '".$symbol."' and b.type in ('SPL', 'SPH')";
	$query = stripslashes($query);

	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['symbol'] = $tmp_data[0];
		$tmpArray['close'] = $tmp_data[1];
		$tmpArray['trade_date'] = $tmp_data[2];
		$tmpArray['sw_type'] = $tmp_data[3];
		$tmpArray['sw_price'] = $tmp_data[4];
		
		array_push ($resultArray, $tmpArray);

	}	
	
	$resultLen = count($resultArray);
	$splCount = 0;
	$sphCount = 0;
	$purchasePrice = 0;


	for ($x=3;$x < $resultLen; $x++) {
	
		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPH" ) {
//		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPH" && $resultArray[$x-3]['sw_type'] == "SPH") {
			$begin_date = $resultArray[$x-1]['trade_date'];
			$end_date = $resultArray[$x]['trade_date'];

			$query = "select type from sw_swing_points where symbol = '".$symbol."' and trade_date <= '".$end_date."' and trade_date >= '".$begin_end."' and type not in ('SPH', 'SPL') ";
			$result = queryMysql($query);

			$non_spl_sph_count = 0;
			while ($tmp_data = mysql_fetch_row($result)) {
				$non_spl_sph_count ++ ;
			}

			if ($non_spl_sph_count > 0) {
				$SPH_SPL_SPECIAL_SPL++;				
			}			
			
			
				$secondSPLDate = $resultArray[$x]['trade_date'];
				$firstSPLDate = $resultArray[$x-1]['trade_date'];
				
				$query = "select count(trade_date) from price_history where symbol = 'AAPL' and trade_date >= '".$firstSPLDate."' and trade_date < '".$secondSPLDate."'";
				$result = queryMysql($query);
				while ($tmp_data = mysql_fetch_row($result)) {
					$dateDiff = $tmp_data[0];
				}	
				
				if ($dateDiff == 6) {$splOnDay6 ++;}
				else if ($dateDiff == 7) {$splOnDay7 ++;}
				else if ($dateDiff == 8) {$splOnDay8 ++;}				
				else if ($dateDiff == 9) {$splOnDay9 ++;}				
				else if ($dateDiff == 10) {$splOnDay10 ++;}				
				else if ($dateDiff == 11) {$splOnDay11 ++;}				
				else if ($dateDiff == 12) {$splOnDay12 ++;}		
				else if ($dateDiff > 12) {$splOnDay13Out ++;}	
		}
		
	
		if ($resultArray[$x]['sw_type'] == "SPH" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPH" ) {
//		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPH" && $resultArray[$x-3]['sw_type'] == "SPH") {
			$begin_date = $resultArray[$x-1]['trade_date'];
			$end_date = $resultArray[$x]['trade_date'];

			$query = "select type from sw_swing_points where symbol = '".$symbol."' and trade_date <= '".$end_date."' and trade_date >= '".$begin_end."' and type not in ('SPH', 'SPL') ";
			$result = queryMysql($query);

			$non_spl_sph_count = 0;
			while ($tmp_data = mysql_fetch_row($result)) {
				$non_spl_sph_count ++ ;
			}

			if ($non_spl_sph_count > 0) {
				$SPH_SPL_SPECIAL_SPH++;				
			}
			
				$secondSPHDate = $resultArray[$x]['trade_date'];
				$firstSPHDate = $resultArray[$x-1]['trade_date'];
				
				$query = "select count(trade_date) from price_history where symbol = 'AAPL' and trade_date >= '".$firstSPLDate."' and trade_date < '".$secondSPLDate."'";
				$result = queryMysql($query);
				while ($tmp_data = mysql_fetch_row($result)) {
					$dateDiff = $tmp_data[0];
				}	
				
				if ($dateDiff == 6) {$sphOnDay6 ++;}
				else if ($dateDiff == 7) {$sphOnDay7 ++;}
				else if ($dateDiff == 8) {$sphOnDay8 ++;}				
				else if ($dateDiff == 9) {$sphOnDay9 ++;}				
				else if ($dateDiff == 10) {$sphOnDay10 ++;}				
				else if ($dateDiff == 11) {$sphOnDay11 ++;}				
				else if ($dateDiff == 12) {$sphOnDay12 ++;}		
				else if ($dateDiff > 12) {$sphOnDay13Out ++;}				
		}
		

		
}

		
		$totalCount = $SPH_SPL_SPECIAL_SPL + $SPH_SPL_SPECIAL_SPH ;
		print "\ttotal count: $totalCount n";
		$pct1 = $SPH_SPL_SPECIAL_SPL * 100 / $totalCount;
		$pct2 = $SPH_SPL_SPECIAL_SPH * 100 / $totalCount;
		
		print " SPH->SPL->SPL count: $SPH_SPL_SPECIAL_SPL Percentage: $pct1 ";
		print " SPH->SPL->SPH count: $SPH_SPL_SPECIAL_SPH Percentage: $pct2 \n ";


		print "SPL: \n";
		$splOnDay6Pct = $splOnDay6 *100  / $totalCount;
		$splOnDay7Pct = $splOnDay7 *100/ $totalCount;		
		$splOnDay8Pct = $splOnDay8 *100/ $totalCount;		
		$splOnDay9Pct = $splOnDay9 *100/ $totalCount;		
		$splOnDay10Pct = $splOnDay10 *100/ $totalCount;		
		$splOnDay11Pct = $splOnDay11 *100/ $totalCount;	
		$splOnDay12Pct = $splOnDay12 *100/ $totalCount;		
		$splOnDay13Pct = $splOnDay13Out *100/ $totalCount;		
	
		print "Second SPL on Day 6: $splOnDay6 Percent: $splOnDay6Pct \n";
		print "Second SPL on Day 7: $splOnDay7 Percent: $splOnDay7Pct \n";
		print "Second SPL on Day 8: $splOnDay8 Percent: $splOnDay8Pct \n";
		print "Second SPL on Day 9: $splOnDay9 Percent: $splOnDay9Pct \n";
		print "Second SPL on Day 10: $splOnDay10 Percent: $splOnDay10Pct \n";
		print "Second SPL on Day 11: $splOnDay11 Percent: $splOnDay11Pct \n";
		print "Second SPL on Day 12: $splOnDay12 Percent: $splOnDay12Pct \n";
		print "Second SPL on Day 13 and Out: $splOnDay13Out Percent: $splOnDay13Pct \n";

		print "SPH: \n";
		$sphOnDay6Pct = $sphOnDay6 *100  / $totalCount;
		$sphOnDay7Pct = $sphOnDay7 *100/ $totalCount;		
		$sphOnDay8Pct = $sphOnDay8 *100/ $totalCount;		
		$sphOnDay9Pct = $sphOnDay9 *100/ $totalCount;		
		$sphOnDay10Pct = $sphOnDay10 *100/ $totalCount;		
		$sphOnDay11Pct = $sphOnDay11 *100/ $totalCount;	
		$sphOnDay12Pct = $sphOnDay12 *100/ $totalCount;		
		$sphOnDay13Pct = $sphOnDay13Out *100/ $totalCount;		
	
		print "Second SPH on Day 6: $sphOnDay6 Percent: $sphOnDay6Pct \n";
		print "Second SPH on Day 7: $sphOnDay7 Percent: $sphOnDay7Pct \n";
		print "Second SPH on Day 8: $sphOnDay8 Percent: $sphOnDay8Pct \n";
		print "Second SPH on Day 9: $sphOnDay9 Percent: $sphOnDay9Pct \n";
		print "Second SPH on Day 10: $sphOnDay10 Percent: $sphOnDay10Pct \n";
		print "Second SPH on Day 11: $sphOnDay11 Percent: $sphOnDay11Pct \n";
		print "Second SPH on Day 12: $sphOnDay12 Percent: $sphOnDay12Pct \n";
		print "Second SPH on Day 13 and Out: $sphOnDay13Out Percent: $sphOnDay13Pct \n";


}
		$totalCount = $SPH_SPL_SPECIAL_SPL + $SPH_SPL_SPECIAL_SPH ;
		print "total count: $totalCount \n";
		$pct1 = $SPH_SPL_SPECIAL_SPL * 100 / $totalCount;
		$pct2 = $SPH_SPL_SPECIAL_SPH * 100 / $totalCount;
		
		print "SPH->SPL->SPL count: $SPH_SPL_SPECIAL_SPL Percentage: $pct1 \n";
		print "SPH->SPL->SPH count: $SPH_SPL_SPECIAL_SPH Percentage: $pct2 \n";


		print "SPL: \n";
		$splOnDay6Pct = $splOnDay6 *100  / $totalCount;
		$splOnDay7Pct = $splOnDay7 *100/ $totalCount;		
		$splOnDay8Pct = $splOnDay8 *100/ $totalCount;		
		$splOnDay9Pct = $splOnDay9 *100/ $totalCount;		
		$splOnDay10Pct = $splOnDay10 *100/ $totalCount;		
		$splOnDay11Pct = $splOnDay11 *100/ $totalCount;	
		$splOnDay12Pct = $splOnDay12 *100/ $totalCount;		
		$splOnDay13Pct = $splOnDay13Out *100/ $totalCount;		
	
		print "Second SPL on Day 6: $splOnDay6 Percent: $splOnDay6Pct \n";
		print "Second SPL on Day 7: $splOnDay7 Percent: $splOnDay7Pct \n";
		print "Second SPL on Day 8: $splOnDay8 Percent: $splOnDay8Pct \n";
		print "Second SPL on Day 9: $splOnDay9 Percent: $splOnDay9Pct \n";
		print "Second SPL on Day 10: $splOnDay10 Percent: $splOnDay10Pct \n";
		print "Second SPL on Day 11: $splOnDay11 Percent: $splOnDay11Pct \n";
		print "Second SPL on Day 12: $splOnDay12 Percent: $splOnDay12Pct \n";
		print "Second SPL on Day 13 and Out: $splOnDay13Out Percent: $splOnDay13Pct \n";

		print "SPH: \n";
		$sphOnDay6Pct = $sphOnDay6 *100  / $totalCount;
		$sphOnDay7Pct = $sphOnDay7 *100/ $totalCount;		
		$sphOnDay8Pct = $sphOnDay8 *100/ $totalCount;		
		$sphOnDay9Pct = $sphOnDay9 *100/ $totalCount;		
		$sphOnDay10Pct = $sphOnDay10 *100/ $totalCount;		
		$sphOnDay11Pct = $sphOnDay11 *100/ $totalCount;	
		$sphOnDay12Pct = $sphOnDay12 *100/ $totalCount;		
		$sphOnDay13Pct = $sphOnDay13Out *100/ $totalCount;		
	
		print "Second SPH on Day 6: $sphOnDay6 Percent: $sphOnDay6Pct \n";
		print "Second SPH on Day 7: $sphOnDay7 Percent: $sphOnDay7Pct \n";
		print "Second SPH on Day 8: $sphOnDay8 Percent: $sphOnDay8Pct \n";
		print "Second SPH on Day 9: $sphOnDay9 Percent: $sphOnDay9Pct \n";
		print "Second SPH on Day 10: $sphOnDay10 Percent: $sphOnDay10Pct \n";
		print "Second SPH on Day 11: $sphOnDay11 Percent: $sphOnDay11Pct \n";
		print "Second SPH on Day 12: $sphOnDay12 Percent: $sphOnDay12Pct \n";
		print "Second SPH on Day 13 and Out: $sphOnDay13Out Percent: $sphOnDay13Pct \n";


}

/*
Donchian Counter Trend Theory
Buy if stock close BELOW n-day low
Filter:
1: close > 70 day ago close

n is a variable
*/
function trade_theory_donchian_counter_trend($symbol) {
	$totalReturn = 0;
	$totalTransaction = 0;
	$stockList = Array();
	
	$query = "select distinct symbol from stock_list where index_group = 'SP500'";
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmp['symbol'] = $tmp_data[0];		
		array_push ($stockList, $tmp);
	}		
	
	$numStock = count($stockList);

	$n_day_low = "10_DAY_LOW";
	
	
for ($i=0; $i<$numStock-490; $i++) {	
	$resultArray = Array();
	$tmpArray = Array();
	$tmp = Array();

	$symbol = $stockList[$i]['symbol'];
//	$symbol = "CREE";

	print "\nworking on number: $i symbol: $symbol \n";

	$query = "select a.symbol, a.close, a.trade_date, b.type, b.price from price_history a, sw_swing_points b where a.symbol = b.symbol and a.trade_date = b.trade_date and a.symbol = '".$symbol."' and b.type in ('SPL', 'SPH') order by 1, 3";
	$query = stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['symbol'] = $tmp_data[0];
		$tmpArray['close'] = $tmp_data[1];
		$tmpArray['trade_date'] = $tmp_data[2];
		$tmpArray['sw_type'] = $tmp_data[3];
		$tmpArray['sw_price'] = $tmp_data[4];
		
		array_push ($resultArray, $tmpArray);

	}	
	
	$resultLen = count($resultArray);
	$splCount = 0;
	$purchasePrice = 0;

	for ($x=2;$x < $resultLen; $x++) {


		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" ) {
//		if ($resultArray[$x]['sw_type'] == "SPL" && $resultArray[$x-1]['sw_type'] == "SPL" && $resultArray[$x-2]['sw_type'] == "SPL") {

			$splCount ++;
			
			if ($splCount == 1) {
				$y = $x;
				$purchasePrice = $resultArray[$y]['close'];
				print "purchase on ";
				print $resultArray[$y]['trade_date'];
				print " price: ";
				print $resultArray[$y]['close'];

			}
		}
		
		if ($resultArray[$x]['sw_type'] == "SPH" && $purchasePrice > 0) {
			$z = $x;
			
			$soldPrice = $resultArray[$z]['close'];
			
			$return = ($soldPrice - $purchasePrice) * 100 / $purchasePrice ;
			
			$totalReturn += $return;

			
			print " sold on ";
			print $resultArray[$z]['trade_date'];
			print " price: ";
			print $resultArray[$z]['close'];
			
			print " period return: $return total return: $totalReturn \n";
		

/*			$query  = "insert into test_trade_theory_result (theory_id, symbol, purchase_date, purchase_price, sell_date, sell_price, trade_return) ";
			$query .= "values (1, '".$symbol."', '".$resultArray[$y]['trade_date']."', ".$purchasePrice.", '".$resultArray[$z]['trade_date']."', ".$soldPrice.", ".$return.")";
			//print "query: $query \n";
			$result = queryMysql($query);	
			
*/	
			$purchasePrice = 0;
			$splCount = 0;		


			$totalTransaction ++;
		}
		
		

		
		
		
		
	}
}

		print "\ntotal transaction: $totalTransaction \n";
		print "avg return per transaction: ";
		$avgReturn = $totalReturn / $totalTransaction;
		print $avgReturn;
		print "\n";
}
}



?>