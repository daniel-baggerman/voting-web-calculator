<?php
include 'vote_db.php';
function get_elections(){
    $data = executeselect("select election_id, description
                           from vote_elections");

    // if error occurred, return the error string
    if(is_string($data)){ 
        return $data;
    }
    // else convert array to json text and return it
    else{
        $output = json_encode($data, JSON_NUMERIC_CHECK);
        if($output === false){
            return "Error encoding sql select array as json.";
        }
        else{
            return $output;
        }
    }
}

echo get_elections();

?>