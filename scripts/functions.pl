use Finance::YahooQuote;
use Finance::QuoteHist::Yahoo;
use Finance::QuoteHist::Google;
use Finance::QuoteHist::BusinessWeek;
use Mysql;
use Math::Round;
use POSIX;
use Switch;


sub db_connect()
{
	# database information
	my $db="db380207220";
	my $host="db380207220.db.1and1.com";
	my $userid="dbo380207220";
	my $passwd="goldman1234";
	my $connectionInfo="dbi:mysql:$db;$host";

	##$dbh = DBI->connect($connectionInfo,$userid,$passwd) or die DBI::errstr;

	$dbh = Mysql->connect($host, $db, $userid, $passwd) or die ( "<H3>Server unreachable</H3>");

	return $dbh;
}

sub sql_query()
{
	my ($my_sql) = @_;

	if ($dbh == undef) {
		db_connect();
	}

	$sth = $dbh->Query($my_sql);

	return $sth;
}

sub get_detail_quote()
{
	my (@mySymbols) = (@_);
	useExtendedQueryFormat(); 

	undef $cusRefTick ;

	my @customQuotes = getquote @mySymbols;

	my $array_len = scalar @customQuotes;	

	if ($array_len == 1) {
	foreach my $cusRefTick (@customQuotes)
	{
		$sql_stmt = "select 1 from detail_quote where symbol = '@$cusRefTick[0]'";
		$sth = &sql_query($sql_stmt);

		if ($sth->rows == 0) {
			$sql_stmt = "insert into detail_quote (symbol) values ('@$cusRefTick[0]') ";
			&sql_query($sql_stmt);
		}

		my $myCompName = @$cusRefTick[1]; 
		$myCompName =~ s/'/''/g;

		#my @split_date = split('\/', @$cusRefTick[3]);
		my ($mm, $dd, $year) = split('\/', @$cusRefTick[3]);
		my $last_trade_date = $year."-".$mm."-".$dd;

		$sql_stmt  = "update detail_quote set ";
		#$sql_stmt .= "company_name = '@$cusRefTick[l]', ";
		$sql_stmt .= "company_name = '$myCompName', ";
		$sql_stmt .= "last_price = '@$cusRefTick[2]', ";
		$sql_stmt .= "last_trade_date = '$last_trade_date', ";
		$sql_stmt .= "last_trade_time = '@$cusRefTick[4]', ";
		$sql_stmt .= "daily_change = '@$cusRefTick[5]', ";
		$sql_stmt .= "percent_change = '@$cusRefTick[6]', ";
		$sql_stmt .= "volume = @$cusRefTick[7], ";
		$sql_stmt .= "average_daily_vol = @$cusRefTick[8], ";
		$sql_stmt .= "bid = '@$cusRefTick[9]', ";
		$sql_stmt .= "ask = '@$cusRefTick[10]', ";
		$sql_stmt .= "previous_close = '@$cusRefTick[11]', ";
		$sql_stmt .= "today_open = '@$cusRefTick[12]', ";
		$sql_stmt .= "day_range = '@$cusRefTick[13]', ";
		$sql_stmt .= "year_range = '@$cusRefTick[14]', ";
		$sql_stmt .= "eps = '@$cusRefTick[15]', ";
		$sql_stmt .= "pe_ratio = '@$cusRefTick[16]', ";
		$sql_stmt .= "div_date = '@$cusRefTick[17]', ";
		$sql_stmt .= "div_ttm = '@$cusRefTick[18]', ";
		$sql_stmt .= "div_yield = '@$cusRefTick[19]', ";
		$sql_stmt .= "market_cap = '@$cusRefTick[20]', ";
		$sql_stmt .= "exchange = '@$cusRefTick[21]', ";
		$sql_stmt .= "short_ratio = '@$cusRefTick[22]', ";
		$sql_stmt .= "1yr_target = '@$cusRefTick[23]', ";
		$sql_stmt .= "eps_current_year = '@$cusRefTick[24]', ";
		$sql_stmt .= "eps_next_year = '@$cusRefTick[25]', ";
		$sql_stmt .= "eps_next_quarter = '@$cusRefTick[26]', ";
		$sql_stmt .= "pe_ratio_current_year = '@$cusRefTick[27]', ";
		$sql_stmt .= "pe_ratio_next_year = '@$cusRefTick[28]', ";
		$sql_stmt .= "peg_ratio = '@$cusRefTick[29]', ";
		$sql_stmt .= "book_value = '@$cusRefTick[30]', ";
		$sql_stmt .= "price_book = '@$cusRefTick[31]', ";
		$sql_stmt .= "price_sales = '@$cusRefTick[32]', ";
		$sql_stmt .= "ebitda = '@$cusRefTick[33]', ";
		$sql_stmt .= "50_ma = '@$cusRefTick[34]', ";
		$sql_stmt .= "200_ma = '@$cusRefTick[35]' ";
		$sql_stmt .= "where symbol = '@$cusRefTick[0]' ";
			        	
		&sql_query($sql_stmt);
	}
	}


	return @customQuotes;
}


sub get_real_time_quote()
{
	my (@mySymbols) = (@_);
	useRealtimeQueryFormat();

	my @customQuotes = getquote @mySymbols;

	foreach my $cusRefTick (@customQuotes)
	{
		$sql_stmt  = "update realtime_quote set ";
		$sql_stmt .= "last_trade = '@$cusRefTick[25]', ";
		$sql_stmt .= "bid = '@$cusRefTick[23]', ";
		$sql_stmt .= "ask = '@$cusRefTick[22]', ";
		$sql_stmt .= "change_percent = '@$cusRefTick[24]', ";
		$sql_stmt .= "daily_change = '@$cusRefTick[26]', ";
		$sql_stmt .= "market_cap = '@$cusRefTick[28]' ";
		$sql_stmt .= "where symbol = '@$cusRefTick[0]' ";
			        	
		&sql_query($sql_stmt);
	}

	return @customQuotes;
}

sub update_today_price_history()
{
	my (@mySymbol) = (@_);
	my $s;
	my $sth;



   	foreach $s (@mySymbol) {
		#### check if price_history already contains latest price update, if it already exists, return 1
		$sql_stmt = "select 1 from price_history a, detail_quote b where a.symbol = '$s' and a.symbol=b.symbol and a.trade_date = b.last_trade_date  ";
		$sth = &sql_query($sql_stmt);

		if ($sth->rows == 0) {
			##### price_history doesn't have current price update, use insert
			$sql_stmt  = "insert into price_history (symbol, trade_date, close, daily_change, pct_change, volume) ";
			$sql_stmt .= "select symbol, last_trade_date, last_price, daily_change, percent_change, volume from detail_quote where symbol = '$s'";
			&sql_query($sql_stmt);

		} else {
			#### current price already exists, use update
			$sql_stmt  = "update price_history a, detail_quote b set a.close=b.last_price, a.daily_change=b.daily_change, a.pct_change=b.percent_change, a.volume=b.volume where ";
			##$sql_stmt  = "update price_history a, detail_quote b set a.close=b.last_price where ";
			$sql_stmt .= "a.symbol = '$s' and b.symbol = '$s' and a.trade_date = b.last_trade_date ";

			&sql_query($sql_stmt);
		}
	}

}

sub get_price_history()
{
	my (@mySymbol) = (@_);
	my $s;
	my $split_factor = 1; 
	my $split_date = "1950/01/01";
	my @split_array_date = ();
	my @split_array_factor = ();


   foreach $s (@mySymbol) {

	# calculate split factor
	$q = new Finance::QuoteHist::Yahoo
	(
		symbols    => $s,
		start_date => '1/1/2007',
		end_date   => 'today',
	);

	# Splits
	foreach $row ($q->splits()) {
        	($symbol, $date, $post, $pre) = @$row;
		$split_factor = $pre / $post;

		($y,$m,$d) = split /\//, $date;
		$split_date = $d+31*$m+365*$y;

	}


	$sql_stmt = "delete from price_history where symbol = '$s' ";
	&sql_query($sql_stmt);
      
        my @date;
        my @high;
        my @low;
        my @close;
        my @volume;
	my $split_count=0;

	my $x=1;
      
        # Values
        foreach $row ($q->quotes()) {
        	($symbol, $date, $open, $high, $low, $close, $volume) = @$row;

		#$sql_stmt = "insert into price_history values ('$symbol', '$date', $open, $high, $low, $close, $volume, null)";

		($y,$m,$d) = split /\//, $date;
		$current_date = $d+31*$m+365*$y;

		if ($split_date > $current_date) {
				$open = $open * $split_factor;
				$high = $high * $split_factor;
				$low = $low * $split_factor;
				$close = $close * $split_factor;
#				$volume = $volume / $split_factor;
		}

		#$sql_stmt = "insert into price_history (symbol, trade_date, trade_date_id, open, high, low, close, volume) values ('$symbol', '$date', $x, $open, $high, $low, $close, $volume)";
		$sql_stmt = "insert into price_history (symbol, trade_date, open, high, low, close, volume) values ('$symbol', '$date', $open, $high, $low, $close, $volume)";
#print "$sql_stmt \n";
		&sql_query($sql_stmt);



		$x++;
      
        }

	}





}

sub get_average_volume()
{
	my ($mySym) = @_;
	my $my_sql;
	my $my_avg_vol = 0;
	my $sth;

	$my_sql = "select average_daily_vol from detail_quote where symbol = '$mySym'";
	$sth = &sql_query($my_sql);

	while (@data = $sth->fetchrow_array()) {
		$my_avg_vol = $data[0];
	}

	if ($sth->rows == 0) {
		print "no average volume found for $mySym \n";
	}

	return $my_avg_vol;
}

sub update_daily_price_change()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my @trade_date = ();
	my @close_price = ();
	my @daily_change = ();
	my @daily_pct_change = ();
	my $array_length = "";
	my $tmp_tbl = "temp_cal_close".$$;

	foreach $s (@mySymbol) {

		# create temp table for calculation
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
		$my_sql = "create table $tmp_tbl select symbol, date_add(trade_date, INTERVAL -1 DAY) as trade_date, close from price_history where symbol='$s'";
		&sql_query($my_sql);
	}

}

sub update_relative_volume()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $s_avg_vol;

	foreach $s (@mySymbol) {
		$s_avg_vol=&get_average_volume($s);

		$my_sql = "update price_history set relative_avg_vol = (volume / $s_avg_vol) * 100 where symbol = '$s'";

		&sql_query($my_sql);
	}
}

sub build_aggregate_query()
{
	# field 0: agg function range start nth day
	# field 1: agg function range end nth day 
	# field 2: agg function
	# field 3: column to be calculated
	# field 4: symbol
	my (@input) = (@_);
	my $s;
	my $my_sql;
	my $num_days = $input[1] - 1 ;

	$my_sql = "select t1.symbol, t1.trade_date_id+1 as trade_date_id, \
			(select $input[2](t2.$input[3])	\ 
			from price_history as t2 \
			where t1.trade_date_id - t2.trade_date_id between $input[0] and $num_days and t2.symbol='$input[4]' and trade_date_id >= $input[1]) as 'result_col' \
			from price_history as t1 where t1.symbol = '$input[4]' order by t1.trade_date_id ";

	return $my_sql;	
}


sub update_20_day_low()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $tmp_tbl = "temp_cal".$$;

	foreach $s (@mySymbol) {
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 20, "min", "low", $s);

		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.20_DAY_LOW = t2.result_col where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
	}
}

sub update_20_day_high()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $tmp_tbl = "temp_cal".$$;

	foreach $s (@mySymbol) {
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 20, "max", "high", $s);

		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.20_DAY_HIGH = t2.result_col where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
	}
}

sub update_55_day_high()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $tmp_tbl = "temp_cal".$$;

	foreach $s (@mySymbol) {
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 55, "max", "high", $s);

		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.55_DAY_HIGH = t2.result_col \
			   where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
	}
}

sub update_50_MA()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $tmp_tbl = "temp_cal".$$;

	foreach $s (@mySymbol) {
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 50, "avg", "close", $s);

		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.50_MA = t2.result_col where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
	}
}

sub update_200_MA()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	my $tmp_tbl = "temp_cal".$$;

	foreach $s (@mySymbol) {
		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 200, "avg", "close", $s);

		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.200_MA = t2.result_col where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);
	}
}

sub clean_up()
{
        my $my_sql;
        my $tmp_tbl_1 = "temp_cal".$$;
	my $tmp_tbl_2 = "temp_cal_close".$$;
	my $tmp_tbl_3 = "temp_tbl".$$;
	my $tmp_tbl_4 = "temp_TR".$$;
	my $tmp_tbl_5 = "temp_TR2".$$;

        $my_sql = "drop table if exists $tmp_tbl_1";
        &sql_query($my_sql);
        $my_sql = "drop table if exists $tmp_tbl_2";
        &sql_query($my_sql);
        $my_sql = "drop table if exists $tmp_tbl_3";
        &sql_query($my_sql);
        $my_sql = "drop table if exists $tmp_tbl_4";
        &sql_query($my_sql);
        $my_sql = "drop table if exists $tmp_tbl_5";
        &sql_query($my_sql);
}


sub update_conquer_table()
{
	my (@mySymbol) = (@_);
	my $s;
	my $my_sql;
	

	foreach $s (@mySymbol) {
		# clear conquer table for this symbol
		$sql_stmt = "delete from conquer where symbol = '$s'";
		&sql_query($sql_stmt);

		$my_sql = "drop table if exists temp_ma_10";
		&sql_query($my_sql);

		### calculate MA(10)
		$my_sql = "create table temp_ma_10 ";
		$my_sql .= &build_aggregate_query(0, 10, "avg", "close", $s);
		&sql_query($my_sql);


		### update conquer table with MA 10
		$my_sql = "insert into conquer (symbol, trade_date_id, close_price, ma_10) select '$s', t1.trade_date_id, t2.close, t1.result_col \
			   from temp_ma_10 t1, price_history t2 where t2.symbol='$s' and t1.trade_date_id = t2.trade_date_id ";
		&sql_query ($my_sql);

#		$my_sql = "drop table if exists temp_ma_10";
#		&sql_query($my_sql);

		### calculate MA 10 day ago
		$my_sql = "create table temp_ma_10_day_ago ";
		$my_sql .= &build_aggregate_query(10, 10, "", "close_price", $s);
		$my_sql .= "select trade_date_id, ";

		$my_sql = "drop table if exists temp_ma_10day_ago";
		&sql_query($my_sql);

		$my_sql = "create table temp_ma_10day_ago \
			select t1.symbol, t1.trade_date_id, \
				(select t2.ma_10 \
				from conquer as t2  \
				where t1.trade_date_id - t2.trade_date_id between 10 and 10 and t2.symbol = '$s') as 'result_col' \
			from conquer as t1 ";
		&sql_query($my_sql);

		# update conquer with ma 10 day ago
		$my_sql = "update conquer t1, temp_ma_10day_ago t2 set t1.ma_10day_ago = t2.result_col \
			   where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);

		$my_sql = "drop table if exists temp_ma_10day_ago";
		&sql_query($my_sql);

		$my_sql = "drop table if exists temp_cal";
		&sql_query($my_sql);
		
		# calculate close 40 day ago
		$my_sql = "create table temp_cal \
			select t1.symbol, t1.trade_date_id, \
				(select t2.close \
				from price_history as t2 \
				where t1.trade_date_id - t2.trade_date_id between 40 and 40 and t2.symbol = '$s') as 'result_col' \
			from conquer as t1 "; 
		&sql_query($my_sql);

		$my_sql = "update conquer t1, temp_cal t2 set t1.close_40day_ago = t2.result_col \
			   where t1.trade_date_id = t2.trade_date_id and t1.symbol = t2.symbol ";
		&sql_query($my_sql);

		$my_sql = "drop table if exists temp_cal";
		&sql_query($my_sql);


		# update condition A, B, C
		# condition A: Close - MA(10)
		# condition B: MA (10) - MA (10 day ago)
		# condition C: Close - Close 40 day ago
		$my_sql = "update conquer set condition_a = close_price - ma_10 where symbol = '$s' ";
		&sql_query($my_sql);

		$my_sql = "update conquer set condition_b = ma_10 - ma_10day_ago where symbol = '$s' ";
		&sql_query($my_sql);

		$my_sql = "update conquer set condition_c = close_price - close_40day_ago where symbol = '$s' ";
#	print $my_sql, "\n";
		&sql_query($my_sql);

#		$my_sql = "update conquer set condition_a = close - ma_10, \
#				    	      condition_b = ma_10 - ma_10day_ago, \
#					      condition_c = close - close_40day_ago \
#				where symbol = '$s' ";
#		&sql_query($my_sql);

		$my_sql = "update conquer set conquer_signal = IF \
			   (condition_a > 0 and condition_b > 0 and condition_c > 0, 'Buy', IF \
			   (condition_a < 0 and condition_b < 0 and condition_c < 0, 'Sell', null))";

		&sql_query($my_sql);


	


	}

}


sub update_true_range()
{
	my (@mySymbol) = (@_);
	my $s;
	my $sth;
	my $my_sql;
	my @trade_date;
	my @open_price;
	my @high_price;
	my @low_price;
	my @close_price;

	my $sma_ATR = new Math::Business::SMA;
	my $tmp_tbl = "temp_tbl".$$;
	my $tmp_TR = "temp_TR".$$;
	my $tmp_TR2 = "temp_TR2".$$;


	foreach $s (@mySymbol) {
		# compute TR1 (DH-DL)
		$my_sql = "update price_history set TR1 = abs(high - low) where symbol = '$s'";
		&sql_query ($my_sql);


		# create temp table for calculation
		$my_sql = "drop table if exists $tmp_TR";
		&sql_query($my_sql);
		$my_sql = "create table $tmp_TR select symbol, date_add(trade_date, INTERVAL 1 DAY) as trade_date, trade_date_id+1 as trade_date_id, close from price_history where symbol='$s'";
		&sql_query($my_sql);


		# update daily change price
		$my_sql =  "update price_history a, $tmp_TR b set a.daily_change = (a.close - b.close) where a.trade_date_id = b.trade_date_id ";
		$my_sql .= "and a.symbol ='$s'";
		&sql_query ($my_sql);

		# update daily percent price change
		$my_sql =  "update price_history a, $tmp_TR b set a.pct_change = ((a.close - b.close)/b.close)*100 where a.trade_date_id = b.trade_date_id ";
		$my_sql .= "and a.symbol ='$s'";
		&sql_query ($my_sql);

		# compute TR2 (DayBeforeClose-DH)
		#$my_sql  = "update price_history a, $tmp_TR b set TR2 = abs(b.close - a.high) where a.trade_date = b.trade_date ";
		$my_sql  = "update price_history a, $tmp_TR b set TR2 = abs(b.close - a.high) where a.trade_date_id = b.trade_date_id ";
		$my_sql .= "and a.symbol = '$s'";
		&sql_query ($my_sql);

		# compute TR3 (DayBeforeClose-DL)
		#$my_sql  = "update price_history a, $tmp_TR b set TR3 = abs(b.close - a.low) where a.trade_date = b.trade_date ";
		$my_sql  = "update price_history a, $tmp_TR b set TR3 = abs(b.close - a.low) where a.trade_date_id = b.trade_date_id ";
		$my_sql .= "and a.symbol = '$s'";
		&sql_query ($my_sql);

		# computer TR (max of TR1, TR2, TR3)
		$my_sql = "drop table if exists $tmp_TR2 ";
		&sql_query ($my_sql);
		$my_sql = "create table $tmp_TR2  \
		   	select symbol, trade_date, TR1 as TR from price_history where symbol='$s' \
			union \
			select symbol, trade_date, TR2 as TR from price_history where symbol='$s' \
			union \
			select symbol, trade_date, TR3 as TR from price_history where symbol='$s'\
			order by trade_date ";

		&sql_query ($my_sql);

		$my_sql = "update price_history a set a.TR = (select max(TR) from $tmp_TR2 b where a.symbol = b.symbol and a.trade_date = b.trade_date group by b.trade_date) where a.symbol='$s'";
		&sql_query ($my_sql);

		# computer ATR (15 day average)
#$sma_ATR->set_days(15);

#$my_sql = "select trade_date, TR from price_history where symbol = '$s' order by trade_date desc";
#		$sth = &sql_query ($my_sql);
#		while (@data = $sth->fetchrow_array()) {
#			push (@trade_date, $data[0]);
#			push (@TR, $data[1]);
#		}

#		my $array_size = scalar @TR;

#		for (my $x=0; $x < ($array_size-15); $x++) {
#			my @TR15 = ();

#			for (my $y=$x+1; $y < $x+16; $y++) {
#				push (@TR15, $TR[$y]);
#			}

#			$sma_ATR->insert (@TR15);
#			#$sma_ATR = $sma_ATR->query;

#			if( defined( my $q = $sma_ATR->query ) ) {
#				push (@ATR, $q);
#         		} 

#		}

#		for (my $x=0; $x < $array_size-15; $x++) {
#			$my_sql = "update price_history set ATR = $ATR[$x] where trade_date = '$trade_date[$x]' and symbol = '$s'";
#			&sql_query($my_sql);

#		}

		$my_sql = "drop table if exists $tmp_tbl";
		&sql_query($my_sql);

		$my_sql = "create table $tmp_tbl ";
		$my_sql .= &build_aggregate_query(0, 15, "avg", "TR", $s);
		&sql_query($my_sql);

		$my_sql = "update price_history t1, $tmp_tbl t2 set t1.ATR = t2.result_col where t1.trade_date_id = t2.trade_date_id and t1.symbol = '$s'";
		&sql_query($my_sql);


	}



}

sub update_portfolio()
{
	my ($buy_or_sell, $symbol, $num_shares, $price, $value) = (@_);

	if ($buy_or_sell eq "buy")
	{
		# update position 
		$my_sql = "insert into portfolio (symbol, num_shares, price, value) values ('$symbol', $num_shares, $price, $value) \
			ON DUPLICATE KEY UPDATE num_shares = num_shares+$num_shares, price=$price, value=(num_shares*price) ";
		&sql_query($my_sql);
		# update cash
		$my_sql = "insert into portfolio (symbol, num_shares, price, value) values ('cash', $num_shares, 1, $num_shares) \
			ON DUPLICATE KEY UPDATE num_shares = num_shares-$value, value=num_shares ";
		&sql_query($my_sql);
	} elsif ($buy_or_sell eq "sell") {
		# update position 
		$my_sql = "insert into portfolio (symbol, num_shares, price, value) values ('$symbol', $num_shares, $price, $value) \
			ON DUPLICATE KEY UPDATE num_shares = num_shares-$num_shares, price=$price, value=(num_shares*price) ";
		&sql_query($my_sql);
		# update cash
		$my_sql = "insert into portfolio (symbol, num_shares, price, value) values ('cash', $num_shares, 1, $num_shares) \
			ON DUPLICATE KEY UPDATE num_shares = num_shares+$value, value=num_shares ";
		&sql_query($my_sql);
	}
}

#### S2 relies on 55 days as break out buy signal, and 2N as stop signal, then use 20-day low as exit
sub turtle_system_2 ()
{
	#my (@mySymbol) = (@_);
	my $mySymbol_ref = shift; 
	my $stop_loss_type_ref = shift;
	my $s;
	my $my_sql;
	my $cash_value;
	my $sth;
	# start trade date after 55 days
	my $sim_start_day = 55;
	my @trade_date = ();
	my @trade_date_id = ();
	my @open = ();
	my @high = ();
	my @low = ();
	my @close = ();
	my @ATR = ();
	my @day_high_55 = ();
	my @day_low_20 = ();
	my $risk_factor = 2;
	#my $pyramid_mode = undef;
	my $pyramid_mode = 3;
#	my $stop_loss_type ;


	# get current portfolio value
	#$portfolio_value = 1000000;

	#foreach $s (@mySymbol) {
	foreach $s (@$mySymbol_ref) {
		# reset turtle s2 system table for this sybmol
		$my_sql = "delete from turtle_s2_system where symbol = '$s'";
		&sql_query($my_sql);

		# get price history of stock
		$my_sql = "select trade_date, trade_date_id, open, high, low, close, ATR, 55_DAY_HIGH, 20_DAY_LOW, 50_MA, 200_MA \
			   from price_history where symbol ='$s' order by trade_date_id asc";
		$sth = &sql_query($my_sql);



		# buy when stock > 55 day high
		# calculate N for the day
		# buy round down to 100s (portfolio * 2% / 2N) shares
		# set stop loss at 1/2 N
		while (@data = $sth->fetchrow_array()) {
			# 0: trade_date
			# 1: trade_date_id
			# 2: open
			# 3: high
			# 4: low
			# 5: close
			# 6: ATR
			# 7: 55 day high
			# 8: 20 day low
			# 9: 50 ma
			# 10: 200 ma
			push (@trade_date, $data[0]);			
			push (@trade_date_id, $data[1]);			
			push (@open, $data[2]);			
			push (@high, $data[3]);			
			push (@low, $data[4]);			
			push (@close, $data[5]);			
			push (@ATR, $data[6]);			
			push (@day_high_55, $data[7]);			
			push (@day_low_20, $data[8]);			
			push (@ma_50, $data[9]);			
			push (@ma_200, $data[10]);			
		}

		$len_array = scalar @trade_date_id;	

		my $current_pos = 0;
		my $next_buy_point = 0;
		my $stop_loss = 0;
		my $num_shares = 0;
		my $current_N = 0;
		my $risk_value = 0;
		my $purchase_value = 0;

		for (my $x=$sim_start_day; $x < $len_array; $x++) {
			#$stop_loss = $day_high_55[$x] - (2*$ATR[$x]);
			#$stop_loss = $day_low_20[$x];
			#$stop_loss = $ma_50[$x] * 0.9;
			#$stop_loss = $ma_200[$x] * 0.9;

			$stop_loss_type = $$stop_loss_type_ref;

			### determine what type of stop loss to use
			switch ($$stop_loss_type_ref) {
				case "200_MA" { $stop_loss = $ma_200[$x] }
				case "50_MA"  { $stop_loss = $ma_50[$x] }
				case "200_MA_B"  { $stop_loss = $ma_200[$x] * 0.9 }
				case "50_MA_B"  { $stop_loss = $ma_50[$x] * 0.9 }
			}

			#if ($$stop_loss_type_ref eq "200_MA") {
			#	$stop_loss = $ma_200[$x];
			#} elsif ($$stop_loss_type_ref eq "50_MA") {
			#	$stop_loss = $ma_50[$x];
			#} else {
			#	$stop_loss = $day_low_20[$x];
			#}
#print "date_: $trade_date[$x]	stop_loss: $stop_loss MA 50: $ma_50[$x]	MA 200: $ma_200[$x]\n";
			

			if ($current_pos == 0) {
			   if ($high[$x] > $day_high_55[$x]) {
				$my_sql = "select value from portfolio where symbol = 'cash'";
				&sql_query($my_sql);
				$sth = &sql_query ($my_sql);
				while (@data = $sth->fetchrow_array()) {
					$cash_balance = $data[0];
				}
				
				$risk_value = $cash_balance * $risk_factor / 100;
				$current_N = $ATR[$x];
				$units_in_100 = floor(($risk_value /(2*$current_N))/100);
				$num_shares = $units_in_100 * 100; 
				$purchase_value = $num_shares * $day_high_55[$x];
				$stop_loss = $day_high_55[$x] - (2*$current_N);
				$next_buy_point = $day_high_55[$x] + $current_N;
#print "trade date: $trade_date[$x]   num shares: $num_shares  value: $purchase_value  price_paid: $day_high_55[$x] stop loss: $stop_loss next buy: $next_buy_point \n";
				if ($cash_balance > $purchase_value) {
					$my_sql = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, \
						price_paid, current_N, stop_loss, next_buy_point, stop_loss_type) \
					   	values ('$s', '$trade_date[$x]', 'Buy', $num_shares, $day_high_55[$x], \
					   	$current_N, $stop_loss, $next_buy_point, '$stop_loss_type') ";

					&sql_query($my_sql);

					&update_portfolio ("buy", $s, $num_shares, $day_high_55[$x], $purchase_value); 
print "Type: Buy1	Date: $trade_date[$x]	Symbol: $s	Num Shares: $num_shares	Price: $day_high_55[$x] cash:$cash_balance   purchase value: $purchase_value\n";	
				}

				$current_pos = $units_in_100;

			    } 
			}
			# if already has position 
			elsif ($current_pos > 0) {
				# if stock reaches next buy point
				if ($high[$x] > $next_buy_point && $pyramid_mode && $pyramid_mode < 4 ) {
					$my_sql = "select value from portfolio where symbol = 'cash'";
					&sql_query($my_sql);
					$sth = &sql_query ($my_sql);
					while (@data = $sth->fetchrow_array()) {
						$cash_value = $data[0];
					}

					$risk_value = $cash_value * $risk_factor / 100;
					$current_N = $ATR[$x];
					$units_in_100 = floor(($risk_value /(2*$current_N))/100);
					$num_shares = $units_in_100 * 100; 
					$purchase_value = $num_shares * $day_high_55[$x];

					if ($num_shares > 0 ) {
						&update_portfolio ("buy", $s, $num_shares, $next_buy_point, $purchase_value); 

print "Type: Buy2	Date: $trade_date[$x]	Symbol: $s	Num Shares: $num_shares	Price: $next_buy_point cash:$cash_balance   purchase value: $purchase_value\n";	

						$my_sql = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, \
						   price_paid, current_N, stop_loss, next_buy_point, stop_loss_type) \
						   values ('$s', '$trade_date[$x]', 'Buy', $num_shares, $day_high_55[$x], \
					   	   $current_N, $stop_loss, $next_buy_point, '$stop_loss_type') ";
						&sql_query($my_sql);

						$stop_loss = $day_high_55[$x] - (2*$current_N);
					}

					$next_buy_point = $next_buy_point + $current_N;
				} 
				# sell if low of the day is below stop loss
			#if ($low[$x] < $stop_loss && $current_pos > 0) {
				if ($low[$x] < $stop_loss) {
					# get current num of shares
					$my_sql = "select num_shares from portfolio where symbol = '$s' ";
					&sql_query($my_sql);
					$sth = &sql_query ($my_sql);
					while (@data = $sth->fetchrow_array()) {
						$num_shares = $data[0];
					}
					$proceed = $num_shares * $stop_loss;

print "Type: Sell	Date: $trade_date[$x]	Symbol: $s	Num Shares: $num_shares	Price: $stop_loss proceed value: $proceed\n";	
					&update_portfolio ("sell", $s, $num_shares, $stop_loss, $proceed); 

					# update turtle table
					$my_sql = "insert into turtle_s2_system (symbol, trade_date, trade_type, num_shares, \
						   price_paid, current_N, stop_loss, next_buy_point, stop_loss_type) \
						   values ('$s', '$trade_date[$x]', 'Sell', $num_shares, $stop_loss, \
					   	   $current_N, 0, 0, '$stop_loss_type') ";
					&sql_query($my_sql);

					$current_pos = 0;



				}
					
			}



			



		}
		

	}

	

	



}

sub get_box_price()
{
	my (@mySymbol) = (@_);
	my $s;

foreach $s (@mySymbol) {
	$q = new Finance::QuoteHist::Yahoo
	(
      		   symbols    => $s,
                   start_date => '1/1/2009',
                   end_date   => 'today',
        );
      
        my $min_price = 10000;
        my $max_price = 0;
        my $day_count = 0;
      
        my @date;
        my @high;
        my @low;
        my @close;
        my @volume;

	$top_break_date = "";
	$topEstDate = "";
	$bottom_break_date = "";
	$bottomEstDate = "";
      
        # Values
        foreach $row ($q->quotes()) {
        	($symbol, $date, $open, $high, $low, $close, $volume) = @$row;

      		push (@date, $date);
                push (@high, $high);
                push (@low, $low);
                push (@close, $close);
                push (@volume, $volume);

      
        }

	$len = @date;
	$day_count = 0;
	$currentMax = 0;
	$currentMin = 10000;
	$topEstablish = 0;
	$bottomEstablish = 1;


	while ($day_count < $len )
	{
		$day_count ++;

		if ($high[$day_count] > $topBox && $topBox > 0) 
		{
			$top_break_date = $date[$day_count];
		}

		if ($bottomEstablish == 1)
		{
			if ($high[$day_count] > $currentMax)
			{
				$currentMax = $high[$day_count];	
				$currentMaxDuration = 0;
			}
			else {
				$currentMaxDuration ++;
			}

			if ($low[$day_count] < $bottomBox) 
			{
				$bottom_break_date = $date[$day_count];
			}
		}

		if ($topEstablish == 1) 
		{
			if ($low[$day_count] < $currentMin) 
			{
				$currentMin = $low[$day_count];
				$currentMinDuration = 0;
			}	
			else 
			{
				$currentMinDuration ++ ;
			}

		}

		if ($currentMaxDuration == 3)
		{
			$topBox = $currentMax;
			$topEstDate = $date[$day_count];
			$buyPoint = $topBox * 1.01;
			$topEstablish = 1;
			$bottomEstablish = 0;
			$currentMaxDuration = -1;
			$currentMax = 0;
		}

		if ($currentMinDuration == 3)
		{
			$bottomBox = $currentMin;
			$bottomEstDate = $date[$day_count];
			$sellPoint = $bottomBox * 0.99;
			$bottomEstablish = 1;
			$topEstablish = 0;
			$currentMinDuration = -1;
			$currentMin = 10000;
		}
	}

		$sql_stmt  = "update stock_box set topbox = $topBox, bottombox = $bottomBox, top_est_date='$topEstDate', ";
		$sql_stmt .= "bottom_est_date='$bottomEstDate', top_break_date='$top_break_date', bottom_break_date='$bottom_break_date' ";
		$sql_stmt .= "where symbol = '$s' ";

#		print "sql: $sql_stmt \n";
			        	
		&sql_query($sql_stmt);

	}

}

sub simulation()
{
	my (@my_input) = (@_);
	my $s = $my_input[0];
	my $startDate = $my_input[1];

	if ($startDate == "") {$startDate = "1/1/2008";}

	my $capital = 10000;
	my $shares = 0;
	my $stop_loss = 0;

	$q = new Finance::QuoteHist::Yahoo
	(
      		   symbols    => $s,
                   start_date => $startDate,
                   end_date   => 'today',
        );
      
        my $min_price = 10000;
        my $max_price = 0;
        my $day_count = 0;
      
        my @date;
        my @high;
        my @low;
        my @close;
        my @volume;

	$top_break_date = "";
	$topEstDate = "";
	$bottom_break_date = "";
	$bottomEstDate = "";
      
        # Values
        foreach $row ($q->quotes()) {
        	($symbol, $date, $open, $high, $low, $close, $volume) = @$row;

      		push (@date, $date);
                push (@high, $high);
                push (@low, $low);
                push (@close, $close);
                push (@volume, $volume);

      
        }

	$len = @date;
	$day_count = 0;
	$currentMax = 0;
	$currentMin = 10000;
	$topEstablish = 0;
	$bottomEstablish = 1;


	while ($day_count < $len-1 )
	{
		$day_count ++;

$total_value = $shares * $close[$day_count];
print "today date: $date[$day_count] price: $close[$day_count] total portfolio value: $total_value stop loss: $stop_loss\n";

		if ($high[$day_count] > $topBox && $topBox > 0) 
		{
			$top_break_date = $date[$day_count];

			if ($capital > 0)
			{
				$shares = $capital / $topBox;
				$capital = 0;

				$stop_loss = $topBox * 0.92;
				print "purchase $shares shares on $date[$day_count] price $topBox set stop loss: $stop_loss\n";
					
			}
		}

		if ($bottomEstablish == 1)
		{
			if ($high[$day_count] > $currentMax)
			{
				$currentMax = $high[$day_count];	
				$currentMaxDuration = 0;
			}
			else {
				$currentMaxDuration ++;
			}

			if ($low[$day_count] < $bottomBox) 
			{
				$bottom_break_date = $date[$day_count];

				if ($shares > 0) 
				{
					$capital = $shares * $bottomBox;
					print "sold $shares shares on $date[$day_count] capital $capital price $bottomBox\n";
					$shares = 0;
				}
			}
		}

		if ($topEstablish == 1) 
		{
			if ($low[$day_count] < $currentMin) 
			{
				$currentMin = $low[$day_count];
				$currentMinDuration = 0;
			}	
			else 
			{
				$currentMinDuration ++ ;
			}

		}

		if ($currentMaxDuration == 3)
		{
			$topBox = $currentMax;
			$topEstDate = $date[$day_count];
			$buyPoint = $topBox * 1.01;
			$topEstablish = 1;
			$bottomEstablish = 0;
			$currentMaxDuration = -1;
			$currentMax = 0;
		}

		if ($currentMinDuration == 3)
		{
			$bottomBox = $currentMin;
			$bottomEstDate = $date[$day_count];
			$sellPoint = $bottomBox * 0.99;
			$bottomEstablish = 1;
			$topEstablish = 0;
			$currentMinDuration = -1;
			$currentMin = 10000;
		}


		if ($close[$day_count] < $stop_loss)
		{

			if ($shares > 0) 
			{
				$capital = $shares * $bottomBox;
				$shares = 0;
				print "stop loss at $close[$day_count] capital: $capital date: $date[$day_count]\n";
			}
		}
		if ($high[$day_count] > $currentMax && $currentMax > 0)
		{
			$stop_loss = $currentMax * 0.92;
		}

	}

#		$sql_stmt  = "update stock_box set topbox = $topBox, bottombox = $bottomBox, top_est_date='$topEstDate', ";
#		$sql_stmt .= "bottom_est_date='$bottomEstDate', top_break_date='$top_break_date', bottom_break_date='$bottom_break_date' ";
#		$sql_stmt .= "where symbol = '$s' ";

#		print "sql: $sql_stmt \n";
			        	
#		&sql_query($sql_stmt);

	print "number of shares: $shares 	capital: $capital	return: \n";


}

sub readFile ()
{
   open (FILENAME, "$filename") || die "couldn't open the file!";

   my $myrecord;
   my @myticker;

   while ($myrecord = <FILENAME>) {
	$myrecord =~ s/^\s+//;
	$myrecord =~ s/\s+$//;
	push (@myticker, uc $myrecord);
   }

   close(FILENAME);

   return @myticker;
}


1;
