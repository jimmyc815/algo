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
include_once('connorsRSI_strat_15.php');


if($_GET){
	if($_GET['action'] == 'register_sim_test'){ 
		
			$enterCRSI = $_GET['enterCRSI'];
			$enterRange = $_GET['enterRange'];
			$pctLimit = $_GET['pctLimit'];
			$exitCRSI = $_GET['exitCRSI'];
			$maxRisk = $_GET['maxRisk'];
			$riskFactor = $_GET['riskFactor'];
			$riskSD = $_GET['riskSD'];
			$orderBy = $_GET['orderBy'];
			
			if ($_GET['commission']) {
				$commission = $_GET['commission'];
			}
			
			if ($enterCRSI && $enterRange && $pctLimit && $exitCRSI && $maxRisk && $riskFactor && $riskSD && $orderBy) {
				register_sim_test ($enterCRSI, $enterRange, $pctLimit, $exitCRSI, $orderBy, $maxRisk, $riskFactor, $riskSD);			
			} else {
				echo "something is empty enter CRSI: $enterCRSI enterRange: $enterRange pctLimt: $pctLimit exitCRSI: $exitCRSI maxRisk: $maxRisk riskF: $riskFactor riskSD: $riskSD orderby: $orderBy ", PHP_EOL;
				return "something is empty";
			}

	} 	elseif($_GET['action'] == 'populate_test_runs'){ 
		
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];

			if ($start_date && $end_date) {
				populate_test_runs ($start_date, $end_date);
			} else
			{
				echo "missing start date $start_date or end date $end_date ", PHP_EOL;
			}

	}   elseif($_GET['action'] == 'populate_test_cases'){ 
		
			$start_date = $_GET['start_date'];
			$end_date = $_GET['end_date'];

			populate_test_cases ();
			


			if ($start_date && $end_date) {
				populate_test_cases ();
			} else
			{
				echo "missing start date $start_date or end date $end_date ", PHP_EOL;
			}

	}   elseif($_GET['action'] == 'execute_test_runs'){ 
			$pid = $_GET['portfolio_id'];
		
			execute_test_runs($pid);
			

	} 
}

function register_sim_test ($enterCRSI, $enterRange, $pctLimit, $exitCRSI, $orderBy, $maxRisk, $riskFactor, $riskSD, $skipFactor)
{
	global $dbname;
	$simTestCases = "simTestCases";
	
	$query = "use $dbname";
	$result = queryMysql($query);	
	
	## copy portfolio holding

	$query = "insert ignore into $dbname.$simTestCases (enter_crsi, enter_range, pct_limit_below, exit_crsi, order_by, max_risk, risk_factor, risk_sd, skip_factor) 
			  values ($enterCRSI, $enterRange, $pctLimit, $exitCRSI, '$orderBy', $maxRisk, $riskFactor, $riskSD, $skipFactor)
			  					       ";

			  					       
    #echo "query : $query ", PHP_EOL;
	
	$result = queryMysql($query);	
	
	#echo "result: $result ", PHP_EOL;
}

function populate_test_cases () {
	global $dbname;
	$simTestCases = "simTestCases";
	
	$query = "use $dbname";
	$result = queryMysql($query);	
	
	
	
	$enterCRSIList = array(20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70);
	$enterRangeList = array(100, 75, 50, 25, 0);
	$limitBelowList = array(1, 2);
	$exitCRSIList = array(20, 30, 40, 50, 60, 70, 80);
	$orderByList = array('crsi desc', 'crsi asc');
	$maxRiskList = array(3, 4, 5, 6, 7);
	$riskFactorList = array(0.2, 0.25, 0.3, 0.35, 0.4, 0.45);
	$riskSDList = array(1, 2);
	$skipFactorList = array(0.05, 0.1, 0.15, 0.2);
	
	$count=0;
	
	for ($x=0; $x<count($enterCRSIList); $x++) {
		$enterCRSI = $enterCRSIList[$x];
		
		for ($y=0; $y<count($enterRangeList); $y++) {
			$enterRange = $enterRangeList[$y];
			
			for ($z=0; $z<count($limitBelowList); $z++) {
				$limitBelow = $limitBelowList[$z];
				
				for ($a=0; $a<count($exitCRSIList); $a++) {
					$exitCRSI = $exitCRSIList[$a];
					
					for ($b=0; $b<count($orderByList); $b++) {
						$orderBy = $orderByList[$b];
						
						for ($c=0; $c<count($maxRiskList); $c++) {
							$maxRisk = $maxRiskList[$c];
							
							for ($d=0; $d<count($riskFactorList); $d++){
								$riskFactor = $riskFactorList[$d];
								
								for ($e=0; $e<count($riskSDList); $e++) {
									$riskSD = $riskSDList[$e];
									
									for ($f=0; $f<count($skipFactorList); $f++) {
										$skipFactor = $skipFactorList[$f];
										
										#echo "count: $count CRSI: $enterCRSI enterRange $enterRange pct below: $limitBelow exitCRSI: $exitCRSI order by: $orderBy maxrisk: $maxRisk riskFactor: $riskFactor riskSD: $riskSD skip factor: $skipFactor ", PHP_EOL;
										
										$query ="insert ignore into $dbname.$simTestCases 
													 (enter_crsi, 
													  enter_range, 
													  pct_limit_below, 
													  exit_crsi, 
													  order_by, 
													  max_risk, 																
													  risk_factor, 
													  risk_sd,
													  skip_factor) 
												values (
													$enterCRSI, 
													$enterRange, 
													$limitBelow, 
													$exitCRSI, 
													'$orderBy', 
													$maxRisk, 
													$riskFactor, 
													$riskSD,
													$skipFactor )
												";

										$result = queryMysql($query);	

										$count++;
										
									}
								}
							}
						}
					}
				}
			}
		
		}
		
	}
	
	
}


function populate_test_runs ($start_date, $end_date)
{
	global $dbname;
	$simTestCases = "simTestCases";
	$simRuns = "simRuns";
	
	$query = "use $dbname";
	$result = queryMysql($query);	
	
	## copy portfolio holding
			  					       
    $query = "insert ignore into $dbname.$simRuns (caseID, start_date, end_date) select caseID, '$start_date', '$end_date' from $dbname.$simTestCases";

			  					       
    echo "query : $query ", PHP_EOL;
	
	$result = queryMysql($query);	
	
	#echo "result: $result ", PHP_EOL;
}

function execute_test_runs ($pid)
{
	global $dbname;
	$simTestCases = "simTestCases";
	$simRuns = "simRuns";
	$simRestart = "simRestart";
	$maxParallelRun = 13;
	$commission = 7;
	
	$query = "use $dbname";
	$result = queryMysql($query);	

	# count number of simulation runs left
	$query = "select count(*) from $simRuns ";
	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$runCount = $data[0];
	}
	
	# count number of current process running simulation
	$query = "select count(*) from $simRestart ";
	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$restartCount = $data[0];
	}
	
	$restartRunID = "";	
	$restartCaseID = "";	
		
	# check if restart table has unfinished run id for this pid
	$query = "select runID, caseID from $simRestart where PID = $pid";
	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$restartRunID = $data[0];
		$restartCaseID = $data[1];
	}
	
		
	#if ($restartCount < $maxParallelRun) {
		
		#while ($runCount > 0) {
			## copy portfolio holding
			
			# if there's unfinished run id for this pid in restart table, rerun simulation for this run id
			if ($restartCaseID) {
				$query = "select a.runID, a.caseID, start_date, end_date, enter_crsi, enter_range, pct_limit_below, exit_crsi, order_by, max_risk, risk_factor, risk_sd, skip_factor 
						  from $simRestart a, $simTestCases b
						  where a.caseID = b.caseID 
						  and a.runID = $restartRunID 
						  ";
				
			} else {
				
			
							       
				$query = "select runID, a.caseID, start_date, end_date, enter_crsi, enter_range, pct_limit_below, exit_crsi, order_by, max_risk, risk_factor, risk_sd, skip_factor 
						  from $simRuns a, $simTestCases b 
						  where a.caseID = b.caseID 
						  order by runID desc limit 1";
				
			}		  					       

						  #and enter_crsi not in (select enter_crsi from $simTestCases c, $simRestart d where c.caseID = d.caseID  ) 

			#echo "query : $query ", PHP_EOL;
			
			$result = queryMysql($query);
			while ($data = mysql_fetch_row($result)) {
				$runID = $data[0];
				$caseID = $data[1];
				$startDate = $data[2];
				$endDate = $data[3];
				$enterCRSI = $data[4];
				$enterRange = $data[5];
				$limitBelow = $data[6];
				$exitCRSI = $data[7];
				$orderBy = $data[8];
				$maxRisk = $data[9];
				$riskFactor = $data[10];
				$riskSD = $data[11];
				$skipFactor = $data[12];	
			}		
	
#echo "resart case id $restartCaseID", PHP_EOL;	
			if (empty($restartCaseID)) {
				# move current RunID to restart table
	#echo "inside ", PHP_EOL;
				$query = "insert into $simRestart select runID, caseID, start_date, end_date, $pid from $simRuns where runID = $runID";
				$result = queryMysql($query);
			
				# delete runID from run table
				
				$query = "delete from $simRuns where runID = $runID ";
				$result = queryMysql($query);
				
			}

#echo "query: $query ", PHP_EOL;
			#$portfolioID = $enterCRSI;
			
			echo "portfolio id: $pid start date: $startDate end date: $endDate enter crsi: $enterCRSI enter range: $enterRange limit below: $limitBelow exit crsi: $exitCRSI commission $commission max risk: $maxRisk risk factor: $riskFactor risk sd: $riskSD order by $orderBy skip factor: $skipFactor ", PHP_EOL;		
				
			
			## start simultation
			$simResult = simulate_range_trade($pid, $startDate, $endDate, $enterCRSI, $enterRange, $limitBelow, $exitCRSI, $commission, $maxRisk, $riskFactor, $riskSD, $orderBy, $skipFactor );	
			
			#echo "sim Result: $simResult ", PHP_EOL;
			
			$pythonOut = system ("python /python_script/PortfolioAnalyzer3.py --run_id $runID --portfolio $pid");
			echo "python out: $pythonOut ", PHP_EOL;
			
			## remove runID from restart table
			$query = "delete from $simRestart where runID = $runID ";
			$result = queryMysql($query);
		
			# count number of simulation runs left
			$query = "select count(*) from $simRuns ";
			$result = queryMysql($query);
			while ($data = mysql_fetch_row($result)) {
				$runCount = $data[0];
			}
			
			echo "run count: $runCount", PHP_EOL;
		#}
	#}
}


?>
