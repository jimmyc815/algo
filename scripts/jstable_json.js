function loadingBody(){
	var json ="{'user':[{'id':'1','name':'Andre','location':'Seattle','phone':'123.456.7890'},{'id':'2','name':'Will','location':'Portland','phone':'911.122.0000'}]}";
	
	document.getElementById('myjson').innerHTML=json;
	
	var jsondoc = eval('(' + json + ')');

	if(jsondoc.user.length > 0){
		displayData(jsondoc);
	}
	else{
		document.getElementById('mytable').innerHTML='No records found!';
	}
}

function createTableRowContent(rowObject, data, cellType){
	var rowContent = document.createElement(cellType);
	var cell = document.createTextNode(data);
	rowContent.appendChild(cell);
	rowObject.appendChild(rowContent);
}

function createTableData(rowObject, data){
	createTableRowContent(rowObject, data, 'td');
}

function createTableHeader(rowObject, data){
	createTableRowContent(rowObject, data, 'th');
}

function displayData(jsonString){
	
	var table = document.createElement('table');
	table.border = "1";
	
	var thead = document.createElement('thead');
	table.appendChild(thead);
	
	var row = document.createElement('tr');
	
	createTableHeader(row, 'Name');
	createTableHeader(row, 'Location');
	createTableHeader(row, 'Phone');;
	
	thead.appendChild(row);
	
	var tbody = document.createElement('tbody');
	table.appendChild(tbody);
	
	for(i=0; i<jsonString.user.length; i++){
		var row = document.createElement('tr');

		createTableData(row, jsonString.user[i].name);
		createTableData(row, jsonString.user[i].location);
		createTableData(row, jsonString.user[i].phone);
				
		tbody.appendChild(row);
	}

	document.getElementById('mytable').innerHTML = '';
	document.getElementById('mytable').appendChild(table);
}
