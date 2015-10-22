#!/usr/local/bin/php5

<?php

// tables in mysql
// turtle_portfolio_performance
// turtle_portfolio_transaction
// turtle_portfolio


include_once 'dbfunction.php';

$max_risk = 10;
$max_num_holdings = 15;
$risk_factor = 1 / $max_num_holdings;
$original_investment = 1000000;


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
		$movingAvg = $_GET['movingAvg'];
		
		$portfolioID = 2;
		$dailyBuyList = "turtle_daily_buy_list_live";

		$trade_date_id = getTradeDateID($symbol, $date);

		populateDailyBuyList ($date, $movingAvg, $symbol, $portfolioID, $dailyBuyList);

		//echo json_encode($trade_date_id);		
		
		
	} elseif($_GET['action'] == 'testGetTradeDate'){ 
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
		
		$breakOutList = getBreakoutStock5 ($date, "55_DAY_HIGH", $testArray, 1, "turtle_daily_buy_list");
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
		
	} elseif ($_GET['action'] == 'test_array_key') {
/*		$array = array(0 => 100, "color" => "red");
		print_r(array_keys($array));
		print "<BR>";
		
		$array = array("blue", "red", "green", "blue", "blue");
		print_r(array_keys($array, "blue"));
		
		$array = array("color" => array("blue", "red", "green"),
		               "size"  => array("small", "medium", "large"));
		print_r(array_keys($array));
		print "<BR>";
		$array = array("AAPL" => array("BUY", "600", "30"),
		               "GOOG"  => array("BUY", "590", "10"),
		               "AAPL"  => array("BUY", "610", "20"),
		               "AAPL"  => array("SELL", "620", "50"),
		               "GOOG"  => array("SELL", "560", "10")
		               
		               );
		//$array = array("AAPL", "GOOG", "AAPL", "IBM", "AAPL");
		$keySpot = array_keys($array);
		print_r(array_keys($array, "AAPL"));
		print "<BR>";
		print "key: ";
		print_r ($keySpot[0]);
		print_r ($array[$keySpot[0]]);
		
		print "<BR>";
		$array = array(0 => "AAPL", 1 => "GOOG", 2=> "AAPL", 3 => "IBM", 4 => "GOOG", 5 => "AAPL");
		print_r (array_search("AAPL", $array));
		
		print "<BR>";
		$array = array("AAPL", "GOOG", "AAPL",  "IBM",  "GOOG", "AAPL");
		print "AAPL <BR>";
		print_r (array_keys($array, "AAPL"));
		print "<BR> array 5 ";
		$key = array_keys($array, "AAPL");
		print $array[$key[2]];
		print "<BR> KEY ";
		print $key[2];
		print "<BR>";
		print "GOOG <BR>";
		print_r (array_keys($array, "GOOG"));
		
		$uniqueKey = array ("AAPL", "GOOG", "IBM", "C");
		$arrayKey = array ("AAPL", "GOOG", "AAPL", "C", "C", "IBM", "AAPL");
		$array = array (array("AAPL", "BUY", "100", "2012-05-02"),
						array("GOOG", "BUY", "100", "2012-04-02"),
						array("AAPL", "BUY", "100", "2012-03-02"),
						array("C", "BUY", "100", "2012-02-02"),
						array("C", "BUY", "100", "2012-01-02"),
						array("IBM", "BUY", "100", "2011-05-02"),
						array("AAPL", "BUY", "100", "2011-09-02"));
		
		print "test <BR>";
		print "unique keys <BR>";
		print_r ($uniqueKey);
		print "keys: <BR>";
		print_r ($arrayKey);
		print "actual data <BR>";
		print_r ($array);
		
		foreach ($uniqueKey as $key)
		{
			print "<BR> key is ";
			print $key;
			$keypos = array_keys($arrayKey, $key);
			foreach ($keypos as $kp)
			{
				print "<BR> keypos " ;
				print $kp;
				print " date: ";
				print $array[$kp][3];
			}
		}
*/

		$uniqueSymbol = array();
		$tranPandL = array();
		// get unique symbol from transaction table that has sell
		$query = "select distinct symbol from turtle_portfolio_transaction where trade_type = 'SELL' order by trade_date asc ";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			array_push ($uniqueSymbol, $data[0]);
		}
		
		$fullTran = array();
		$tranKey = array();
		// get full transaction table 
		$query = "select symbol, trade_type, trade_date,shares, price, risk, risk_pct from turtle_portfolio_transaction where portfolio_id = 1 order by trade_date asc ";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			array_push ($tranKey, $data[0]);
			array_push ($fullTran, array($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]));
			
		}
		
##		print "<BR> unique trade symbol";
##		print_r ($uniqyeSymbol);	
##		print "<BR> Key ";
##		print_r ($tranKey);
##		print "<BR> Full Tran: ";
##		print_r ($fullTran);

		$maxRMGain = 0;
		$maxRMLoss = 100000;
		$avgRMultiple = 0;
		
		$profitTradeCount =0;
		$lossTradeCount = 0;
		
		$profitRML = 0;
		$lossRML = 0;
		$avgProfitRML = 0;
		$avgLossRML = 0;
		
		$totalSellCount = 0;	
		$totalRMultiple = 0;

		foreach ($uniqueSymbol as $key)
		{
			$tranCount = 0;

			print "<BR> key is ";
			print $key;
			print "<BR>";
			$keypos = array_keys($tranKey, $key);
			foreach ($keypos as $kp)
			{
				$tradeType = $fullTran[$kp][1];
				$tradeDate = $fullTran[$kp][2];
				$shares = $fullTran[$kp][3];
				$price = $fullTran[$kp][4];
				$risk_dollar = $fullTran[$kp][5];
				$risk_pct = $fullTran[$kp][6];



				//print "<BR> keypos " ;
				//print $kp;
		/*		print " Type: ";
				print $fullTran[$kp][1];
				print " Date: ";
				print $fullTran[$kp][2];
				print " Shares: ";
				print $fullTran[$kp][3];
				print " Price: ";
				print $fullTran[$kp][4];
				print " tran count: ";
				print $tranCount;
				print "<BR>";	
		*/		

				if ($tradeType == 'BUY') 
				{
					if ($tranCount == 0) 
					{
						$totalShares = $shares;
						$firstPurchaseDate = $tradeDate;
						$riskValue = $shares * $price;
						$cost = $riskValue;
						$initRiskDollar = $risk_dollar;
						$initRiskPct = $risk_pct;
						
					}
					//pyramid buy
					else 
					{
						$totalShares += $shares;
						$cost += ($shares * $price); 
					}
					
					$tranCount ++;

				} elseif ($tradeType == 'SELL')
				{
					$soldValue = $shares * $price;
					$soldDate = $tradeDate;
					
					$holdingPeriod = dateDiff($firstPurchaseDate, $soldDate);
					$pl = $soldValue - $cost ;
					
					$soldRiskDollar = $risk_dollar;
					$soldRiskPct = $risk_pct;
					
					$profitLossRMultiple = $pl / $initRiskDollar;
					
					
					
					print "<BR>";
					print "holding period: ";
					print $holdingPeriod;
					print " profit/loss: ";
					print $pl ;
					print " PL Multiple: ";
					print $profitLossRMultiple;
					
					$totalRMultiple += $profitLossRMultiple;	
					if ($profitLossRMultiple > $maxRMGain) {
						$maxRMGain = $profitLossRMultiple;
					}
					if ($profitLossRMultiple < $maxRMLoss) {
						$maxRMLoss = $profitLossRMultiple;
					}
					
					if ($pl > 0)
					{
						$profitTradeCount ++;
						$profitRML += $pl / $initRiskDollar;
					} else {
						$lossTradeCount ++;
						$lossRML += $pl / $initRiskDollar;
					}
							
					$totalSellCount ++;
					
					array_push($tranPandL, array($key, $holdingPeriod, $pl, $profitLossRMultiple));

					
					$tranCount = 0;
				}
				


				
				
			}
			
			
		}
		
						$avgRMultiple = $totalRMultiple / $totalSellCount;
						$avgProfitRML = $profitRML / $profitTradeCount;
						$avgLossRML = $lossRML / $lossTradeCount;
						
						$profitTradePct = $profitTradeCount / ($profitTradeCount + $lossTradeCount);
						$lossTradePct = $lossTradeCount / ($profitTradeCount + $lossTradeCount);
						$expRML = ($profitTradePct * $avgProfitRML) + ($lossTradePct * $avgLossRML);
						
				print "<BR> avg R Multiple: ";
				print $avgRMultiple;
				print " num of sell: ";
				print $totalSellCount;
				print " max Gain: ";
				print $maxRMGain;
				print " max Loss: ";
				print $maxRMLoss;
				print "<BR> profit trade: ";
				print $profitTradeCount;
				print " profit pct: ";
				print $profitTradePct;
				print " avg profit RML: ";
				print $avgProfitRML;
				print " <BR>loss trade: ";
				print $lossTradeCount;
				print " loss pct: ";
				print $lossTradePct;
				print " avg loss RML: ";
				print $avgLossRML;
				print "<BR> Exp:";
				print $expRML;

		//print array
		$arraycount = count($tranPandL);
		print "<BR> array count: ";
		print $arraycount;
		
		for ($i=0; $i < $arraycount; $i ++) {
			print "<BR> symbol: ";
			print $tranPandL[$i][0];
			print " holding days: ";
			print $tranPandL[$i][1];
			print " PandL: ";
			print $tranPandL[$i][2];
			print " RMultiple: ";
			print $tranPandL[$i][3];
			
			$query  = "insert into turtle_transaction_pandl values (1, '";
			$query .= $tranPandL[$i][0]."', ".$tranPandL[$i][1].", ".$tranPandL[$i][2].", ".$tranPandL[$i][3].")";
			$result = queryMysql($query);
		}


//$interval = $date1->diff($date2);
//echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days ";



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
		
		
	} elseif ($_GET['action'] == 'calculateTransactionPandL') {
		$uniqueSymbol = array();
		// get unique symbol from transaction table that has sell
		$query = "select distinct symbol from turtle_portfolio_transaction where trade_type = 'SELL' order by trade_date asc ";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			array_push ($uniqueSymbol, $data[0]);
		}
		
		$fullTran = array();
		$tranKey = array();
		$tranPandL = array();
		// get full transaction table 
		$query = "select symbol, trade_type, trade_date,shares, price, risk, risk_pct from turtle_portfolio_transaction where portfolio_id = 1 order by trade_date asc ";
		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			array_push ($tranKey, $data[0]);
			array_push ($fullTran, array($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]));
			
		}

		$maxRMGain = 0;
		$maxRMLoss = 100000;
		$avgRMultiple = 0;
		
		$profitTradeCount =0;
		$lossTradeCount = 0;
		
		$profitRML = 0;
		$lossRML = 0;
		$avgProfitRML = 0;
		$avgLossRML = 0;
		
		$totalSellCount = 0;	
		$totalRMultiple = 0;

		foreach ($uniqueSymbol as $key)
		{
			$tranCount = 0;

			$keypos = array_keys($tranKey, $key);
			foreach ($keypos as $kp)
			{
				$tradeType = $fullTran[$kp][1];
				$tradeDate = $fullTran[$kp][2];
				$shares = $fullTran[$kp][3];
				$price = $fullTran[$kp][4];
				$risk_dollar = $fullTran[$kp][5];
				$risk_pct = $fullTran[$kp][6];

				if ($tradeType == 'BUY') 
				{
					if ($tranCount == 0) 
					{
						$totalShares = $shares;
						$firstPurchaseDate = $tradeDate;
						$riskValue = $shares * $price;
						$cost = $riskValue;
						$initRiskDollar = $risk_dollar;
						$initRiskPct = $risk_pct;
print " first buy: ";
print " symbol: ";
print $key;
print " shares: ";
print $totalShares;
print " risk value: ";;
print $riskValue;
print " cost: ";
print $cost*$totalShares;
print "<br>";		

					}
					//pyramid buy
					else 
					{				
						$totalShares += $shares;
						$cost += ($shares * $price); 

print " pyramid: symbol: ";
print $key;	
print " total shares: ";
print $totalShares;
print " shares: ";
print $shares;
print " price: ";
print $price;
print " cost: ";
print $cost;
print "<br>";

					}
					
					$tranCount ++;

				} elseif ($tradeType == 'SELL')
				{
					//$soldValue = $shares * $price;
					$soldValue = $totalShares * $price;

					$soldDate = $tradeDate;
					
					$holdingPeriod = dateDiff($firstPurchaseDate, $soldDate);
					$pl = $soldValue - $cost ;
print " SOLD: ";					
print "symbol: ";
print $key;
print " cost: ";
print $cost;
print " shares sold: ";
print $shares;
print " total shares: ";
print $totalShares;
print "sold value: ";
print $soldValue;
print " price: ";
print $price;
print " pl: ";
print $pl;
print "<br>";
					
					$soldRiskDollar = $risk_dollar;
					$soldRiskPct = $risk_pct;
					
					$profitLossRMultiple = $pl / $initRiskDollar;
					
					$totalRMultiple += $profitLossRMultiple;	
					if ($profitLossRMultiple > $maxRMGain) {
						$maxRMGain = $profitLossRMultiple;
					}
					if ($profitLossRMultiple < $maxRMLoss) {
						$maxRMLoss = $profitLossRMultiple;
					}
					
					if ($pl > 0)
					{
						$profitTradeCount ++;
						$profitRML += $pl / $initRiskDollar;
					} else {
						$lossTradeCount ++;
						$lossRML += $pl / $initRiskDollar;
					}
							
					$totalSellCount ++;
					
					// store p and l information in an array
					array_push($tranPandL, array($key, $holdingPeriod, $pl, $profitLossRMultiple));
						
					$tranCount = 0;
					$totalShares = 0;
				}	
			}
			
			
		}
		
		$avgRMultiple = round($totalRMultiple / $totalSellCount, 2);
		$avgProfitRML = $profitRML / $profitTradeCount;
		$avgLossRML = $lossRML / $lossTradeCount;
		
		$profitTradePct = $profitTradeCount / ($profitTradeCount + $lossTradeCount);
		$lossTradePct = $lossTradeCount / ($profitTradeCount + $lossTradeCount);
		$expRML = ($profitTradePct * $avgProfitRML) + ($lossTradePct * $avgLossRML);
						

		//insert p and l result into table
		$arraycount = count($tranPandL);

		for ($i=0; $i < $arraycount; $i ++) {
			$query  = "insert into turtle_transaction_pandl values (1, '";
			$query .= $tranPandL[$i][0]."', ".$tranPandL[$i][1].", ".$tranPandL[$i][2].", ".$tranPandL[$i][3].")";
			$result = queryMysql($query);
		}
		
		
	} elseif ($_GET['action'] == 'getTransactionPandL') {
		// get all transactions p and l
		$query= "select symbol, holding_days, profit_loss, r_multiple from turtle_transaction_pandl where portfolio_id = 1 order by profit_loss desc";
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
		
		turtle_portfolio_sell ($date);
		
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
		
		$value = get_historical_turtle_portfolio_value($date);
		
		$json1 = array();
		$json1[0]['pvalue'] = $value ;
		
		
		echo json_encode($json1);
	}  elseif($_GET['action'] == 'get_historical_portfolio_return'){ 
		global $original_investment;
		
		$today_date = $_GET['today_date'];
		$date = $_GET['date'];
		
		
		$preturn = get_historical_turtle_portfolio_value($date);
		
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
		

		
	}  elseif($_GET['action'] == 'simulate_range_trade'){ 
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
		$portfolioID = 1;
		reset_portfolio($portfolioID);

//print strftime('%c');
		$breakOutSignal = "55_DAY_HIGH";

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
			

		//create temporary table to store daily pricing for comparisons		
		$simplePriceHistory = "simple_price_history";
		$query = "drop table if exists ".$simplePriceHistory;
		$result = queryMysql($query);
		$query  = "create table ".$simplePriceHistory." select symbol, trade_date, trade_date_id, open, high, low, close, daily_change, pct_change,  ";
		$query .= "55_DAY_HIGH, 20_DAY_HIGH from price_history where trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		$result = queryMysql($query);


		$query = "select trade_date from price_history where symbol = 'AAPL' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
		$result = queryMysql($query);

		$minReturn = 100;
		$maxReturn = -100;
		

		while ($data = mysql_fetch_row($result)) {
			$trade_date = $data[0];
			
			turtle_portfolio_sell ($trade_date);		
			turtle_portfolio_pyramid_buy ($trade_date);
			turtle_portfolio_buy($trade_date, $breakOutSignal, $ADX_filter, $breakOutOrderBy);
			turtle_portfolio_update_stop_loss($trade_date); 
		
			$value = get_historical_turtle_portfolio_value($trade_date);
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

			$query2 = "insert into turtle_portfolio_performance values (1, '".$portfolioReturn[$count]['trade_date']."', ".$portfolioReturn[$count]['dollar_return'].", ".$portfolioReturn[$count]['return'].")";
			$result2 = queryMysql($query2);			

			//$retArray[$count] = array($trade_date, $preturn);
			//array_push($retArray, array(strtotime($trade_date), $preturn));
//$newDateStr = gmdate("Y-m-d", strtotime('2012-07-25'));
//$newDateStr = $newDateStr * 1000;
$newDateStr = strtotime($trade_date);
$newDateStr = $newDateStr * 1000 - 14400000;

			array_push($retArray, array($newDateStr, $preturn));
			
			$count ++;
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
				//print "return: $preturn";

				//array_push($retArray, array($newDateStr, $preturn));
				array_push($retArray, array($newDateStr, $popen, $phigh, $plow, $pclose));
				//array_push($retArray, {$newDateStr, $preturn});
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
			// if end date is not supplied, default to today		
			if (!$end_date) {
				$end_date = date("Y-m-d");  
			}
			
			$retArray = array();
			$stockRetArray = array();	
	
			$stockRetArray = stock_transaction_record($symbol, $start_date, $end_date);
	
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
		
		// get performance of stock for each trade date compared to price on starting day
		$query  = "select trade_date, close, ((close - ".$startPrice.")/".$startPrice." * 100), open, high, low ";
		$query .= "from price_history where symbol = '".$symbol."' and trade_date > '".$start_date."' and trade_date <= '".$end_date."'";		

		$result = queryMysql($query);
		while ($data = mysql_fetch_row($result)) {
			$perfArray[$count]['trade_date'] = $data[0];
			$perfArray[$count]['close'] = $data[1];
			$perfArray[$count]['return'] = $data[2];
			$perfArray[$count]['open'] = $data[3];
			$perfArray[$count]['high'] = $data[4];
			$perfArray[$count]['low'] = $data[5];

			$count ++;	
		}
		
		return $perfArray;
}

function stock_transaction_record ($symbol, $start_date) {
		$stockArray = array();
		$count = 0;
		
		// get close price for symbol on starting date
		$query  = "select trade_type, trade_date, shares, price, risk, risk_pct from turtle_portfolio_transaction where symbol = '".$symbol."' and portfolio_id = 1  ";
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

function turtle_portfolio_sell ($date) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		$sell_price = 0;

		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares, risk, risk_pct, high ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = 1 ";
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


			$i++;						
		}

		for ($x=0; $x < $i; $x++) {
			# determine sell price. If price of day high is < stop loss, sell price will be price of day high
			if ($ph[$i]['high'] < $ph[$x]['stop_loss']) {
				$sell_price = $ph[$x]['high'];
			} else {
				$sell_price = $ph[$x]['stop_loss'];
			}
//print "sell price";
//print $sell_price;
			# calculate sales proceed for stock, stock to be sold at stop_loss price 
			//$stock_sales = $ph[$x]['shares'] * $ph[$x]['stop_loss'];
			$stock_sales = $ph[$x]['shares'] * $sell_price;

			
			$update_portfolio_query = "update turtle_portfolio set shares = shares + ".$stock_sales." where portfolio_id = 1 and symbol ='CASH'";
			$result = queryMysql($update_portfolio_query);
			$delete_stock_query = "delete from turtle_portfolio where portfolio_id = 1 and symbol = '".$ph[$x]['symbol']."'";
			$result = queryMysql($delete_stock_query);
			
			//$insert_transaction_history = "insert into turtle_portfolio_transaction values (1, '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$ph[$x]['stop_loss'].", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";
			$insert_transaction_history = "insert into turtle_portfolio_transaction values (1, '".$ph[$x]['symbol']."', 'SELL', '".$date."', ".$ph[$x]['shares'].", ".$sell_price.", ".$ph[$x]['risk'].", ".$ph[$x]['risk_pct'].")";

			$result = queryMysql($insert_transaction_history);
			
		}		  

}  
	
function turtle_portfolio_update_stop_loss ($date) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		$query  = "select a.symbol, close, low, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW, 10_DAY_LOW, 50_MA, 200_MA, stop_loss, stop_buy, shares ";
		$query .= "from turtle_portfolio a, price_history b ";
		$query .= "where a.symbol = b.symbol ";
		$query .= "and a.portfolio_id = 1 ";
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
			$new_stop_loss = $ph[$x]['close'] - (2*$ph[$x]['ATR']);


			if ($ph[$x]['stop_loss'] < ($ph[$x]['close'] - 2*$ph[$x]['ATR']))
			{
				$new_stop_loss = $ph[$x]['close'] - (2*$ph[$x]['ATR']);
				
				$update_sql = "update turtle_portfolio set stop_loss = ".$new_stop_loss." where symbol = '".$ph[$x]['symbol']."'";
				$result = queryMysql($update_sql);
			}
		
		}		  


	}

function turtle_portfolio_buy($date, $breakOutSignal, $ADX_filter, $breakOutOrderBy) {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		$portfolioID = 1;
		$dailyBuyList = "turtle_daily_buy_list";
		
		//$breakOutSignal = "55_DAY_HIGH";

		### get list of breakout stocks
		//$breakOutStockArray = array();

		$breakOutStockArray = getBreakoutStock5 ($date, $breakOutSignal, $breakOutOrderBy, $portfolioID, $dailyBuyList);

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
				
				$portfolio_value = get_historical_turtle_portfolio_value($date);

				### get current available cash		
				$query = "select shares from turtle_portfolio where portfolio_id = 1 and symbol = 'CASH'";
				$result = queryMysql($query);
				while ($data = mysql_fetch_row($result)) {
					$cash = $data[0];
				}		
				$risk_value = $portfolio_value * $risk_factor / 100;
				$current_N = $breakOutStockArray[$x]['ATR'];
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

				$current_risk = get_current_risk();

				if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
					$cash = $cash - $purchase_value;
				
					$risk_dollar = $num_shares * (2 * $current_N);
					$risk_pct = ($risk_dollar / $portfolio_value) * 100;

					// insert into turtle_portfolio
					$my_sql  = "insert into turtle_portfolio (portfolio_id, symbol, last_price, shares, cost_basis, stop_loss, stop_buy, risk, risk_pct) ";
					$my_sql .= "values (1, '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= $breakOutStockArray[$x]['close'].", ";
					$my_sql .= $num_shares.", ";
//					$my_sql .= $breakOutStockArray[$x][$breakOutSignal].", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $stop_loss.", ";
					$my_sql .= $stop_buy.", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";
//print $my_sql;
//print "\n";
					$result = queryMysql($my_sql);

					// insert into transaction history table
					$my_sql  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct) ";
					$my_sql .= "values (1, '".$breakOutStockArray[$x]['symbol']."', ";
					$my_sql .= "'BUY', ";
					$my_sql .= "'".$date."', ";
					$my_sql .= $num_shares.", ";
//					$my_sql .= $breakOutStockArray[$x][$breakOutSignal].", ";
					$my_sql .= $breakOutStockArray[$x]['purchase_price'].", ";
					$my_sql .= $risk_dollar.", ";
					$my_sql .= $risk_pct." )";
	
//print "tranx: $my_sql";				
					$result = queryMysql($my_sql);

					// update cash position
					$my_sql  = "update turtle_portfolio set shares = ".$cash." where symbol = 'CASH'";
					$result = queryMysql($my_sql);
//print "update: $my_sql";

				}
				
				$pyramid_mode ++;
		}
		
		populateDailyBuyList ($date, $breakOutSignal, $rankAndWeightArray, $portfolioID, $dailyBuyList);

} 


function turtle_portfolio_pyramid_buy ($date) { 
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;
		
		$risk_factor = 1 / $max_num_holdings;
	
		$my_sql = "select a.symbol, a.shares, c.high, c.trade_date, a.stop_loss, a.stop_buy, c.ATR, a.cost_basis, a.risk, a.risk_pct, c.low, c.open from turtle_portfolio a, price_history c where a.symbol = c.symbol  and c.trade_date = '".$date."' and c.high > a.stop_buy order by c.pct_change desc";

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

			### get portfolio value
			$portfolio_value = get_historical_turtle_portfolio_value($date);

			### get current available cash		
			$query = "select shares from turtle_portfolio where portfolio_id = 1 and symbol = 'CASH'";
			$result2 = queryMysql($query);
			while ($data2 = mysql_fetch_row($result2)) {
				$cash = $data2[0];
			}					

			$risk_value = $portfolio_value * $risk_factor / 100;

			$num_shares = floor($risk_value /(2*$this_ATR));


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


			$current_risk = get_current_risk();

			if (($cash > $purchase_value) && ($current_risk < $max_risk)) {
		
				$stop_loss = $this_stop_buy - (2*$this_ATR);
				$stop_buy = $this_stop_buy + $this_ATR;
				
				$avg_cost_basis = (($this_shares * $this_cost_basis) + $purchase_value) / ($this_shares + $num_shares) ;
				$total_shares = $this_shares + $num_shares;
				
				$risk_dollar = $this_shares * (2 * $this_ATR) + $this_risk;
				$risk_pct = ($risk_dollar / $portfolio_value) * 100;
				
				$query3 = "update turtle_portfolio set shares = ".$total_shares.", cost_basis = ".$avg_cost_basis.", stop_loss = ".$stop_loss.", stop_buy=".$stop_buy.", risk=".$risk_dollar.", risk_pct=".$risk_pct." where symbol = '".$this_symbol."'";
				$result3 = queryMysql($query3);
//print "query 3: $query3 ";

				// insert into transaction history table
				$query4  = "insert into turtle_portfolio_transaction (portfolio_id, symbol, trade_type, trade_date, shares, price, risk, risk_pct) ";
				$query4 .= "values (1, '".$this_symbol."', ";
				$query4 .= "'BUY', ";
				$query4 .= "'".$date."', ";
				$query4 .= $num_shares.", ";
				$query4 .= $this_stop_buy.", ";
				$query4 .= $risk_dollar.", ";
				$query4 .= $risk_pct." )";
				
				$result4 = queryMysql($query4);

				$cash_left = $cash - $purchase_value;
				// update cash position
				$query5  = "update turtle_portfolio set shares = ".$cash_left." where symbol = 'CASH'";
				$result5 = queryMysql($query5);
				
			}				
				
		}

}


 
function getBreakoutStock ($today_date, $movingAvg, $orderBy) {
		//$movingAvg = "55_DAY_HIGH";
	
		$query  = "select a.symbol, trade_date, high, low, close, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 50_MA, 200_MA ";
		$query .= "from price_history a, stock_list b ";
		$query .= "where trade_date = '".$today_date."'";
		$query .= "and a.symbol = b.symbol ";
		$query .= " and close > ".$movingAvg;
		$query .= " and 50_MA > 200_MA";
		$query .= " and ".$movingAvg." > 0 ";
		// check if price movement is at least > 0.8xATR
		//$query .= " and daily_change > 0.8*ATR ";
		$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
		//$query .= " order by pct_change desc";
		
		//$query .= " and relative_avg_vol > 150 ";
		$query .= " order by ".$orderBy." desc";

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

 		//echo json_encode($ret);

		return $ret;		
} 

// get breakout stock with weighted critiria
function getBreakoutStock2 ($today_date, $movingAvg, $rankAndWeightArray) {
	
		$masterRankByResult = array();
		$rankResult = array();
	
		foreach ($rankAndWeightArray as $rankBy => $rankWeight)
		{		
			$query  = "select a.symbol, trade_date, open, high, low, close, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 50_MA, 200_MA ";
			$query .= "from price_history a, stock_list b ";
			$query .= "where trade_date = '".$today_date."'";
			$query .= "and a.symbol = b.symbol ";
			$query .= " and close > ".$movingAvg;
			$query .= " and 50_MA > 200_MA";
			$query .= " and ".$movingAvg." > 0 ";
			$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
			$query .= " order by ".$rankBy." desc";
	
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
	            
	            if ($tmpArray[$i][$movingAvg] > $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i][$movingAvg];
	            }  elseif ($tmpArray[$i][$movingAvg] < $tmpArray[$i]['open']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['open'];
	            }  elseif ($tmpArray[$i][$movingAvg] < $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['low'];
	            }
	            
	            if (!$tmpArray[$i]['purchase_price']){
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i][$movingAvg];
		            
	            }
	            
	        	$i++;
	        }
		
	        $masterRankByResult[$rankBy] = $tmpArray;
	        $rankResult[$rankBy] = $tmpRankArray;
		
		}
		
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

	return $finalRet;
}

function getBreakoutStock3 ($today_date, $movingAvg, $rankAndWeightArray) {
		//$movingAvg = "55_DAY_HIGH";
	
		$masterRankByResult = array();
		$rankResult = array();
	
		foreach ($rankAndWeightArray as $rankBy => $rankWeight)
		{
			print "rank by: ";
			print $rankBy;
			print "rank weight: ";
			print $rankWeight;
			print "<br>";
		
			$query  = "select a.symbol, trade_date, high, low, close, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 50_MA, 200_MA ";
			$query .= "from price_history a, stock_list b ";
			$query .= "where trade_date = '".$today_date."'";
			$query .= "and a.symbol = b.symbol ";
			$query .= " and close > ".$movingAvg;
			$query .= " and 50_MA > 200_MA";
			$query .= " and ".$movingAvg." > 0 ";
			$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
			$query .= " order by ".$rankBy." desc";
	
			$query = stripslashes($query);
			$result = queryMysql($query);
			
			$tmpArray = array();
			$tmpRankArray = array();
	
			$i = 0;
	  	  	while ($row = mysql_fetch_assoc($result)) {
	    		foreach ($row as $key => $value) {
	   	         //$rowRet[] = $value;
	   	         	$tmpArray[$i][$key] = $value;
	   	         	
	   	         	if ($key == "symbol")
	   	         	{
		   	         	$tmpRankArray[$value] = $i;		   	    
	   	         	}
	            }
	        	$i++;
	        }
		
	        $masterRankByResult[$rankBy] = $tmpArray;
	        $rankResult[$rankBy] = $tmpRankArray;
		
		}
		
		$combineRank = array();

		print "rank array <br>";
		foreach ($rankResult as $rankBy => $resultArray)
		{
			print "array count: ";
			print count($resultArray);
			print_r($resultArray);
			
			foreach ($resultArray as $symbol => $value) 
			{
				print " symbol: ";
				print $symbol;
				print " weigth: ";
				print $rankAndWeightArray[$rankBy];
				$combineRank[$symbol] += $value * $rankAndWeightArray[$rankBy];
		
				print " calculated weight";
				print $combineRank[$symbol];
				
			}
			
			print "<br>";
		}
		print "end:----- <br>";		
		

		asort($combineRank);
		
		print "<br> final rank <br>";
		print_r($combineRank);
		

		$i = 0;

		foreach ($combineRank as $key => $value)
		{
			$keyPos = $tmpRankArray[$key];
			$finalRet[$i] = $tmpArray[$keyPos];
			$i++;
		}

	return $finalRet;
}

// get breakout stock with weighted critiria
function getBreakoutStock4 ($today_date, $movingAvg, $rankAndWeightArray) {
	
		$masterRankByResult = array();
		$rankResult = array();
	
		foreach ($rankAndWeightArray as $rankBy => $rankWeight)
		{		
			/* types of query
			1: pct_change
			2: relative_avg_vol
			3: vsSpyEMA
			
			query stock where today's high is greater than yesterda's moving average
			provided that yesterday's 50 MA is greater than yesterday's 200 MA
			use table simple_price_history to compare against full price_history with trade_date_id - 1
			*/
	/*		$query  = "select a.symbol, trade_date, high, low, close, daily_change, pct_change, ATR, 55_DAY_HIGH, 20_DAY_HIGH, 50_MA, 200_MA ";
			$query .= "from price_history a, stock_list b ";
			$query .= "where trade_date = '".$today_date."'";
			$query .= "and a.symbol = b.symbol ";
			$query .= " and close > ".$movingAvg;
			$query .= " and 50_MA > 200_MA";
			$query .= " and ".$movingAvg." > 0 ";
			$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
			$query .= " order by ".$rankBy." desc";
*/
			$query  = "select c.symbol, c.trade_date, c.open, c.high, c.low, c.close, c.pct_change, ";
			$query .= "a.ATR, a.55_DAY_HIGH, a.20_DAY_HIGH, a.50_MA, a.200_MA ";
			$query .= "from price_history a, stock_list b, simple_price_history c ";
			$query .= "where c.trade_date = '".$today_date."' ";
			$query .= "and c.symbol = b.symbol ";
			$query .= "and a.symbol = c.symbol ";
			$query .= "and a.trade_date_id = (c.trade_date_id - 1) ";
			$query .= " and c.high > a.".$movingAvg;
			$query .= " and a.50_MA > a.200_MA";
			$query .= " and a.".$movingAvg." > 0 ";
			$query .= " and c.symbol not in (select symbol from turtle_portfolio where portfolio_id = 1) ";
			$query .= " order by a.".$rankBy." desc";

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
	            // if high > moving avg 
	            // 		AND moving avg > low, then purchase price = moving avg
	            //		AND moving avg < open, the purchase price = opening price
	            if ($tmpArray[$i][$movingAvg] > $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i][$movingAvg];
	            } elseif ($tmpArray[$i][$movingAvg] < $tmpArray[$i]['open']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['open'];
	            } elseif ($tmpArray[$i][$movingAvg] < $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['low'];
	            }
	            
	            if (!$tmpArray[$i]['purchase_price']){
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i][$movingAvg];
		            
	            }
	            
	        	$i++;
	        }
		
	        $masterRankByResult[$rankBy] = $tmpArray;
	        $rankResult[$rankBy] = $tmpRankArray;
		
		}
		
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

	return $finalRet;
}

// get breakout stock with weighted critiria
// put stocks in the daily buy list
function getBreakoutStock5 ($today_date, $movingAvg, $rankAndWeightArray, $portfolioID, $dailyBuyList) {
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
			$query  = "select d.rank, a.symbol, a.trade_date, a.open, a.high, a.low, a.close, a.pct_change, ";
			$query .= "a.ATR, a.55_DAY_HIGH, a.20_DAY_HIGH, a.50_MA, a.200_MA, d.buy_price ";
			$query .= "from price_history a, turtle_daily_buy_list d ";
			$query .= " where a.trade_date = '".$today_date."'";
			$query .= " and a.symbol = d.symbol ";
			$query .= " and a.high > d.buy_price ";
			//$query .= " and a.trade_date = d.trade_date ";
			$query .= " order by d.rank asc";
			$query = stripslashes($query);

//print $query;
//print "\n";

			$result = queryMysql($query);
	
/*if ($today_date == "2012-12-05")
{
	print "breakout stock: ";
	print $query;
	print "<br>";
	
	//exit;
}
*/		
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
	            //		AND stop buy price < open, the purchase price = opening price
	            if ($tmpArray[$i]['buy_price'] > $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['buy_price'];
	            } elseif ($tmpArray[$i]['buy_price'] < $tmpArray[$i]['open']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['open'];
	            } elseif ($tmpArray[$i]['buy_price'] < $tmpArray[$i]['low']) {
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i]['low'];
	            }
	            
	            if (!$tmpArray[$i]['purchase_price']){
		            $tmpArray[$i]['purchase_price'] = $tmpArray[$i][$movingAvg];
		            
	            }
	            
	        	$i++;
	        }
		
	        $masterRankByResult[$rankBy] = $tmpArray;
	        $rankResult[$rankBy] = $tmpRankArray;
		
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

	return $finalRet;
}

function populateDailyBuyList ($today_date, $movingAvg, $rankAndWeightArray, $portfolioID, $dailyBuyList) {
			if (!$portfolioID) {
				$portfolioID = 1;
			}
			if (!$dailyBuyList) {
				$dailyBuyList = "turtle_daily_buy_list";
			}
			//clear daily buy list
			$query = "delete from ".$dailyBuyList." where portfolio_id = ".$portfolioID;
			$result = queryMysql($query);
			// reset identity column
			$query = "alter table ".$dailyBuyList." auto_increment = 1";
			$result = queryMysql($query);
			
			$query = "insert into ".$dailyBuyList." (portfolio_id, trade_date, symbol, buy_price) ";
			$query .= "select ".$portfolioID.", a.trade_date, a.symbol, a.".$movingAvg;
			$query .= " from price_history a, stock_list b ";
			$query .= "where a.trade_date = '".$today_date."'";
			$query .= "and a.symbol = b.symbol ";
			$query .= " and a.50_MA > a.200_MA";
			$query .= " and a.".$movingAvg." > 0 ";
			$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = ".$portfolioID.") ";
			$query .= " order by vsSpyEMA desc";
			$query = stripslashes($query);

			$result = queryMysql($query);
}



function get_real_time_turtle_portfolio_value() {
	$my_sql = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	
	$result = queryMysql($my_sql);
	
	while ($data = mysql_fetch_row($result)) {
		$value = $data[0];
	}
	
	return $value;
}

function get_historical_turtle_portfolio_value($trade_date) {
	//$my_sql = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = 1 and a.symbol = b.symbol ";
	$my_sql  = "select sum(a.shares * b.close) from turtle_portfolio a, price_history b where a.portfolio_id = 1 and a.symbol = b.symbol and ";
	$my_sql .= "b.trade_date = '".$trade_date."' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = 1 ";
	
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
	
}
 
function get_current_risk() {
	$p_value =get_real_time_turtle_portfolio_value();
	
	$tmp_sql = "select sum(risk) from turtle_portfolio where symbol != 'CASH'";
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

function test_cross_script () {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;

		print "max num holding: ";
		print $max_num_holdings;
		
}

?>