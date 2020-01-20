<?php
include 'vote_db.php';
function get_election_from_url_name($url_election_name){
    $data = executeselect("select election_id, description
                           from vote_elections
                           where url_election_name='".$url_election_name."'");

    // if error occurred, return the error string
    if(is_string($data)){ 
        return $data;
    }
    // else convert array to json text and return it
    else{
        $data = json_encode(["status" => "Success!",
                             "message" => "Election data successfully retrieved!",
                             "data" => $data
                             ],JSON_NUMERIC_CHECK);
        if($data === false){
            return "Error encoding sql select array as json.";
        }
        else{
            return $data;
        }
    }
}

if(isset($_GET['url_election_name'])){
    echo get_election_from_url_name($_GET['url_election_name']);
}
?>