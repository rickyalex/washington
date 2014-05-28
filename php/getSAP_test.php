<?php
    $salesOrg = isset($_GET['SalesOrg']) ? $_GET['SalesOrg'] : "";
	$noSO = isset($_GET['NoSO']) ? $_GET['NoSO'] : "";
	if(!$_GET){
		$salesOrg = isset($_POST['SalesOrg']) ? $_POST['SalesOrg'] : "";
		$noSO = isset($_POST['NoSO']) ? $_POST['NoSO'] : "";
	}
    $salesOrg = '2374';
    $noSO = '6700195788';
    
    $client = new SoapClient('http://172.16.162.29/wssap/service.asmx?wsdl');
	$something =  $client->GetDetailBySO(array('SalesOrg' => $salesOrg, 'NoSO' => $noSO));
    if($something!=null){
        $myJSON = $something->GetDetailBySOResult;
        $myJSON = str_replace('[','',$myJSON);
        $myJSON = str_replace(']','',$myJSON);
        $myJSON = '{"rows":['.$myJSON.']}';
        echo $myJSON;
        //$result = json_decode($myJSON);
        //echo $myJSON;
        //echo $result[0]->NAME1;
//        echo $myJSON[0]->KUNNR2;
//        echo $myJSON[0]->NAME2;
//        echo $myJSON[0]->INCO1;
//        echo $myJSON[0]->INCO2;
//        echo $myJSON[0]->VSTEL;
//        echo $myJSON[0]->VTEXT;
//        echo $myJSON[0]->BMENG;
//        echo $myJSON[0]->VRKME;
//        echo $myJSON[0]->VDATU;
//        echo $myJSON[0]->BRGEW;
//        echo $myJSON[0]->GEWEI;
//        echo $myJSON[0]->TEXT;
//        echo $myJSON[0]->ENAME1;
//        echo $myJSON[0]->ENAME2;
//        echo $myJSON[0]->SHIPTO;
    }
    
    //print_r ($result);
    
?>