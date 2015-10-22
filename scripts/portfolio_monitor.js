$(document).ready(function(){
		$( "#runQuery" ).buttonset();
		$("#datepicker_begin").datepicker({ changeMonth: true, changeYear: true});
		$( "#type_select" ).buttonset();
		$("stop_loss_check").buttonset();
		$("#date_range_picker").buttonset();
		$("#btnStart").buttonset();
		$("#btnStop").buttonset();
		$("#portMaxReturn").val(100);

		/*
		$( "#progressbar" ).progressbar({
			value: 37
		});
*/

		$( "#progressbar" ).progressbar("disable");


		var begin_date = $("#datepicker_begin").val();
		

		var freq_date = $("input[name='freq_box']:checked").val();
		var turtleFlag = new Array();

	var FREQ = 30000;
	var repeat = true;
	var refreshStockQuoteId = null;
	var handle;
	var clickToStopUpdate = 0;
	var original_investment = 1000000;
	var portfolio_beta;

	getTime();
	//executeHP();

	var spy=get_real_time_price_change("SPY");
	var today_portfolio_performance=calculate_today_gain_loss();

	var portfolio_value = get_real_time_portfolio_value();
	var total_portfolio_return = (portfolio_value - original_investment) * 100 / original_investment;
	var today_portfolio_return = today_portfolio_performance * 100 / (today_portfolio_performance + portfolio_value);
	
	var portfolio_beta = get_portfolio_beta(2);
		
	var portfolio_risk = get_real_time_portfolio_risk();

	//resetPortfolio();
	display_portfolio_overview();
	display_portfolio2();
	display_transaction();
	//display_transaction_p_and_l();
	display_daily_buy_list();
	chart_overall_return();
	chart_daily_return();

 
 	jQuery('#portfolioOverview').setCaption("Portfolio Overview: ");

 
 	jQuery('#portfolioResult').setCaption("Current Portfolio Value: " + portfolio_value.toFixed(2) + " | Overall Return: " + total_portfolio_return.toFixed(2) + "% | Today Portfolio Gain/Loss: " + today_portfolio_return.toFixed(2) + "% | Today S&P 500: " + spy + "% | Total Risk: " + portfolio_risk + "% | Beta: " + portfolio_beta.toFixed(2));
 	jQuery('#transactionResult').setCaption("All Transactions: ");
 	//jQuery('#transactionPandL').setCaption("Profit and Loss for Transactions: ");
	jQuery('#dailyStockPickResult').setCaption("Today Buy List: ");


	$("#dialog").dialog(
	       {
	        bgiframe: true,
	        autoOpen: false,
	        height: 100,
	        modal: true
	       }
	);

	$( "#update_freq" ).val(180);

	
	var fn_editSubmit=function(response,postdata){
 			var json=response.responseText; //in my case response text form server is "{sc:true,msg:''}"
 			var result=eval("("+json+")"); //create js object from server reponse
 			return [result.sc,result.msg,null]; 
	}
		
	function display_portfolio2() {
//			var textInput = "select symbol, last_price,  shares, profit_loss, overall_return, cost_basis, stop_loss, stop_buy, risk, risk_pct from turtle_portfolio where portfolio_id = 2 order by profit_loss desc";

			var textInput = "select a.symbol, a.last_price, b.last_trade_time, b.daily_change, concat(b.percent_change,' %') as percent_change, shares*b.daily_change as day_profit_loss, shares, profit_loss, concat(overall_return, ' %') as overall_return,  shares*b.last_price as market_value, concat(cast(shares*b.last_price*100/"+portfolio_value.toFixed(2)+" as decimal(5,2)), ' %') as portfolio_weight, cost_basis, stop_loss, stop_buy, risk, risk_pct from turtle_portfolio a, detail_quote b where a.symbol=b.symbol and portfolio_id = 2 order by profit_loss desc";
			
	
           	jQuery("#portfolioResult").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	//url: "scripts/portfolio_selection.php?action=getDynamicSQLResult&txtInputQuery="+textInput,
                	url: "scripts/portfolio_selection.php?action=getPortfolioHolding&txtInputQuery="+textInput,

//portfolio_id | symbol | last_price | shares | cost_basis | overall_return | stop_loss | stop_buy | risk    | risk_pct |

                	//url: "scripts/service.php?action=getStockQuote2",
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['Symbol', 'Last Price', 'Last Update', 'Day Change', '% Change', 'Day Return','Shares', 'Gain/Loss', 'Return %', 'Market Value', 'Weight', 'Cost Basis', 'Stop Loss', 'Stop Buy', 'Risk Dollar', 'Risk %'],
                	colModel:[  
                		//{name:'symbol', width:80, index:'symbol', editable:true, editoptions:{size:20}, align:'center'},
                		{name:'symbol', width:80, formatter: function (cellvalue, options, rowObject) {
	                return '<a href=stock_chart.html?portfolio_id=2&symbol=' + cellvalue + ' onclick="OpenPopup(link); return false">' + cellvalue + '</a>';
	                //return '<a href=stock_chart.html?portfolio_id=2&symbol=' + cellvalue + ' onclick="window.open(link, _blank); window.focus(); return false">' + cellvalue + '</a>';
	                } },                		
                		
                		
                		{name:'last_price', width:80, align:'center'},
                		{name:'last_trade_time', width:90, align:'center'},
                     	{name:'daily_change', width:100, align:'center'},
                     	{name:'percent_change', width:80, align:'center'},
                		{name:'day_profit_loss', width:100, align:'center'},
                		{name:'shares', width:80, align:'center'},
                		{name:'profit_loss', width:80, align:'center'},
                		{name:'overall_return', width:80, align:'center'},
                		{name:'market_value', width:100, align:'center'},
                		{name:'portfolio_weight', width:80, align:'center'},
                		{name:'cost_basis', width:80, align:'center'},
                		{name:'stop_loss', width:80, align:'center'},
                		{name:'stop_buy', width:80, align:'center'},
                		{name:'risk', width:80, align:'center'},
                		{name:'risk_pct', width:80, align:'center'}
                	],
                	pager: jQuery('#pager4'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	//height: "550px",
                	height:'auto',
                	//autowidth: true,
                	
                	viewrecords: true,
					editurl: 'scripts/service.php', 
					
					loadComplete: function() {  
        				var getChange;
        				var getReturn;
        				var getPrice;
        				var getStopLoss;
        				var getStopBuy;
        			
        				var rowData = jQuery("#portfolioResult").getDataIDs();  
        				var cn = jQuery("#portfolioResult").jqGrid('getGridParam','colNames');
        				var cm = jQuery("#portfolioResult").jqGrid('getGridParam','colModel');
        				var rowid = jQuery("#portfolioResult").jqGrid('getGridParam', 'records');
        				for (var i = 0; i < rowData.length; i++)   {
            				getReturn = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'overall_return');
            				getDayReturn = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'day_profit_loss');

            				getChange = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'daily_change');
            				getPctChange = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'percent_change');

            				getStopLoss = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'stop_loss');
            				getStopBuy = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'stop_buy');
            				getPrice = jQuery("#portfolioResult").jqGrid('getCell',rowData[i],'last_price');


            				if (getReturn.charAt(0) == '-') {
                				jQuery("#portfolioResult").jqGrid('setCell',i+1,'overall_return',"",{background:'red', color:'white'});
                				jQuery("#portfolioResult").jqGrid('setCell',i+1,'profit_loss',"",{background:'red', color:'white'});
                			} else {
                    			jQuery("#portfolioResult").jqGrid('setCell',i+1,'overall_return',"",{background:'green', color:'white'}); 
                    			jQuery("#portfolioResult").jqGrid('setCell',i+1,'profit_loss',"",{background:'green', color:'white'});
                    			}
            
            				if (getChange.charAt(0) == '-') {
                 				jQuery("#portfolioResult").jqGrid('setCell',i+1,'daily_change',"",{background:'red', color:'white'});
                 				jQuery("#portfolioResult").jqGrid('setCell',i+1,'percent_change',"",{background:'red', color:'white'});
                 				jQuery("#portfolioResult").jqGrid('setCell',i+1,'day_profit_loss',"",{background:'red', color:'white'});

                			} else {
                    			jQuery("#portfolioResult").jqGrid('setCell',i+1,'daily_change',"",{background:'green', color:'white'});
                    			jQuery("#portfolioResult").jqGrid('setCell',i+1,'percent_change',"",{background:'green', color:'white'});
                    			jQuery("#portfolioResult").jqGrid('setCell',i+1,'day_profit_loss',"",{background:'green', color:'white'});

                    		}
	
                    	
            				if (((getPrice - getStopLoss) / getStopLoss) < 0.01) {
                 				jQuery("#portfolioResult").jqGrid('setCell',i+1,'stop_loss',"",{background:'#F7819F', color:'white'});
                			} 	
            				if (((getStopBuy - getPrice) / getPrice) < 0.01) {
                 				jQuery("#portfolioResult").jqGrid('setCell',i+1,'stop_buy',"",{background:'#04B486', color:'white'});
                			} 	


                    	}
                    }
                    
				});
		
		     jQuery("#portfolioResult").jqGrid('navGrid','#pager4',  
					{edit:false, search:false}, //options 
					{}, // edit options 
					{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
					{delData: {
				                   symbol: function() {
                                        var sel_id = jQuery("#portfolioResult").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#portfolioResult").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
                },
                reloadAfterSubmit:true}, // del options 
                	{} // search options 
               );
               
               $("#portfolioResult").jqGrid('setGridParam',{datatype:'json'}); 
               

	}

	function display_transaction() {
			var textInput = "select symbol, trade_type, trade_date, trade_time, shares, price, gain_loss from turtle_portfolio_transaction where portfolio_id = 2 order by trade_date desc, trade_time asc";
			
			var link = "www.google.com";
			
           	jQuery("#transactionResult").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	//url: "scripts/portfolio_selection.php?action=getDynamicSQLResult&txtInputQuery="+textInput,
                	url: "scripts/portfolio_selection.php?action=getPortfolioHolding&txtInputQuery="+textInput,

//portfolio_id | symbol | last_price | shares | cost_basis | overall_return | stop_loss | stop_buy | risk    | risk_pct |

                	//url: "scripts/service.php?action=getStockQuote2",
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['Symbol', 'Trade Type', 'Trade Date', 'Trade Time', 'Shares', 'Price', 'Gain/Loss'],
                	colModel:[  
                //		{name:'symbol', width:100, index:'symbol', editable:true, editoptions:{size:20}, 
                //		formatter:'showlink', 
               // 		formatoptions:{baseLinkUrl:'stock_chart.html', addParam: '&symbol=SBUX', idName:'myid'} 
                //		}
                		//	formatter: function(cellvalue, options, rowObject) {
	                	//		var stockSymbol = rowObject[0];  //when the grid first load, it used integer as the index of the array, after loadComplete, the grid will use the column name as the index.
                    //return "<a href='stock_chart.html&symbol="+cellvalue+"</a>";
                
                
	                {name:'symbol', width:80, formatter: function (cellvalue, options, rowObject) {
	                return '<a href=stock_chart.html?portfolio_id=2&symbol=' + cellvalue + ' onclick="OpenPopup(link); return false">' + cellvalue + '</a>';
	                } }
                
                ,
                //}}
                		{name:'trade_type', width:80},
                		{name:'trade_date', width:100},
                		{name:'trade_time', width:100},
                		{name:'shares', width:80},
                		{name:'price', width:80},
                		{name:'gain_loss', width:100}                	],
                	pager: jQuery('#pager6'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	//autowidth: true,
                	viewrecords: true,
					editurl: 'scripts/service.php',
					
					loadComplete: function() {  
        				var getChange;
        				var getReturn;
        				var getPrice;
        				var getStopLoss;
        				var getStopBuy;
        			
        				var rowData = jQuery("#transactionResult").getDataIDs();  
        				var cn = jQuery("#transactionResult").jqGrid('getGridParam','colNames');
        				var cm = jQuery("#transactionResult").jqGrid('getGridParam','colModel');
        				var rowid = jQuery("#transactionResult").jqGrid('getGridParam', 'records');
        				for (var i = 0; i < rowData.length; i++)   {
            				getGainLoss = jQuery("#transactionResult").jqGrid('getCell',rowData[i],'gain_loss');
            				            
            				if (getGainLoss.charAt(0) == '-') {
                 				jQuery("#transactionResult").jqGrid('setCell',i+1,'gain_loss',"",{background:'red', color:'white'});
                			} else if (getGainLoss.charAt(0) == '+') {
                    			jQuery("#transactionResult").jqGrid('setCell',i+1,'gain_loss',"",{background:'green', color:'white'});
                    		}



                    	}
                    }
					
					
				});
		
		     jQuery("#transactionResult").jqGrid('navGrid','#pager6',  
					{edit:false, search:false}, //options 
					{}, // edit options 
					{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
					{delData: {
				                   symbol: function() {
                                        var sel_id = jQuery("#transactionResult").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#transactionResult").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
                },
                reloadAfterSubmit:true}, // del options 
                	{} // search options 
               );
               
               $("#transactionResult").jqGrid('setGridParam',{datatype:'json'}); 
               

	}
	
	function display_daily_buy_list() {
			var textInput = "select a.symbol, a.trade_date, rank, buy_price, last_price, concat(percent_change, ' %') as day_percent_change from turtle_daily_buy_list_live a, detail_quote b where a.symbol = b.symbol and portfolio_id = 2 order by rank asc";
						
           	jQuery("#dailyStockPickResult").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	url: "scripts/portfolio_selection.php?action=getPortfolioHolding&txtInputQuery="+textInput,

                	//url: "scripts/service.php?action=getStockQuote2",
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['Symbol', 'Trade Date', 'Rank', 'Buy Price', 'Current Price', 'Day Change %' ],
                	colModel:[  
        
	                {name:'symbol', width:80, formatter: function (cellvalue, options, rowObject) {
	                //return '<a href=stock_chart.html?symbol=' + cellvalue + ' onclick="OpenPopup(link); return false">' + cellvalue + '</a>';
	                return '<a href=stock_chart.html?portfolio_id=2&symbol=' + cellvalue + ' onclick="OpenPopup(link); return false">' + cellvalue + '</a>';
	                } }
                   ,
                		{name:'trade_date', width:100, align:'center'},
                		{name:'rank', width:80, align:'center'}                	,
                		{name:'buy_price', width:100, align:'center'},
                		{name:'last_price', width:100, align:'center'},
                		{name:'day_percent_change', width:100, align:'center'}],
                	
                	pager: jQuery('#pager5'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
					editurl: 'scripts/service.php',
					
					loadComplete: function() {  
        				var getChange;
        				var getReturn;
        				var getPrice;
        				var getStopLoss;
        				var getStopBuy;
        			
        				var rowData = jQuery("#dailyStockPickResult").getDataIDs();  
        				var cn = jQuery("#dailyStockPickResult").jqGrid('getGridParam','colNames');
        				var cm = jQuery("#dailyStockPickResult").jqGrid('getGridParam','colModel');
        				var rowid = jQuery("#dailyStockPickResult").jqGrid('getGridParam', 'records');
        				for (var i = 0; i < rowData.length; i++)   {
            				getBuyPrice = jQuery("#dailyStockPickResult").jqGrid('getCell',rowData[i],'buy_price');
            				getCurrentPrice = jQuery("#dailyStockPickResult").jqGrid('getCell',rowData[i],'last_price');
            				getDayChange = jQuery("#dailyStockPickResult").jqGrid('getCell',rowData[i],'day_percent_change');

            				            
            				if (((getBuyPrice - getCurrentPrice) / getCurrentPrice) < 0.01) {
                 				jQuery("#dailyStockPickResult").jqGrid('setCell',i+1,'last_price',"",{background:'#04B486', color:'white'});
                			} 	
            				if (getDayChange > spy) {
                 				jQuery("#dailyStockPickResult").jqGrid('setCell',i+1,'day_percent_change',"",{background:'green', color:'white'});
                			} 
                    	}
                    }
				});
		
		     jQuery("#dailyStockPickResult").jqGrid('navGrid','#pager5',  
					{edit:false, search:false}, //options 
					{}, // edit options 
					{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
					{delData: {
				                   symbol: function() {
                                        var sel_id = jQuery("#dailyStockPickResult").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#dailyStockPickResult").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
                },
                reloadAfterSubmit:true}, // del options 
                	{} // search options 
               );
               
               $("#dailyStockPickResult").jqGrid('setGridParam',{datatype:'json'}); 
               

	}
	
	
	function calculate_transaction_p_and_l() {	
		$.ajax(
		{
			type: "GET",
            url: "scripts/portfolio_selection.php?action=calculateTransactionPandL",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});		
	
	}		
	
	function get_real_time_portfolio_value() {
		var pvalue;
		
		$.ajax(
		{
			type: "GET",
            url: "scripts/turtle_live_monitor.php?action=get_portfolio_value",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}

		});		
		return pvalue;
	}

	function get_yesterday_portfolio_value() {
		var pvalue;
		var pid=2;
		
		$.ajax(
		{
			type: "GET",
            url: "scripts/turtle_live_monitor.php?action=get_yeseterday_portfolio_value&portfolio_id=".pid,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].portfolio_value;
				
				alert ("pvalue " + pvalue);
			}

		});		
		return pvalue;
	}

	function get_real_time_portfolio_risk() {
		var prisk;
		
		$.ajax(
		{
			type: "GET",
            url: "scripts/turtle_live_monitor.php?action=get_portfolio_risk",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				prisk = portfolioValue[0].prisk;
			}

		});		
		return prisk;
	}

	function display_transaction_p_and_l() {			
	
           	jQuery("#transactionPandL").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	//url: "scripts/portfolio_selection.php?action=getDynamicSQLResult&txtInputQuery="+textInput,
                	url: "scripts/portfolio_selection.php?action=getTransactionPandL&portfolio_id=2",
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['symbol', 'holding_days', 'profit_loss', 'r_multiple'],
                	colModel:[  
                		{name:'symbol', width:100, index:'symbol', editable:true, editoptions:{size:20}},
                		{name:'holding_days'},
                		{name:'profit_loss'},
                		{name:'r_multiple'}                	],
                	pager: jQuery('#pager7'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
					editurl: 'scripts/service.php'
				});
		
		     jQuery("#transactionPandL").jqGrid('navGrid','#pager7',  
					{edit:false, search:false}, //options 
					{}, // edit options 
					{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
					{delData: {
				                   symbol: function() {
                                        var sel_id = jQuery("#transactionPandL").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#transactionPandL").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
                },
                reloadAfterSubmit:true}, // del options 
                	{} // search options 
               );
               
               $("#transactionPandL").jqGrid('setGridParam',{datatype:'json'}); 
               

	}

	function display_portfolio_overview(pid) {			

			//var today_portfolio_performance=calculate_today_gain_loss();
		
			//var portfolio_value = get_real_time_portfolio_value();
			//var total_portfolio_return = (portfolio_value - original_investment) * 100 / original_investment;
			//var today_portfolio_return = today_portfolio_performance * 100 / (today_portfolio_performance + portfolio_value);
			
			var realized_gain_loss = get_realized_gain_loss(2);
			var unrealized_gain_loss = get_unrealized_gain_loss(2);
			//var portfolio_beta = get_portfolio_beta(2);
				
			//var portfolio_risk = get_real_time_portfolio_risk();
			
			


           	jQuery("#portfolioOverview").jqGrid({
                	datatype: 'local',
                	colNames: ['Portfolio ID', 'Portfolio Value', 'Overall Return %', 'Realized Gain/Loss', 'Unrealized Gain/Loss %', 'Beta'],
                	colModel:[  
                		{name:'pid', width:100, index:'pid'},
                		{name:'portfolio_value', width:120, align:'center'},
                		{name:'overall_return', width:100, align:'center'},
                		{name:'realized_gain_loss', width:140, align:'center'},
                		{name:'unrealized_gain_loss', width:150, align:'center'},
                		{name:'beta', width:100, align:'center'}

                		                	],
                	//pager: jQuery('#pager8'),

                	height: "auto"
      				});
		
      		var mydata = [ {pid:'2', portfolio_value:"100", overall_return:"10",realized_gain_loss:"100", unrealized_gain_loss:"2", beta:"1", spy_return:"3"  }];
      		//mydata[0].portfolio_value = portfolio_value.toFixed(2);
      		//mydata[0].overall_return = total_portfolio_return.toFixed(2);
      		//mydata[0].realized_gain_loss = realized_gain_loss;
      		//mydata[0].unrealized_gain_loss = unrealized_gain_loss;
      		//mydata[0].beta = portfolio_beta.toFixed(2);
      		   
      		for(var i=0;i<=mydata.length;i++) jQuery("#portfolioOverview").jqGrid('addRowData',i+1,mydata[i]);
	}

	function update_key_portfolio_stats() {			
		//$("#maxReturn").val(100);
		//$("#minReturn").val(0);	
	
		$.ajax(
		{
			type: "GET",
			url: "scripts/portfolio_selection.php?action=calculatePostSimulationKeyStats" ,
			async: true,
			data: "",
			dataType: "json",
			success: function(statsArray)
			{
		//		$("#maxReturn").val(statsArray[0].max_portfolio_return);
		//		$("#minReturn").val(statsArray[0].min_portfolio_return);
				$("#totalTranCount").val(statsArray[0].tran_count);
				$("#posTranProb").val(statsArray[0].pos_tran_probability);
				$("#negTranProb").val(statsArray[0].neg_tran_probability);


				$("#portMaxReturn").val(statsArray[0].max_portfolio_return);
				$("#portMinReturn").val(statsArray[0].min_portfolio_return);
				//$("#portMinReturn").val(-100);

				
				$("#tranMaxReturn").val(statsArray[0].max_tran_return);
				$("#tranMinReturn").val(statsArray[0].min_tran_return);
				$("#tranMaxHoldingDays").val(statsArray[0].max_tran_holding_days);
				$("#tranMinHoldingDays").val(statsArray[0].min_tran_holding_days);
				$("#tranAvgHoldingDays").val(statsArray[0].avg_tran_holding_days);
				$("#tranMaxRMul").val(statsArray[0].max_tran_r_multiple);
				$("#tranMinRMul").val(statsArray[0].min_tran_r_multiple);
				$("#tranAvgRMul").val(statsArray[0].avg_tran_r_multiple);

				$("#posTranMaxReturn").val(statsArray[0].max_pos_tran_return);
				$("#posTranMinReturn").val(statsArray[0].min_pos_tran_return);
				$("#posTranMaxHoldingDays").val(statsArray[0].max_pos_tran_holding_days);
				$("#posTranMinHoldingDays").val(statsArray[0].min_pos_tran_holding_days);
				$("#posTranAvgHoldingDays").val(statsArray[0].avg_pos_tran_holding_days);
				$("#posTranMaxRMul").val(statsArray[0].max_pos_tran_r_multiple);
				$("#posTranMinRMul").val(statsArray[0].min_pos_tran_r_multiple);
				$("#posTranAvgRMul").val(statsArray[0].avg_pos_tran_r_multiple);				

				$("#negTranMaxReturn").val(statsArray[0].max_neg_tran_return);
				$("#negTranMinReturn").val(statsArray[0].min_neg_tran_return);
				$("#negTranMaxHoldingDays").val(statsArray[0].max_neg_tran_holding_days);
				$("#negTranMinHoldingDays").val(statsArray[0].min_neg_tran_holding_days);
				$("#negTranAvgHoldingDays").val(statsArray[0].avg_neg_tran_holding_days);
				$("#negTranMaxRMul").val(statsArray[0].max_neg_tran_r_multiple);
				$("#negTranMinRMul").val(statsArray[0].min_neg_tran_r_multiple);
				$("#negTranAvgRMul").val(statsArray[0].avg_neg_tran_r_multiple);	
				
			}
		});

               

	}
	
	
	$("#date_range_6_month").click(function() {
		var begin_date = $("#datepicker_end").val();
		begin_date = new Date(begin_date.replace(/-/g, "/"));

		begin_date.setMonth(begin_date.getMonth() - 6);
		$("#datepicker_begin").val(begin_date.getMonth()+"/"+begin_date.getDate()+"/"+begin_date.getFullYear());
	});
	
	$("#date_range_1_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		begin_date.setMonth(begin_date.getMonth() - 11);
		$("#datepicker_begin").val(begin_date.getMonth()+"/"+begin_date.getDate()+"/"+begin_date.getFullYear());
	});	

	
	$("#date_range_2_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 2+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDate()+"/"+ year);
	});	
	
	$("#date_range_3_year").click(function() {
		var begin_date = $("#datepicker_end").val();

		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 3+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDate()+"/"+ year);
	});		
	
	$("#date_range_5_year").click(function() {
		var begin_date = $("#datepicker_end").val();
		
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() - 5+ 1900);
		var mon = begin_date.getMonth() + 1;
		$("#datepicker_begin").val(mon+"/"+begin_date.getDate()+"/"+ year);
	});		
		
	
	function get_portfolio_return(trade_date) {
		var pvalue;
		
		simulate_day_trade(trade_date);
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/portfolio_selection.php?action=get_historical_portfolio_return&date="+trade_date,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].preturn;
			}
		});	
		
		//pvalue = ((pvalue - 1000000) / 1000000)  * 100;
        //   var y = Math.round(Math.random() * 10);
        // return y;
        //pvalue = 20;
        pvalue = Math.round(pvalue*100)/100;
        return pvalue;
		
		
	}
	
	function get_close_price(symbol, trade_date) {
		var price;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/portfolio_selection.php?action=get_close_price&symbol="+symbol+"&date="+trade_date,
			async: false,
			data: "",
			dataType: "json",
			success: function(closePrice)
			{
				price = closePrice[0].price;
			}
		});	
		
        return price;
	}
	
	function get_real_time_price(symbol) {
		var price;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_real_time_price&symbol="+symbol,
			async: false,
			data: "",
			dataType: "json",
			success: function(realtimePrice)
			{
				price = realtimePrice[0].price;
			}
		});	
		
        return price;
	}
	
	function get_real_time_price_change(symbol) {
		var price_change;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_real_time_price_change&symbol="+symbol,
			async: false,
			data: "",
			dataType: "json",
			success: function(realtimePrice)
			{
				price_change = realtimePrice[0].percent_change;
			}
		});	
		
        return price_change;
	}
	
	
	function get_portfolio_beta(pid) {
		var beta;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=calculate_portfolio_beta&portfolio_id="+pid,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioBeta)
			{
				beta = portfolioBeta[0].beta;
			}
		});	
		
        return beta;
	}
	
	function get_realized_gain_loss(pid) {
		var realized_gain_loss;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_realized_gain_loss&portfolio_id="+pid,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolio)
			{
				realized_gain_loss = portfolio[0].realized_gain_loss;
			}
		});	
		
        return realized_gain_loss;
	}
	
	
	function get_all_portfolio_return(start_date) {
		var pvalue;
		var portfolioReturn = new Array(); 
		var data;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/portfolio_selection.php?action=simulate_range_trade&start_date="+start_date,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioReturn)
			{
				//pvalue = portfolioValue[0];
				data = portfolioReturn;
				return data;
			}
		});	
		
        return data;	
	}
		
	
	
	
	function chartPortfolioReturn(thisSeries) {
		var dateList=new Array(2001, 2002, 2003);

        var x = get_x();
        y = get_y();
        thisSeries.addPoint([x, y], true, true);
		
	}
	
	function resetLivePortfolio() {
		$.ajax(
		{
			type: "GET",
            url: "scripts/portfolio_selection.php?action=reset_portfolio&cash=1000000&portfolio_id=2",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});	
		
	}
  
  
   function refreshDisplayPortfolio() {
	   	//$("#portfolioResult").trigger("reloadGrid");
	   	//displayPortfolioHandle = setInterval(function(){ display_portfolio(); }, 1000);
	    // display_portfolio();
   	$("#portfolioResult").trigger("reloadGrid");
		$("#portfolioResult").jqGrid('setGridParam',{datatype:'json'});
	
	//    var grid = $("#portfolioResult");
	//    grid.trigger("reloadGrid");
	//    t = setTimeout("refreshDisplayPortfolio()", 5000);

	  
   }
   
   function calculate_today_gain_loss() {
		var portfolio_change;
				
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_today_portfolio_change&portfolio_id=2",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioChange)
			{
				portfolio_change = portfolioChange[0].daily_change;
			}
		});	
		
        return portfolio_change;
	  
   }
   
   function get_unrealized_gain_loss(pid) {
			var unrealized_gain_loss = 0;
			
			$.ajax(
			{
				type: "GET",
	            url: "scripts/turtle_live_monitor.php?action=get_unrealized_gain_loss&portfolio_id="+pid,
				async: false,
				data: "",
				dataType: "json",
				success: function(portfolioValue)
				{
			
					unrealized_gain_loss = portfolioValue[0].unrealized_gain_loss;

				}
			});	
		   
		   return unrealized_gain_loss;
	   
   }
 
       
    
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
	
/*	function reload(rowid, result) {
		$("#algoResult").trigger("reloadGrid");
		getTime();

	}
*/
	
	function clearTable()
	{
	 	var tableRef = document.getElementById('optimizeTable');
	 	while ( tableRef.rows.length > 0 )
	 	{
	  		tableRef.deleteRow(0);
	 	}
	}	
	
	function chart_overall_return() {		
		var data ;
		var spyData;
		var pid = 2;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_cumulative_benchmark_return&portfolio_id="+pid,
			data: "",
			async: false,
			dataType: "json",
			success: function(spyReturn)
			{
				//pvalue = portfolioValue[0];
				spyData = spyReturn;

			}
		});	
		
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_cumulative_portfolio_return&portfolio_id="+pid,
			data: "",
			dataType: "json",
			success: function(portfolioReturn)
			{
				data = portfolioReturn;
	        	
				window.chart = new Highcharts.StockChart({

	           		chart : {
	                	renderTo : 'container' ,
	                	                type: 'line',
                marginBottom: 10
	                	},
	
		            rangeSelector : {
		                selected : 0
		            },
		
		            title : {
		                text : 'Portfolio VS S&P 500 Cumulative Return'
		            },
		            
		            yAxis: {
		                labels: {
		                    formatter: function() {
		                        return (this.value > 0 ? '+' : '') + this.value + '%';
		                    }
		                },
		                plotLines: [{
		                    value: 0,
		                    width: 1,
		                    color: 'silver'
		                }] 
		                
		            },
		           
		            tooltip: {
		                valueDecimals: 2
		            },		            
		            series : [{
		                name : 'Portfolio Overall Return',
		                data : data
		                } 
		                ,
		                {
		                name : 'SPY Overall Return',
		                data : spyData
		            } 
		            ]

		       });

			}
		});		
	}
	
	function chart_daily_return() {		
		var data ;
		var spyData;
		var pid = 2;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_daily_benchmark_return&portfolio_id="+pid,
			data: "",
			async: false,
			dataType: "json",
			success: function(spyReturn)
			{
				//pvalue = portfolioValue[0];
				spyData = spyReturn;

			}
		});	
		
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/turtle_live_monitor.php?action=get_daily_portfolio_return&portfolio_id="+pid,
			data: "",
			dataType: "json",
			success: function(portfolioReturn)
			{
				data = portfolioReturn;
	        	
				window.chart = new Highcharts.StockChart({

	           		chart : {
	                	renderTo : 'container2' ,
	                	                type: 'line',
                marginBottom: 10
	                	},
	
		            rangeSelector : {
		                selected : 0
		            },
		
		            title : {
		                text : 'Portfolio VS S&P 500 Daily Return'
		            },
		            
		            yAxis: {
		                labels: {
		                    formatter: function() {
		                        return (this.value > 0 ? '+' : '') + this.value + '%';
		                    }
		                },
		                plotLines: [{
		                    value: 0,
		                    width: 1,
		                    color: 'silver'
		                }] 
		                
		            },
		           
		            tooltip: {
		                valueDecimals: 2
		            },		            
		            series : [{
		                name : 'Portfolio Daily Return',
		                data : data
		                } 
		                ,
		                {
		                name : 'SPY Daily Return',
		                data : spyData
		            } 
		            ]

		       });

			}
		});		
	}
	
	function showStockQuote_2() {
           	jQuery("#stockQuote").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	url: 'scripts/service.php?action=getStockQuote2',
                	datatype: 'json',
                	mtype: 'GET',
                	colNames: ['symbol', 'last_trade', 'price', 'pct_change', 'change'],
                	colModel:[  
                		{name:'symbol', width:100, index:'symbol', editable:true, editoptions:{size:20}},
                		{name:'last_trade'},
                		{name:'price'},
                		{name:'pct_change'},
                		{name:'change'}
                	],
                	pager: jQuery('#pager3'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
					editurl: 'scripts/service.php',

				loadComplete: function() {  
        			var getChange;
        			
        			var rowData = jQuery("#stockQuote").getDataIDs();  
        			var cn = jQuery("#stockQuote").jqGrid('getGridParam','colNames');
        			var cm = jQuery("#stockQuote").jqGrid('getGridParam','colModel');
        			var rowid = jQuery("#stockQuote").jqGrid('getGridParam', 'records');
        			for (var i = 0; i < rowData.length; i++)   {
            			getChange = jQuery("#stockQuote").jqGrid('getCell',rowData[i],'change');

            			if (getChange.charAt(0) == '-') {

                			//for (var j = 0; j < cn.length; j++) {
                    		//var name = cm[4].name;  
                    		//alert ('name: ' + name );
                    		jQuery("#stockQuote").jqGrid('setCell',i+1,'change',"",{color: 'red'});
                    		jQuery("#stockQuote").jqGrid('setCell',i+1,'pct_change',"",{color: 'red'});

                			//}
            			} else {
                    		jQuery("#stockQuote").jqGrid('setCell',i+1,'change',"",{color:'green'}); 
                    		jQuery("#stockQuote").jqGrid('setCell',i+1,'pct_change',"",{color: 'green'});

						}
	
        			}
    			},


            	}
            	
            	
            	);
            	
            	jQuery("#stockQuote").jqGrid('navGrid','#pager3',  
					{edit:false, search:false}, //options 
					{}, // edit options 
					{height:140,reloadAfterSubmit:true, closeAfterAdd: true}, // add options 
					{delData: {
				                   symbol: function() {
                                        var sel_id = jQuery("#stockQuote").jqGrid('getGridParam', 'selrow');
                                        var value = jQuery("#stockQuote").jqGrid('getCell', sel_id, 'symbol');
                                        return value;
                                   }
                },
                reloadAfterSubmit:true}, // del options 
                	{} // search options 
               );

	}

	function reload(rowid, result) {
		//$("#stockQuote").trigger("reloadGrid");
		$("#portfolioResult").trigger("reloadGrid");
		getTime();


	}
	
	function OpenPopup (c) {
		window.open(c,
		'window',
		'width=480,height=480,scrollbars=yes,status=yes');
	}

	function getTimeAjax(){
		$('#updatedTime').load("scripts/time.php");
	}
	
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
		getTime();
		reload();
		 refreshStockQuoteId = setInterval(function(){ reload(); },$( "#update_freq" ).val()*1000);
	});
});


