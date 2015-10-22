
<?php

include_once 'dbfunction.php';
//include_once 'trend_setup.php';
//include_once 'trading_engine.php';


$max_risk = 10;
$max_num_holdings = 5;
$risk_factor = 1 / $max_num_holdings;
$original_investment = 1000000;

$stop_loss_muptiplier = 3;

if($_GET){
	if($_GET['action'] == 'get_market_forward_perf'){ 
			//$symbol = $_GET['symbol'];
			$day_forward = $_GET['day_forward'];
			$time_frame = $_GET['time_frame'];


				
			$swingArray = array();	
	
			$swingArray = chart_market_forward_perf($day_forward);
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
		
	} elseif($_GET['action'] == 'get_market_ratio'){ 
			//$symbol = $_GET['symbol'];
			$day_forward = $_GET['day_forward'];
			$time_frame = $_GET['time_frame'];


				
			$swingArray = array();	
	
			$swingArray = chart_market_ratio();
			
			$arrayLen = count($swingArray);

			echo json_encode($swingArray);
		
	} 

}

function chart_market_forward_perf ($day_forward) {
		$spArray = array();	
		$retArray = array();
		

		$query  = "select trade_date, 1_day_perf, 5_day_perf, 10_day_perf, 20_day_perf, 40_day_perf, 60_day_perf from spy_hist_price where symbol = 'SPY' ";
		//$query  = "select trade_date, ".$day_forward." from spy_hist_price where symbol = 'SPY' order by 1";
		

		$query= stripslashes($query);
		$result = queryMysql($query);
	
		while ($tmp_data = mysql_fetch_row($result)) {
			$eachRow = "";
			$eachRow['trade_date'] = $tmp_data[0];
//			$eachRow['day_forward_change'] = $tmp_data[1]*1/1;		
			
			if (($tmp_data[1]*1) == -100 ) {$tmp_data[1]=0;}
			if (($tmp_data[2]*1) == -100 ) {$tmp_data[2]=0;} 
			if (($tmp_data[3]*1) == -100 ) {$tmp_data[3]=0;} 
			if (($tmp_data[4]*1) == -100 ) {$tmp_data[4]=0;} 
			if (($tmp_data[5]*1) == -100 ) {$tmp_data[5]=0;} 
			if (($tmp_data[6]*1) == -100 ) {$tmp_data[6]=0;} 
 
			$eachRow['1_day_perf'] = $tmp_data[1]*1/1;			
			$eachRow['5_day_perf'] = $tmp_data[2]*1/1;			
			$eachRow['10_day_perf'] = $tmp_data[3]*1/1;			
			$eachRow['20_day_perf'] = $tmp_data[4]*1/1;		
			$eachRow['40_day_perf'] = $tmp_data[5]*1/1;			
			$eachRow['60_day_perf'] = $tmp_data[6]*1/1;			
	
	
			array_push($spArray, $eachRow);
		}		

	    $eachRow = "";
		
		$arrayLen = count($spArray);

		for ($x=0; $x<$arrayLen; $x++){
			//only display SPH / SPL			 		
				$newDateStr = strtotime($spArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;

				#$eachRow['x'] = $spArray[$x]['sell_date'];
				$eachRow['x'] = $newDateStr;

				$eachRow['y'] = $spArray[$x]['day_forward_change']*1;
				#$eachRow['title'] = $spArray[$x]['type'];
				
				
				if (($spArray[$x]['day_forward_change']*1) > 0 ) {
					$text = "Gain: ".$spArray[$x]['day_forward_change']*1;
				} else {
					$text = "Loss: ".$spArray[$x]['day_forward_change']*1;
				}
				#$eachRow['text'] = $text;

				##array_push($retArray, array($newDateStr, ($spArray[$x]['day_forward_change']*1)));
				array_push($retArray, array($newDateStr, ($spArray[$x]['1_day_perf']*1), ($spArray[$x]['5_day_perf']*1), ($spArray[$x]['10_day_perf']*1), ($spArray[$x]['20_day_perf']*1), ($spArray[$x]['40_day_perf']*1), ($spArray[$x]['60_day_perf']*1)));

				#array_push($retArray, $eachRow);
			
		}
		
		return $retArray;
		
		

}

function chart_market_ratio () {
		$spArray = array();	
		$retArray = array();
		

		$query  = "select trade_date, 20_high_over_20_low, 55_high_over_20_low, above_50_over_below_50, above_200_over_below_50, above_50_over_below_200, above_200_over_below_200, 20_high_over_above_50, 20_high_over_above_200, 20_high_over_below_50, 20_high_over_below_200 from spy_hist_price where symbol = 'SPY' ";
		//$query  = "select trade_date, ".$day_forward." from spy_hist_price where symbol = 'SPY' order by 1";
		

		$query= stripslashes($query);
		$result = queryMysql($query);
	
		while ($tmp_data = mysql_fetch_row($result)) {
			$eachRow = "";
			$eachRow['trade_date'] = $tmp_data[0];
//			$eachRow['day_forward_change'] = $tmp_data[1]*1/1;		
			

			$eachRow['20_high_over_20_low'] = $tmp_data[1]*1/1; 
			$eachRow['55_high_over_20_low'] = $tmp_data[2]*1/1;
			$eachRow['above_50_over_below_5'] = $tmp_data[3]*1/1; 
			$eachRow['above_200_over_below_50'] = $tmp_data[4]*1/1; 
			$eachRow['above_50_over_below_200'] = $tmp_data[5]*1/1; 
			$eachRow['above_200_over_below_200'] = $tmp_data[6]*1/1; 
			$eachRow['20_high_over_above_50'] = $tmp_data[7]*1/1; 
			$eachRow['20_high_over_above_200'] = $tmp_data[8]*1/1; 
			$eachRow['20_high_over_below_50'] = $tmp_data[9]*1/1; 
			$eachRow['20_high_over_below_200'] = $tmp_data[10]*1/1;
 	
	
			array_push($spArray, $eachRow);
		}		

	    $eachRow = "";
		
		$arrayLen = count($spArray);

		for ($x=0; $x<$arrayLen; $x++){
			//only display SPH / SPL			 		
				$newDateStr = strtotime($spArray[$x]['trade_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;

				#$eachRow['x'] = $spArray[$x]['sell_date'];
				$eachRow['x'] = $newDateStr;

				$eachRow['y'] = $spArray[$x]['day_forward_change']*1;
				#$eachRow['title'] = $spArray[$x]['type'];
				
				
				if (($spArray[$x]['day_forward_change']*1) > 0 ) {
					$text = "Gain: ".$spArray[$x]['day_forward_change']*1;
				} else {
					$text = "Loss: ".$spArray[$x]['day_forward_change']*1;
				}
				#$eachRow['text'] = $text;

				##array_push($retArray, array($newDateStr, ($spArray[$x]['day_forward_change']*1)));
				array_push($retArray, array($newDateStr, ($spArray[$x]['20_high_over_20_low']*1), ($spArray[$x]['55_high_over_20_low']*1), ($spArray[$x]['above_50_over_below_5']*1), ($spArray[$x]['above_200_over_below_50']*1), ($spArray[$x]['above_50_over_below_200']*1), ($spArray[$x]['above_200_over_below_200']*1) , ($spArray[$x]['20_high_over_above_50']*1) , ($spArray[$x]['20_high_over_above_200']*1) , ($spArray[$x]['20_high_over_below_50']*1) , ($spArray[$x]['20_high_over_below_200']*1) ));

				#array_push($retArray, $eachRow);
			
		}
		
		return $retArray;
		
		

}


?>
