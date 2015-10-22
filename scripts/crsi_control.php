
<?php

if($_GET){
	if($_GET['action'] == 'get_crsi'){ 
		$symbol = $_GET['symbol'];
		$startdate = $_GET['startdate'];
		$enddate = $_GET['enddate'];
		$enterCRSI = $_GET['enterCRSI'];
		$enterRange = $_GET['enterRange'];
		$enterLimit = $_GET['enterLimit'];
		$exitCRSI = $_GET['exitCRSI'];		
		
		#$command = escapeshellcmd("/ven276/bin/python /python_script/daily_rsi_reporter.py -s ".$symbol);
		$cmd  = "/ven276/bin/python /python_script/test_php.py ";
		$cmd  .= " --startdate ".$startdate." --enddate ".$enddate." --symbol ".$symbol;
		$cmd  .= " --CRSI ".$enterCRSI." --range ".$enterRange." --limit ".$enterLimit." --exit".$exitCRSI;
		 		
		$command = escapeshellcmd("/ven276/bin/python /python_script/test_php.py --startdate ".$startdate." --enddate ".$enddate." --symbol ".$symbol);
		#$command = escapeshellcmd($cmd);


		$output = shell_exec($command);
		#echo $output;
		$ret = $output;

		echo json_encode($output);		
		#echo json_encode("finish 2");
		}
		
		
}

	
	function test_cross_script () {
		global $max_num_holdings;
		global $max_risk;
		global $risk_factor;

		print "max num holding: ";
		print $max_num_holdings;
	
	}

#$output = shell_exec("python /python_script/daily_rsi_reporter.py -s DATA");
#echo $output;
#$ret = $output;

#echo json_encode($ret);		


?>

