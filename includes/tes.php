<?php
    //include('./employee_class.php');
    include('./eofficefunctions.php');

    //$id_employee        = '0001104166';
    $id_role            = 'GA00';
    $id_notification    = 'GASRG';
    
	$tes            = GetLotusNotesGA($id_role, $id_notification);
    echo $tes;

//    $array = array('lastname', 'email', 'phone');
//    $comma_separated = implode(",", $array);
//    
//    echo $comma_separated; 
    
    /*
    $employee = new Employee_Class($id_employee);
    if($employee->isFound){
        echo $employee->full_name.'<br/>';
        echo $employee->cost_center;    
    }else{
        echo "not found!";
    }
    
    //echo GetEmployee($id_employee);
    */
?>