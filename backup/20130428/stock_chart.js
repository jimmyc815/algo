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

	stock_transaction_record = requestStockTransaction(symbol, portfolio_id);
	
	
	stock_50_ma = requestStock50MA(symbol);
	stock_200_ma = requestStock200MA(symbol);


	$.getJSON('scripts/portfolio_selection.php?action=get_stock_price_history_ohlc&portfolio_id='+portfolio_id+'&symbol='+symbol+'&start_date=2005-01-01', function(data) {
		var ohlc = [],
			volume = [],
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
				renderTo : 'container'
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
		            text: 'Volume'
		        },
		        top: 300,
		        height: 100,
		        offset: 0,
		        lineWidth: 2
		    }],
		    
			series : [
				{
					type: 'candlestick',
					name : symbol,
					data : ohlc,
					tooltip: {
						valueDecimals: 2
					}
					
					, dataGrouping: {
						//units: groupingUnits
					}
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
				,{type : 'flags',
		        shape: 'squarepin',

				data : stock_transaction_record
				},
				{
				name : 'Swing Points',
				type : 'flags',
		        shape: 'circle',

				data : swing_points
				}, 
				{
			        type: 'column',
			        name: 'Volume',
			        data: volume,
			        yAxis: 1,
			        dataGrouping: {
						//units: groupingUnits
						}
			        }
					
				
			]
		});
	});
	
	
	$.getJSON('scripts/portfolio_selection.php?action=get_stock_ranking_history&portfolio_id='+portfolio_id+'&symbol='+symbol+'&start_date=2005-01-01', function(data) {
		// Create the chart
		window.chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container2'
			},

			rangeSelector : {
				selected : 2
			},

			title : {
				text : symbol+' Performance Ranking VS SP 500'
			},
		    yAxis:{
			    min: 0,
			    max: 550,
			    tickInterval: 100			    
			
			
			},			
			series : [
				{
					name : symbol,
					data : data,
					tooltip: {
						valueDecimals: 0
					}
				}
			]
		});
	});
	
	
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
	
    $.ajax({
//		url: 'scripts/trend_setup.php?action=get_swing_points&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
		url: 'scripts/portfolio_selection.php?action=get_swing_points&start_date=2005-01-01&portfolio_id='+pid+'&symbol='+symbol, 
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


