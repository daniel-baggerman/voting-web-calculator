<?php
include 'vote_db.php';

if(isset($_GET['url_election_name'])){
    echo get_election_type($_GET['url_election_name']);
}

function get_election_type($url_election_name){
    $data = executeselect("SELECT coalesce(public_private,1) public_private, coalesce(password_protect,0) password_protect
                            FROM elections 
                            WHERE url_election_name = ?", false, [$url_election_name])[0];

    // if error occurred, return the error string
    if(is_string($data)){ 
        http_response_code(500);
        return json_encode(["status" => "Error!",
                            "message" => "Error retrieving election type data. ".$data,
                            "data" => []
                            ],JSON_NUMERIC_CHECK);$data;
    }
    // else convert array to json text and return it
    else{
        http_response_code(200);
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