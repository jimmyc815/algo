#!/usr/local/bin/php

<?php
include_once 'dbfunction.php';


//	date_default_timezone_set('America/Los_Angeles');

if($_GET){
	if($_GET['action'] == 'getTurtleData'){ 
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
		
	} elseif ($_GET['action'] == 'getRealTimeQuote') {
		$symbol = $_GET['symbol'];
	
		$quote = get_yahoo_rt_quote ($symbol);	
	
		echo json_encode($quote);
	} elseif ($_GET['action'] == 'getClosePrice') {
		$symbol = $_GET['symbol'];
		$date = $_GET['date'];
		$price = array();

		$query  = "SELECT close ";
		$query .= "FROM price_history where symbol='".strtoupper($symbol)."' ";
		$query .= "and trade_date < STR_TO_DATE('".$date."', '%m/%d/%Y') ";

		$result = queryMysql($query);

		while ($data = mysql_fetch_row($result)) {
			$price[0]['close'] = $data[0];
		}
		echo json_encode($price);	

	} elseif($_GET['action'] == 'turtle_s2'){ 
		$symbol = $_GET['symbol'];
		$begin_date = $_GET['begin_date'];
		$end_date = $_GET['end_date'];
		$stop_loss_type = $_GET['stop_loss'];
		$buy_signal = $_GET['buy_signal'];
		
		$cash = $_GET['cash'];
		
		//$query = "select * from price_history where symbol = '".$symbol."'";
        $my_sql = "delete from turtle_s2_system where symbol = '$symbol'";
        $my_sql .= " and stop_loss_type = '$stop_loss_type' and buy_type = '$buy_signal'"; 
		$result = queryMysql($my_sql);
		
		$query  = "SELECT trade_date, trade_date_id, open, high, low, close, ATR, ";
		$query .= "55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW,50_MA, 200_MA ";
		$query .= "FROM price_history where symbol='".strtoupper($symbol)."' ";
		$query .= "and trade_date between STR_TO_DATE('".$begin_date."', '%m/%d/%Y') ";
		$query .= "and STR_TO_DATE('".$end_date."', '%m/%d/%Y') ";
		$query .= "order by trade_date_id ASC ";

		$query= stripslashes($query);
		$result = queryMysql($query);

		$sightings = array();

/*		$sightings = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($sightings);
*/
		
		# buy when stock > 55 day high
		# calculate N for the day
		# buy round down to 100s (portfolio * 2% / 2N) shares
		# set stop loss at 1/2 N
		$ph = array();
		$i = 0;
		
		while ($data = mysql_fetch_row($result)) {
			# 0: trade_date
			# 1: trade_date_id
			# 2: open
			# 3: high
			# 4: low
			# 5: close
			# 6: ATR
			# 7: 55 day high
			# 8: 20 day high
			# 9: 20 day low
			# 10: 50 ma
			# 11: 200 ma

			$ph[$i]['trade_date'] = str_replace("\"", "",$data[0]);
			$ph[$i]['trade_date_id'] = str_replace("\"", "",$data[1]);
			$ph[$i]['open_price'] = str_replace("\"", "",$data[2]);
			$ph[$i]['high'] = str_replace("\"", "",$data[3]);
			$ph[$i]['low'] = str_replace("\"", "",$data[4]);
			$ph[$i]['close_price'] = str_replace("\"", "",$data[5]);
			$ph[$i]['ATR'] = str_replace("\"", "",$data[6]);
			$ph[$i]['day_high_55'] = str_replace("\"", "",$data[7]);
			$ph[$i]['day_high_20'] = str_replace("\"", "",$data[8]);
			$ph[$i]['day_low_20'] = str_replace("\"", "",$data[9]);
			$ph[$i]['ma_50'] = str_replace("\"", "",$data[10]);
			$ph[$i]['ma_200'] = str_replace("\"", "",$data[11]);


			$i++;

		}
		
		$len_array = $i;	

		$current_pos = 0;
		$next_buy_point = 100000;
		$stop_loss = 0;
		$num_shares = 0;
		$current_N = 0;
		$risk_value = 0;
		$purchase_value = 0;
		$sim_start_day = 55;
        $risk_factor = 2;
        $pyramid_mode = 0;
		
		
		if (!$stop_loss_type) {$stop_loss_type = "8PCT";}
		if (!$cash_balance) {$cash_balance = 100000;}

		$stop_loss = 0;
		
//		for ($x=$sim_start_day; $x < $len_array; $x++) {
		for ($x=0; $x < $len_array; $x++) {

$stop_loss = get_stop_loss($stop_loss_type, $stop_loss, $ph[$x]['close_price'], $ph[$x]['high'], $ph[$x]['low'], $ph[$x]['day_high_55'], $ph[$x]['day_high_20'], $ph[$x]['day_low_20'], $ph[$x]['ma_50'], $ph[$x]['ma_200'], $ph[$x]['ATR'] );

//echo "date: ", $ph[$x]['trade_date'], " stop loss $stop_loss \n";
$buy_point = get_buy_point($buy_signal, $next_buy_point, $ph[$x]['day_high_55'], $ph[$x]['day_high_20']);
//echo "date: ", $ph[$x]['trade_date'] , " stop loss: ", $stop_loss, " buy point: ", $buy_point, "next buy point ", $next_buy_point;
//echo " date ", $ph[$x]['trade_date'] , " stop loss: ", $stop_loss, " buy point: ", $buy_point;
			if ($current_pos == 0) {
//			   if ($ph[$x]['high'] > $buy_point && $buy_point > $stop_loss ) {
				// add new condition where only buy if 50 MA > 200 MA
			   if ($ph[$x]['high'] > $buy_point && $buy_point > $stop_loss && $buy_point > 0 && $ph[$x]['ma_50'] > $ph[$x]['ma_200']) {

				$risk_value = $cash_balance * $risk_factor / 100;
				$current_N = $ph[$x]['ATR'];
				if ($current_N > 0) {
				$num_shares = floor($risk_value /(2*$current_N));
				}
				
				$purchase_value = $num_shares * $buy_point;

				//$stop_loss = get_stop_loss($stop_loss_type, $stop_loss, $ph[$x]['close_price'], $ph[$x]['high'], $ph[$x]['low'], $ph[$x]['day_high_55'], $ph[$x]['day_high_20'], $ph[$x]['day_low_20'], $ph[$x]['ma_50'], $ph[$x]['ma_200'], $ph[$x]['ATR'] );
				// once we make a purchase, instead of using 55 day high as the basis for stop loss
				// we use the buy point
				$stop_loss = get_stop_loss($stop_loss_type, $stop_loss, $ph[$x]['close_price'], $ph[$x]['high'], $ph[$x]['low'], $buy_point, $ph[$x]['day_high_20'], $ph[$x]['day_low_20'], $ph[$x]['ma_50'], $ph[$x]['ma_200'], $ph[$x]['ATR'] );

				$next_buy_point = $buy_point + $current_N;

				if ($cash_balance > $purchase_value) {
					$current_pos += $num_shares;
					$cash_balance = $cash_balance - $purchase_value;				
				
				
					$my_sql  = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, ";
					$my_sql .= "price_paid, current_N, stop_loss, next_buy_point, stop_loss_type, cash_balance, current_pos, buy_type) ";
					$my_sql .= "values ('".$symbol."', '".$ph[$x]['trade_date']."', 'Buy', $num_shares, ".$buy_point.", ";
					$my_sql .= "$current_N, $stop_loss, $next_buy_point, '$stop_loss_type', $cash_balance, $current_pos, '$buy_signal') ";

					$result = queryMysql($my_sql);
					$pp = $ph[$x]['day_high_55'];
					$td = $ph[$x]['trade_date'];
	
				}
				
				$pyramid_mode ++;
			    } 
			}
			
			# if already has position 
			elseif ($current_pos > 0) {
				# if stock reaches next buy point

				if ($ph[$x]['high'] > $next_buy_point && $pyramid_mode && $pyramid_mode < 3 && $ph[$x]['ma_50'] > $ph[$x]['ma_200']) {
					$risk_value = $cash_balance * $risk_factor / 100;
					$current_N = $ph[$x]['ATR'];

					$num_shares = floor($risk_value /(2*$current_N));

					$purchase_value = $num_shares * $next_buy_point;
					$last_buy_point = $next_buy_point;
					
					if ($num_shares > 0 ) {
						$cash_balance = $cash_balance - $purchase_value;
						$current_pos += $num_shares;
						$pyramid_mode ++;
						$next_buy_point = $next_buy_point + $current_N;
						// once we make a purchase, instead of using 55 day high as the basis for stop loss
						// we use the buy point
//						$stop_loss = get_stop_loss($stop_loss_type, $stop_loss, $ph[$x]['close_price'], $ph[$x]['high'], $ph[$x]['low'], $ph[$x]['day_high_55'], $ph[$x]['day_high_20'], $ph[$x]['day_low_20'], $ph[$x]['ma_50'], $ph[$x]['ma_200'], $ph[$x]['ATR'] );
						$stop_loss = get_stop_loss($stop_loss_type, $stop_loss, $ph[$x]['close_price'], $ph[$x]['high'], $ph[$x]['low'], $buy_point, $ph[$x]['day_high_20'], $ph[$x]['day_low_20'], $ph[$x]['ma_50'], $ph[$x]['ma_200'], $ph[$x]['ATR'] );



						$pp = $last_buy_point;
						$td = $ph[$x]['trade_date'];


					$my_sql  = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, ";
					$my_sql .= "price_paid, current_N, stop_loss, next_buy_point, stop_loss_type, cash_balance, current_pos, buy_type) ";
					$my_sql .= "values ('".$symbol."', '".$ph[$x]['trade_date']."', 'Buy', $num_shares, ".$last_buy_point.", ";
					$my_sql .= "$current_N, $stop_loss, $next_buy_point, '$stop_loss_type', $cash_balance, $current_pos, '$buy_signal') ";
					$result = queryMysql($my_sql);

					}

				} 
				# sell if low of the day is below stop loss
			#if ($ph[$x]['low'] < $stop_loss && $current_pos > 0) {
				elseif ($ph[$x]['low'] < $stop_loss ) {
					# get current num of shares
					$my_sql = "select num_shares from portfolio where symbol = '$s' ";

					$next_buy_point = $stop_loss + $current_N;
//echo "next buy point ", $next_buy_point;
					$proceed = $current_pos * $stop_loss;
					$cash_balance += $proceed;
					

$td = $ph[$x]['trade_date'];
//print "Type: Sell	Date: $td	Symbol: $s	current pos:$current_pos	Price: $stop_loss proceed value: $proceed cash balance: $cash_balance\n";	
//print "<br>";


					$my_sql  = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, ";
					$my_sql .= "price_paid, current_N, stop_loss, next_buy_point, stop_loss_type, cash_balance, buy_type) ";
					$my_sql .= "values ('".$symbol."', '".$ph[$x]['trade_date']."', 'Sell', $current_pos, ".$stop_loss.", ";
					$my_sql .= "$current_N, 0, 0, '$stop_loss_type', $cash_balance, '$buy_signal') ";
					$result = queryMysql($my_sql);
					
					$current_pos = 0;
					$pyramid_mode = 0;

				}
					
			}		

		}
		
		$query  = "select * from turtle_s2_system where symbol = '".$symbol."'";
        $query .= " and stop_loss_type = '$stop_loss_type' and buy_type = '$buy_signal'"; 

		
		$query= stripslashes($query);
		$result = queryMysql($query);

		$json_result = array();

		$json_result = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($json_result);
	exit ;

	}


}
 
function get_stop_loss ($type, $current_stop, $close_price, $day_high, $day_low, $high_55, $high_20, $low_20, $ma_50, $ma_200, $ATR) {
	$stop = 0;

	if ($type == "ATR_1N") {
		$stop = $high_55 - $ATR;
	} else if ($type == "ATR_2N") {
		$stop = $high_55 - (2*$ATR);
	} else if ($type == "8PCT") {
		$stop = $high_55 * 0.92;
	} else if ($type == "50MA") {
		$stop = $ma_50;
	} else if ($type == "200MA") {
		$stop = $ma_200;
	}  			

	if ($stop > $close_price && $current_stop > 0)
	{
//echo "current stop: ", $current_stop, " close price: ", $close_price;
		return $current_stop;
	} else
	{
//echo "new stop: ", $stop, " close price: ", $close_price;

		return $stop;
	}
}

function get_buy_point($type, $buy_point, $high_55_day, $high_20_day) {
	$buy = $buy_point;
	
	if ($type == "HIGH_55" && $buy_point > $high_55_day) {
//	if ($type == "HIGH_55" ) {
		$buy = $high_55_day;
	} else if ($type == "HIGH_20" && $buy_point > $high_20_day) {
//	} else if ($type == "HIGH_20") {
		$buy = $high_20_day;
	}		

	return $buy;
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

?>