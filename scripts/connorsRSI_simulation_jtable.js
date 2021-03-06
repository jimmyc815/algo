$(document).ready(function(){
		$( "#runQuery" ).buttonset();
		$("#datepicker_begin").datepicker({ changeMonth: true, changeYear: true});
		$( "#type_select" ).buttonset();
		$("stop_loss_check").buttonset();
		$("#date_range_picker").buttonset();
		$("#portMaxReturn").val(100);

		/*
		$( "#progressbar" ).progressbar({
			value: 37
		});
*/

 		var enterCRSI = 45;
 		var enterRange = 75;
 		var enterLimit = 1;
 		var exitCRSI = 50;
 		
 		$("#enterCRSI").val(enterCRSI);
 		$("#enterRange").val(enterRange);
 		$("#enterLimit").val(enterLimit);
  		$("#exitCRSI").val(exitCRSI);

		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();
		today = mm+'/'+dd+'/'+yyyy;		
		//var lastyear = today.getFullYear()-1;
		var lastyear = yyyy - 1;
		var yearago = mm+'/'+dd+'/'+lastyear;


		$( "#progressbar" ).progressbar("disable");

		$("#datepicker_begin").val("3/2/2009");
		$("#datepicker_end").val("3/2/2010");
		
		$("#datepicker_end").val(today);
		//$("#datepicker_begin").val(yearago);
		$("#datepicker_begin").val("1/1/2015");

		var begin_date = $("#datepicker_begin").val();
		
		$('#predefined_YTD').click(function(e) {
			$("#datepicker_begin").val("1/1/2015");
		});				
		
		$('#predefined_3_2009_to_3_2010').click(function(e) {
			$("#datepicker_begin").val("3/2/2009");
			$("#datepicker_end").val("3/2/2010");
		});				

		$('#predefine_past_1_year').click(function(e) {
			$("#datepicker_begin").val("3/2/2012");
			$("#datepicker_end").val("3/2/2013");
		});				
	
		$('#predefined_past_2_years').click(function(e) {
			$("#datepicker_begin").val("3/2/2011");
			$("#datepicker_end").val("3/2/2013");
		});				

		$('#predefined_past_3_years').click(function(e) {
			$("#datepicker_begin").val("3/2/2010");
			$("#datepicker_end").val("3/2/2013");
		});				

		$('#predefined_2014').click(function(e) {
			$("#datepicker_begin").val("1/1/2014");
			$("#datepicker_end").val("12/31/2014");
		});				
		$('#predefined_2013').click(function(e) {
			$("#datepicker_begin").val("1/1/2013");
			$("#datepicker_end").val("12/31/2013");
		});				
		$('#predefined_2012').click(function(e) {
			$("#datepicker_begin").val("1/1/2012");
			$("#datepicker_end").val("12/31/2012");
		});				
		$('#predefined_2011').click(function(e) {
			$("#datepicker_begin").val("1/1/2011");
			$("#datepicker_end").val("12/31/2011");
		});				
		$('#predefined_2010').click(function(e) {
			$("#datepicker_begin").val("1/1/2010");
			$("#datepicker_end").val("12/31/2010");
		});				
		$('#predefined_2009').click(function(e) {
			$("#datepicker_begin").val("1/1/2009");
			$("#datepicker_end").val("12/31/2009");
		});				
		$('#predefined_2008').click(function(e) {
			$("#datepicker_begin").val("1/1/2008");
			$("#datepicker_end").val("12/31/2008");
		});				
		$('#predefined_2007').click(function(e) {
			$("#datepicker_begin").val("1/1/2007");
			$("#datepicker_end").val("12/31/2007");
		});				
	

		sort_by = "crsi asc";
		$('#sort_by_crsi_desc').click(function(e) {
			//$('#sort_by_crsi_asc').prop('checked', false);
			sort_by = "crsi desc";
		});	

		$('#sort_by_crsi_asc').click(function(e) {
			//$('#sort_by_crsi_desc').prop('checked', false);
			sort_by = "crsi asc";
		});	

		
	//	var start_date = $("#datepicker_begin").val();
		// get ADX Filte value
		var adxFilter = "Off";
		// get variables selected on the page
		$('#adx_filter').click(function(e) {
			adxFilter = "On";
		});
		
		// get breakout signal type, default to 55_DAY_HIGH 
		var breakoutSignal = "55_DAY_HIGH";
		// get variables selected on the page
		$('#buy_55_high').click(function(e) {
			breakoutSignal = "55_DAY_HIGH";
		});		
		$('#buy_20_high').click(function(e) {
			breakoutSignal = "20_DAY_HIGH";
		});		
		
		var breakoutOrderBy = "pct_change";
		// get variables selected on the page
		$('#order_by_pct_change').click(function(e) {
			breakoutOrderBy = "pct_change";
		});		
		$('#order_by_rel_vol').click(function(e) {
			breakoutOrderBy = "relative_avg_vol";
		});		
		
		// get order by preference when choosing break out stocks
		var valueOrderByPctChange = $("#valueOrderByPctChange").val();	
		var	valueOrderByRelVol = $("#valueOrderByRelVol").val();	
		var	valueOrderByVsSpy = $("#valueOrderByVsSpy").val();
		
		$('#valueOrderByPctChange').change(function(e) {	
			valueOrderByPctChange = $("#valueOrderByPctChange").val();
		});		
		$('#valueOrderByRelVol').click(function(e) {	
			valueOrderByRelVol = $("#valueOrderByRelVol").val();
		});		
		$('#valueOrderByVsSpy').click(function(e) {	
			valueOrderByVsSpy = $("#valueOrderByVsSpy").val();
		});		
		
		$('#order_by_split').click(function(e) {
			$("#valueOrderByPctChange").val(33) ;	
			valueOrderByPctChange = $("#valueOrderByPctChange").val();

			$("#valueOrderByRelVol").val(33);
			valueOrderByRelVol = $("#valueOrderByRelVol").val();

			$("#valueOrderByVsSpy").val(34); 
			valueOrderByVsSpy = $("#valueOrderByVsSpy").val();

		});			
		
 		$('#enterCRSI').change(function(e) {	
 			enterCRSI = $("#enterCRSI").val();
 		});		
 		$('#enterRange').change(function(e) {	
 			enterRange = $("#enterRange").val();
 		});		
 		$('#enterLimit').change(function(e) {	
 			enterLimit = $("#enterLimit").val();
 		});		
 		$('#exitCRSI').change(function(e) {	
 			exitCRSI = $("#exitCRSI").val();
 		});			

		//risk tolerance
		var maxRisk = 7;
		var riskFactor = 0.45;
		var riskSD = 1;
		$("#maxRisk").val(maxRisk);
		$("#riskFactor").val(riskFactor);
		$("#riskSD").val(riskSD);
		
		$('#maxRisk').change(function(e) {	
			maxRisk = $("#maxRisk").val();
		});		
		$('#riskFactor').change(function(e) {	
			riskFactor = $("#riskFactor").val();
		});		
		$('#riskSD').change(function(e) {	
			riskSD = $("#riskSD").val();		
		});		

		var cash = 1000000;
		var commission = 7;
		var skipFactor = 0.15;

		$("#cash").val(cash);
		$("#commission").val(commission);
		$("#skipfactor").val(skipFactor);

		$('#cash').change(function(e) {	
			cash = $("#cash").val();
		});		

		$('#commission').change(function(e) {	
			commission = $("#commission").val();
		});		

		$('#skipfactor').change(function(e) {	
			skipFactor = $("#skipfactor").val() ;
		});				

		//$('#simulateSubmit').click(function(e) {
   		//	startSimulation();
   		//});

		var freq_date = $("input[name='freq_box']:checked").val();
		var turtleFlag = new Array();

	var FREQ = 30000;
	var repeat = true;
	var refreshStockQuoteId = null;
	var handle;
	var clickToStopUpdate = 0;

	getTime();
	//executeHP();

	display_portfolio2();
	display_transaction();
	display_transaction_p_and_l();
	display_daily_buy_list();

 
 	//jQuery('#transactionResult').setCaption("All Transactions (Limit 100 entries): ");
 	//jQuery('#transactionPandL').setCaption("Profit and Loss for Transactions (Limit 100 entries): ");
	
  	$("#dialog").dialog(
	       {
	        bgiframe: true,
	        autoOpen: false,
	        height: 100,
	        modal: true
	       }
	);

	$( "#update_freq" ).val(10);

	
	var fn_editSubmit=function(response,postdata){
 			var json=response.responseText; //in my case response text form server is "{sc:true,msg:''}"
 			var result=eval("("+json+")"); //create js object from server reponse
 			return [result.sc,result.msg,null]; 
	}
	
		var textInput = "select symbol, trade_date, round(buy_price, 2) as buy_price from crsi_daily_buy_list1 where portfolio_id = 1 ";

        $('#dailyBuyListContainer').jtable({
            title: 'Daily Buy List',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date ASC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
                //createAction: '/GettingStarted/CreatePerson',
                //updateAction: '/GettingStarted/UpdatePerson',
                //deleteAction: '/GettingStarted/DeletePerson'
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,

                    title: 'Symbol',
                    width: '40%'
                },
                trade_date: {
                    title: 'Added date',
                    width: '30%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_price: {
                    title: 'Buy price',
                    width: '20%'
                }
            }


        });
		$('#dailyBuyListContainer').jtable('option', 'pageSize', 10);
		$('#dailyBuyListContainer').jtable('load');
		
		//var textInput = "select symbol, trade_date, round(buy_price, 2) as buy_price from crsi_daily_buy_list1 where portfolio_id = 1 ";
		var textInput = "select symbol, last_price, shares, cost_basis, risk, risk_pct from portfolio1 where portfolio_id = 1 ";


        $('#portfolioContainer').jtable({
            title: 'Current Portfolio Holding',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'symbol ASC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
                //createAction: '/GettingStarted/CreatePerson',
                //updateAction: '/GettingStarted/UpdatePerson',
                //deleteAction: '/GettingStarted/DeletePerson'
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,
                    title: 'Symbol',
                    width: '25%'
                },
                last_price: {
                    title: 'Last price',
                    width: '15%'
                },
                shares: {
                    title: 'Shares',
                    width: '15%'
                },
                cost_basis: {
                    title: 'Cost Basis',
                    width: '15%'
                },
                risk: {
                    title: 'Risk Dollar',
                    width: '15%'
                },
                risk_pct: {
                    title: 'Risk Pct',
                    width: '15%'
                }
            }

        });
		$('#portfolioContainer').jtable('option', 'pageSize', 10);
		$('#portfolioContainer').jtable('load');
		
		var textInput = "select xid, symbol, round(PnL*100/(buy_price * buy_shares), 2) as PnL, buy_date, buy_shares, round(buy_price, 2) as buy_price, sell_date, sell_shares, round(sell_price, 2) as sell_price from transactions1 where portfolio_id = 1 ";


        $('#pnlContainer').jtable({
            title: 'Detail Profit and Loss for Each Transaction',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'sell_date DESC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                xid: {
	                key: true,
                    title: 'xid',
                    width: '5%'
                },
                symbol: {
                    title: 'Symbol',
                    width: '10%'
                },
                PnL: {
                    title: 'Profit %',
                    width: '15%'
                },
                buy_date: {
                    title: 'Buy Date',
                    width: '15%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_shares: {
                    title: 'Buy Shares',
                    width: '10%'
                },
                buy_price: {
                    title: 'Buy Price',
                    width: '10%'
                },
                sell_date: {
                    title: 'Sell Date',
                    width: '15%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                sell_shares: {
                    title: 'Sell Shares',
                    width: '10%'
                },
                sell_price: {
                    title: 'Sell Price',
                    width: '10%'
                }
            }


        });
		$('#pnlContainer').jtable('option', 'pageSize', 10);
		$('#pnlContainer').jtable('load');	
	

		var textInput = "select symbol, trade_type, trade_date, shares, round(price, 2) as price, risk, risk_pct from turtle_portfolio_transaction1 where portfolio_id = 1 ";


        $('#transactionsContainer').jtable({
            title: 'List of All Buy and Sell Transactions',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date DESC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,
                    title: 'Symbol',
                    width: '15%'
                },
                trade_type: {
                    title: 'Trade Type',
                    width: '15%'
                },
                trade_date: {
                    title: 'Trade Date',
                    width: '12%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                shares: {
                    title: 'Trade Shares',
                    width: '10%'
                },
                price: {
                    title: 'Trade Price',
                    width: '10%'
                },
                risk: {
                    title: 'Risk Dollar',
                    width: '15%',
                },
                risk_pct: {
                    title: 'Risk Pct',
                    width: '15%'
                }
            }

        });
		$('#transactionsContainer').jtable('option', 'pageSize', 10);
		$('#transactionsContainer').jtable('load');	

		var textInput = "select id, trade_date, return_dollar, return_pct, portfolio_value from crsi_portfolio_performance where portfolio_id = 1 ";


        $('#historyContainer').jtable({
            title: 'Historical Portfolio Value',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date DESC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                id: {
					type: 'hidden',
	                key: true
                },
                trade_date: {
                    title: 'Trade Date',
                    width: '12%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                return_dollar: {
                    title: 'Portfolio Return in Dollars',
                    width: '20%'
                },
                return_pct: {
                    title: 'Portfolio Return in %',
                    width: '20%'
                },
                portfolio_value: {
                    title: 'Portfolio Value',
                    width: '20%'
                }
            }

        });
		$('#historyContainer').jtable('option', 'pageSize', 10);
		$('#historyContainer').jtable('load');	
	
	function dynamicJGrid2() {
		var textInput = "select distinct symbol from stock_list";
	
		$.ajax(
    	{
       		type: "GET",
       		url: "scripts/connorsRSI_strat.php?action=getDynamicSQLResult&txtInputQuery="+textInput,

       		data: "",
       		dataType: "json",
       		success: function(result)
       		{
            	colD = result.gridModel;            	
            	colN = result.colNames;
            	colM = result.colModel;
            	
            	jQuery("#refData").jqGrid({
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
    	
    	setTimeout(function() {$("#refData").jqGrid('setGridParam',{datatype:'json'}); },500);

	}
	
	function display_portfolio2() {
		var textInput = "select symbol, trade_date, round(buy_price, 2) as buy_price from crsi_daily_buy_list1 where portfolio_id = 1 ";

        $('#dailyBuyListContainer').jtable({
            title: 'Daily Buy List',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date ASC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
                //createAction: '/GettingStarted/CreatePerson',
                //updateAction: '/GettingStarted/UpdatePerson',
                //deleteAction: '/GettingStarted/DeletePerson'
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,

                    title: 'Symbol',
                    width: '40%'
                },
                trade_date: {
                    title: 'Added date',
                    width: '30%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_price: {
                    title: 'Buy price',
                    width: '20%'
                }
            }


        });
		$('#dailyBuyListContainer').jtable('option', 'pageSize', 10);
		$('#dailyBuyListContainer').jtable('load');
		
		



	}

	function display_transaction() {
		var textInput = "select symbol, trade_type, trade_date, shares, round(price, 2) as price, risk, risk_pct from turtle_portfolio_transaction1 where portfolio_id = 1 ";


        $('#transactionsContainer').jtable({
            title: 'List of All Buy and Sell Transactions',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date DESC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,
                    title: 'Symbol',
                    width: '15%'
                },
                trade_type: {
                    title: 'Trade Type',
                    width: '15%'
                },
                trade_date: {
                    title: 'Trade Date',
                    width: '12%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                shares: {
                    title: 'Trade Shares',
                    width: '10%'
                },
                price: {
                    title: 'Trade Price',
                    width: '10%'
                },
                risk: {
                    title: 'Risk Dollar',
                    width: '15%',
                },
                risk_pct: {
                    title: 'Risk Pct',
                    width: '15%'
                }
            }

        });
		$('#transactionsContainer').jtable('option', 'pageSize', 10);
		$('#transactionsContainer').jtable('load');			
		

	}
	
	function display_daily_buy_list() {
		var textInput = "select symbol, trade_date, round(buy_price, 2) as buy_price from crsi_daily_buy_list1 where portfolio_id = 1 ";

        $('#dailyBuyListContainer').jtable({
            title: 'Daily Buy List',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'trade_date ASC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
                //createAction: '/GettingStarted/CreatePerson',
                //updateAction: '/GettingStarted/UpdatePerson',
                //deleteAction: '/GettingStarted/DeletePerson'
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                symbol: {
	                key: true,

                    title: 'Symbol',
                    width: '40%'
                },
                trade_date: {
                    title: 'Added date',
                    width: '30%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_price: {
                    title: 'Buy price',
                    width: '20%'
                }
            }


        });
		$('#dailyBuyListContainer').jtable('option', 'pageSize', 10);
		$('#dailyBuyListContainer').jtable('load');		
		

               

	}


	function calculate_transaction_p_and_l() {	
		$.ajax(
		{
			type: "GET",
            url: "scripts/connorsRSI_strat.php?action=calculateTransactionPandL",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});		
	
	}		

	function get_final_simulate_portfolio_value() {
		$.ajax(
		{
			type: "GET",
            url: "scripts/connorsRSI_strat.php?action=get_historical_turtle_portfolio_value&date="+endingDate,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});		
	
		
	}

	
	function display_transaction_p_and_l() {		
		var textInput = "select xid, symbol, round(PnL*100/(buy_price * buy_shares), 2) as PnL, buy_date, buy_shares, buy_price, sell_date, sell_shares, sell_price from transactions1 where portfolio_id = 1 ";


        $('#pnlContainer').jtable({
            title: 'Detail Profit and Loss for Each Transaction',
            paging: true, //Enable paging
            pageSize: 10, //Set page size (default: 10)
            sorting: true, //Enable sorting
            defaultSorting: 'sell_date DESC', //Set default sorting
			toolbar: {
			    items: [{
			        icon: 'jtable/images/excel.png',
			        text: 'Export to Excel',
			        click: function () {
						window.location = 'scripts/connorsRSI_strat.php?action=export_to_csv&txtInputQuery='+textInput;
			        }
			    }]
			},
            actions: {
                listAction: "scripts/connorsRSI_strat.php?action=jtableList&txtInputQuery="+textInput
            },
            fields: {
                //PersonId: {
                //    key: true,
                //    list: false
                //},
                xid: {
	                key: true,
                    title: 'xid',
                    width: '5%'
                },
                symbol: {
                    title: 'Symbol',
                    width: '10%'
                },
                PnL: {
                    title: 'Profit %',
                    width: '15%'
                },
                buy_date: {
                    title: 'Buy Date',
                    width: '15%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                buy_shares: {
                    title: 'Buy Shares',
                    width: '10%'
                },
                buy_price: {
                    title: 'Buy Price',
                    width: '10%'
                },
                sell_date: {
                    title: 'Sell Date',
                    width: '15%',
                    type: 'date',
                    create: false,
                    edit: false
                },
                sell_shares: {
                    title: 'Sell Shares',
                    width: '10%'
                },
                sell_price: {
                    title: 'Sell Price',
                    width: '10%'
                }
            }


        });
		$('#pnlContainer').jtable('option', 'pageSize', 10);
		$('#pnlContainer').jtable('load');	
			
               

	}

	function update_key_portfolio_stats() {			
		//$("#maxReturn").val(100);
		//$("#minReturn").val(0);	
	
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=calculatePostSimulationKeyStats" ,
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

	function get_current_turtle_portfolio() {
		var textInput = "select * from turtle_portfolio";
	
		$.ajax(
    	{
       		type: "GET",
       		url: "scripts/connorsRSI_strat.php?action=getCurrentTurtlePortfolio&txtInputQuery="+textInput,

       		data: "",
       		dataType: "json",
       		success: function(result)
       		{
            	colD = result.gridModel;            	
            	colN = result.colNames;
            	colM = result.colModel;
            	
            	jQuery("#refData").jqGrid({
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
    	
    	//setTimeout(function() {$("#refData").jqGrid('setGridParam',{datatype:'json'}); },500);

	}
	
	//define edit options for navgrid
	var editOptions={
 		top: 50, left: "100", width: 1000  
 		,closeOnEscape: true, afterSubmit: fn_editSubmit
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
		
	function get_x() {
		var begin_date = $("#datepicker_end").val();


	
           var x = (new Date()).getTime();
           return x;
           
         

		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=get_valid_trade_dates&start_date=" + begin_date ,
			async: true,
			data: "",
			dataType: "json",
			success: function(dateArray)
			{
				pvalue = dateArray[0].pvalue;
			}
		});	
	}
	
	function get_range_date() {
		var begin_date = $("#datepicker_begin").val();
		var realDate;
		
		begin_date = new Date(begin_date.replace(/-/g, "/"));
		var year = (begin_date.getYear() + 1900 );
		var mon = begin_date.getMonth() + 1;
		
		begin_date = year+ "-" + mon + "-" + begin_date.getDate();

		var dateList=new Array();

        jQuery.ajaxSetup({async:false});
        
		$.getJSON("scripts/connorsRSI_strat.php?action=get_valid_trade_dates&start_date=" + begin_date, function(json) {	
				
			if (json.length > 0) {
    				$.each(json, function(key, val) {
    					//realDate = new Date(val.trade_date.replace(/-/g, "/")).getTime();
    					//dateList.push(val.trade_date);
    					//dateList.push(realDate);				
    					dateList.push(val.trade_date);				

    				});
       		}
		});
		
		return dateList;	
		
	}
	
	
	
	function get_y() {
		var pvalue;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=get_current_portfolio_return",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});	
		
		//pvalue = ((pvalue - 1000000) / 1000000)  * 100;
        //   var y = Math.round(Math.random() * 10);
        // return y;
        pvalue = 20;
        
        return pvalue;	
	}
	
	function simulate_day_trade(trade_date){
	
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=simulate_1_day_trade&date="+trade_date,
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{}
		});			//
		
	}
	
	function get_portfolio_return(trade_date) {
		var pvalue;
		
		simulate_day_trade(trade_date);
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=get_historical_portfolio_return&date="+trade_date,
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
			url: "scripts/connorsRSI_strat_2.php?action=get_close_price&symbol="+symbol+"&date="+trade_date,
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
	
	function get_all_portfolio_return(start_date) {
		var pvalue;
		var portfolioReturn = new Array(); 
		var data;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=simulate_range_trade&start_date="+start_date,
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
	
	function resetPortfolio() {
		$.ajax(
		{
			type: "GET",
            url: "scripts/connorsRSI_strat.php?action=reset_portfolio&cash=1000000&portfolio_id=1",
			async: false,
			data: "",
			dataType: "json",
			success: function(portfolioValue)
			{
				pvalue = portfolioValue[0].pvalue;
			}
		});	
		
	}
		
	function startSimulation() {
		// reset portfolio value
		resetPortfolio();
		
		var testList=new Array(10, 20, 30, 40, 50, 60, 70);

		var dateList= get_range_date();
		var startingDate = new Date($("#datepicker_begin").val());
		var startingUTCDate = new Date(startingDate.getUTCFullYear(), startingDate.getUTCMonth(), startingDate.getUTCDate());
		startingDate = ($("#datepicker_begin").val());
		var previousDate = new Date();
		var maxReturn = -100;
		var minReturn = 100;
		
		var spyInitial = 0;
		
		
		startingDate = new Date(startingDate.replace(/-/g, "/"));
		var year = (startingDate.getYear() + 1900 );
		var mon = startingDate.getMonth() + 1;
		
		startingDate = year+ "-" + mon + "-" + startingDate.getDate();
		// get initial benchmark index value
		// in this case, get SPY
				
		spyInitial = get_close_price("SPY", startingDate); 
		window.alert("spy initial: ".spyInitial);

    	Highcharts.setOptions({
      	  global : {
            useUTC : false
            }
            });
    
            // Create the chart
            window.chart = new Highcharts.StockChart({
	            	chart : {
		            renderTo : 'container',
		        	type: 'line',

		            events : {
			        load : function() {

                    // set up the updating of the chart each second
                    var series = this.series[0];
                    var seriesSPY = this.series[1];
                   		
                                       		
                    handle = setInterval(function() {

                 		trade_date = dateList.pop();
                 		x = new Date(trade_date.replace(/-/g, "/")).getTime();
                 		//y = get_y();
							//series.addPoint([x, y], true, true);

                 		if (dateList.length > 0) {
							//y = testList.pop();         
							//y =    Math.round(Math.random() * 100)	;  
							y = get_portfolio_return(trade_date);    
							spyValue = get_close_price("SPY", trade_date);
							spyReturn = Math.round(((spyValue - spyInitial) / spyInitial * 100)*100)/100;  
							// record max and min return during the simulation
							if ( y > maxReturn) {
								maxReturn = y;
							}
							
							if ( y < minReturn) {
								minReturn = y;
							}
							
							$("#maxReturn").val(maxReturn);
							$("#minReturn").val(minReturn);
							
							series.addPoint([x, y], true, true);
							
							seriesSPY.addPoint([x, spyReturn], true, true);
							
							$("#portfolioResult").trigger("reloadGrid");
							jQuery('#portfolioResult').setCaption("Portfolio Holding as of Date: " + trade_date);

							$("#transactionResult").trigger("reloadGrid");
							jQuery('#transactionResult').setCaption("Transaction Date: " + trade_date);


						}
						else {
							clearInterval(handle);
						}

						if (clickToStopUpdate.val > 0) {
							clearInterval(handle);
						}

                    }, 1000) ;
                    
                    
/*                    setIntervalfunction() {
                 		var x = get_x();
                 		y = get_y();
                        series.addPoint([x, y], true, true);
                    }, 1000);
*/                   
                    }
                    }
            },
            
        rangeSelector: {
            buttons: [{
                count: 1,
                type: 'day',
                text: '1D'
            },{
                count: 5,
                type: 'day',
                text: '5D'
            }, {
                count: 10,
                type: 'day',
                text: '10D'
            }, {
                type: 'all',
                text: 'All'
            }],
            inputEnabled: false,
            selected: 1
        },
        title : {
            text : 'Turtle System Portfolio Return'
        },
		xAxis: {
			type: 'datetime',
			minRange: 14 * 24 * 3600000, // fourteen days
			max: null,
			title: {
				text: "Trade Date"
			}
		},
		yAxis: {
			title: {
				text: 'Portfolio Return'
			},
			min: -20,
			max: 20,
			startOnTick: false,
			showFirstLabel: false
		},
        
        exporting: {
            enabled: false
        },
		plotOptions: {
			line: {
				dataLabels: {
					enabled: true
				}
//				enableMouseTracking: false
			}
		},

        series : [{
            name : 'Portfolio Return',
            tickInterval: 24 * 3600 * 1000,
			lineWidth: 4,
			marker: {
				radius: 4
			},
			symbol: 'square',
			data : (function() {
                var data = [], i; //, time = (new Date()).getTime(), i;

                for( i = -100; i <= 0; i++) {
                    data.push([
        				startingUTCDate.getTime() + (i * (24 * 60 * 60 * 1000)),
        				0
                    ]);
                }

				//data.push([1122940800000, 10]);
                return data;
            })()
        }, {
            name : 'SPY Return',
            tickInterval: 24 * 3600 * 1000,
			lineWidth: 4,
			marker: {
				radius: 4,
				symbol: 'diamond'
			},
			data : (function() {
                var data = [], i; //, time = (new Date()).getTime(), i;
                for( i = -100; i <= 0; i++) {
                    data.push([
        				startingUTCDate.getTime() + (i * (24 * 60 * 60 * 1000)),
        				0
                    ]);
                }
                return data;
            })()
        }
        
        ]
        
        
 
 
 
	    });
		
		
	}
  
	function startSimulationFast() {
		// reset portfolio value
		resetPortfolio();

		//var dateList= get_range_date();
		var startingDate = new Date($("#datepicker_begin").val());
		var startingUTCDate = new Date(startingDate.getUTCFullYear(), startingDate.getUTCMonth(), startingDate.getUTCDate());
		startingDate = ($("#datepicker_begin").val());
		var previousDate = new Date();
		var maxReturn = -100;
		var minReturn = 100;
		
		var spyInitial = 0;
		
		startingDate = new Date(startingDate.replace(/-/g, "/"));
		var year = (startingDate.getYear() + 1900 );
		var mon = startingDate.getMonth() + 1;
				
		startingDate = year+ "-" + mon + "-" + startingDate.getDate();
		
		//get desire end date
		var endingDate = new Date($("#datepicker_end").val());
		var endingUTCDate = new Date(endingDate.getUTCFullYear(), endingDate.getUTCMonth(), endingDate.getUTCDate());
		endingDate = ($("#datepicker_end").val());		
		endingDate = new Date(endingDate.replace(/-/g, "/"));
		var end_year = (endingDate.getYear() + 1900 );
		var end_mon = endingDate.getMonth() + 1;	
		endingDate = end_year+ "-" + end_mon + "-" + endingDate.getDate();
		// get initial benchmark index value
		// in this case, get SPY
		var spyData;
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=get_historical_stock_return&symbol=SPY&start_date="+startingDate+"&end_date="+endingDate,
			data: "",
			async: false,
			dataType: "json",
			success: function(spyReturn)
			{
				//pvalue = portfolioValue[0];
				spyData = spyReturn;

			}
		});	
		
		var data ;
		
		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat_validate.php?action=simulate_range_trade&start_date="+startingDate+"&end_date="+endingDate+"&enterCRSI="+enterCRSI+"&enterRange="+enterRange+"&enterLimit="+enterLimit+"&exitCRSI="+exitCRSI+"&orderBy="+sort_by+"&maxRisk="+maxRisk+"&riskFactor="+riskFactor+"&riskSD="+riskSD+"&cash="+cash+"&commission="+commission+"&skipFactor="+skipFactor,
			data: "",
			dataType: "json",
			success: function(portfolioReturn)
			{
				//pvalue = portfolioValue[0];
				data = portfolioReturn;
	        
//	        	$("#maxReturn").val(data.max);
//				$("#minReturn").val(data.min);
							
				window.chart = new Highcharts.StockChart({
	           		chart : {
	                	renderTo : 'container'
	                	},
	
		            rangeSelector : {
		                selected : 2
		            },
		
		            title : {
		                text : 'Turtle Portfolio Simulation Performance'
		            },
		            
		            yAxis: {
		                labels: {
		                    formatter: function() {
		                        return (this.value > 0 ? '+' : '') + this.value + '%';
		                    }
		                },
		                plotLines: [{
		                    value: 0,
		                    width: 2,
		                    color: 'silver'
		                }]
		            },
		            
		            /*plotOptions: {
		                series: {
		                    compare: 'value'
		                }
		            },
		            */
		            tooltip: {
		               // pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
		                valueDecimals: 2
		            },
            
           // series: seriesOptions
		            
		            
		            series : [{
		                name : 'Portfolio Return',
		                data : data
		                }
		                ,
		                {
		                name : 'SPY Return',
		                data : spyData
		            }]
		       });

			}
		});	

		// display progress bar by calculating total process time and divide it by 10
		// progress bar should refresh 10 times
		var processTimePerTradeDay = 10;
		var numDays;
		var refreshCount = 1;

		$.ajax(
		{
			type: "GET",
			url: "scripts/connorsRSI_strat.php?action=get_num_of_trade_days&start_date="+startingDate+"&end_date="+endingDate,
			data: "",
			async: false,
			dataType: "json",
			success: function(dayReturn)
			{
				numDays = dayReturn;
			}
		});	

		var processTime = processTimePerTradeDay * numDays;
		var refreshFrequency = processTime / 20;		
		
		handle = setInterval(function() {
		
						if (refreshCount < 21) {
							progressValue = refreshCount * 5;
							$("#progressbar")
								.progressbar({"value":progressValue})
								.children('.ui-progressbar-value')
								.html("Loading... " + progressValue + '%');
															
							$('#portfolioContainer').jtable('load');
							$('#dailyBuyListContainer').jtable('load');

				
						}	else {
							$( "#progressbar" ).progressbar("destroy");

							clearInterval(handle);

							calculate_transaction_p_and_l();
							display_transaction_p_and_l();
							update_key_portfolio_stats();

							$('#portfolioContainer').jtable('load');
							$('#dailyBuyListContainer').jtable('load');
							$('#pnlContainer').jtable('load');	
							$('#transactionsContainer').jtable('load');	

							update_key_portfolio_stats();

						}  

						refreshCount ++;	
							update_key_portfolio_stats();
							
						if (clickToStopUpdate.val > 0) {
							$( "#progressbar" ).progressbar("destroy");
							

							$('#portfolioContainer').jtable('load');
							$('#dailyBuyListContainer').jtable('load');
							$('#pnlContainer').jtable('load');	
							$('#transactionsContainer').jtable('load');	


							
							calculate_transaction_p_and_l();
							display_transaction_p_and_l();
							update_key_portfolio_stats();

							//display_transaction_p_and_l();
							//$("#transactionPandL").trigger("reloadGrid");

							
							clearInterval(handle);


						}

		}, refreshFrequency) ;

		
	}
  
  
     
   $('#simulateSubmit').click(function() {
			
		display_portfolio2();
		display_transaction();
		startSimulationFast();

   });

   $('#stopSimulate').click(function(e) {
	   	// kill out our timers
		clearInterval(handle);
		clickToStopUpdate = 1;
		// prevent the browsers default click action
		if (e.preventDefault) {
			e.preventDefault();
		}
		return false;
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

	
	function OpenPopup (c) {
		window.open(c,
		'window',
		'width=480,height=480,scrollbars=yes,status=yes');
	}


});


