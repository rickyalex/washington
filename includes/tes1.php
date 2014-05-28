<?php

    include('./eofficefunctions.php');
    //$id_transaction = 'CF000152';
    //$tes            = GetRevision($id_transaction);
    //echo $tes;
    
    $workflow = new Workflow_Class('030', 2, 'AP');
    if ($workflow->isFound)
    {
        echo 'next_state: '.$workflow->next_state.'<br/>';
        echo 'id_status: '.$workflow->id_status.'<br/>';
        echo 'from_user: '.$workflow->from_user.'<br/>';
        echo 'to_user: '.$workflow->to_user.'<br/>';
        echo 'cc_user: '.$workflow->cc_user.'<br/>';
        
    } else {
        echo 'ID workflow not found!! (030)';
    }
    
    
    
/*    
    $id_user = "ajamalu5";
    $tes = GetIdEmployee($id_user);
    echo $tes;
    
    */
    
/*
    $id_employee = "0001114045"; //0001114045
    $approver = new Approver_Class($id_employee);
    
    echo $approver->id_employee1.'<br/>';
    echo $approver->id_user1.'<br/>';
    echo $approver->fullname1.'<br/>';
    echo $approver->email1.'<br/>';
    echo $approver->level1.'<br/>';
    echo $approver->position_code1.'<br/>';
    echo $approver->position_info1.'<br/>';
    echo $approver->cost_center1.'<br/>';
    echo $approver->cost_center_info1.'<br/>';
    echo $approver->id_company1.'<br/>';
    echo $approver->company_info1.'<br/>';
    echo $approver->id_location1.'<br/>';   
    echo $approver->location_info1.'<br/>';    
    echo '---------------------------------<br/>';
    echo $approver->id_employee2.'<br/>';
    echo $approver->id_user2.'<br/>';
    echo $approver->fullname2.'<br/>';
    echo $approver->email2.'<br/>';
    echo $approver->level2.'<br/>';
    echo $approver->position_code2.'<br/>';
    echo $approver->position_info2.'<br/>';
    echo $approver->cost_center2.'<br/>';
    echo $approver->cost_center_info2.'<br/>';
    echo $approver->id_company2.'<br/>';
    echo $approver->company_info2.'<br/>';
    echo $approver->id_location2.'<br/>';   
    echo $approver->location_info2.'<br/>';
    */
?>