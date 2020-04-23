<?php
include 'vote_db.php';

if(isset($_GET['string'])){
    echo get_elections_like( $_GET['string'] );
}

function get_elections_like($as_string){
    $data = executeselect("SELECT election_id, description, url_election_name
                           FROM vote_elections
                           WHERE lower(description) LIKE :election_name ",
                           false,
                           [":election_name" => strtolower("%{$as_string}%")] );

    // if error occurred, return the error string
    if(is_string($data)){ 
        return $data;
    }
    // else convert array to json text and return it
    else{
        $data = json_encode(["status" => "Success!",
                             "message" => "Election retrieved.",
                             "data" => ["election_args" => array(":election_name" => "%{$as_string}%"),
                                        "elections" => $data]
                             ],JSON_NUMERIC_CHECK);
        if($data === false){
            return "Error encoding array as json.";
        }
        else{
            return $data;
        }
    }
}
?>