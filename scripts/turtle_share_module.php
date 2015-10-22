<?php // turtle_share_module.php

function populateDailyBuyList ($today_date, $movingAvg, $rankAndWeightArray, $portfolioID, $dailyBuyList) {
			if (!$portfolioID) {
				$portfolioID = 1;
			}
			if (!$dailyBuyList) {
				$dailyBuyList = "turtle_daily_buy_list";
			}
			//clear daily buy list
			$query = "delete from ".$dailyBuyList." where portfolio_id = ".$portfolioID;
			$result = queryMysql($query);
			// reset identity column
			$query = "alter table ".$dailyBuyList." auto_increment = 1";
			$result = queryMysql($query);
			
			$query = "insert into ".$dailyBuyList." (portfolio_id, trade_date, symbol, buy_price) ";
			$query .= "select ".$portfolioID.", a.trade_date, a.symbol, a.".$movingAvg;
			$query .= " from price_history a, stock_list b ";
			$query .= "where a.trade_date = '".$today_date."' ";
			$query .= "and a.symbol = b.symbol ";
			$query .= " and a.50_MA > a.200_MA";
			$query .= " and a.".$movingAvg." > 0 ";
			$query .= " and a.symbol not in (select symbol from turtle_portfolio where portfolio_id = ".$portfolioID.") ";
			$query .= " order by vsSpyEMA desc";
			$query .= " limit 0, 100 ";
//print "$query \n";
			$query = stripslashes($query);
//print "$query ";
			$result = queryMysql($query);
}

function get_current_risk($portfolioID) {
	$p_value =get_real_time_turtle_portfolio_value($portfolioID);
	
	$tmp_sql = "select sum(risk) from turtle_portfolio where symbol != 'CASH' and portfolio_id = ".$portfolioID;
	$tmp_result = queryMysql($tmp_sql);
	
	while ($tmp_data = mysql_fetch_row($tmp_result)) {
		$r_value = $tmp_data[0];
	}
	
	$current_risk = ($r_value / $p_value) * 100;
	
	return $current_risk;
	
}

function stock_return($symbol, $start_date, $end_date)
{
	$count = 0;
	$stock_return = array();

	$query = "select symbol,  trade_date, close, pct_change from price_history where symbol = '".$symbol."' and trade_date >= '".$start_date."' and trade_date <= '".$end_date."'";
	$result = queryMysql($query);

	while ($data = mysql_fetch_row($result)) {
		$stock_return[$count] = $data[3];
		$count ++;
	}

	return $stock_return;
}

function turtle_portfolio_value($portfolioID) {
	
	$my_sql  = "select sum(a.shares * b.last_price) from turtle_portfolio a, detail_quote b where a.portfolio_id = ".$portfolioID." and a.symbol = b.symbol and a.symbol != 'CASH' ";
	$my_sql .= "union ";
	$my_sql .= "select shares from turtle_portfolio where symbol = 'CASH' and portfolio_id = ".$portfolioID;
//print "my sql: $my_sql \n";
	$result = queryMysql($my_sql);
	while ($data = mysql_fetch_row($result)) {
		$value += ($data[0] * 1 / 1);
	}

	return $value;
}


?>