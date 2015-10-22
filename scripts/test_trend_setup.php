#!/usr/local/bin/php5


<?php // trend_setup.php

include_once 'dbfunction.php';
include_once 'portfolio_selection.php';
include_once 'trend_setup.php';

if($_GET){
	if ($_GET['action'] == 'backup_get_swing_points'){ 
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
			
			$start_date = '2013-01-01';
	
//			$swingArray = calculate_swing_points($symbol, $start_date, $end_date, $pid);
			$swingArray = chart_swing_points($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} else if ($_GET['action'] == 'test_generate_swing_points'){ 
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

			$swingArray = generate_swing_points($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} else if ($_GET['action'] == 'test_generate_swing_points_and_trends'){ 
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

			$swingArray = generate_trends($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	}  else if ($_GET['action'] == 'test_get_last_spl') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$pid = $_GET['portfolio_id'];
		
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$retArray = array();
			$retArray = get_last_spl ($symbol, $current_date);
			
			print "spl date: ";
			print $retArray['date'];
			print " spl: ";
			print $retArray['price'];
	} else if ($_GET['action'] == 'test_get_last_sph') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$pid = $_GET['portfolio_id'];
		
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$retArray = array();
			$retArray = get_last_sph ($symbol, $current_date);
			
			print "sph date: ";
			print $retArray['date'];
			print " sph: ";
			print $retArray['price'];
	} else if ($_GET['action'] == 'test_get_most_hit_sp') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
			$pid = $_GET['portfolio_id'];
		
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$retArray = array();
			$retArray = get_most_hit_sp ($symbol, $current_date);
			
			print "spl date: ";
			print $retArray['date'];
			print " spl: ";
			print $retArray['price'];
	} else if ($_GET['action'] == 'test_get_current_trend') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$current_trend = get_current_trend ($symbol, $current_date);
			
			print "current trend: $current_trend";			
	} else if ($_GET['action'] == 'test_get_previous_trend') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$previous_trend = get_previous_trend ($symbol, $current_date);
			
			print "previous trend: $previous_trend";			
	} else if ($_GET['action'] == 'test_get_max_sph') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$max_sph = get_max_sph ($symbol, $current_date);
			
			print "max sph: ";
			print $max_sph['price'];			
	} else if ($_GET['action'] == 'test_get_min_spl') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}

			$min_spl = get_min_spl ($symbol, $current_date);
			
			print "min spl: ";
			print $min_spl['price'];			
	} else if ($_GET['action'] == 'test_generate_anchor_points'){ 
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

			$swingArray = generate_anchor_points($symbol, $start_date, $end_date, $pid);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
	} else if ($_GET['action'] == 'test_populate_swing_points') {
			$symbol = $_GET['symbol'];
			$current_date = $_GET['current_date'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}
			$time_frame = "ST";

			$spArray = populate_swing_points ($symbol, $current_date, $start_date, $time_frame);
			
			print "price: ";
			print $spArray['price'];			
	} else if ($_GET['action'] == 'test_get_max_sph_from_table') {
			$symbol = $_GET['symbol'];
			$time_frame = "ST";

			$spArray = get_max_sph_from_table($symbol, $time_frame);
			print $spArray['price'];
	} else if ($_GET['action'] == 'test_get_min_spl_from_table') {
			$symbol = $_GET['symbol'];
			$time_frame = "ST";
			$current_date = $_GET['current_date'];

			get_min_spl_from_table($symbol, $time_frame, $current_date);
	} else if ($_GET['action'] == 'test_get_last_sph_from_table') {
			$symbol = $_GET['symbol'];
			get_last_sph_from_table($symbol);
	} else if ($_GET['action'] == 'test_get_last_spl_from_table') {
			$symbol = $_GET['symbol'];
			get_last_spl_from_table($symbol);
	} else if ($_GET['action'] == 'test_populate_stock_trend') {
			$symbol = $_GET['symbol'];
			
			$current_date = $_GET['current_date'];
			$time_frame = 'ST';
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}			
			
			$start_date = null;
			populate_stock_trend($symbol, $current_date, $start_date, $time_frame);
	} else if ($_GET['action'] == 'test_get_last_stock_trend_from_table') {
			$symbol = $_GET['symbol'];
			$swingArray = array();	

			$swingArray = get_last_stock_trend_from_table($symbol);
			
			print "price: ";
			print $swingArray['price'];
	} else if ($_GET['action'] == 'test_get_second_to_last_stock_trend_from_table') {
			$symbol = $_GET['symbol'];
			$swingArray = array();	

			$swingArray = get_second_to_last_stock_trend_from_table($symbol);
	
			
			print "price: ";
			print $swingArray['price'];
	} else if ($_GET['action'] == 'test_populate_sw_trading_scan') {
			$current_date = $_GET['current_date'];
			$time_frame = $_GET['time_frame'];
	
			if (!$current_date) {
				$current_date = date("Y-m-d");  
			}			

			populate_sw_trading_scan($current_date, $time_frame);
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
	
			trade_theory_2_sw_lows_sell_min_spl($symbol);	
			
	} 

}

function trade_theory_2_sw_lows_sell_min_spl($symbol) {
	$resultArray = Array();
	$tmpArray = Array();
	$stockList = Array();
	$tmp = Array();
	$totalReturn = 0;
	$totalTransaction = 0;
	//$symbol = "SPY";
	
	$query = "select distinct symbol from stock_list where index_group = 'SP500'";
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmp['symbol'] = $tmp_data[0];		
		array_push ($stockList, $tmp);
	}		
	
	$numStock = count($stockList);

	
//for ($i=300; $i<$numStock; $i++) {	
//	$symbol = $stockList[$i]['symbol'];
//	$symbol = "CREE";

	print "working on number: $i symbol: $symbol \n";

	$query = "select a.symbol, a.close, a.trade_date, b.type, b.price from price_history a, sw_swing_points b where a.symbol = b.symbol and a.trade_date = b.trade_date and a.symbol = '".$symbol."'";
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
		
			$purchasePrice = 0;
			$splCount = 0;		

			
			print " sold on ";
			print $resultArray[$z]['trade_date'];
			print " price: ";
			print $resultArray[$z]['close'];
			
			print " period return: $return total return: $totalReturn \n";
			
			$totalTransaction ++;
		}
		
		

		
		
		
		
	}
//}

		print "\ntotal transaction: $totalTransaction \n";
		print "avg return per transaction: ";
		$avgReturn = $totalReturn / $totalTransaction;
		print $avgReturn;
		print "\n";
}



?>