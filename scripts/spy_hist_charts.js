$(document).ready(function() {
    var url = window.location.href;
    var q_str_part = url.match(/\?(.+)$/)[1];
 
    var val_pairs = q_str_part.split('&');
    
    var params = {};
 
    for (var i = 0; i < val_pairs.length; i++) {
        var tmp = val_pairs[i].split('=');

        params[tmp[0]] = typeof tmp[1] != 'undefined' ? tmp[1] : '';

        if (tmp[0] == 'symbol') 
        {
	        var symbol = tmp[1];
	        symbol = symbol.toUpperCase();
	                }  
        if (tmp[0] == 'portfolio_id') 
        {
	        var portfolio_id = tmp[1];
	    }        
    }

// symbol = "SBUX";
	swing_points = requestSwingPoints(symbol, portfolio_id);
	trend_changes = requestTrends(symbol, portfolio_id);
	anchor_points = requestAnchorPoints(symbol, portfolio_id);
	test_transaction_points_gain = requestTestTransaction(symbol, 'gain');
	test_transaction_points_loss = requestTestTransaction(symbol, 'loss');
	
	var day_forward = "1_day_perf";
	all_post_perf = requestMarketForwardPerf(day_forward);
	
	var hist_forward_perf = [];
	var post_1_day_perf = [];
	var post_5_day_perf = [];
	var post_10_day_perf = [];
	var post_20_day_perf = [];
	var post_40_day_perf = [];
	var post_60_day_perf = [];


	forward_perf_length = all_post_perf.length;

		for (i = 0; i < forward_perf_length; i++) {
			post_1_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][1] // change			
			]);

			post_5_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][2] // change			
			]);
			post_10_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][3] // change			
			]);
			post_20_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][4] // change			
			]);
			post_40_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][5] // change			
			]);		

			post_60_day_perf.push([
				all_post_perf[i][0], // the date
				all_post_perf[i][6] // change			
			]);	
			//	console.log ("date: " );
			//	console.log ( one_day_forward_perf[i][0] );
		}

	all_market_ratio = requestMarketRatio();
	
	var	ratio_20_high_over_20_low = []; 
	var	ratio_55_high_over_20_low = [];
	var	ratio_above_50_over_below_50 = []; 
	var	ratio_above_200_over_below_50 = []; 
	var	ratio_above_50_over_below_200 = []; 
	var	ratio_above_200_over_below_200 = []; 
	var	ratio_20_high_over_above_50 = []; 
	var	ratio_20_high_over_above_200 = []; 
	var	ratio_20_high_over_below_50 = []; 
	var	ratio_20_high_over_below_200 = [];	

	ratio_length = all_market_ratio.length;

		for (i = 0; i < ratio_length; i++) {
			ratio_20_high_over_20_low.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][1] // change			
			]);

			ratio_55_high_over_20_low.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][2] // change			
			]);
			
			ratio_above_50_over_below_50.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][3] // change			
			]);
			
			ratio_above_200_over_below_50.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][4] // change			
			]);
			
			ratio_above_50_over_below_200.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][5] // change			
			]);
			
			ratio_above_200_over_below_200.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][6] // change			
			]);
			
			ratio_20_high_over_above_50.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][7] // change			
			]);
			
			ratio_20_high_over_above_200.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][8] // change			
			]);
			
			ratio_20_high_over_below_50.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][9] // change			
			]);
			
			ratio_20_high_over_below_200.push([
				all_market_ratio[i][0], // the date
				all_market_ratio[i][10] // change			
			]);

		}		
	//var tmp_length = test_transaction_points.length;
	
	//for (j = 0; j < tmp_length; j++) {
		//print "title: ",  test_transaction_points[j]['title'];
	//}


	stock_transaction_record = requestStockTransaction(symbol, portfolio_id);
	
	
	stock_50_ma = requestStock50MA(symbol);
	stock_200_ma = requestStock200MA(symbol);


	$.getJSON('scripts/portfolio_selection.php?action=get_stock_price_history_ohlc&portfolio_id='+portfolio_id+'&symbol='+symbol+'&start_date=2005-01-01', function(data) {
		var ohlc = [],
			volume = [],
			max_volume = 0,
			dataLength = data.length;

		for (i = 0; i < dataLength; i++) {
			ohlc.push([
				data[i][0], // the date
				data[i][1], // open
				data[i][2], // high
				data[i][3], // low
				data[i][4] // close
			]);
			
			volume.push([
				data[i][0], // the date
				data[i][5] // the volume
			])
			
			// find max volume
			if (data[i][5] > max_volume) {
				max_volume = data[i][5];
			}
		}
		
	
		// set the allowed units for data grouping
		var groupingUnits = [[
			'week',                         // unit name
			[1]                             // allowed multiples
		], [
			'month',
			[1, 2, 3, 4, 6]
		]];
	
		// Create the chart
		window.chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container',
				                borderWidth: 1

			},

			rangeSelector : {
				selected : 2
			},

			title : {
				text : symbol+' Stock Price'
			},
			yAxis: [{
		        title: {
		            text: 'OHLC'
		        },
		        height: 200,
		        lineWidth: 2
		    }, {
		        title: {
		            text: 'SPY Forward Perf'
		        },
		        top: 300,
		        height: 200,
		        //max: max_volume, 
		        offset: 0,
		        lineWidth: 2
		    }, {
		        title: {
		            text: 'Market Ratios'
		        },
		        top: 550,
		        height: 200,
		        //max: max_volume, 
		        offset: 0,
		        lineWidth: 2
		    }],
		    legend : {
			    enabled: true,
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: 10,
                y: 100,
                borderWidth: 1
		    },
		    
			series : [
				{
					type: 'candlestick',
					name : symbol,
					data : ohlc,
					tooltip: {
						valueDecimals: 2
					}
					
					//, dataGrouping: {
					//	//units: groupingUnits
					//}
				}
				,
				{
					name: "50 Day Moving Average",
					data : stock_50_ma,
					tooltip: {
						valueDecimals: 2
						},
						color: 'red'
				},	
				{
					name: "200 Day Moving Average",
					data : stock_200_ma,
					tooltip: {
						valueDecimals: 2
						},
						color: 'green'
				}
				,{
					name : 'Swing Points',
					type : 'flags',
			        shape: 'circle',
	
					data : swing_points
				}
				,{
					name : 'Test Transaction Records - Loss',
					type : 'flags',
			        shape: 'squarepin',
					fillColor: 'red',
					data : test_transaction_points_loss
				}
				,{
					name : 'Test Transaction Records - Gain',
					type : 'flags',
			        shape: 'squarepin',
					fillColor: 'green',
					data : test_transaction_points_gain
				}
				,{
					type : 'flags',
					shape: 'squarepin',
					name: 'Trend',
					fillColor: '#66CCFF',
					color: '#000000',
					data : trend_changes,
					visible: false

				}, 

				
				/*{
					name : 'Anchor Points',
					type : 'flags',
			        shape: 'circle',
					fillColor: 'red',
					data: anchor_points
				}, */ 
				{
			       // type: 'column',
			        type: 'spline',
			        name: '1 Day Forward Perf',
			        data: post_1_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    } , 
			    {
			       // type: 'column',
			        type: 'spline',
			        name: '5 Day Forward Perf',
			        data: post_5_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    }, 
			    {
			       // type: 'column',
			        type: 'spline',
			        name: '10 Day Forward Perf',
			        data: post_10_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    }, 
			    {
			       // type: 'column',
			        type: 'spline',
			        name: '20 Day Forward Perf',
			        data: post_20_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    }, 
			    {
			       // type: 'column',
			        type: 'spline',
			        name: '40 Day Forward Perf',
			        data: post_40_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    }, 
			    {
			       // type: 'column',
			        type: 'spline',
			        name: '60 Day Forward Perf',
			        data: post_60_day_perf,
			        yAxis: 1,
			        dataGrouping: {
						units: groupingUnits
						}
			    }
			    
/*				,{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 20 High Over 20 Low',
			        data: ratio_20_high_over_20_low,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 50 High Over 20 Low',
			        data: ratio_55_high_over_20_low,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    }
*/			    
			    ,{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio Above 50 Over Below 50',
			        data: ratio_above_50_over_below_50,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio Above 200 Over Below 50',
			        data: ratio_above_200_over_below_50,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio Above 50 Over Below 200',
			        data: ratio_above_50_over_below_200,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio Above 200 Over Below 200',
			        data: ratio_above_200_over_below_200,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 20 High Over Above 50',
			        data: ratio_20_high_over_above_50,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 20 High Over Above 200',
			        data: ratio_20_high_over_above_200,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 20 High Over Below 50',
			        data: ratio_20_high_over_below_50,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    },{
			       // type: 'column',
			        type: 'spline',
			        name: 'Ratio 20 High Over Below 200',
			        data: ratio_20_high_over_below_200,
			        yAxis: 2,
			        dataGrouping: {
						units: groupingUnits
						}
			    }
			    
			    
			    /*
				{
			        type: 'column',
			        name: 'Volume',
			        data: volume,
			        yAxis: 1//,
			        //dataGrouping: {
						//units: groupingUnits
					//	}
			    }
				*/	
				
			]
		});
	});
	
	
	/*$.getJSON('scripts/portfolio_selection.php?action=get_stock_ranking_history&portfolio_id='+portfolio_id+'&symbol='+symbol+'&start_date=2005-01-01', function(data) {
		// Create the chart
		
		// set the allowed units for data grouping
		var groupingUnits = [[
			'week',                         // unit name
			[1]                             // allowed multiples
		], [
			'month',
			[1, 2, 3, 4, 6]
		]];
		
		window.chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container2'
			},

			rangeSelector : {
				selected : 2
			},

			title : {
				text : symbol+' SPY Forward Performance '
			},
		    yAxis:{
			    min: -100,
			    max: 100,
			    tickInterval: 100			    
			
			
			},			
			series : [
			        {name: '1 Day Forward Perf',
			        data: post_1_day_perf,
			        dataGrouping: {
						units: groupingUnits
						}
					} , 
					{
			       // type: 'column',
			        name: '5 Day Forward Perf',
			        data: post_5_day_perf,
			        dataGrouping: {
						units: groupingUnits
						}
			    }
			]
		});
	});
	*/
	
});

		
/**
 * Request data from the server, add it to the graph and set a timeout to request again
 */
function requestData() {
    $.ajax({
//        url: 'live-server-data.php',
		url: 'scripts/portfolio_selection.php?action=get_historical_stock_return&symbol=SPY&start_date=2012-01-01', 
        success: function(point) {
            var series = chart.series[0],
               shift = series.data.length > 20; // shift if the series is longer than 20

            // add the point
            //chart.series[0].addPoint(point, true, shift);
            chart.series[0].addPoint(point);

            // call it again after one second
        //    setTimeout(requestData, 1000);    
        },
        cache: false
    });
}

function requestStockTransaction(symbol, pid) {
	
	
    $.ajax({
		url: 'scripts/portfolio_selection.php?action=get_stock_transaction_record&portfolio_id='+pid+'&symbol='+symbol, 
		async: false,
			dataType: "json",
			success: function(stockTransactionReturn)
			{
				//pvalue = portfolioValue[0];
				data = stockTransactionReturn;
				return data;
			}
    });	

    return data;	
}

function requestSwingPoints(symbol, pid) {
	//var swingPointReturn ;
	
	var time_frame ;
	time_frame = "ST";
	
    $.ajax({
//		url: 'scripts/trend_setup.php?action=get_swing_points&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
//		url: 'scripts/portfolio_selection.php?action=get_swing_points_and_trends&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		url: 'scripts/trading_engine.php?action=get_swing_points_and_trends&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol+'&time_frame='+time_frame, 

		async: false,
			dataType: "json",
			success: function(swingPointReturn)
			{
				//pvalue = portfolioValue[0];
				data = swingPointReturn;

				//return swingPointReturn;
				return data;
			}
    });	

    //return swingPointReturn;	
    return data;
}

function requestTrends(symbol, pid) {
	//var swingPointReturn ;
	
    $.ajax({
		//url: 'scripts/portfolio_selection.php?action=get_swing_points_and_trends&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		url: 'scripts/portfolio_selection.php?action=get_trends&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		async: false,
			dataType: "json",
			success: function(trendReturn)
			{
				//pvalue = portfolioValue[0];
				data = trendReturn;

				//return swingPointReturn;
				return data;
			}
    });	

    //return swingPointReturn;	
    return data;
}

function requestAnchorPoints(symbol, pid) {
	//var swingPointReturn ;
	
    $.ajax({
		//url: 'scripts/portfolio_selection.php?action=get_swing_points_and_trends&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		url: 'scripts/portfolio_selection.php?action=get_anchor_points&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		async: false,
			dataType: "json",
			success: function(anchorReturn)
			{
				//pvalue = portfolioValue[0];
				data = anchorReturn;

				//return swingPointReturn;
				return data;
			}
    });	

    //return swingPointReturn;	
    return data;
}

function requestTestTransaction(symbol, gain_or_loss) {	
	var time_frame ;
	time_frame = "ST";
	
    $.ajax({
		url: 'scripts/trading_engine.php?action=get_test_transactions&&start_date=2005-01-01&symbol='+symbol+'&gain_or_loss='+gain_or_loss+'&time_frame='+time_frame, 

		async: false,
			dataType: "json",
			success: function(transactionRecordReturn)
			{
				//pvalue = portfolioValue[0];
				data = transactionRecordReturn;

				//return swingPointReturn;
				return data;
			}
    });	

    //return swingPointReturn;	
    return data;
}


function requestStock50MA(symbol) {
    $.ajax({
		url: 'scripts/portfolio_selection.php?action=get_stock_50_MA&symbol='+symbol, 
		async: false,
			dataType: "json",
			success: function(stock50MA)
			{
				//pvalue = portfolioValue[0];
				data = stock50MA;
				return data;
			}
    });	

    return data;	
}

function requestStock200MA(symbol) {
    $.ajax({
		url: 'scripts/portfolio_selection.php?action=get_stock_200_MA&symbol='+symbol, 
		async: false,
			dataType: "json",
			success: function(stock200MA)
			{
				//pvalue = portfolioValue[0];
				data = stock200MA;
				return data;
			}
    });	

    return data;	
}

function requestMarketForwardPerf(day_forward) {
    $.ajax({
		url: 'scripts/market_analysis_engine.php?action=get_market_forward_perf&day_forward='+day_forward, 
		async: false,
			dataType: "json",
			success: function(day_forward_change)
			{
				//pvalue = portfolioValue[0];
				data = day_forward_change;
				return data;
			}
    });	

    return data;	
}

function requestMarketRatio() {
    $.ajax({
		url: 'scripts/market_analysis_engine.php?action=get_market_ratio', 
		async: false,
			dataType: "json",
			success: function(market_ratio)
			{
				//pvalue = portfolioValue[0];
				data = market_ratio;
				return data;
			}
    });	

    return data;	
}

