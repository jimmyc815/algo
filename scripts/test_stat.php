#!/usr/local/bin/php5

<?php

// tables in mysql
// turtle_portfolio_performance
// turtle_portfolio_transaction
// turtle_portfolio


include_once 'dbfunction.php';
//include_once 'portfolio_selection.php';
include_once 'turtle_share_module.php';
include_once 'stats_module.php';


if ($_GET['action'] == 'test_cov') {
	$count = 0;
	$spy = array();
	$stock = array();
	
	$today_date = date("Y-m-d");  
	$year_ago_date=date('Y-m-d',strtotime('-1 year'));

	$spy = stock_return('SPY', $year_ago_date, $today_date);
	$stock = stock_return('AAPL', $year_ago_date, $today_date);

	$cov = stat_cov($spy, $stock);
	
	print "cov: $cov \n";
	
	$cc = stat_corr_coef($spy, $stock );
	
	print "corr coeff: $cc \n";
	
	$r_squared = stat_r_squared($spy, $stock);
	//$r_sqaured = pow($cc, 2);
	
	print "r sqaured: $r_squared \n";
	
	//$beta = stat_cov($spy, $stock) / stat_var($spy);
	$beta = stat_beta($spy, $stock);
	print "beta: $beta \n";
	
} else if ($_GET['action'] == 'test_calculate_portfolio_beta') {
	$today_date = date("Y-m-d");  
	$year_ago_date=date('Y-m-d',strtotime('-1 year'));

	$spy = stock_return('SPY', $year_ago_date, $today_date);


	$count = 0;
	
	$pvalue = turtle_portfolio_value(2);
			
	$query  = "select symbol, last_price, shares, last_price*shares, last_price*shares/".$pvalue;
	$query .= " from turtle_portfolio where portfolio_id = 2 and symbol != 'CASH' order by 4";
	
	$result = queryMysql($query);
	while ($data = mysql_fetch_row($result)) {
		$stock_return = array();
	
		$portfolio_array[$count]['symbol'] = $data[0];
		$portfolio_array[$count]['last_price'] = $data[1];
		$portfolio_array[$count]['shares'] = $data[2];
		$portfolio_array[$count]['market_value'] = $data[3];
		$portfolio_array[$count]['weight'] = $data[4];
		
		$stock_return = stock_return($data[0], $year_ago_date, $today_date);

		$portfolio_array[$count]['beta'] = stat_beta($spy, $stock_return);
		
	//	print "symbol: $data[0]	market value: $data[3]	weight: $data[4]	beta: ";
	//	print $portfolio_array[$count]['beta'] ;
		
		$pbeta += $portfolio_array[$count]['weight']*$portfolio_array[$count]['beta'];
		
	//	print "	pbeta: $pbeta \n";
	}
	
	print " final beta: $pbeta \n";

} else if ($_GET['action'] == 'test_calculate_portfolio_VaR') {
	$today_date = date("Y-m-d");  
	$year_ago_date=date('Y-m-d',strtotime('-1 year'));

	$spy = stock_return('SPY', $year_ago_date, $today_date);


	$count = 0;
	
	$pvalue = turtle_portfolio_value(2);
			
	$query  = "select symbol, last_price, shares, last_price*shares, last_price*shares/".$pvalue;
	$query .= " from turtle_portfolio where portfolio_id = 2 and symbol != 'CASH' order by 4";
	
	$result = queryMysql($query);
	
	$matrix = array();
	
	while ($data = mysql_fetch_row($result)) {
		$stock_return = array();
	
		$portfolio_array[$count]['symbol'] = $data[0];
		$portfolio_array[$count]['last_price'] = $data[1];
		$portfolio_array[$count]['shares'] = $data[2];
		$portfolio_array[$count]['market_value'] = $data[3];
		$portfolio_array[$count]['weight'] = $data[4];
		
		$stock_return = stock_return($data[0], $year_ago_date, $today_date);

		print "portfolio array $count ";
		print $portfolio_array[$count]['symbol'] ;
		print "\n";
		$matrix[$count] = $portfolio_array;
		//$portfolio_array[$count]['beta'] = stat_beta($spy, $stock_return);
		
	//	print "symbol: $data[0]	market value: $data[3]	weight: $data[4]	beta: ";
	//	print $portfolio_array[$count]['beta'] ;
		
	//	$pbeta += $portfolio_array[$count]['weight']*$portfolio_array[$count]['beta'];
		
	//	print "	pbeta: $pbeta \n";
	}
	
	stat_portfolio_VaR($matrix);
	print " final beta: $pbeta \n";

} 


?>
