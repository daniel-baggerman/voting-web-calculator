<?php
include 'vote_db.php';

if(isset($_GET['string'])){
    echo get_elections_like( $_GET['string'] );
}

function get_elections_like($as_string){
    $data = executeselect("select election_id, description, url_election_name
                           from vote_elections
                           where lower(description) like '%".strtolower($as_string)."%'");

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
?>