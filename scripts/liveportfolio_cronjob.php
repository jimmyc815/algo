<?php

date_default_timezone_set('America/New_York');
error_reporting(E_ALL & ~E_NOTICE);

include_once('connorsRSI_strat_13.php');
include_once('order_execution_engine.php');
include_once('connors_strat_live.php');

$liveportfolio = "liveportfolio";
$liveportfolio_account = "liveportfolio_account";
$liveportfolio_trade_hist = "liveportfolio_trade_hist";
$liveportfolio_trade_pnl_hist = "liveportfolio_trade_pnl_hist";
$liveportfolio_hist_performance = "liveportfolio_hist_performance";

$quote_table = "quotes";

if($_GET){
	if($_GET['action'] == 'daily_loop_all_portfolios'){ 
		$accounts = get_all_active_portfolio_id();
		
		daily_loop_all_portfolios();
	}	
}

function daily_loop_all_portfolios() {
	$accounts = get_all_active_portfolio_id();
	
	foreach ($accounts as $pid){
		echo "pid: $pid", PHP_EOL;
		daily_buy_sell_cron($pid);
		sleep(2);
	}

	
}

function daily_buy_sell_cron($pid) {
	$today = date("Y-m-d");
	#$today = "2015-05-15";	

	$account = live_get_account_setting($pid);

	## delete order_queue
	echo "delete order queue for $pid ", PHP_EOL;
	delete_queue($pid);

	## sell		
	echo "before sell", PHP_EOL;
	live_crsi_sell ($today, $pid, $account['exit_crsi'], "");	
   
    ## make sure the orders are inserted into table at separate time
    sleep (1);
    echo "before buy", PHP_EOL;
    ## buy        
    live_crsi_buy ($today, $pid, $account['enter_crsi'], $account['enter_range'], $account['pct_limit_below'], $account['order_by'], $account['risk_factor'], $account['risk_sd'], $account['max_risk']);
	
	echo "before record performance", PHP_EOL;
	## record portfolio value and performance
	record_daily_performance($pid);

    ## populate daily buy list
	live_populateDailyBuyList ($today, $account['enter_crsi'], $account['enter_range'], $pid, $account['pct_limit_below']);

}


?>