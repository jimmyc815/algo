<?php
date_default_timezone_set('America/New_York');
error_reporting(E_ALL & ~E_NOTICE);


include_once 'dbfunction.php';
include_once 'trend_setup.php';


$dbname="db380207220";
$dbhost="localhost";
$dbuser="root";
$dbpass=NULL;

#$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);


if($_GET){
	if($_GET['action'] == 'list'){ 
		$query = $_GET['txtInputQuery']." ORDER BY ". $_REQUEST['jtSorting']. " LIMIT ". $_REQUEST['jtStartIndex'] . "," . $_REQUEST['jtPageSize'];

		$query= stripslashes($query);
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
	}	
	
	elseif($_GET['action'] == 'export_to_csv'){ 
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

?>
