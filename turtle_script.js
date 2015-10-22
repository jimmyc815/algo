$(document).ready(function(){
		$( "#runQuery" ).buttonset();
//		$( "#radio" ).buttonset();
		$("#datepicker_begin").datepicker({ changeMonth: true, changeYear: true});
		$("#datepicker_end").datepicker({ changeMonth: true, changeYear: true});
		$( "#type_select" ).buttonset();


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

	function executeHP() {
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
		reload();
		 refreshStockQuoteId = setInterval(function(){ reload(); },$( "#update_freq" ).val()*1000);
	});
	
	$("#btnSubmit").click(function() {
		executeSQL();
	});
	
    $('#historicalPriceSubmit').click(function() {
    		
     			$("#algoResult").GridUnload();

			//executeHP();
			getHistoricalPrice();
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
	



});


