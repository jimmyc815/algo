<?php

// Version 1.6 - November 2012


/*******************************************************************
	generateReport($sQuery, $header, $footer, $color)
	================================================================
	@PARAMETERS
	$reportName - text - name of report
	$sQuery - text - the SQL query
	$header - html - header message displayed above the report
	$footer - html - footer message displayed under the report
	$color - text - The colour of the report ("grey", "green" or "blue")
********************************************************************/

function generateReport($sQuery, $header, $footer, $color) {
	
	$color = strtolower($color);
	
	if ($color != 'red' && $color != 'green' && $color != 'blue' && $color != 'grey')	{
		$color = 'grey';	
	}

	//setup variable function pointers based on choice of color
	switch ($color)
		{
		case 'red':
			$h1Class 		 = 'h1Red';
			$tableClass  	 = 'tableRed';
			$thClass 	 	 = 'thRed';
			$trClass0	 	 = 'tr0Red';
			$trClass1 	 	 = 'tr1Red';
			$tdClass0		 = 'td0Red';
			$tdClass1		 = 'td1Red';
			break;			
		case 'green':
			$h1Class 		 = 'h1Green';
			$tableClass  	 = 'tableGreen';
			$thClass 	 	 = 'thGreen';
			$trClass0	 	 = 'tr0Green';
			$trClass1 	 	 = 'tr1Green';
			$tdClass0		 = 'td0Green';
			$tdClass1		 = 'td1Green';
			break;
		case 'blue':
			$h1Class 		 = 'h1Blue';		
			$tableClass 	 = 'tableBlue';
			$thClass		 = 'thBlue';
			$trClass0		 = 'tr0Blue';
			$trClass1		 = 'tr1Blue';
			$tdClass0		 = 'td0Blue';
			$tdClass1		 = 'td1Blue';
			break;
		case 'grey':
			$h1Class 		 = 'h1Grey';
			$tableClass 	 = 'tableGrey';
			$thClass		 = 'thGrey';
			$trClass0		 = 'tr0Grey';
			$trClass1		 = 'tr1Grey';
			$tdClass0		 = 'td0Grey';
			$tdClass1		 = 'td1Grey';
			break;			
		default:
			$tableClass 	 = 'tableGrey';
			$thClass		 = 'thGrey';
			$trClass0		 = 'tr0Grey';
			$trClass1		 = 'tr1Grey';
			$tdClass0		 = 'td0Grey';
			$tdClass1		 = 'td1Grey';
		}
	
	//Initialise our report
	$report = '';
	
	//Add Header Message
	$report .= '<p>'.$header.'</p>';	

	//Connect to SQL Server
	$link = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if (!$link) {
		$report .= '<p>An error occured connecting to the SQL Server</p>';
		$report .= '<p>'.mysql_error().'</p>';
		return $report;
	}
	
	//Connect to Database
	$db_selected = mysql_select_db(DB_DATABASE, $link);
	if (!$db_selected) {
		$report .= '<p>Connected successfully to the SQL Server but an error occured selecting the database '.DB_DATABASE.'</p>';
		$report .= '<p>'.mysql_error().'</p>';
		return $report;
	}

	//Execute Query
	$result = mysql_query($sQuery);	
	
	//If report fails to run correctly then send email
	if (!$result) {
		$report .= '<p>'.mysql_error().'</p>';
		echo 'An error occured: '.mysql_error().'<br />';
		return $report;
	}
	
	//process results and generate report
	if (mysql_num_rows($result) == 0) {
		$report = '<p>This report generated no results today.</p>';
	} else {
		//Get Field Names (Used as Table Headers)
		$fieldArray = array_keys( mysql_fetch_array( mysql_query( $sQuery, $link), MYSQL_ASSOC)); 
		
		//Build Table Headers
		$report.= '<table frame="BOX" rules="COLS" cellspacing="0" cellpadding="10" "'.$tableClass().'"><tr>';
		for ($i=0;$i<count($fieldArray);$i++) {
			$report.= '<th class="'.$thClass().'"><strong>'.$fieldArray[$i].'</strong></th>';
		}
		$report.= '</tr>';		

		$rowNo = 0;
		while ($row = mysql_fetch_array($result)) {
			$rowNo = 1 - $rowNo;
			if ($rowNo == 0) {
				$rowStyle = $trClass0();
				$cellStyle = $tdClass0();
			} else {
				$rowStyle = $trClass1();
				$cellStyle = $tdClass1();
			}
			
			$report .= '<tr "'.$rowStyle.'">';
			
			//step through fields
			for ($i=0;$i<count($fieldArray);$i++) {
				$report .= '<td "'.$cellStyle.'">'.$row[$fieldArray[$i]].'</td>';
			}
			$report .= '</tr>';
		}
		$report.= '</table>';		
	}
	
	//Add Footer
	$report .= '<p>'.$footer.'</p>';

	mysql_close($link);		
	return $report;
}



//	Generate HTML Email
function html_email($to,$subject,$msg) {
	
	if (!TEST_MODE) {
		
		require_once('class.phpmailer-lite.php');
		
		$mail = new PHPMailerLite(); // defaults to using php "Sendmail" (or Qmail, depending on availability)	
		$mail->IsMail(); // telling the class to use native PHP mail()	
		//$mail->CharSet	  = MY_CHARSET;
		$mail->Encoding   = 'base64';
		$mail->IsHTML(true);
		$body = $msg;
		
		//convert to base 64	
		$mail->MsgHTML($body);
		
		$body = rtrim( chunk_split( base64_encode($body) ) ); 		
		
		$mail->SetFrom(FROM_EMAIL, FROM_NAME);
		
		$address = $to;
		$mail->AddAddress($address, "");
		
		$mail->Subject    = $subject;
		
		if(!$mail->Send()) {
			echo 'The mail to '.$to.' failed to send.<br />';
		} else {
			echo 'The mail was sent successfully to '.$to.'.<br />';
		}
	} else {
		echo '<b>Report running in test mode.</b><br />Disable test mode in the config.php when you are ready for the report to go live.<br /><br />';	
		echo '<br />'.$msg.'<br />';
	}
}	
	

	
/* INLINE BLUE STYLE SHEET SCHEME FOR HTML EMAIL */
function h1Blue(){
	$style = '';
	return $style;
}
function tableBlue(){
	$style = 'style="border-collapse: collapse;"';
	return $style;
}
function thBlue(){
	$style = 'bgcolor="#37475f" style="border:1px solid #000; font-weight: bold; font-size: 125%; background-color: #37475f; color: #fff; text-align: left;"';
	return $style;
}
function tr0Blue() {
	$style = 'bgcolor="#f2f5f8" style="background-color: #f2f5f8; color: #000; font-weight: normal;"';
	return $style;
}
function tr1Blue() {
	$style = 'bgcolor="#dfe4e9" style="background-color: #dfe4e9; color: #000; font-weight: normal;"';
	return $style;
}
function td0Blue() {
	$style = 'style="text-align: left;"';
	return $style;
}
function td1Blue() {
	$style = 'style="text-align: left;"';
	return $style;
}
function headerBlue() {
	$style = '';
	return $style;
}
function footerBlue() {
	$style = '';
	return $style;
}
/* END BLUE COLOR SCHEME */




/* INLINE GREEN STYLE SHEET SCHEME FOR HTML EMAIL */
function h1Green(){
	$style = '';
	return $style;
}
function tableGreen(){
	$style = 'style="border-collapse: collapse;"';
	return $style;
}
function thGreen(){
	$style = 'bgcolor="#a8cc44" style="border:1px solid #000; font-weight: bold; font-size: 125%; background-color: #a8cc44; color: #fff; text-align: left;"';
	return $style;
}
function tr0Green() {
	$style = 'bgcolor="#f3f7ec" style="background-color: #f3f7ec; color: #000; font-weight: normal;"';
	return $style;
}
function tr1Green() {
	$style = 'bgcolor="#dee6cc" style="background-color: #dee6cc; color: #000; font-weight: normal;"';
	return $style;
}
function td0Green() {
	$style = 'style="text-align: left;"';
	return $style;
}
function td1Green() {
	$style = 'style="text-align: left;"';
	return $style;
}
function headerGreen() {
	$style = '';
	return $style;
}
function footerGreen() {
	$style = '';
	return $style;
}
/* END Green COLOR SCHEME */






/* INLINE Red STYLE SHEET SCHEME FOR HTML EMAIL */
function h1Red(){
	$style = '';
	return $style;
}
function tableRed(){
	$style = 'style="border-collapse: collapse;"';
	return $style;
}
function thRed(){
	$style = 'bgcolor="#000" style="border:1px solid #000; font-weight: bold; font-size: 125%; background-color: #000; color: #fff; text-align: left;"';
	return $style;
}
function tr0Red() {
	$style = 'bgcolor="#f8f2f2" style="background-color: #f8f2f2; color: #000; font-weight: normal;"';
	return $style;
}
function tr1Red() {
	$style = 'bgcolor="#e9dfdf" style="background-color: #e9dfdf; color: #000; font-weight: normal;"';
	return $style;
}
function td0Red() {
	$style = 'style="text-align: left;"';
	return $style;
}
function td1Red() {
	$style = 'style="text-align: left;"';
	return $style;
}
function headerRed() {
	$style = '';
	return $style;
}
function footerRed() {
	$style = '';
	return $style;
}
/* END Red COLOR SCHEME */






/* INLINE GREY STYLE SHEET SCHEME FOR HTML EMAIL */
function h1Grey(){
	$style = '';
	return $style;
}
function tableGrey(){
	$style = 'style="border-collapse: collapse;"';
	return $style;
}
function thGrey(){
	$style = 'bgcolor="#4d4d4d" style="border:1px solid #000; font-weight: bold; font-size: 125%; background-color: #4d4d4d; color: #fff; text-align: left;"';
	return $style;
}
function tr0Grey() {
	$style = 'bgcolor="#CCC" style="background-color: #CCC; color: #555; font-weight: normal;"';
	return $style;
}
function tr1Grey() {
	$style = 'bgcolor="#EEE" style="background-color: #EEE; color: #777; font-weight: normal;"';
	return $style;
}
function td0Grey() {
	$style = 'style="text-align: left;"';
	return $style;
}
function td1Grey() {
	$style = 'style="text-align: left;"';
	return $style;
}
function headerGrey() {
	$style = '';
	return $style;
}
function footerGrey() {
	$style = '';
	return $style;
}
/* END Grey COLOR SCHEME */

?>