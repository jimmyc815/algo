$(document).ready(function(){
 		var enterCRSI = 30;
 		var enterRange = 100;
 		var pctLimit = 1;
 		var exitCRSI = 70;
 		
 		$("#enterCRSI").val(enterCRSI);
 		$("#enterRange").val(enterRange);
 		$("#pctLimit").val(pctLimit);
  		$("#exitCRSI").val(exitCRSI);

		sort_by = "crsi desc";
		$('#sort_by_crsi_desc').click(function(e) {
			//$('#sort_by_crsi_asc').prop('checked', false);
			alert ("crsi desc");
			sort_by = "crsi desc";
		});	

		$('#sort_by_crsi_asc').click(function(e) {
			//$('#sort_by_crsi_desc').prop('checked', false);
			sort_by = "crsi asc";
		});	
		
 		$('#enterCRSI').change(function(e) {	
 			enterCRSI = $("#enterCRSI").val();
 		});		
 		$('#enterRange').change(function(e) {	
 			enterRange = $("#enterRange").val();
 		});		
 		$('#pctLimit').change(function(e) {	
 			pctLimit = $("#pctLimit").val();
 		});		
 		$('#exitCRSI').change(function(e) {	
 			exitCRSI = $("#exitCRSI").val();
 		});			


		//risk tolerance
		var maxRisk = 7;
		var riskFactor = 0.4;
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

		//skip factor
		var skipFactor = 0.1;
		$("#skipFactor").val(skipFactor);
		
		$('#skipFactor').change(function(e) {	
			skipFactor = $("#skipFactor").val();		
		});		
		

		var cash = 1000000;
		var commission = 7;
		$("#cash").val(cash);
		$("#commission").val(commission);

		display_registered_sim_tests();

		function display_registered_sim_tests() {
			var textInput = "select caseID, enter_crsi, enter_range, pct_limit_below, exit_crsi, order_by, max_risk, risk_factor, risk_sd, skip_factor from simTestCases ";
	
	        $('#RegisteredSimulationContainer').jtable({
	            title: 'Registered Simulation Cases',
	            paging: true, //Enable paging
	            pageSize: 10, //Set page size (default: 10)
	            sorting: true, //Enable sorting
	            defaultSorting: 'caseID ASC', //Set default sorting
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
	                caseID: {
		                key: true,
	
	                    title: 'CaseID',
	                    width: '5%'
	                },
	                enter_crsi: {
	                    title: 'Enter CRSI',
	                    width: '12%',
	                },
	                enter_range: {
	                    title: 'Enter Range',
	                    width: '12%'
	                },
	                pct_limit_below: {
	                    title: 'Enter Pct Below',
	                    width: '15%'
	                },
	                exit_crsi: {
	                    title: 'Exit CRSI',
	                    width: '12%'
	                },
	                order_by: {
	                    title: 'Order By',
	                    width: '12%'
	                },
	                max_risk: {
	                    title: 'Max Risk',
	                    width: '12%'
	                },
	                risk_factor: {
	                    title: 'Risk Factor',
	                    width: '12%'
	                },
	                risk_sd: {
	                    title: 'Risk SD',
	                    width: '12%'
	                },
	                skip_factor: {
	                    title: 'Skip',
	                    width: '5%'
	                }
	            }
	
	
	        });
			$('#RegisteredSimulationContainer').jtable('option', 'pageSize', 10);
			$('#RegisteredSimulationContainer').jtable('load');		
	
		}

	    $('#register').click(function() {
		    
			$.ajax(
			{
				type: "GET",
	            url: "scripts/simulation_test.php?action=register_sim_test&enterCRSI="+enterCRSI+"&enterRange="+enterRange+"&pctLimit="+pctLimit+"&exitCRSI="+exitCRSI+"&orderBy="+sort_by+"&maxRisk="+maxRisk+"&riskFactor="+riskFactor+"&riskSD="+riskSD+"&skipFactor="+skipFactor,
				async: false,
				data: "",
				dataType: "json",
				success: function(portfolioValue)
				{
					pvalue = portfolioValue[0].pvalue;
				}
			});		

			display_registered_sim_tests();
	
	    });
	    





		var freq_date = $("input[name='freq_box']:checked").val();
		var turtleFlag = new Array();

});
