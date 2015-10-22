$(document).ready(function(){
		$( "#runQuery" ).buttonset();
		$( "#radio" ).buttonset();

// 	tableToGrid(”#table-to-grid”, {}); 
	
	var FREQ = 30000;
	var repeat = true;
	var refreshStockQuoteId = null;
	
	function startAJAXcalls(){
	   if(repeat) {
		setTimeout(function() {
			showStockQuote();
			startAJAXcalls();
			}, 
			FREQ
		);
	    }	
	}

	getTime();
//	showStockQuote();
	showStockQuote_2();

 /*           	jQuery("#stockQuote").jqGrid({
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
                		{name:'symbol', width:100},
                		{name:'last_trade'},
                		{name:'price'},
                		{name:'pct_change'},
                		{name:'change'}
                	],
                	pager: jQuery('#pager3'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true
            	});

*/
	//setInterval(function(){  jQuery("#stockQuote").trigger("reloadGrid"); },10000);
	
	//startAJAXcalls();

//	setInterval(function(){  jQuery("#stockQuote").trigger("reloadGrid"); },10000);

//	showFrequency();
/*	getTime();
	startAJAXcalls();
*/

	$( "#slide_freq" ).slider({
			value:10,
			min: 10,
			max: 600,
			step: 10,
			slide: function( event, ui ) {
				$( "#update_freq" ).val( ui.value);
			}
		});
//	$( "#update_freq" ).val( $( "#slide_freq" ).slider( "value" ));
	$( "#update_freq" ).val(10);

	function executeSQL() {
		   	var textInput = document.getElementById('txtInputQuery').value;
		   	
			$.getJSON("scripts/service.php?action=executeSQL", function(json) {

			document.getElementById('jsonOutput').innerHTML=json.sightings[0];
			var row1 = json.sightings;
			var col1 = row1[1];
			
			if (json.sightings.length > 0) {
			
    			$("#sql_result").empty();
    			$.each(json.sightings, function() {
    				var info='Date: ' + this['date'] + ', Type: ' + this['type'];
    				var $li = $("<li />");
    				$li.html(info);
    				$li.addClass("sql_result");
    				$li.attr('id', this['id']);
    				$li.appendTo("#sql_result");
    			});
    			
    			$('#mytable').append(CreateTableView(json.sightings)).fadeIn();
    		}
    			
	   		var textInput = document.getElementById('txtInputQuery').value;
    		var info='Text Input ' + textInput;
    		var $li = $("<li />");
    				$li.html(info);
    				$li.addClass("sql_result");
    				$li.attr('id', this['id']);
    				$li.appendTo("#sql_result");
			var json ="{'user':[{'id':'1','name':'Andre','location':'Seattle','phone':'123.456.7890'},{'id':'2','name':'Will','location':'Portland','phone':'911.122.0000'}]}";

			document.getElementById('myjson').innerHTML=json;
			var jsondoc = eval('(' + json + ')');

			if (jsondoc.user.length > 0) {

 			}
 				else{
		document.getElementById('mytable').innerHTML='No records found!';
		}
			
		});
				
	}
		

	function executeSQL2() {
		   	var textInput = document.getElementById('txtInputQuery').value;
		   	
			$.getJSON("scripts/service.php?action=executeSQL2&txtInputQuery="+textInput, function(json) {

  			if (json.length > 0) {
			
    			$('#mytable').append(CreateTableView(json)).fadeIn();
    		}
		});
	}

	function executeJGrid2() {
		jQuery("#list2").jqGrid({
			type: "GET",
   			url:'scripts/service.php?action=getDynamicSQLResult',
			datatype: "json",
   			colNames:['sighting_id', 'sighting_date', 'creature_type'],
   			colModel:[
   				{name:'sighting_id',index:'sighting_id', width:100},
   				{name:'sighting_date',index:'sighting_date', width:200},
   				{name:'creature_type',index:'creature_type asc, sighting_date', width:300},
			],
   			//rowNum:10,
   			rowList:[10,20,30],
   			pager: '#pager2',
   			sortname: 'sighting_id',
    		viewrecords: true,
    		sortorder: "desc",
    		caption:"Sightings Table"
		});

		jQuery("#list2").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false});

	}

	function dynamicJGrid2() {
		var textInput = document.getElementById('txtInputQuery').value;
	
		$.ajax(
    	{
       		type: "GET",
       		url: "scripts/service.php?action=getDynamicSQLResult&txtInputQuery="+textInput,

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
	
	function showStockQuote() {
    //	$("#sql_result").empty();
    // 	$("#mytable").empty();
    // 	$("#refData").GridUnload();
     	$("#stockQuote").GridUnload();
     			
		var textInput = document.getElementById('txtInputQuery').value;
	
		$.ajax(
    	{
       		type: "GET",
       		url: "scripts/service.php?action=getStockQuote",
       		data: "",
       		dataType: "json",
       		success: function(result)
       		{
            	colD = result.gridModel;            	
            	colN = result.colNames;
            	colM = result.colModel;
            	
            	jQuery("#stockQuote").jqGrid({
                	jsonReader : {
                    	repeatitems: false,
                    	root:"dataset",
                    	cell: "",
                    	id: ""
                	},
                	url: 'scripts/service.php?action=getStockQuote',
                	datatype: 'json',
                	mtype: 'GET',
                	datastr : colD,
                	colNames: colN,
                	colModel :colM,
                	pager: jQuery('#pager3'),
                	rowNum: '',
                	rowList: ['ALL', 5, 10, 20, 50, 100],
                	height: "300px",
                	viewrecords: true,
            	})
            	
            	//setInterval(function(){  jQuery("#stockQuote").trigger("reloadGrid"); },10000);

       		},
       		error: function(x, e)
       		{
            	alert(x.readyState + " "+ x.status +" "+ e.msg);   
       		}
    	});
    	
//    	setTimeout(function() {$("#stockQuote").jqGrid('setGridParam',{datatype:'json'}); },500);
    	$("#stockQuote").jqGrid('setGridParam',{datatype:'json'}); 

		//reload();
			getTime();

	}
	
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
 /*       			onCellSelect : function(rowid,iCol, cellcontent,e){
            			var td = $("#"+rowid).find("td");
            			var reqTd = td[iCol];

            			if($(reqTd).attr("class") == undefined){
                			$(reqTd).addClass("green");
                			$(td).addClass("green");
            			} else {
                			$(reqTd).removeClass("green");
                			$(td).removeClass("green");
                			$(reqTd).css("background-color", "red");
                			$(td).css("background-color", "red");

            			}
            
        			},
    */    			
/*				loadComplete: function(data){

        			$.each(data.rows,function(i,item){
            			if(data.rows[i].change >0){
                			$("#" + data.rows[i].change).find("td").eq(4).css("color", "red");
                			$("#2".change).find("td").addClass("green");
                			$("#2".symbol).find("td").css("background-color", "red");

                		//	$("#" + data.rows[i].change).find("td").addClass("green");

            			}else{
            			        $("#"+2).find("td").css("background-color", "blue");
                			$("#2".change).find("td").addClass("green");
                			$("#2".symbol).find("td").css("background-color", "red");
            			}
        			});
      			},
  */    			
      			

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
            	
            	
            	);//.navGrid('#stockQuote',{parameters}, prmEdit, prmAdd, prmDel, prmSearch, prmView);

		//jQuery("#stockQuote").jqGrid('navGrid','#paper3',{parameters},prmEdit, prmAdd, prmDel, prmSearch, prmView);
//jQuery("#stockQuote").jqGrid('navGrid',options,pAdd,pDel,pSearch ); 
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

/*		jQuery("#stockQuote").jqGrid('navGrid', '#pager3', 
			 {edit: true, add: true, del: false, search: false }
    ,{
        modal:true,
        recreateForm: true,
        editCaption: "Edit subscriber",
        saveData: "Data has been changed! Save changes?",
        bYes: "Yes",
        bNo: "No",
        bExit: "Cancel",
        resize:false,
        checkOnSubmit:true,
        width: 400

    },

    { 
        modal:true,
        recreateForm: true,
        addCaption: "Add a Stock Symbol",
        saveData: "Data has been changed! Save changes?",
        editData: {view: "Rain"},
        url: 'scripts/service.php?action=getStockQuote2',
        mtype: 'GET',
        bYes: "Yes",
        bNo: "No",
        bExit: "Cancel",
        resize:true,
        checkOnSubmit:true,
        width: 200,
        closeAfterAdd: true,
        closeAfterEdit: true,
        reloadAfterSubmit: true

    }

     );

*/
	//	jQuery("#stockQuote").jqGrid('inlineNav',"#pager3");

    	$("#stockQuote").jqGrid('setGridParam',{datatype:'json'}); 

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
     			$("#stockQuote").GridUnload();


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
		$("#stockQuote").trigger("reloadGrid");
		getTime();

	}
	



});


