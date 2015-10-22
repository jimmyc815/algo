$(document).ready(function(){
		$( "#runQuery" ).buttonset();
//		$( "#radio" ).buttonset();
		$("#datepicker_begin").datepicker({ changeMonth: true, changeYear: true});
		$("#datepicker_end").datepicker({ changeMonth: true, changeYear: true});
		$( "#type_select" ).buttonset();
		$("stop_loss_check").buttonset();
		$("#date_range_picker").buttonset();
//		$("buy_signal_check").buttonset();




		var inputSymbol = document.getElementById('inputSymbol').value;
		var begin_date = $("#datepicker_begin").val();
		var start_date = $("#datepicker_begin").val();

		var end_date = $("#datepicker_end").val();
		var freq_date = $("input[name='freq_box']:checked").val();
		var turtleFlag = new Array();

	var FREQ = 30000;
	var repeat = true;
	var refreshStockQuoteId = null;
	
	getTime();
	//executeHP();

 
	$( "#update_freq" ).val(10);

	
	var fn_editSubmit=function(response,postdata){
 			var json=response.responseText; //in my case response text form server is "{sc:true,msg:''}"
 			var result=eval("("+json+")"); //create js object from server reponse
 			return [result.sc,result.msg,null]; 
	}

	//define edit options for navgrid
	var editOptions={
 		top: 50, left: "100", width: 1000  
 		,closeOnEscape: true, afterSubmit: fn_editSubmit
	}
	
	function turtle_s2_system() {
		var inputSymbol = document.getElementById('inputSymbol').value;
		var begin_date = $("#datepicker_begin").val();
		var end_date = $("#datepicker_end").val();
		var stop_loss_type = "ATR_1N";
		var buy_type = "55_HIGH";
	
		getBuyHoldReturn();

		if ($("#check_1N").prop("checked") == true) {
			stop_loss_type = "ATR_1N";
		} else if ($("#check_2N").prop("checked") == true) {
			stop_loss_type = "ATR_2N";
		} else if ($("#check_8PCT").prop("checked") == true) {
			stop_loss_type = "8PCT";
		} else if ($("#check_50MA").prop("checked") == true) {
			stop_loss_type = "50MA";
		} else if ($("#check_200MA").prop("checked") == true) {
			stop_loss_type = "200MA";
		} 
	
		if ($("#buy_55_high").prop("checked") == true) {
			buy_type = "HIGH_55";
		} else if ($("#buy_20_high").prop("checked") == true) {
			buy_type = "HIGH_20";
		} 
	
		$.ajax(
    	{
       		type: "GET",
       		url: "scripts/turtle_system.php?action=turtle_s2&symbol="+inputSymbol+'&begin_date='+begin_date+'&end_date='+end_date+'&buy_signal='+buy_type+'&stop_loss='+stop_loss_type,

       		data: "",
       		dataType: "json",
       		success: function(result)
       		{
            	colD = result.gridModel;            	
            	colN = result.colNames;
            	colM = result.colModel;
            	
            	jQuery("#turtle_result").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: "0"
                	},
                	url: 'SomeUrl/Getdata',
                	datatype: 'jsonstring',
                	mtype: 'POST',
                	datastr : colD,
                	colNames:colN,
                	colModel :colM,
                	pager: jQuery('#pager2'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true
            	})

       		},
       		error: function(x, e)
       		{
            	alert(x.readyState + " "+ x.status +" "+ e.msg);   
       		}


    	});

    	setTimeout(function() {$("#turtle_result").jqGrid('setGridParam',{datatype:'json'}); },500);

	}
		
	function optimize_return()
	{
		var inputSymbol = document.getElementById('inputSymbol').value;
		var begin_date = $("#datepicker_begin").val();
		var end_date = $("#datepicker_end").val();
		var begin_date_js_format;
		var end_date_js_format;
		var time_diff;
		var stop_loss_array = new Array();
		var buy_type_array = new Array();
		var latest_price = 0;
		var portfolio_value = 0;
		var perf = 0;
		var annual_perf = 0;
		var init_portfolio = 100000;
		var best_perf = 1;
		var best_annual_perf = 0;
		var best_stop_loss_type ;
		var best_buy_type;
		
		stop_loss_array = ["ATR_1N", "ATR_2N", "8PCT", "50MA", "200MA"];
		buy_type_array = ["HIGH_55", "HIGH_20"];
		
		var algo_length = stop_loss_array.length * buy_type_array.length;
		var progressCount = 1;


  		$("<tr><th>Buy Type</th><th>Stop Loss Type</th><th>Total Return</th><th>Annualized Return</th>/td></tr>").appendTo("#optimizeTable tbody");

		getBuyHoldReturn();

		// get latst price
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_system.php?action=getRealTimeQuote&symbol="+inputSymbol,
			data: "",
			dataType: "json",
			success: function(realTimeQuote)
			{
				latest_price = realTimeQuote[0].price;
			}
		});

	    $.each(stop_loss_array, function(key, stop_loss_type) {
			$.each(buy_type_array, function(key2, buy_type) {


				// display progress bar
				progressValue = progressCount / algo_length * 100;
				$( "#progressbar" )
					.progressbar({value: progressValue})
					.children('.ui-progressbar-value')
					.html("Loading... " + parseFloat(progressValue).toFixed(0) + '%');
				
				progressCount ++;	

				$.ajax(
				{
					type: "GET",
					url: "scripts/turtle_system.php?action=turtle_s2&symbol="+inputSymbol+'&begin_date='+begin_date+'&end_date='+end_date+'&buy_signal='+buy_type+'&stop_loss='+stop_loss_type,
					data: "",
					dataType: "json",
//					async: true,
					success: function(result)
					 {
						$.ajax(
						{
							type: "GET",
							url: "scripts/turtle_system.php?action=getTurtleData&symbol="+inputSymbol+'&buy_signal='+buy_type+'&stop_loss='+stop_loss_type,
							data: "",
							dataType: "json",
//							async: true,
							success: function(turtleData)
							{
								$.each(turtleData, function(key, val) {
		
									var date  = + new Date(val.trade_date.replace(/-/g, "/"));
		
									var trade_type = val.trade_type;
									var price_paid = val.price_paid;
									var num_shares = val.num_shares;
									var current_pos = val.current_pos;
									portfolio_value = parseFloat(val.cash_balance) + (current_pos * latest_price) ;
								});
								
		
							begin_date_js_format = new Date(begin_date.replace(/-/g, "/"));
							end_date_js_format = new Date(end_date.replace(/-/g, "/"));
							time_diff = end_date_js_format - begin_date_js_format;
							var ONE_DAY = 1000 * 60 * 60 * 24;
							var day_diff = Math.round(time_diff/ONE_DAY);
					
							// calculate performance over the period
							perf = parseFloat(((portfolio_value) - init_portfolio ) / init_portfolio * 100);
							perf = perf.toFixed(2);
							
							// annualize performance
							annual_perf = (Math.pow((1+ (perf / 100 )) , (365 / day_diff)) - 1 ) * 100;
							annual_perf = annual_perf.toFixed(2);	


    $("<tr><td>"+buy_type+"</td><td>"+stop_loss_type+"</td><td>"+perf+"%</td><td>"+annual_perf+"%</td></tr>").appendTo("#optimizeTable tbody");
//    $("<tr><td>New</td><td>New</td><td>New</td><td>New</td></tr>").appendTo("optimizeResult tbody");
					
//alert ("performance " + perf + " best perf " + best_perf + " buy type " + buy_type + " stop loss " + stop_loss_type );
							if (parseFloat(perf) > parseFloat(best_perf))
							{
								best_perf = perf;
								best_annual_perf = annual_perf;
								best_stop_loss_type = stop_loss_type;
								best_buy_type = buy_type;
							}	
	
							}
						});
		
		
					},
		  
					error: function(x, e)
					{
						alert(x.readyState + " "+ x.status +" "+ e.msg);   
					}
				});

	    	});
	    });


//		$( "#progressbar" ).progressbar("destroy");

		$("#turtle_return").val(best_perf+'%');
		$("#turtle_annual_return").val(best_annual_perf+'%');
		$("#best_buy_type").val(best_buy_type);
		$("#best_stop_loss_type").val(best_stop_loss_type);

   		$("<tr><td><FONT COLOR=YELLOW >Best Performer</font></tr>").appendTo("#optimizeTable tbody");
   		$("<tr><td><FONT COLOR=YELLOW>"+best_buy_type+"</td><td><FONT COLOR=YELLOW>"+best_stop_loss_type+"</td><td><FONT COLOR=YELLOW>"+best_perf+"%</td><td><FONT COLOR=YELLOW>"+best_annual_perf+"%</td></font></tr>").appendTo("#optimizeTable tbody");


		stop_loss_array = ["ATR_1N", "ATR_2N", "8PCT", "50MA", "200MA"];
		buy_type_array = ["HIGH_55", "HIGH_20"];

		if (best_buy_type == "HIGH_55") {
			$("#buy_55_high").prop("checked", true);
		} else if (best_buy_type == "HIGH_20") {
			$("#buy_20_high").prop("checked", true);
		}
		
		if (best_stop_loss_type == "ATR_1N") {
			$("#check_1N").prop("checked", true);
		} else if (best_stop_loss_type == "ATR_2N") {
			$("#check_2N").prop("checked", true);
		} else if (best_stop_loss_type == "8PCT") {
			$("#check_8PCT").prop("checked", true);
		} else if (best_stop_loss_type == "50MA") {
			$("#check_50MA").prop("checked", true);
		} else if (best_stop_loss_type == "200MA") {
			$("#check_200MA").prop("checked", true);
		}

   		chartYahooHistoryPrice();

            getTurtlePerformance();


	}
	
	function testAjax($inputSymbol) {
				var current_price;
				
				$.ajax(
    			{
       				type: "GET",
       				url: "scripts/turtle_system.php?action=getRealTimeQuote&symbol="+inputSymbol,
       				data: "",
       				dataType: "json",
       				success: function(realTimeQuote)
       				{
       					current_price = realTimeQuote[0].price;
						alert ('stop loss '+ stop_loss_type + ' buy type ' + buy_type + ' price: ' + realTimeQuote[0].price);
					}
				});	
	
	}

	function executeHP() {
/*		   	var inputSymbol = document.getElementById('inputSymbol').value;
		   	var begin_date = $("#datepicker_begin").val();
		   	var end_date = $("#datepicker_end").val();
			var freq_date = $("input[name='freq_box']:checked").val();
*/
           	jQuery("#algoResult").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	url: 'scripts/service.php?action=getHP&inputSymbol='+inputSymbol+'&begin_date='+begin_date+'&end_date='+end_date+'&freq='+freq_date,
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['trade_date', 'day_high', 'day_low', 'volume', 'close'],
                	colModel:[  
                		{name:'trade_date', width:100, index:'trade_date', editable:false, editoptions:{size:20}},
                		{name:'day_high'},
                		{name:'day_low'},
                		{name:'volume'},
                		{name:'close'}
                	],
                	pager: jQuery('#pager3'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
					editurl: 'scripts/service.php',

      			

	/*			loadComplete: function() {  
        			var getChange;
        			
        			var rowData = jQuery("#algoResult").getDataIDs();  
        			var cn = jQuery("#algoResult").jqGrid('getGridParam','colNames');
        			var cm = jQuery("#algoResult").jqGrid('getGridParam','colModel');
        			var rowid = jQuery("#algoResult").jqGrid('getGridParam', 'records');
        			for (var i = 0; i < rowData.length; i++)   {
            			getChange = jQuery("#algoResult").jqGrid('getCell',rowData[i],'close');

            			if (getChange.charAt(0) == '-') {

                			//for (var j = 0; j < cn.length; j++) {
                    		//var name = cm[4].name;  
                    		//alert ('name: ' + name );
                    		jQuery("#algoResult").jqGrid('setCell',i+1,'change',"",{color: 'red'});
                    		jQuery("#algoResult").jqGrid('setCell',i+1,'pct_change',"",{color: 'red'});

                			//}
            			} else {
                    		jQuery("#algoResult").jqGrid('setCell',i+1,'change',"",{color:'green'}); 
                    		jQuery("#algoResult").jqGrid('setCell',i+1,'pct_change',"",{color: 'green'});

						}
	
        			}
    			},
*/

            	}
            	
            	
            	);
            	
jQuery("#algoResult").jqGrid('navGrid','#pager3',  
	{edit:false, search:false}, //options 
	{}, // edit options 
	{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
	{delData: {
                             symbol: function() {
                                        var sel_id = jQuery("#algoResult").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#algoResult").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
              },
		reloadAfterSubmit:true}, // del options 
	{} // search options 
);

    	$("#algoResult").jqGrid('setGridParam',{datatype:'json'}); 

	}

	function getHistoricalPrice() {
		   	var inputSymbol = document.getElementById('inputSymbol').value;
		   	var begin_date = $("#datepicker_begin").val();
		   	var end_date = $("#datepicker_end").val();
			var freq_date = $("input[name='freq_box']:checked").val();

           	jQuery("#algoResult").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	url: 'scripts/service.php?action=getHPFromDB&inputSymbol='+inputSymbol+'&begin_date='+begin_date+'&end_date='+end_date+'&freq='+freq_date,
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['trade_date','close','daily_change','pct_change','volume','55_day_high','20_day_high','20_day_low','50_ma','200_ma'],
                	colModel:[  
                		{name:'trade_date', width:100, index:'trade_date', editable:false, editoptions:{size:25}},
                		{name:'close', width:70},
                		{name:'daily_change',width:100},
                		{name:'pct_change',width:100},
                		{name:'volume',width:90},
                		{name:'55_day_high',width:100},
                		{name:'20_day_high',width:100},
                		{name:'20_day_low',width:100},
                		{name:'50_ma',width:80},
                		{name:'200_ma',width:80}
                	],
                	pager: jQuery('#pager3'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
					editurl: 'scripts/service.php',

      		

            	}
            	
            	
            	);


    	$("#algoResult").jqGrid('setGridParam',{datatype:'json'}); 

	}
	
	function test_perl() {
	
		
		alert('test');
	}


	function getTimeAjax(){
		$('#updatedTime').load("scripts/time.php");
	}
	
	$("#date_range_6_month").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		begin_date.setMonth(begin_date.getMonth() - 5);
		$("#datepicker_begin").val(begin_date.getMonth()+"/"+begin_date.getDay()+"/"+begin_date.getFullYear());
	});
	
	$("#date_range_1_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		begin_date.setMonth(begin_date.getMonth() - 11);
		$("#datepicker_begin").val(begin_date.getMonth()+"/"+begin_date.getDay()+"/"+begin_date.getFullYear());
	});	

	
	$("#date_range_2_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 2+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDay()+"/"+ year);
	});	
	
	$("#date_range_3_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 3+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDay()+"/"+ year);
	});		
	
	$("#date_range_5_year").click(function() {
		var begin_date = $("#datepicker_end").val();
		
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 5+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDay()+"/"+ year);
	});		
		
	$("#btnStop").click(function() {
		repeat = false;

		$("#updatedTime").html("Updates paused.");

		clearInterval(refreshStockQuoteId);
		refreshStockQuoteId = null;
	});
	
	$("#btnStart").click(function(){
		repeat = true;
		//startAJAXcalls();
		//showStockQuote();
		reload();
		 refreshStockQuoteId = setInterval(function(){ reload(); },$( "#update_freq" ).val()*1000);
	});
	
	$("#btnSubmit").click(function() {
		executeSQL();
	});
	
    $('#historicalPriceSubmit').click(function() {
    		
     		$("#algo_result").GridUnload();
			getHistoricalPrice();
   });
   
    $('#turtleS2Submit').click(function() {
			$("#turtle_result").GridUnload();

   			turtle_s2_system();
			               
   			chartYahooHistoryPrice();

            getTurtlePerformance();


   });
   
   $('#optimizeSubmit').click(function() {

//		$("#progressbar").progressbar({"value":37});
//		document.getElementById("optimizeResult").innerHTML = "";
  		clearTable();
  		$("#buy_hold_return").val("");
		$("#buy_hold_annual_return").val("");
	  optimize_return();
   
   		   
   });
    
    $('#radioSubmit').click(function() {
    			$("#sql_result").empty();
     			$("#mytable").empty();
     			$("#refData").GridUnload();

			executeSQL2();
    });
	
	$('#radioSubmit4').click(function() {
	     	$("#sql_result").empty();
     		$("#mytable").empty();
     		$("#refData").empty();
     		$("#list2").empty();
     		$("#pager2").empty();
     		$("#refData").GridUnload();
	    	$("#refData").setGridHeight(300,true);

			dynamicJGrid2();

	});

    $('#radioStockQuote').click(function() {
    			$("#sql_result").empty();
     			$("#mytable").empty();
     			//$("#refData").GridUnload();
     			$("#algoResult").GridUnload();


			showStockQuote();
    });
    
    
	function getTime(){
        var a_p = "";
        var d = new Date();
        var curr_hour = d.getHours();
        
        (curr_hour < 12) ? a_p = "AM" : a_p = "PM";
        (curr_hour == 0) ? curr_hour = 12 : curr_hour = curr_hour;
        (curr_hour > 12) ? curr_hour = curr_hour - 12 : curr_hour = curr_hour;
        
        var curr_min = d.getMinutes().toString();
        var curr_sec = d.getSeconds().toString();
        
        if (curr_min.length == 1) { curr_min = "0" + curr_min; }
        if (curr_sec.length == 1) { curr_sec = "0" + curr_sec; } 
        
        $('#updatedTime').html(curr_hour + ":" + curr_min + ":" + curr_sec + " " + a_p );
    }

	function showFrequency(){
//		$("#freq").html("Pages refreshes every " + FREQ/1000 + " second(s).");
		$("#freq").html("Pages refreshes every " + $( "#update_freq" ).val()/1000 + " second(s).");
		
		

	}
	
	function reload(rowid, result) {
		$("#algoResult").trigger("reloadGrid");
		getTime();

	}

   	var seriesOptions = [],   	
        yAxisOptions = [],
        seriesCounter = 0,
        names = [],
        colors = Highcharts.getOptions().colors;	
        
	function chartYahooHistoryPrice() {
		names = [];
		inputSymbol = document.getElementById('inputSymbol').value;
		begin_date = $("#datepicker_begin").val();
		end_date = $("#datepicker_end").val();
   		names.push(inputSymbol); 
   		
   		seriesOptions = [];
   		yAxisOptions = [];
   		seriesCounter = 0;

        $.each(names, function(i, name) {

        	var YQL = format_YQL_request(name, begin_date, end_date);
        	$.getJSON(YQL, function(data) {

                var yahooData = parseYahooData(data);

                jQuery.ajaxSetup({async:false});
                turtleFlag = getTurtleData();
                
                ma200data = getMovingAvg(200);
                ma50data = getMovingAvg(50);

                jQuery.ajaxSetup({async:true});

                seriesOptions[i] = {
                    name: name,
                    data: yahooData,
          		    id : 'dataseries'
                };
                seriesOptions.push(turtleFlag);

                seriesOptions[2] = {
                	name: '200 MA',
                	data: ma200data,
                	id : '200_MA'
                };
                
                seriesOptions[3] = {
                	name: '50 MA',
                	data: ma50data,
                	id : '50_MA'
                };

                // As we're loading the data asynchronously, we don't know what order it will arrive. So
                // we keep a counter and create the chart when all the data is loaded.
                seriesCounter++;

                if (seriesCounter == names.length) {
                     createChart();
                     chart.redraw();
                }
            });            
        });	
	}

    function format_YQL_request(symbol, start_date, end_date) {

        // a = Begin month (starting at offset 0 = January)
        // b = Begin day
        // c = Begin year
        // d,e,f = End (same as above) = Defaults to "today" if blank

        // start_date = new Date(2000,1,1);    
        start_date = new Date(start_date || "2011/01/01");
        end_date   = new Date(end_date || Date() );
    
        var URL = 'http://ichart.finance.yahoo.com/table.csv?s='
            + symbol 
            + '%26a=' + start_date.getMonth() 
            + '%26b=' + start_date.getDate() 
            + '%26c=' + start_date.getFullYear() 
            + '%26d=' + end_date.getMonth() 
            + '%26e=' + end_date.getDate() 
            + '%26f=' + end_date.getFullYear() 
            + '%26g=d%26ignore=.csv';

        var YQL = 'http://query.yahooapis.com/v1/public/yql?q='
                  + 'select col0, col4 from csv where url=';

        YQL = YQL + "'" + URL + "'" + '&format=json&callback=?';


        return YQL;

    }

	function parseYahooData(data) {

        var rows = data.query.results.row;
        rows.shift();        // remove first row of headers
    
        var yahooData = new Array();

        $.each(rows, function(key,val) {
            var date  = + new Date(val.col0.replace(/-/g, "/"));
            var price = parseFloat(val.col4); 

            yahooData.push([date, price]);
        });
        
        return yahooData.reverse();
    }
  
    function getMovingAvg(mv)
    {
			inputSymbol = document.getElementById('inputSymbol').value;
		   	var begin_date = $("#datepicker_begin").val();
		   	var end_date = $("#datepicker_end").val();
			var movingAvgData = new Array();

			$.getJSON("scripts/turtle_system.php?action=getMovingAvg&symbol="+inputSymbol+'&begin_date='+begin_date+'&end_date='+end_date+"&mv="+mv+"_MA", function(json) {

			if (json.length > 0) {
												
    				$.each(json, function(key, val) {
    				    var date  = + new Date(val.trade_date.replace(/-/g, "/"));    
    				    var movingAvg = parseFloat(val.MA);
			            movingAvgData.push([date, movingAvg]);
    				});
    			
 				}
 			}); 
//    	return movingAvgData.reverse();
 		return movingAvgData;
   
 }
    function getTurtleData()
    {
			inputSymbol = document.getElementById('inputSymbol').value;
			var turtleMarkers = new Array();
			var returnMarkers = new Array();

			if ($("#check_1N").prop("checked") == true) {
				stop_loss_type = "ATR_1N";
			} else if ($("#check_2N").prop("checked") == true) {
				stop_loss_type = "ATR_2N";
			} else if ($("#check_8PCT").prop("checked") == true) {
				stop_loss_type = "8PCT";
			} else if ($("#check_50MA").prop("checked") == true) {
				stop_loss_type = "50MA";
			} else if ($("#check_200MA").prop("checked") == true) {
				stop_loss_type = "200MA";
			} 
	
			if ($("#buy_55_high").prop("checked") == true) {
				buy_type = "HIGH_55";
			} else if ($("#buy_20_high").prop("checked") == true) {
				buy_type = "HIGH_20";
			} 

//jQuery.ajaxSetup({async:false});

			$.getJSON("scripts/turtle_system.php?action=getTurtleData&symbol="+inputSymbol+'&buy_signal='+buy_type+'&stop_loss='+stop_loss_type, function(json) {

				if (json.length > 0) {
												
    				$.each(json, function(key, val) {

    				    var date  = + new Date(val.trade_date.replace(/-/g, "/"));
    				    var trade_type = val.trade_type;
    				    var price_paid = val.price_paid;
    				    var num_shares = val.num_shares;
    				    var portfolio_value = val.cash_balance + val.current_pos ;
	
						turtleMarkers.push({x:date, title: trade_type, text: trade_type + " " + num_shares + " shares at " + price_paid});

    				});
    			
    			
//			});    
		

				returnMarkers = {
					type : "flags",
					data : turtleMarkers,
            	    onSeries: 'dataseries',
                	shape: 'flag'
				};	

 				}
 			}); 
 			
 //jQuery.ajaxSetup({async:false});

    	return returnMarkers;
 
   
 }
 
 	function getBuyHoldReturn() {
 			inputSymbol = document.getElementById('inputSymbol').value;

		   	var begin_date = $("#datepicker_begin").val();
		   	var end_date = $("#datepicker_end").val();
		   	var begin_price = 0;
		   	var end_price = 0;
		   	
        	jQuery.ajaxSetup({async:false});

			$.getJSON("scripts/turtle_system.php?action=getClosePrice&symbol="+inputSymbol+'&date='+begin_date, function(json) {
				if (json.length > 0) {		
    				$.each(json, function(key, val) {
						begin_price = val.close;
    				});
 				}
 			}); 
 
 			$.getJSON("scripts/turtle_system.php?action=getRealTimeQuote&symbol="+inputSymbol+'&date='+end_date, function(json) {
				if (json.length > 0) {							
    				$.each(json, function(key, val) {
						end_price = val.price;
    				});
 				}
 			});  
 			
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		end_date = new Date(end_date.replace(/-/g, "/"));
		time_diff = end_date - begin_date;
		var ONE_DAY = 1000 * 60 * 60 * 24;
		var day_diff = Math.round(time_diff/ONE_DAY);

		// calculate performance over the period
		perf = parseFloat(((end_price) - begin_price ) / begin_price * 100);
		perf = perf.toFixed(2);
		
		// annualize performance
		annual_perf = (Math.pow((1+ (perf / 100 )) , (365 / day_diff)) - 1 ) * 100;
		annual_perf = annual_perf.toFixed(2);
		
		$("#buy_hold_return").val(perf+'%');
		$("#buy_hold_annual_return").val(annual_perf+'%');
//alert ("begin price " + begin_price + " end price " + end_price + " date diff " + day_diff + "  perf " + perf + " annual " + annual_perf );
/*
		begin_date = $("#datepicker_begin").val();
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		end_date = $("#datepicker_end").val();
		end_date = new Date(end_date.replace(/-/g, "/"));
		time_diff = end_date - begin_date;
		var ONE_DAY = 1000 * 60 * 60 * 24;
		var day_diff = Math.round(time_diff/ONE_DAY);		 	
*/ 	
 	
 	}
 
 	function getTurtlePerformance($inputSymbol, $stop_loss_type, $buy_type) {
 			var latest_price = 0;
        	var portfolio_value = 0;
        	var perf = 0;
        	var stop_loss_type;
        	var buy_type;
        	
			if (stop_loss_type == undefined) {
						if ($("#check_1N").prop("checked") == true) {
							stop_loss_type = "ATR_1N";
						} else if ($("#check_2N").prop("checked") == true) {
							stop_loss_type = "ATR_2N";
						} else if ($("#check_8PCT").prop("checked") == true) {
							stop_loss_type = "8PCT";
						} else if ($("#check_50MA").prop("checked") == true) {
							stop_loss_type = "50MA";
						} else if ($("#check_200MA").prop("checked") == true) {
							stop_loss_type = "200MA";
						} 
			}
			
			if (buy_type == undefined) {
						if ($("#buy_55_high").prop("checked") == true) {
							buy_type = "HIGH_55";
						} else if ($("#buy_20_high").prop("checked") == true) {
							buy_type = "HIGH_20";
						} 
			}
        	
        	jQuery.ajaxSetup({async:false});
			$.getJSON("scripts/turtle_system.php?action=getRealTimeQuote&symbol="+inputSymbol, function(json) {
				if (json.length > 0) {							
    				$.each(json, function(key, val) {
						latest_price = val.price;
    				});
 				}
 			});  	 	
 	        	
 	        jQuery.ajaxSetup({async:false});

			$.getJSON("scripts/turtle_system.php?action=getTurtleData&symbol="+inputSymbol+'&buy_signal='+buy_type+'&stop_loss='+stop_loss_type, function(json) {

				if (json.length > 0) {
												
    				$.each(json, function(key, val) {

    				    var date  = + new Date(val.trade_date.replace(/-/g, "/"));
    				    var trade_type = val.trade_type;
    				    var price_paid = val.price_paid;
    				    var num_shares = val.num_shares;
    				    var current_pos = val.current_pos;
    				    portfolio_value = parseFloat(val.cash_balance) + (current_pos * latest_price) ;

    				});

 				}
 			});  	
		begin_date = $("#datepicker_begin").val();
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		end_date = $("#datepicker_end").val();
		end_date = new Date(end_date.replace(/-/g, "/"));
		time_diff = end_date - begin_date;
		var ONE_DAY = 1000 * 60 * 60 * 24;
		var day_diff = Math.round(time_diff/ONE_DAY);		

		var start_val = 100000;
		
		// calculate performance over the period
		perf = parseFloat(((portfolio_value) - start_val ) / start_val * 100);
		perf = perf.toFixed(2);
		
		// annualize performance
		annual_perf = (Math.pow((1+ (perf / 100 )) , (365 / day_diff)) - 1 ) * 100;
		annual_perf = annual_perf.toFixed(2);

		$("#turtle_return").val(perf+'%');
		$("#turtle_annual_return").val(annual_perf+'%');

 	 			jQuery.ajaxSetup({async:true});

 	}

	// create the chart when all data is loaded
    function createChart() {

    	
        chart = new Highcharts.StockChart({
            chart: {
                renderTo: 'container'
            },

            rangeSelector: {
                selected: 2
                  },
            yAxis: {
				title : {
					text : 'Price'
				}
				,

			// the event marker flags
/*			{
				type : 'flags',
				data : [{
					x : Date.UTC(2011, 3, 25),
					title : 'H',
					text : 'Euro Contained by Channel Resistance'
				}
//				{
//					x : Date.UTC(2011, 3, 28),
//					title : 'G',
//					text : 'EURUSD: Bulls Clear Path to 1.50 Figure'
//				}
				],
				onSeries : 'dataseries',
				shape : 'circlepin',
				width : 16
			}]
*/
/*                labels: {
                    formatter: function() {
                        return (this.value > 0 ? '+' : '') + this.value + '%';
                    }
                },
                plotLines: [{
                    value: 0,
                    width: 2,
                    color: 'silver'
                }]
*/            },
          
 /*           plotOptions: {
                series: {
                    compare: 'percent'
                }
            },
            
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
                yDecimals: 2
            },
   */         
            series: seriesOptions, 
	/*		series : [
				{
				type : 'flags',
				data : [{
					x : Date.UTC(2012, 4, 11),
					title : 'H',
					text : 'Euro Contained by Channel Resistance'
				}]
				}]
    */           

        });
    }
	
	function clearTable()
	{
	 	var tableRef = document.getElementById('optimizeTable');
	 	while ( tableRef.rows.length > 0 )
	 	{
	  		tableRef.deleteRow(0);
	 	}
	}	
	
	function drawChart() {
	$.getJSON('http://www.highcharts.com/samples/data/jsonp.php?filename=usdeur.json&callback=?', function(data) {

		// Create the chart
		window.chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container'
			//	width: 700,
			//	height: 300

			},

			rangeSelector : {
				selected : 1
			},

			title : {
				text : 'USD to EUR exchange rate'
			},
			
			tooltip: {
				style: {
					width: '200px'
				},
				valueDecimals: 4
			},
			
			yAxis : {
				title : {
					text : 'Exchange rate'
				}
			},

			series : [{
				name : 'USD to EUR',
				data : data,
				id : 'dataseries'
			},
			// the event marker flags
			{
				type : 'flags',
				data : [{
					x : Date.UTC(2011, 3, 25),
					title : 'H',
					text : 'Euro Contained by Channel Resistance'
				}, {
					x : Date.UTC(2011, 3, 28),
					title : 'G',
					text : 'EURUSD: Bulls Clear Path to 1.50 Figure'
				}, {
					x : Date.UTC(2011, 4, 4),
					title : 'F',
					text : 'EURUSD: Rate Decision to End Standstill'
				}, {
					x : Date.UTC(2011, 4, 5),
					title : 'E',
					text : 'EURUSD: Enter Short on Channel Break'
				}, {
					x : Date.UTC(2011, 4, 6),
					title : 'D',
					text : 'Forex: U.S. Non-Farm Payrolls Expand 244K, U.S. Dollar Rally Cut Short By Risk Appetite'
				}, {
					x : Date.UTC(2011, 4, 6),
					title : 'C',
					text : 'US Dollar: Is This the Long-Awaited Recovery or a Temporary Bounce?'
				}, {
					x : Date.UTC(2011, 4, 9),
					title : 'B',
					text : 'EURUSD: Bearish Trend Change on Tap?'
				}],
				onSeries : 'dataseries',
				shape : 'circlepin',
				width : 16
			}]
		});
	});	
	
	
	}
	



});


