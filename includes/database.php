<?php

include_once('global.php');

/*
print ROOT_DIR;
print '<br/>';
print INCLUDES_DIR;
print '<br/>';
print LIB_DIR;
print '<br/>';
print JS_DIR;
print '<br/>';
*/

//include_once(LIB_DIR.'/Adodb/adodb-errorhandler.inc.php');
//include_once(LIB_DIR.'/Adodb/adodb-exceptions.inc.php');
include_once(LIB_DIR.'/Adodb/adodb.inc.php');

$ADODB_QUOTE_FIELDNAMES = 'MSSQL';

function Create_Connection($DSN, $type='P')
{
	try {
		$conn = ADONewConnection('ado_mssql');
		
		//$conn->debug = true;
		if($type=='P')                 
			$conn->PConnect($DSN);     //Persisten Connection
		elseif($type=='N')             
			$conn->NConnect($DSN);     //New Connection
		else 
			$conn->Connect($DSN);      //Default
			
		$conn->SetFetchMode(ADODB_FETCH_ASSOC);
	} 
	catch (exception $e) { 
		$conn = array();
        //var_dump($e); 
        //adodb_backtrace($e->gettrace());		
	} 		
	//debugging
	//print '<pre>';
	//print_r($conn);
	//print '</pre>';
	//print count($conn);
	return $conn;	
}


//Daftarkan setiap database disini
//function LotusNotes_Connection($type='P')
//{
//	$DSN='PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=172.16.162.7;DATABASE=intranetmail;UID=intranet;PWD=dotnet;';	
//	return Create_Connection($DSN, $type);
//}

function eOFFICE_Connection($type='P')
{
	$DSN='PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=172.16.160.2;DATABASE=transaction_data;UID=eoffice;PWD=srgeoffice235;'; //srgfs5	
	return Create_Connection($DSN, $type);
}

function Penarikan_Connection($type='P')
{
	$DSN='PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=172.16.160.2;DATABASE=Penarikan;UID=penarikan;PWD=penarikan99;'; //srgfs5	
	return Create_Connection($DSN, $type);
}

?>