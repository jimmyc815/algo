<?php
include_once 'dbfunction.php';


	date_default_timezone_set('America/Los_Angeles');
	
	
if($_POST){
	if($_GET['action'] == 'executeSQL3'){
		//$query = "SELECT sighting_id, sighting_date, creature_type FROM sightings order by sighting_date ASC ";
		$query = "select * from sightings";
		//$query = "SELECT sighting_id, sighting_date, creature_type FROM " + $_GET['txtInputQuery'];
		//$query = $_GET['txtInputQuery'];
		//$query = $_POST['txtInputQuery'];
		//$result = db_connection($query);
		$result = queryMysql($query);


		$sightings = array();

		$sightings = mysql_resultTo2DAssocArray($result);

		//echo json_encode(array("sightings" => $sightings));
		echo json_encode($sightings);
		
		exit;	
	}elseif ($_POST['oper'] == 'add'){
		$newSymbol = $_POST['symbol'];

		$query = "insert into my_portfolio (symbol) values ('".strtoupper($newSymbol)."')";

		$result = queryMysql($query);
		
		exit;	
	}elseif ($_POST['oper'] == 'del'){
		$newSymbol = $_POST['symbol'];

		$query = "delete from my_portfolio where symbol = '".strtoupper($newSymbol)."'";

		$result = queryMysql($query);
		
		exit;	
	}
}	

if($_GET){
	if($_GET['action'] == 'executeSQL'){
		
		$query = "SELECT sighting_id, sighting_date, creature_type FROM sightings order by sighting_date ASC ";
		$result = queryMysql($query);

		$sightings = array();
		

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			array_push($sightings, array('id' => $row['sighting_id'], 'date' => $row['sighting_date'], 'type' => $row['creature_type'] ));
		}
		

		echo json_encode(array("sightings" => $sightings));
		

		exit;	
	}elseif($_GET['action'] == 'executeSQL2'){
		$query = $_GET['txtInputQuery'];
		$query= stripslashes($query);

		$result = queryMysql($query);

		$sightings = array();
		$sightings = mysql_resultTo2DAssocArray($result);

		echo json_encode($sightings);
		exit;	
	}elseif($_GET['action'] == 'getDynamicSQLResult'){
		$query = $_GET['txtInputQuery'];

		$query= stripslashes($query);
		$result = queryMysql($query);

		$sightings = array();

		$sightings = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($sightings);
		
		exit;	
	}elseif($_GET['action'] == 'getStockQuote'){
/*		$query = $_GET['txtInputQuery'];

		$query= stripslashes($query);
		$result = queryMysql($query);

		$sightings = array();

		$sightings = mysql_resultTo2DAssocArray_JGrid($result);

		echo json_encode($sightings);
*/

		$stock_list = get_stock_list_from_portfolio();
//echo "stock list", $stock_list;
//		$ret = get_yahoo_rt_quote ("MA+MCP+GMCR+F+WYNN+BAC+C+GS+SZYM+PER+RIMM+SFLY+PCLN");
		$ret = get_yahoo_rt_quote ($stock_list);

		echo json_encode($ret);

		exit;	
	}elseif($_GET['action'] == 'getStockQuote2'){
		$stock_list = get_stock_list_from_portfolio();

		$ret = get_yahoo_rt_quote_2 ($stock_list);

		echo json_encode($ret);

		exit;	
	}elseif($_GET['action'] == 'getHP'){
		$symbol = $_GET['inputSymbol'];
		$begin_date = $_GET['begin_date'];
		$end_date = $_GET['end_date'];
		$freq = $_GET['freq'];
	
		$bd = explode('/', $begin_date);
		$ed = explode('/', $end_date);
		
		$begin_mon = $bd[0]-1;
		$begin_day = $bd[1];
		$begin_year = $bd[2];

		$end_mon = $ed[0]-1;
		$end_day = $ed[1];
		$end_year = $ed[2];

		$data = file_get_contents('http://ichart.finance.yahoo.com/table.csv?s='.$symbol.'&d='.$end_mon.'&e='.$end_day.'&f='.$end_year.'&g='.$freq.'&a='.$begin_mon.'&b='.$begin_day.'&c='.$begin_year.'&ignore=.csv');

		$result = array();
		$json1 = array();
		$json2 = array();
    	$colN = array();
    	$colM = array();	
    	$id = array();
    	$cell = array();
    	$colN =array('trade_date', 'day_high', 'day_low', 'volume', 'close');
		$colM =array (
				array(
					"edittype" => "date",
					"name" => "trade_date"
				),
				array(
					"edittype" => "real",
					"name" => "day_high",
					"width" => 100
				),
				array(
					"edittype" => "real",
					"name" => "day_low",
					"width" => 100
				),
				array(
					"edittype" => "real",
					"name" => "volume",
					"width" => 100
				),
				array(
					"edittype" => "real",
					"name" => "close",
					"width" => 100
				)					
			);		
		$rows = explode("\n", $data);

		for($i = 1; $i < count($rows)-1; $i++)
		{
//echo "rows $rows[$i] \n";
			$temp = explode(',', $rows[$i]);

			$date = $temp[0];
			$open = $temp[1];
			$high = $temp[2];
			$low = $temp[3];
			$close = $temp[4];
			$volume = $temp[5];
			$adj_vol = $temp[6];
			
			$json1[$i-1]['trade_date'] = str_replace("\"", "", $date); ;
			$json1[$i-1]['day_high'] = str_replace("\"", "",$high);
			$json1[$i-1]['day_low'] = str_replace("\"", "",$low);
			$json1[$i-1]['volume'] = str_replace("\"", "",$volume);
			$json1[$i-1]['close'] = str_replace("\"", "",$adj_vol);
			

			$cell[$i-1]['id'] = $i-1;
			$cell[$i-1]['cell'] = $json1[$i-1];

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

		echo json_encode($ret2);

		exit;	
	}elseif($_GET['action'] == 'getHPFromDB'){
		$symbol = $_GET['inputSymbol'];
		$begin_date = $_GET['begin_date'];
		$end_date = $_GET['end_date'];
		$freq = $_GET['freq'];
	
		$query  = "SELECT symbol, trade_date, close, daily_change, pct_change, volume, ";
		$query .= "55_DAY_HIGH, 20_DAY_HIGH, 20_DAY_LOW,50_MA, 200_MA ";
		$query .= "FROM price_history where symbol='".strtoupper($symbol)."'";
		$query .= "and trade_date between STR_TO_DATE('".$begin_date."', '%m/%d/%Y') ";
		$query .= "and STR_TO_DATE('".$end_date."', '%m/%d/%Y') ";
		$query .= "order by trade_date_id DESC ";

		$result = queryMysql($query);


		$data = array();
		

		$json1 = array();
		$json2 = array();
    	$colN = array();
    	$colM = array();	
    	$id = array();
    	$cell = array();
    	$colN = array('symbol','trade_date','close','daily_change','pct_change','volume','55_DAY_HIGH','20_DAY_HIGH','20_DAY_LOW','50_MA','200_MA');
		$colM =array (
				array(
					"edittype" => "text",
					"name" => "symbol"
				),
				array(
					"edittype" => "date",
					"name" => "trade_date"
				),
				array(
					"edittype" => "real",
					"name" => "close",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "daily_change",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "pct_change",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "volume",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "55_day_high",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "20_day_high",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "20_day_high",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "50_ma",
					"width" => 50
				),
				array(
					"edittype" => "real",
					"name" => "200_ma",
					"width" => 50
				)
		);		
		
	 	$i = 0;
		$rows = explode("\n", $data);
		
//		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		while ($row = mysql_fetch_row($result)) {

			$symbol = $row[0];
			$trade_date = $row[1];
			$close = $row[2];
			$daily_change = $row[3];
			$pct_change = $row4[4];
			$volume = $row[5];
			$day_high_55 = $row[6];
			$day_high_20 = $row[7];
			$day_low_20 = $row[8];
			$ma_50 = $row[9];
			$ma_20 = $row[10]; 
			
			$json1[$i]['symbol'] = str_replace("\"", "", $row[0]);
			$json1[$i]['trade_date'] = str_replace("\"", "",$row[1]);
			$json1[$i]['close'] = str_replace("\"", "",$row[2]);
			$json1[$i]['daily_change'] = str_replace("\"", "",$row[3]);
			$json1[$i]['pct_change'] = str_replace("\"", "",$row[4]).'%';
			$json1[$i]['volume'] = str_replace("\"", "",$row[5]);
			$json1[$i]['55_day_high'] = str_replace("\"", "",$row[6]);
			$json1[$i]['20_day_high'] = str_replace("\"", "",$row[7]);
			$json1[$i]['20_day_low'] = str_replace("\"", "",$row[8]);
			$json1[$i]['50_ma'] = str_replace("\"", "",$row[9]);
			$json1[$i]['200_ma'] = str_replace("\"", "",$row[10]);


			$cell[$i]['id'] = $i;
			$cell[$i]['cell'] = $json1[$i];

			$i++;

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

		echo json_encode($ret2);

		exit;	
	}else{}
}	


	function fail($message) {
		die(json_encode(array('status' => 'fail', 'message' => $message)));
	}
	function success($message) {
		die(json_encode(array('status' => 'success', 'message' => $message)));
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
    
function mysql_resultTo2DAssocArray ( $result) {
    $i=0;
    $ret = array();
    while ($row = mysql_fetch_assoc($result)) {
        foreach ($row as $key => $value) {
            $ret[$i][$key] = $value;
            }
        $i++;
        }
    return ($ret);
    }
    
function mysql_singleRowToArray ( $singleRow) {
    $i=0;
    $ret = array();
    //while ($row = mysql_fetch_assoc($result)) {
        foreach ($singleRow as $key => $value) {
            //$ret[$i][$key] = $value;
            array_push ($ret, $value);
            
            }
        $i++;
    //    }
    return ($ret);
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
					"name" => "symbol",
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


	$data = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=slk2c6c');

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
		$pc = $temp[4];
		
		
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

	return $ret3;

}

function get_yahoo_rt_quote_2 ($symbol) {
	$result = array();
	$json1 = array();
	$json2 = array();
    $colN = array();
    $colM = array();	
    $id = array();
    $cell = array();
    $colN =array('symbol', 'last_trade', 'price', 'pct_change', 'change');
	$colM =array (
				array(
					"edittype" => "text",
					"name" => "symbol",
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


	$data = file_get_contents('http://finance.yahoo.com/d/quotes.csv?s='.$symbol.'&f=slk2c6c');

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
		$pc = $temp[4];
		
		
		
		$json1[$i]['symbol'] = str_replace("\"", "", $sym); ;
		//$json1[$i]['name'] = str_replace("\"", "", $name);
		$json1[$i]['last_trade'] = str_replace("\"", "",$last_trade);
		
		$json1[$i]['price'] = str_replace("<b>", "", str_replace("/", "", (str_replace("\"", "", $price))));
		//$json1[$i]['price'] = $price;

		$json1[$i]['pct_change'] = str_replace("/", "", (str_replace("\"", "", $percent)));
		$json1[$i]['change'] = str_replace("/", "", (str_replace("\"", "", $change)));

		$cell[$i]['id'] = $i;
		$cell[$i]['cell'] = $json1[$i];
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
	  $ret2["total"] = 1; //count($rows)-1;

	  $ret3["JSON"] = "success";
	  $ret3["colModel"] = $colM;
	  $ret3["colNames"] = $colN;
	  $ret3["gridModel"] = $ret2;

	return $ret2;

}


function get_stock_list_from_portfolio() {
		$query = "SELECT symbol FROM my_portfolio order by symbol desc ";
		$result = queryMysql($query);
		$return;
		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$return = $row['symbol']."+".$return;
		}
		
		return $return;
}


?>