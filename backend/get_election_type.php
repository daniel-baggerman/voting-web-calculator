<?php
include 'vote_db.php';

if(isset($_GET['url_election_name'])){
    echo get_election_type($_GET['url_election_name']);
}

function get_election_type($url_election_name){
    $data = executeselect("SELECT ifnull(public_private,1) public_private, ifnull(password_protect,0) password_protect
                            FROM vote_elections 
                            WHERE url_election_name = ?", false, [$url_election_name])[0];

    // if error occurred, return the error string
    if(is_string($data)){ 
        return json_encode(["status" => "Error!",
                            "message" => "Error retrieving election type data. ".$data,
                            "data" => []
                            ],JSON_NUMERIC_CHECK);$data;
    }
    // else convert array to json text and return it
    else{
        $data = json_encode(["status" => "Success!",
                             "message" => "Election type retrieved.",
                             "data" => $data
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