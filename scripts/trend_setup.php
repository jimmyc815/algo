<?php // trend_setup.php


function calculate_swing_points ($symbol, $start_date, $end_date) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();

		$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);

		$arrayLen = count($stockRetArray);

		$sph_count = 0;
		$spl_count = 0;	
		$sph = 0;
		$spl = 10000;	
			
			
		for ($x = 0; $x < $arrayLen; $x++)
		{
			$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
			$newDateStr = $newDateStr * 1000 - 14400000;
			
			$pclose = $stockRetArray[$x]['close'] * 1 / 1;
			$popen = $stockRetArray[$x]['open'] * 1 / 1;
			$phigh = $stockRetArray[$x]['high'] * 1 / 1;
			$plow = $stockRetArray[$x]['low'] * 1 / 1;
			$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;
			$pavg_volume = $stockRetArray[$x]['avg_volume'] * 1 / 1;
			$prelative_avg_vol = $stockRetArray[$x]['relative_avg_vol'] * 1 / 1;
			
			if (!$prelative_avg_vol) {$prelative_avg_vol = 1;}
			if (!$pavg_volume) {$pavg_volume = 1;}
			
			// calculate weighted_volume = 20% * yesterday volume + 
			//							   30% * today volume + 
			//							   30% * tomorrow volume + 
			//							   20% * day after tomorrow volume
			$weighted_volume = 0 * $stockRetArray[$x-1]['volume'] +	0.8 * $stockRetArray[$x]['volume'] + 0.1 * $stockRetArray[$x+1]['volume'] + 0.1 * $stockRetArray[$x+2]['volume'] ;
			
			//$prelative_avg_vol = $weighted_volume * 100 / $pavg_volume * 1;
			/*
			$price = $stockRetArray[$x]['price'] * 1 / 1;
			$title = $stockRetArray[$x]['trade_type'];
			$text = $stockRetArray[$x]['trade_type']." ".$stockRetArray[$x]['shares']." shares at ".$price;

			$eachRow = array();
			$eachRow['x'] = $newDateStr;
			$eachRow['y'] = $price;
			$eachRow['title'] = $title;
			$eachRow['text'] = $text;
			array_push($retArray, $eachRow);
			*/
			
			
			if ($sph < $phigh) {
				$sph = $phigh;
				$sph_date = $newDateStr;
				$sph_count = 0;
				$sph_relative_avg_vol = $prelative_avg_vol;
			} else {
				$sph_count ++;
			}
			
			if ($sph_count == 6) {
				$eachRow['x'] = $sph_date;
				$eachRow['y'] = $sph;
				$eachRow['title'] = "SPH";
				//if ($sph_relative_avg_vol > 0) {
					array_push($retArray, $eachRow);
					$sph_relative_avg_vol = 0;
				//}
				
				//array_push($retArray, array($newDateStr, $sph));
				$sph_count = 0;
				$sph_date = $newDateStr;
				$sph = $phigh;
			}
			
			if ($spl > $plow) {
				$spl = $plow;
				$spl_date = $newDateStr;
				$spl_count = 0;
				$spl_relative_avg_vol = $prelative_avg_vol;

			} else {
				$spl_count ++;
			}
			
			if ($spl_count == 6) {
				$eachRow['x'] = $spl_date;
				$eachRow['y'] = $spl;
				$eachRow['title'] = "SPL";
				//if ($spl_relative_avg_vol > 0) {
					array_push($retArray, $eachRow);
					$spl_relative_avg_vol = 0;
				//}
				
				//array_push($retArray, array($newDateStr, $spl));
				$spl_count = 0;
				$spl_date = $newDateStr;
				$spl = $plow;
			}
		
		}			
			
		return $retArray;
	
}




function chart_swing_points ($symbol, $start_date, $end_date, $time_frame) {
		$spArray = array();	
		$retArray = array();
		
		$spArray = generate_swing_points ($symbol, $start_date, $end_date, $time_frame);
		
		$arrayLen = count($spArray);

		for ($x=0; $x<$arrayLen; $x++){
			//only display SPH / SPL
			if ($spArray[$x]['type'] == "SPH" || $spArray[$x]['type'] == "SPL")
			{ 
				if ($spArray[$x]['type'] == "SPH") {
					$sw_type = "H";
				} else 	if ($spArray[$x]['type'] == "SPL") {
					$sw_type = "L";
				} 
		
				$eachRow['x'] = $spArray[$x]['date'];
				$eachRow['y'] = $spArray[$x]['price'];
				#$eachRow['title'] = $spArray[$x]['type'];
				$eachRow['title'] = $sw_type;

				
				array_push($retArray, $eachRow);
			}
		}
		
		return $retArray;


}

function chart_test_transaction ($symbol, $gain_or_loss, $start_date, $end_date, $time_frame) {
		$spArray = array();	
		$retArray = array();
		
		if ($gain_or_loss == "gain") {
			$gain_loss_sql = " and pct_return >= 0 ";
		} else {
			$gain_loss_sql = " and pct_return < 0 ";
			
		}
		
		$query  = "select purchase_date, purchase_price, sell_date, sell_price, pct_return, holding_days from sw_test_transaction where symbol = '".$symbol."' and purchase_date > '".$start_date."' and sell_date <= '".$end_date."' ".$gain_loss_sql;

		$query= stripslashes($query);
		$result = queryMysql($query);
	
		while ($tmp_data = mysql_fetch_row($result)) {
			$eachRow = "";
			$eachRow['symbol'] = $symbol;
			$eachRow['purchase_date'] = $tmp_data[0];
			$eachRow['purchase_price'] = $tmp_data[1];
			$eachRow['sell_date'] = $tmp_data[2];
			$eachRow['sell_price'] = $tmp_data[3];
			$eachRow['pct_return'] = $tmp_data[4];
			$eachRow['holding_days'] = $tmp_data[5];
			
			array_push($spArray, $eachRow);
		}		

	    $eachRow = "";
		
		$arrayLen = count($spArray);

		for ($x=0; $x<$arrayLen; $x++){
			//only display SPH / SPL			 		
				$newDateStr = strtotime($spArray[$x]['sell_date']);
				$newDateStr = $newDateStr * 1000 - 14400000;

				#$eachRow['x'] = $spArray[$x]['sell_date'];
				$eachRow['x'] = $newDateStr;

				$eachRow['y'] = $spArray[$x]['sell_price']*1;
				#$eachRow['title'] = $spArray[$x]['type'];
				$text = "Purchase on: ".$spArray[$x]['purchase_date']." for ".$spArray[$x]['purchase_price']." Sell on ".$spArray[$x]['sell_date']." for ".$spArray[$x]['sell_price']." pct return: ".$spArray[$x]['pct_return']." holding days: ".$spArray[$x]['holding_days'];
				if (($spArray[$x]['pct_return']*1) > 0 ) {
					$title = "Gain: ".$spArray[$x]['pct_return']*1;
				} else {
					$title = "Loss: ".$spArray[$x]['pct_return']*1;
				}
				$eachRow['title'] = $title;
				$eachRow['text'] = $text;

				
				array_push($retArray, $eachRow);
			
		}
		
		return $retArray;


}

function chart_trends ($symbol, $start_date, $end_date, $time_frame) {
		$allArray = array();
		$spArray = array();	
		$retArray = array();
		$trendArray = array();
		
	//	$allArray = generate_swing_points_and_trends ($symbol, $start_date, $end_date);
		$allArray = generate_trends ($symbol, $start_date, $end_date, $time_frame);

	//	$spArray = $allArray[0];
		$trendArray = $allArray;
		
		//$trendArray = generate_swing_points_and_trends ($symbol, $start_date, $end_date);

		
		$arrayLen = count($trendArray);

		for ($x=0; $x<$arrayLen; $x++){
			$eachRow['x'] = $trendArray[$x]['date'] * 1 / 1;
			$eachRow['y'] = $trendArray[$x]['price'] * 1 / 1;
			$eachRow['title'] = $trendArray[$x]['trend']."-".$trendArray[$x]['strength'];
				
			array_push($retArray, $eachRow);

		}
	
		
		return $retArray;


}

function chart_swing_points_and_trends ($symbol, $start_date, $end_date, $time_frame) {
		$allArray = array();
		$spArray = array();	
		$retArray = array();
		
		$allArray = generate_swing_points_and_trends ($symbol, $start_date, $end_date, $time_frame);
		
		$spArray = $allArray[0];
		
		$arrayLen = count($spArray);
		
		for ($x=0; $x<$arrayLen; $x++){
			$eachRow['x'] = $spArray[$x]['date'];
			$eachRow['y'] = $spArray[$x]['price'];
			$eachRow['title'] = $spArray[$x]['type'];
				
			array_push($retArray, $eachRow);

		}
		
		return $retArray;


}


function generate_swing_points ($symbol, $start_date, $end_date, $time_frame) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();

		$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);

		$arrayLen = count($stockRetArray);

		$sph_count = 0;
		$spl_count = 0;	
		$sph = 0;
		$spl = 10000;	
		$max_sph = 0;
		$min_spl = 10000;
			
		// use previous sph/spl data to determine trend transistion
		$previous_sph = 0;
		$previous_sph_volume = 0;
		$previous_spl = 10000;
		$previous_spl_volume = 0;
		
		// there are 7 possible current
		// CB = confirmed bullish
		// SB = suspect bullish
		// CR = confirmed bearish
		// SR = suspect bearish
		// CS = confirmed sideway
		// SS = suspect sideway 
		$current_trend = "SS";
		
		for ($x = 0; $x < $arrayLen; $x++)
		{
			$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
			$newDateStr = $newDateStr * 1000 - 14400000;
			
			$pclose = $stockRetArray[$x]['close'] * 1 / 1;
			$popen = $stockRetArray[$x]['open'] * 1 / 1;
			$phigh = $stockRetArray[$x]['high'] * 1 / 1;
			$plow = $stockRetArray[$x]['low'] * 1 / 1;
			$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;
			$pavg_volume = $stockRetArray[$x]['avg_volume'] * 1 / 1;
			$prelative_avg_vol = $stockRetArray[$x]['relative_avg_vol'] * 1 / 1;
			$pATR = $stockRetArray[$x]['ATR'] * 1 / 1;
			$pDateString = $stockRetArray[$x]['trade_date'];

			if (!$prelative_avg_vol) {$prelative_avg_vol = 1;}
			if (!$pavg_volume) {$pavg_volume = 1;}
			
			// calculate weighted_volume = 20% * yesterday volume + 
			//							   30% * today volume + 
			//							   30% * tomorrow volume + 
			//							   20% * day after tomorrow volume
			$weighted_volume = 0 * $stockRetArray[$x-1]['volume'] +	0.8 * $stockRetArray[$x]['volume'] + 0.1 * $stockRetArray[$x+1]['volume'] + 0.1 * $stockRetArray[$x+2]['volume'] ;
			$prelative_avg_vol = ($weighted_volume / $pavg_volume) * 100;
			
			if ($sph < $phigh) {
				$sph = $phigh;
				$sph_date = $newDateStr;
				$sph_date_string = $pDateString;
				
				$sph_high = $phigh;
				$sph_low = $plow;
				$sph_volume = $pvolume;
				$sph_avg_volume = $pavg_volume;
				$sph_ATR = $pATR;
				$sph_weighted_volume = $weighted_volume;
				
				$sph_count = 0;
				$sph_relative_avg_vol = $prelative_avg_vol;

			} else {
				$sph_count ++;
			}
			
			if ($sph_count == 6) {
				$eachRow['date'] = $sph_date;
				$eachRow['date_string'] = $sph_date_string;

				$eachRow['price'] = $sph;
				$eachRow['type'] = "SPH";
				$eachRow['high'] = $sph_high;
				$eachRow['low'] = $sph_low;
				$eachRow['volume'] = $sph_volume;
				$eachRow['avg_volume'] = $sph_avg_volume;
				$eachRow['weighted_volume'] = $sph_weighted_volume;
				$eachRow['relative_avg_volume'] = ($sph_weighted_volume / $sph_avg_volume) * 100;

				$eachRow['ATR'] = $sph_ATR;

				array_push($retArray, $eachRow);
				
				$previous_sph = $sph;
				$previous_sph_date = $sph_date;
				$previous_sph_volume = $sph_volume;
				
				//array_push($retArray, array($newDateStr, $sph));
				$sph_count = 0;
				$sph_date = $newDateStr;
				$sph_date_string = $pDateString;
				$sph = $phigh;
				$sph_ATR = $pATR;
			}
			
			if ($spl > $plow) {
				$spl = $plow;
				$spl_date = $newDateStr;
				$spl_date_string = $pDateString;
				
				$spl_high = $phigh;
				$spl_low = $plow;
				$spl_volume = $pvolume;
				$spl_avg_volume = $pavg_volume;
				$spl_ATR = $pATR;
				$spl_weighted_volume = $weighted_volume;
								
				$spl_count = 0;
				$spl_relative_avg_vol = $prelative_avg_vol;

			} else {
				$spl_count ++;
			}
			
			if ($spl_count == 6) {
			
				$eachRow['date'] = $spl_date;
				$eachRow['date_string'] = $spl_date_string;

				$eachRow['price'] = $spl;
				$eachRow['type'] = "SPL";
				$eachRow['high'] = $spl_high;
				$eachRow['low'] = $spl_low;
				$eachRow['volume'] = $spl_volume;
				$eachRow['avg_volume'] = $spl_avg_volume;
				$eachRow['weighted_volume'] = $spl_weighted_volume;
				$eachRow['relative_avg_volume'] = ($spl_weighted_volume / $spl_avg_volume) * 100;

				$eachRow['ATR'] = $spl_ATR;			

				array_push($retArray, $eachRow);
					
				$spl_relative_avg_vol = 0;
			
				$previous_spl = $spl;
				$previous_spl_date = $spl_date;
				$previous_spl_date_string = $spl_date_string;
				$previous_spl_volume = $spl_volume;
				
				//array_push($retArray, array($newDateStr, $spl));
				$spl_count = 0;
				$spl_date = $newDateStr;
				$spl_date_string = $pDateString;
				$spl = $plow;
				$spl_ATR = $pATR;
			}
			
			
			
				// check if wide price range
				if (($phigh - $plow) > (2 * $pATR)) {
					$eachRow['date'] = $newDateStr;
					$eachRow['date_string'] = $pDateString;
					$eachRow['price'] = $pclose;
					$eachRow['high'] = $phigh;
					$eachRow['low'] = $plow;
					$eachRow['volume'] = $pvolume;
					$eachRow['avg_volume'] = $pavg_volume;
					$eachRow['weighted_volume'] = $weighted_volume;
					$eachRow['relative_avg_volume'] = $prelative_avg_vol;
					$eachRow['ATR'] = $pATR;
					$eachRow['type'] = "WIDE";
					array_push($retArray, $eachRow);	
				} else if ($pvolume > (1.5 * $pavg_volume)) {
					$eachRow['date'] = $newDateStr;
					$eachRow['date_string'] = $pDateString;
					$eachRow['price'] = $pclose;
					$eachRow['high'] = $phigh;
					$eachRow['low'] = $plow;
					$eachRow['volume'] = $pvolume;
					$eachRow['avg_volume'] = $pavg_volume;
					$eachRow['weighted_volume'] = $weighted_volume;
					$eachRow['relative_avg_volume'] = $prelative_avg_vol;
					$eachRow['ATR'] = $pATR;				
				
					$eachRow['type'] = "VOLUME";
					array_push($retArray, $eachRow);	
				// gap open higher than 1 ATR
				} else if (($popen - ($stockRetArray[$x-1]['close'] * 1)) > $pATR) {
					$eachRow['date'] = $newDateStr;
					$eachRow['date_string'] = $pDateString;
					$eachRow['price'] = $pclose;
					$eachRow['high'] = $phigh;
					$eachRow['low'] = $plow;
					$eachRow['volume'] = $pvolume;
					$eachRow['avg_volume'] = $pavg_volume;
					$eachRow['weighted_volume'] = $weighted_volume;
					$eachRow['relative_avg_volume'] = $prelative_avg_vol;
					$eachRow['ATR'] = $pATR;				
				
					$eachRow['type'] = "GAP-HIGHER";
					array_push($retArray, $eachRow);	
				} else if (abs(($popen - ($stockRetArray[$x-1]['close'] * 1))) > $pATR) {
					$eachRow['date'] = $newDateStr;
					$eachRow['date_string'] = $pDateString;
					$eachRow['price'] = $pclose;
					$eachRow['high'] = $phigh;
					$eachRow['low'] = $plow;
					$eachRow['volume'] = $pvolume;
					$eachRow['avg_volume'] = $pavg_volume;
					$eachRow['weighted_volume'] = $weighted_volume;
					$eachRow['relative_avg_volume'] = $prelative_avg_vol;
					$eachRow['ATR'] = $pATR;				
				
					$eachRow['type'] = "GAP-LOWER";
					array_push($retArray, $eachRow);	
				}
		
		}			
			
		$retLen = count($retArray);
		$max_sph_row = array();
		$max_sph_row['price'] = 0;
		$min_spl_row = array();
		$min_spl_row['price'] = 100000;

		// find max SPH and min SPL from the swing point array
		for ($x = 0; $x < $retLen; $x++) {
			if ($retArray[$x]['type'] == "SPH") {
				if ($retArray[$x]['price'] > $max_sph_row['price']) {
					$max_sph_row = $retArray[$x];
				}
			} else if ($retArray[$x]['type'] == "SPL") {
				if ($retArray[$x]['price'] < $min_spl_row['price']) {
					$min_spl_row = $retArray[$x];
				}
			}
		}

		$max_sph_row['type'] = "MAX_SPH";
		$min_spl_row['type'] = "MIN_SPL";

/*		if ($max_sph_row['price'] > 0) 
		{	
			array_push($retArray, $max_sph_row);	
		}
		if ($min_spl_row['price'] < 10000) {
			array_push($retArray, $min_spl_row);
		}	
		
*/
		return $retArray;
	
}


/* 3 types of anchor points
	1: Extreme day High and Low (long bar)
	2: Extreme volume
	3: Gap
*/
function generate_anchor_points ($symbol, $start_date, $end_date) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();

		$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);

		$arrayLen = count($stockRetArray);

		$sph_count = 0;
		$spl_count = 0;	
		$sph = 0;
		$spl = 10000;	
			
		// use previous sph/spl data to determine trend transistion
		$previous_sph = 0;
		$previous_sph_volume = 0;
		$previous_spl = 10000;
		$previous_spl_volume = 0;
		
		// there are 7 possible current
		// CB = confirmed bullish
		// SB = suspect bullish
		// CR = confirmed bearish
		// SR = suspect bearish
		// CS = confirmed sideway
		// SS = suspect sideway 
		$current_trend = "SS";
		
			
		for ($x = 0; $x < $arrayLen; $x++)
		{
			$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
			$newDateStr = $newDateStr * 1000 - 14400000;
			
			$pclose = $stockRetArray[$x]['close'] * 1 / 1;
			$popen = $stockRetArray[$x]['open'] * 1 / 1;
			$phigh = $stockRetArray[$x]['high'] * 1 / 1;
			$plow = $stockRetArray[$x]['low'] * 1 / 1;
			$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;
			$pavg_volume = $stockRetArray[$x]['avg_volume'] * 1 / 1;
			$prelative_avg_vol = $stockRetArray[$x]['relative_avg_vol'] * 1 / 1;
			$pATR = $stockRetArray[$x]['ATR'] * 1 / 1;

			if (!$prelative_avg_vol) {$prelative_avg_vol = 1;}
			if (!$pavg_volume) {$pavg_volume = 1;}
			
			// calculate weighted_volume = 20% * yesterday volume + 
			//							   30% * today volume + 
			//							   30% * tomorrow volume + 
			//							   20% * day after tomorrow volume
			$weighted_volume = 0 * $stockRetArray[$x-1]['volume'] +	0.8 * $stockRetArray[$x]['volume'] + 0.1 * $stockRetArray[$x+1]['volume'] + 0.1 * $stockRetArray[$x+2]['volume'] ;
			$prelative_avg_vol = ($weighted_volume / $pavg_volume) * 100;
		
		
			$eachRow['date'] = $newDateStr;
			$eachRow['price'] = $pclose;
			$eachRow['high'] = $phigh;
			$eachRow['low'] = $plow;
			$eachRow['volume'] = $pvolume;
			$eachRow['avg_volume'] = $pavg_volume;
			$eachRow['weighted_volume'] = $weighted_volume;
			$eachRow['relative_avg_volume'] = $prelative_avg_vol;
			$eachRow['ATR'] = $pATR;

				// check if wide price range
				if (($phigh - $plow) > (3 * $pATR)) {
					$eachRow['type'] = "WIDE";
					array_push($retArray, $eachRow);	
				} else if ($pvolume > (1.5 * $pavg_volume)) {
					$eachRow['type'] = "VOLUME";
					array_push($retArray, $eachRow);	
				// gap open higher than 1 ATR
				} else if (($popen - ($stockRetArray[$x-1]['close'] * 1)) > $pATR) {
					$eachRow['type'] = "GAP-HIGHER";
					array_push($retArray, $eachRow);	
				} else if (abs(($popen - ($stockRetArray[$x-1]['close'] * 1))) > $pATR) {
					$eachRow['type'] = "GAP-LOWER";
					array_push($retArray, $eachRow);	
				}
				
				

		}

		return $retArray;

}




function generate_swing_points_and_trends ($symbol, $start_date, $end_date) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();

		$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);

		$arrayLen = count($stockRetArray);

		$sph_count = 0;
		$spl_count = 0;	
		$sph = 0;
		$spl = 10000;	
			
		// use previous sph/spl data to determine trend transistion
		$previous_sph = 0;
		$previous_sph_volume = 1;
		$previous_spl = 10000;
		$previous_spl_volume = 1;
		
		// there are 7 possible current
		// CB = confirmed bullish
		// SB = suspect bullish
		// CR = confirmed bearish
		// SR = suspect bearish
		// CS = confirmed sideway
		// SS = suspect sideway 
		$current_trend = "SS";
		$trend_array = array();
		$each_trend_row = array();		
			
		for ($x = 0; $x < $arrayLen; $x++)
		{
			$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
			$newDateStr = $newDateStr * 1000 - 14400000;
			
			$pclose = $stockRetArray[$x]['close'] * 1 / 1;
			$popen = $stockRetArray[$x]['open'] * 1 / 1;
			$phigh = $stockRetArray[$x]['high'] * 1 / 1;
			$plow = $stockRetArray[$x]['low'] * 1 / 1;
			$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;
			$pavg_volume = $stockRetArray[$x]['avg_volume'] * 1 / 1;
			$prelative_avg_vol = $stockRetArray[$x]['relative_avg_vol'] * 1 / 1;
			$pATR = $stockRetArray[$x]['ATR'] * 1 / 1;

			if (!$prelative_avg_vol) {$prelative_avg_vol = 1;}
			if (!$pavg_volume) {$pavg_volume = 1;}
			
			// calculate weighted_volume = 20% * yesterday volume + 
			//							   30% * today volume + 
			//							   30% * tomorrow volume + 
			//							   20% * day after tomorrow volume
			$weighted_volume = 0 * $stockRetArray[$x-1]['volume'] +	0.8 * $stockRetArray[$x]['volume'] + 0.1 * $stockRetArray[$x+1]['volume'] + 0.1 * $stockRetArray[$x+2]['volume'] ;
			$prelative_avg_vol = ($weighted_volume / $pavg_volume) * 100;
			

			// find new trend
			if ($phigh > $sph) {
				$new_trend = get_new_bullish_trend($current_trend, $phigh, $pvolume, $newDateStr, $previous_sph, $previous_sph_volume, $previous_sph_date);
//print "sph: $previous_sph sph date: $previous_sph_date phigh: $phigh $current_trend new trend: $new_trend \n";

				if ($new_trend != $current_trend) {
					$each_trend_row['date'] = $newDateStr;
					$each_trend_row['price'] = $phigh;
					$each_trend_row['trend'] = $new_trend;
					array_push ($trend_array, $each_trend_row);
					
					$current_trend = $new_trend;
				}
			}
			// find new trend
			if ($plow < $spl) {
				$new_trend = get_new_bearish_trend($current_trend, $plow, $pvolume, $newDateStr, $previous_spl, $previous_spl_volume, $previous_spl_date);
				
				if ($new_trend != $current_trend) {	
					$each_trend_row['date'] = $newDateStr;
					$each_trend_row['price'] = $plow;
					$each_trend_row['trend'] = $new_trend;
					array_push ($trend_array, $each_trend_row);
					
					$current_trend = $new_trend;
				}
			}					
			
			if ($sph < $phigh) {
				$sph = $phigh;
				$sph_date = $newDateStr;
				
				$sph_high = $phigh;
				$sph_low = $plow;
				$sph_volume = $pvolume;
				$sph_avg_volume = $pavg_volume;
				$sph_ATR = $pATR;
				$sph_weighted_volume = $weighted_volume;
				
				$sph_count = 0;
				$sph_relative_avg_vol = $prelative_avg_vol;

			} else {
				$sph_count ++;
			}
			
			if ($sph_count == 6) {
				$eachRow['date'] = $sph_date;
				$eachRow['price'] = $sph;
				$eachRow['type'] = "SPH";
				$eachRow['high'] = $sph_high;
				$eachRow['low'] = $sph_low;
				$eachRow['volume'] = $sph_volume;
				$eachRow['avg_volume'] = $sph_avg_volume;
				$eachRow['weighted_volume'] = $sph_weighted_volume;
				$eachRow['relative_avg_volume'] = ($sph_weighted_volume / $sph_avg_volume) * 100;

				$eachRow['ATR'] = $sph_ATR;

				array_push($retArray, $eachRow);
				
				$previous_sph = $sph;
				$previous_sph_date = $sph_date;
				$previous_sph_volume = $sph_volume;
//print "set new sph: $previous_sph new date: $previous_sph_date \n";
				//array_push($retArray, array($newDateStr, $sph));
				$sph_count = 0;
				$sph_date = $newDateStr;
				$sph = $phigh;
			}
			
			if ($spl > $plow) {
				$spl = $plow;
				$spl_date = $newDateStr;
				
				$spl_high = $phigh;
				$spl_low = $plow;
				$spl_volume = $pvolume;
				$spl_avg_volume = $pavg_volume;
				$spl_ATR = $pATR;
				$spl_weighted_volume = $weighted_volume;
								
				$spl_count = 0;
				$spl_relative_avg_vol = $prelative_avg_vol;

			} else {
				$spl_count ++;
			}
			
			if ($spl_count == 6) {
			
				$eachRow['date'] = $spl_date;
				$eachRow['price'] = $spl;
				$eachRow['type'] = "SPL";
				$eachRow['high'] = $spl_high;
				$eachRow['low'] = $spl_low;
				$eachRow['volume'] = $spl_volume;
				$eachRow['avg_volume'] = $spl_avg_volume;
				$eachRow['weighted_volume'] = $spl_weighted_volume;
				$eachRow['relative_avg_volume'] = ($spl_weighted_volume / $spl_avg_volume) * 100;

				$eachRow['ATR'] = $sph_ATR;			
			
				array_push($retArray, $eachRow);
					
					$spl_relative_avg_vol = 0;
			
				$previous_spl = $spl;
				$previous_spl_date = $spl_date;
				$previous_spl_volume = $spl_volume;
				
				//array_push($retArray, array($newDateStr, $spl));
				$spl_count = 0;
				$spl_date = $newDateStr;
				$spl = $plow;
			}
			
			
	
		
		}			
			
		return array($retArray, $trend_array);
		//return $trend_array;
}

function generate_trends ($symbol, $start_date, $end_date, $time_frame) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();
		$spArray = array();
		$sphArray = array();
		$splArray = array();
		
		$trend_strength = 0;

		$spArray = generate_swing_points ($symbol, $start_date, $end_date, $time_frame);
		$spArrayLen = count($spArray);

		for ($x=0; $x<$spArrayLen; $x++) {
			if ($spArray[$x]['type'] == "SPH") {
				array_push($sphArray, $spArray[$x] );
			} else if ($spArray[$x]['type'] == "SPL") {
				array_push($splArray, $spArray[$x] );
			}
		}
		
		
		$sphLen = count($sphArray);
		$splLen = count($splArray);
		$sphPos = 0;
		$splPos = 0;
		
		$stockRetArray = historical_stock_price($symbol, $start_date, $end_date);

		$arrayLen = count($stockRetArray);
			
		// use previous sph/spl data to determine trend transistion
		$previous_sph = 0;
		$previous_sph_volume = 1;
		$previous_spl = 10000;
		$previous_spl_volume = 1;
		
		// there are 7 possible current
		// CB = confirmed bullish
		// SB = suspect bullish
		// CR = confirmed bearish
		// SR = suspect bearish
		// CS = confirmed sideway
		// SS = suspect sideway 
		$current_trend = "SS";
		$trend_array = array();
		$each_trend_row = array();		
			
		for ($x = 0; $x < $arrayLen; $x++)
		{
			$newDateStr = strtotime($stockRetArray[$x]['trade_date']);
			$newDateStr = $newDateStr * 1000 - 14400000;

			$pclose = $stockRetArray[$x]['close'] * 1 / 1;
			$popen = $stockRetArray[$x]['open'] * 1 / 1;
			$phigh = $stockRetArray[$x]['high'] * 1 / 1;
			$plow = $stockRetArray[$x]['low'] * 1 / 1;
			$pvolume = $stockRetArray[$x]['volume'] * 1 / 1;
			$pavg_volume = $stockRetArray[$x]['avg_volume'] * 1 / 1;
			$prelative_avg_vol = $stockRetArray[$x]['relative_avg_vol'] * 1 / 1;
			$pATR = $stockRetArray[$x]['ATR'] * 1 / 1;
			
			// update SPH and SPL position
			if (($sphArray[$sphPos+1]['date'] >= $newDateStr) && ($newDateStr > $sphArray[$sphPos]['date'])) {
				$sphPos ++;
			}
			
			if (($splArray[$splPos+1]['date'] >= $newDateStr) && ($newDateStr > $splArray[$splPos]['date'])) {
				$splPos ++;
			}

			// find new trend
			if ($phigh > $sphArray[$sphPos-1]['price']) {

				$new_trend = get_new_bullish_trend($current_trend, $phigh, $pvolume, $newDateStr, $previous_sph, $sphArray[$sphPos-1]['volume'], $sphArray[$sphPos-1]['date']);
				// each SPH point will only be used to change trend once
				if ($sphPos < $sphLen) {$sphPos ++;}

				//determine trend strength
/*				if (($previous_trend == "CR" || $previous_trend == "SR") && ($current_trend == "SS")) {
					$trend_strength = -1;
				} else if ($current_trend == "CB") {
					$trend_strength = 3;
				} else if ($current_trend == "SB") {
					$trend_strength = 2;
				} else if ($current_trend == "SS") {
					$trend_strength = 0;
				} else if ($current_trend == "CS") {
					$trend_strength = 0;
				} else if ($current_trend == "SR") {
					$trend_strength = -2;
				} else if ($current_trend == "CR") {
					$trend_strength = -3;
				}
*/
				if (($current_trend == "CR" || $current_trend == "SR") && ($new_trend == "SS")) {
					$trend_strength = -1;
				} else if ($new_trend == "CB") {
					$trend_strength = 3;
				} else if ($new_trend == "SB") {
					$trend_strength = 2;
				} else if ($new_trend == "SS") {
					$trend_strength = 0;
				} else if ($new_trend == "CS") {
					$trend_strength = 0;
				} else if ($new_trend == "SR") {
					$trend_strength = -2;
				} else if ($new_trend == "CR") {
					$trend_strength = -3;
				}	
				
//print "HIGH: current trend: $current_trend new trend: $enw_trend strength: $trend_strength \n";

/*
else if HIGH > SPH
	if prior trend = CR/SR   AND current trend = SS
		strength = -1
	else if current trend = CB
		strength = 3
	else if current trend = SB
		strength = 2
	else if current trend = CS
		strength = 0
	else if current trend = SS
		strength = 0
	else if current trend = SR
		strength = -2
	else if current trend = CR
		strength = --3
*/
//print "sph: ";
//print $sphArray[$sphPos]['price'];
//print " $previous_sph sph date: $previous_sph_date phigh: $phigh $current_trend new trend: $new_trend \n";

				if ($new_trend != $current_trend) {
					$previous_trend = $current_trend;

					$each_trend_row['date'] = $newDateStr;
					$each_trend_row['date_string'] = $stockRetArray[$x]['trade_date'];

					$each_trend_row['price'] = $phigh;
					$each_trend_row['trend'] = $new_trend;
					$each_trend_row['strength'] = $trend_strength;

					
					array_push ($trend_array, $each_trend_row);
/*
print "HIGH: date: $newDateStr price: $phigh  sph price: ";
print $sphArray[$sphPos-1]['price'];
print " position: $sphPos trend: $new_trend \n";
*/
					$current_trend = $new_trend;
				}
			}
			// find new trend

			if ($plow < $splArray[$splPos-1]['price']) {
				$new_trend = get_new_bearish_trend($current_trend, $plow, $pvolume, $newDateStr, $previous_spl, $splArray[$splPos-1]['volume'], $splArray[$splPos-1]['date']);
				if ($splPos < $splLen) {$splPos ++;}


				//determine trend strength
/*				if (($previous_trend == "CB" || $previous_trend == "SB") && ($current_trend == "SS")) {
					$trend_strength = 1;
				} else if ($current_trend == "CB") {
					$trend_strength = 3;
				} else if ($current_trend == "SB") {
					$trend_strength = 2;
				} else if ($current_trend == "SS") {
					$trend_strength = 0;
				} else if ($current_trend == "CS") {
					$trend_strength = 0;
				} else if ($current_trend == "SR") {
					$trend_strength = -2;
				} else if ($current_trend == "CR") {
					$trend_strength = -3;
				}
*/

				if (($current_trend == "CB" || $current_trend == "SB") && ($new_trend == "SS")) {
					$trend_strength = 1;
				} else if ($new_trend == "CB") {
					$trend_strength = 3;
				} else if ($new_trend == "SB") {
					$trend_strength = 2;
				} else if ($new_trend == "SS") {
					$trend_strength = 0;
				} else if ($new_trend == "CS") {
					$trend_strength = 0;
				} else if ($new_trend == "SR") {
					$trend_strength = -2;
				} else if ($new_trend == "CR") {
					$trend_strength = -3;
				}


//		print "current trend: $current_trend new trend: $enw_trend strength: $trend_strength \n";
		/*		
	if prior trend = CB/SB	AND	current trend is SS
		strength = 1
	else if current trend = CB
		strength = 3
	else if current trend = SB
		strength = 2
	else if current trend = SS
		strength = 0
	else if current trend = CS
		strength = 0
	else if current trend = SR
		strength = -2
	else if current trend = CR
		strength = -3				
				
				*/
				if ($new_trend != $current_trend) {	
					$previous_trend = $current_trend;
					$each_trend_row['date'] = $newDateStr;
					$each_trend_row['date_string'] = $stockRetArray[$x]['trade_date'];
					$each_trend_row['price'] = $plow;
					$each_trend_row['trend'] = $new_trend;
					$each_trend_row['strength'] = $trend_strength;

					array_push ($trend_array, $each_trend_row);
					
//print "LOW: date: $newDateStr price: $plow	tred: $new_trend \n";
					$current_trend = $new_trend;
				}
			}					
	
		
		}			
			
		return $trend_array;
		//return $trend_array;
}



function get_last_spl ($symbol, $current_date) {
		$begin_date = getPrevious60Days($current_date);
		
		$swingArray = array();
		$splArray = array();
				
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);
		$arrayLen = count($swingArray);
		
		for ($x=0; $x<$arrayLen; $x++) {
			if ($swingArray[$x]['type'] == "SPL") {
				array_push($splArray, $swingArray[$x]);
			}
		}
		
		return end($splArray);	
}

function get_last_sph ($symbol, $current_date) {
		$begin_date = getPrevious60Days($current_date);
		
		$swingArray = array();
		$sphArray = array();
				
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);
		$arrayLen = count($swingArray);
		
		for ($x=0; $x<$arrayLen; $x++) {
			if ($swingArray[$x]['type'] == "SPH") {
				array_push($sphArray, $swingArray[$x]);
			}
		}
		
		return end($sphArray);		
}

function get_max_sph ($symbol, $current_date) {
		$begin_date = getPrevious60Days($current_date);
		
		$swingArray = array();
		$max_sph_row = array();
		$max_sph = 0;
				
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);
		$arrayLen = count($swingArray);
		
		for ($x=0; $x<$arrayLen; $x++) {
			if ($swingArray[$x]['price'] > $max_sph) {
				$max_sph = $swingArray[$x]['price'];
				
				$max_sph_row = $swingArray[$x];
			}
		}
		
		return $max_sph_row;		
	
}

function get_min_spl ($symbol, $current_date) {
		$begin_date = getPrevious60Days($current_date);
		
		$swingArray = array();
		$min_spl_row = array();
		$min_spl = 100000000000;
				
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);
		$arrayLen = count($swingArray);
		
		for ($x=0; $x<$arrayLen; $x++) {
			if ($swingArray[$x]['price'] < $min_spl) {
				$min_spl = $swingArray[$x]['price'];
				
				$min_spl_row = $swingArray[$x];
			}
		}
		
		return $min_spl_row;		

}

// get the SPH that has range (+- ATR) covers most SPH points
function get_most_hit_sp ($symbol, $current_date) {
		$begin_date = getPrevious60Days($current_date);
		
		$swingArray = array();
		$min_spl_row = array();
		$min_spl = 100000000000;
				
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);
		$arrayLen = count($swingArray);	
		

		for ($x=0; $x<$arrayLen; $x++) {
			if ($swingArray[$x]['type'] == "SPH") {
				$sphUpperBound = $swingArray[$x]['price'] + $swingArray[$x]['ATR'];
				$sphLowerBound = $swingArray[$x]['price'] - $swingArray[$x]['ATR'];
		
				for ($i=0; $i<$arrayLen; $i++) {
					if ($swingArray[$i]['type'] == "SPH") {
						if (($swingArray[$i]['price'] > $sphLowerBound) && ( $sphUpperBound > $swingArray[$i]['price'])) {
							$swingArray[$i]['count'] = $swingArray[$i]['count'] + 1;
						}
					}
				}
			} else if ($swingArray[$x]['type'] == "SPL") {
				$splUpperBound = $swingArray[$x]['price'] + $swingArray[$x]['ATR'];
				$splLowerBound = $swingArray[$x]['price'] - $swingArray[$x]['ATR'];
		
				for ($i=0; $i<$arrayLen; $i++) {
					if ($swingArray[$i]['type'] == "SPL") {
						if (($swingArray[$i]['price'] > $splLowerBound) && ( $splUpperBound > $swingArray[$i]['price'])) {
							$swingArray[$i]['count'] = $swingArray[$i]['count'] + 1;
						}
					}
				}
			}
			
		}
		
		$max_hit_sph = array();
		$max_hit_sph['count'] = 0;
		
		for ($y=0; $y<$arrayLen; $y++) {
			if ($swingArray[$y]['type'] == "SPH") {
				if ($swingArray[$y]['count'] > $max_hit_sph['count']) {
					$max_hit_sph = $swingArray[$y];
				} else if (($swingArray[$y]['count'] == $max_hit_sph['count']) && ($swingArray[$y]['price'] > $max_hit_sph['price'])) {
					$max_hit_sph = $swingArray[$y];
				}
				

			}
			
			if ($swingArray[$y]['type'] == "SPL") {
				if ($swingArray[$y]['count'] > $max_hit_spl['count']) {
					$max_hit_spl = $swingArray[$y];
				} else if (($swingArray[$y]['count'] == $max_hit_spl['count']) && ($swingArray[$y]['price'] < $max_hit_spl['price'])) {
					$max_hit_spl = $swingArray[$y];
				}
			
			}
		}
	
				print " MAX SPH: ";
				print $max_hit_sph['price'];
				print " Type: ";
				print $max_hit_sph['type'];
				print " count: ";
				print $max_hit_sph['count'];
				
				print " MAX SPL: ";
				print $max_hit_spl['price'];
				print " Type: ";
				print $max_hit_spl['type'];
				print " count: ";
				print $max_hit_spl['count'];
		return $swingArray;
}

/* determine the current trend
	Bullish: current price > last swing point high
	Bearish: current_price < last swing poing low
	Sideway: last swing poing high > current price > last swing poing low
*/
function get_current_trend ($symbol, $current_date) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();
	
		$trendArray = array();
		$current_trend_array = array();
		
		$previous_60_days = getPrevious60Days($current_date);
		$trendArray = generate_trends($symbol, $previous_60_days, $current_date);
		$current_trend_array = end($trendArray);
		
		return $current_trend_array;
			
}

/* determine the current trend
	Bullish: current price > last swing point high
	Bearish: current_price < last swing poing low
	Sideway: last swing poing high > current price > last swing poing low
*/
function get_previous_trend ($symbol, $current_date) {
		$stockRetArray = array();	
		$retArray = array();
		$eachRow = array();
		
		$lastSPHfound = 0;
		$lastSPLfound = 0;

		$begin_date = getPrevious60Days($current_date);
		
		
		$trendArray = array();
		$previous_trend_array = array();
		
		$previous_60_days = getPrevious60Days($current_date);
		$trendArray = generate_trends($symbol, $begin_date, $current_date);
		
		$trendLen = count($trendArray);
		
		$previous_trend_array = $trendArray[$trendLen-2];	

		return $previous_trend_array;
	
	

/*		$swingArray = array();	
	
		$swingArray = generate_swing_points($symbol, $begin_date, $current_date);

		$arrayLen = count($swingArray);
		
		$count = $arrayLen;

		// while loop to find out which posistion the 2nd to last SPH and SPL occurs
		while ((!$lastSPHfound) || (!$lastSPLfound) ) {
			$last_sp_type = $swingArray[$count-1]['type'];
		
			if ($last_sp_type == 'SPH') {
					$lastSPHfound = 1;
					$lastSPHpos = $count;
			} else {
					$lastSPLfound = 1;
					$lastSPLpos = $count;
			}
			$count --;
		}
		
		$secondLastSPHfound = 0;
		$secondLastSPHpos = -1;
		$secondLastSPLfound = 0;
		$secondLastSPLpos = -1;
		$secondLastMaxSPH = 0;
		$secondLastMinSPL = 10000;
		
		while (((!$secondLastSPHfound) || (!$secondLastSPLfound))  && ($count >= 1)) {
			$second_last_sp_type = $swingArray[$count-1]['type'];

print "date: ";
print $swingArray[$count-1]['date'];
print " type: ";
print $swingArray[$count-1]['type'];
print " SPH: ";
print $swingArray[$count-1]['price'];
print " SPL: ";
print $swingArray[$count-1]['price'];
print "\n";
			if ($second_last_sp_type == 'SPH') {
					$secondLastSPHfound = 1;
					$secondLastSPHpos = $count;
					
					if ($swingArray[$count-1]['price'] > $secondLastMaxSPH) {
						$secondLastMaxSPH = $swingArray[$count-1]['price'];
					}
			} else {
					$secondLastSPLfound = 1;
					$secondLastSPLpos = $count;
					
					if ($swingArray[$count-1]['price'] < $secondLastMinSPL) {
						$secondLastMinSPL = $swingArray[$count-1]['price'];
					}
			}
			$count --;
		}		
		
		
		print " final count: $count ";
print "second last SPH pos: $secondLastSPHpos second last SPL pos: $secondLastSPLpos \n";
		print " max SPH: $secondLastMaxSPH min SPL: $secondLastMinSPL \n";

		$last_y = $swingArray[$arrayLen-2]['y'];
		$last_x = $swingArray[$arrayLen-2]['x'];
		$last_title = $swingArray[$arrayLen-2]['title'];

		print "last y: $last_y last x: $last_x last title: $last_title ";

		$last_sph_array = array();
		$last_spl_array = array();

		$last_sph_array = get_last_sph($symbol, $current_date);
		$last_sph = $last_sph_array[1];
		$last_spl_array = get_last_spl($symbol, $current_date);
		$last_spl = $last_spl_array[1];

		$query  = "select close from price_history where symbol = '".$symbol."' and trade_date = '".$current_date."' ";

		$query= stripslashes($query);
		$result = queryMysql($query);
	
		while ($tmp_data = mysql_fetch_row($result)) {
			$current_price = $tmp_data[0] * 1 / 1;
		}		
				
		if ($current_price > $last_sph) {
			return "bullish";
		} else if ($current_price > $last_spl) {
			return "sideway";
		} else {
			return "bearish";
		}
		
*/
			
}


function getShortTermStartDate ($trade_date) {
	$stStartDate = getPrevious60Days($trade_date);
	return $stStartDate;	
}

function getMediumTermStartDate ($trade_date) {
	$mtStartDate = getPrevious250Days($trade_date);
	return $mtStartDate;	
}

function getLongTermStartDate ($trade_date) {
	$ltStartDate = getPrevious1250Days($trade_date);
	return $ltStartDate;	
}

// time frame = Short Term
function getPrevious60Days($trade_date) {	
	$query  = "select trade_date from price_history where symbol = 'AAPL' and trade_date_id = ";
	$query .= "(select max(trade_date_id) - 60 from price_history where symbol = 'AAPL' and trade_date <= '".$trade_date."') ";
	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$previous_date = $tmp_data[0];
	}
	return $previous_date;
}


// time frame = Medium Term
function getPrevious250Days($trade_date) {	
	$query  = "select trade_date from price_history where symbol = 'AAPL' and trade_date_id = ";
	$query .= "(select max(trade_date_id) - 250 from price_history where symbol = 'AAPL' and trade_date <= '".$trade_date."') ";
	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$previous_date = $tmp_data[0];
	}
	return $previous_date;
}

// time frame = Long Term
function getPrevious1250Days($trade_date) {	
	$query  = "select trade_date from price_history where symbol = 'AAPL' and trade_date_id = ";
	$query .= "(select max(trade_date_id) - 1250 from price_history where symbol = 'AAPL' and trade_date <= '".$trade_date."') ";
	$query= stripslashes($query);
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$previous_date = $tmp_data[0];
	}
	return $previous_date;
}

// input: current trend, current stock price, current volume, previous SPH, previous SPH volume, previous SPL, previous SPL volume
// output: new trend
// there are 7 possible current
// CB = confirmed bullish = 3
// SB = suspect bullish = 2
// CR = confirmed bearish = -3
// SR = suspect bearish = -2
// CS = confirmed sideway = 1
// SS = suspect sideway = -1
// AS = amphibious sideway = 0

function get_new_bullish_trend($old_trend, $current_price, $current_volume, $current_date, $previous_sph, $previous_sph_volume, $previous_sph_date) {
	$new_trend = $old_trend;
	$breakout_indicator = 130;
	if (!$previous_sph_volume) {$previous_sph_volume = 100000000000;}
	$breakout_volume = ($current_volume / $previous_sph_volume) * 100;

	if ($old_trend == 'SS' || $old_trend == 'CS') {	// if current trend is sideway
		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CB";
		} else {
			$new_trend = "SB";
		}		
	} else if ($old_trend == 'CR' || $old_trend == 'SR') {	// if current trend is bearish
		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CS";
		} else {
			$new_trend = "SS";
		} 
	} else if ($old_trend == 'CB' || $old_trend == 'SB') {	// if current trend is bullish
/*		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CB";
		} 
		else {
			$new_trend = "SB";
		} 
*/	} 
	
	return $new_trend;
	
}


// input: current trend, current stock price, current volume, previous SPH, previous SPH volume, previous SPL, previous SPL volume
// output: new trend
function get_new_bearish_trend($old_trend, $current_price, $current_volume, $current_date, $previous_spl, $previous_spl_volume, $previous_spl_date) {

	$new_trend = $old_trend;
	$breakout_indicator = 130;
	if (!$previous_spl_volume) {$previous_spl_volume = 10000000000;}

	
	$breakout_volume = ($current_volume / $previous_spl_volume) * 100;

	if ($old_trend == 'SS' || $old_trend == 'CS') { // if current trend is sideway
		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CR";
		} else {
			$new_trend = "SR";
		}		
	} else if ($old_trend == 'CR' || $old_trend == 'SR') {	// if current trend is bearish
/*		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CR";
		} 
		else {
			$new_trend = "SR";
		} 
*/
	} else if ($old_trend == 'CB' || $old_trend == 'SB') {	// if current trend is bullish
		if ($breakout_volume > $breakout_indicator ) {
			$new_trend = "CS";
		} else {
			$new_trend = "SS";
		} 
	} 
	
	return $new_trend;
	
	
}

function populate_swing_points($symbol, $end_date, $start_date, $time_frame) {
	$swing_point_table = "sw_swing_points";
	$query = "";
	$dateString = "";
	$spArray = Array();
	
	if (!$start_date) {
		$start_date = getPrevious60Days($end_date);
	}
//print "symbol: $symbol start date: $start_date end date: $end_date time frame: $time_frame \n";			
	
	$spArray = generate_swing_points ($symbol, $start_date, $end_date, $time_frame);
	$arrayLen = count($spArray);


	for ($x=0; $x<$arrayLen; $x++) {

		$query  = "insert into ".$swing_point_table." (symbol, trade_date, type, price, ATR, time_frame) ";
		$query .= "values ('".$symbol."', '".$spArray[$x]['date_string']."', '".$spArray[$x]['type']."', ".$spArray[$x]['price'].", ".$spArray[$x]['ATR'].", '".$time_frame."') ";
		$query .= "ON DUPLICATE KEY UPDATE symbol = '".$symbol."', trade_date='".$spArray[$x]['date_string']."', type='".$spArray[$x]['type']."', price=".$spArray[$x]['price'].", ATR=".$spArray[$x]['ATR'].", time_frame='".$time_frame."'";
		$query = stripslashes($query);	

		$result = queryMysql($query);
		
	}

}

function get_max_sph_from_table($symbol, $time_frame) {
	$swing_point_table = "sw_swing_points";

	$query = "";
	$max_sph_array = Array();


	$query = "select trade_date, price, ATR from ".$swing_point_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and type = 'MAX_SPH'";
		
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$max_sph_array['trade_date'] = $tmp_data[0];
		$max_sph_array['price'] = $tmp_data[1];
		$max_sph_array['ATR'] = $tmp_data[2];
		$max_sph_array['time_frame'] = $time_frame;

	}	
	
	return $max_sph_array;
}

function get_min_spl_from_table($symbol, $time_frame, $end_date) {
	$swing_point_table = "sw_swing_points";
	$previous_60_days = getPrevious60Days($end_date);

	$query = "";
	$min_spl_array = Array();
	
	$query  = "select trade_date, type, price, ATR, time_frame from ".$swing_point_table." where type = 'SPL' and symbol = '".$symbol."' and time_frame = '".$time_frame;
	$query .= "' and trade_date < '".$end_date."' and trade_date > '".$previous_60_days."' ";
	$query .= "and price = (select min(price) from ".$swing_point_table." where type = 'SPL' and symbol = '".$symbol."' and time_frame = '".$time_frame;
	$query .= "' and trade_date < '".$end_date."' and trade_date > '".$previous_60_days."')";

	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$min_spl_array['trade_date'] = $tmp_data[0];
		$min_spl_array['type'] = $tmp_data[1];
		$min_spl_array['price'] = $tmp_data[2];
		$min_spl_array['ATR'] = $tmp_data[3];
		$min_spl_array['time_frame'] = $time_frame;
	}	
	
	return $min_spl_array;
}

function get_last_sph_from_table($symbol, $time_frame) {
	$swing_point_table = "sw_swing_points";

	$query = "";
	$last_sph_array = Array();

	$query  = "select trade_date, price, ATR from ".$swing_point_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and type = 'SPH' and trade_date = ";
	$query .= "(select max(trade_date) from ".$swing_point_table." where symbol='".$symbol."' and time_frame = '".$time_frame."' and type = 'SPH')";
		
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$last_sph_array['trade_date'] = $tmp_data[0];
		$last_sph_array['price'] = $tmp_data[1];
		$last_sph_array['ATR'] = $tmp_data[2];
		$last_sph_array['time_frame'] = $time_frame;
	}	
	
	return $last_sph_array;
}

function get_last_spl_from_table($symbol) {
	$swing_point_table = "sw_swing_points";


	$query = "";
	$last_spl_array = Array();

	$query  = "select trade_date, price, ATR from ".$swing_point_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and type = 'SPL' and trade_date = ";
	$query .= "(select max(trade_date) from ".$swing_point_table." where symbol='".$symbol."' and time_frame = '".$time_frame."' and type = 'SPL')";
		
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$last_spl_array['trade_date'] = $tmp_data[0];
		$last_spl_array['price'] = $tmp_data[1];
		$last_spl_array['ATR'] = $tmp_data[2];
		$last_spl_array['time_frame'] = $time_frame;

	}	
		
	return $last_spl_array;
}

function populate_stock_trend ($symbol, $end_date, $start_date, $time_frame) {
	$stock_trend_table = "sw_stock_trend";

	$query = "";
	$dateString = "";
	$trendArray = Array();
	
	$spArray = Array();
	
	if (!$start_date) {
		$start_date = getPrevious60Days($end_date);
	}
			
	$trendArray = generate_trends ($symbol, $start_date, $end_date, $time_frame);
	$arrayLen = count($trendArray);
	
	for ($x=0; $x<$arrayLen; $x++) {		
		$query  = "insert into ".$stock_trend_table." (symbol, trade_date, price, trend, trend_strength, time_frame) ";
		$query .= "values ('".$symbol."', '".$trendArray[$x]['date_string']."', ".$trendArray[$x]['price'].", '".$trendArray[$x]['trend']."', ".$trendArray[$x]['strength'].", '".$time_frame."') ";
		$query .= "ON DUPLICATE KEY UPDATE symbol = '".$symbol."', trade_date='".$trendArray[$x]['date_string']."', trend='".$trendArray[$x]['trend']."', price=".$trendArray[$x]['price'].", trend_strength=".$trendArray[$x]['strength'].", time_frame='".$time_frame."'";
		
		$query = stripslashes($query);
		
		$result = queryMysql($query);
	
	}

}

function get_last_stock_trend_from_table($symbol, $time_frame) {
	$stock_trend_table = "sw_stock_trend";
	$query = "";
	$tmpArray = Array();

	$query  = "select trade_date, price, trend, trend_strength from ".$stock_trend_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and trade_date = ";
	$query .= "(select max(trade_date) from ".$stock_trend_table." where symbol='".$symbol."' and time_frame = '".$time_frame."' )";
		
	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['trade_date'] = $tmp_data[0];
		$tmpArray['price'] = $tmp_data[1];
		$tmpArray['trend'] = $tmp_data[2];
		$tmpArray['trend_strenth'] = $tmp_data[3];
		$tmpArray['time_frame'] = $time_frame;

	}	
		
	return $tmpArray;
}

function get_second_to_last_stock_trend_from_table($symbol, $time_frame) {
	$stock_trend_table = "sw_stock_trend";
	$query = "";
	$tmpArray = Array();

	$query  = "select trade_date, price, trend, trend_strength from ".$stock_trend_table." where symbol = '".$symbol."' and time_frame = '".$time_frame."' and trade_date < ";
	$query .= "(select max(trade_date) from ".$stock_trend_table." where symbol='".$symbol."' and time_frame = '".$time_frame."'  ) ORDER BY 1 DESC limit 1  ";

	$result = queryMysql($query);

	while ($tmp_data = mysql_fetch_row($result)) {
		$tmpArray['trade_date'] = $tmp_data[0];
		$tmpArray['price'] = $tmp_data[1];
		$tmpArray['trend'] = $tmp_data[2];
		$tmpArray['trend_strenth'] = $tmp_data[3];
		$tmpArray['time_frame']= $time_frame;

	}	
/*		
	print "trade date: ";
	print $tmpArray['trade_date'];
	print " price: ";
	print $tmpArray['price'];
	print " trend: ";
	print $tmpArray['trend'];
	print " strength: ";
	print $tmpArray['trend_strength'];
*/		
	return $tmpArray;
}


function populate_sw_trading_scan ($current_date, $time_frame) {
	$trading_scan_table = "sw_trading_scan";
	$swing_point_table = "sw_swing_points";

	$query = "";
	$dateString = "";
	$spArray = Array();
	
	if ($time_frame == "ST") {
		$start_date = getShortTermStartDate($current_date);
	} elseif ($time_frame == "MT") {
		$start_date = getMediumTermStartDate($current_date);
	} elseif ($time_frame == "LT") {
		$start_date = getLongTermStartDate($current_date);
	}   
	//populate MAX SPH

	$query  = "insert into ".$trading_scan_table." (symbol, buy_signal_date, buy_signal_type, buy_price, buy_ATR, time_frame )  "; 
	$query .= "select a.symbol, a.trade_date, 'MAX_SPH', a.price, a.ATR, '".$time_frame."' ";
	$query .= "from ".$swing_point_table." a, stock_list b where a.symbol = b.symbol and a.time_frame ='".$time_frame."' and "; 
	$query .= "a.type = 'SPH' and a.trade_date >= '".$start_date."' and a.price = (select max(c.price) from sw_swing_points c ";
	$query .= "where c.symbol=a.symbol and c.trade_date >= '".$start_date."' and c.time_frame = '".$time_frame."' and c.type = 'SPH') ";
	$query .= "ON DUPLICATE KEY update buy_signal_date=a.trade_date, buy_signal_type='MAX_SPH', buy_price=a.price, buy_ATR=a.ATR  ";

	$result = queryMysql($query);

	//populate MIN SPL
	$query  = "insert into ".$trading_scan_table." (symbol, sell_signal_date, sell_signal_type, sell_price, sell_ATR, time_frame )  "; 
	$query .= "select a.symbol, a.trade_date, 'MIN_SPL', a.price, a.ATR, '".$time_frame."' ";
	$query .= "from ".$swing_point_table." a, stock_list b where a.symbol = b.symbol and a.time_frame ='".$time_frame."' and "; 
	$query .= "a.type = 'SPL' and a.trade_date >= '".$start_date."' and a.price = (select min(c.price) from sw_swing_points c ";
	$query .= "where c.symbol=a.symbol and c.trade_date >= '".$start_date."' and c.time_frame = '".$time_frame."' and c.type = 'SPL') ";
	$query .= "ON DUPLICATE KEY update sell_signal_date=a.trade_date, sell_signal_type='MIN_SPL', sell_price=a.price, sell_ATR=a.ATR  ";

	$result = queryMysql($query);

}



?>