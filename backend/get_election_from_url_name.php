<?php
include 'vote_db.php';
function get_election_from_url_name($url_election_name){
    $data = executeselect( "SELECT election_id, description, long_description, ifnull(public_private,1) public_private, ifnull(password_protect,0) password_protect
                            FROM vote_elections
                            WHERE url_election_name = ?",
                            false,
                            [$url_election_name]);

    // if error occurred, return the error string
    if(is_string($data)){
        return $data;
    }
    // else convert array to json text and return it
    else{
        $output = json_encode(["status" => "Success!",
                               "message" => "Election data successfully retrieved!",
                               "data" => $data
                               ],JSON_NUMERIC_CHECK);
        if($output === false){
            return "Error encoding sql select array as json.";
        }
        else{
            return $output;
        }
    }
}

if(isset($_GET['url_election_name'])){
    echo get_election_from_url_name($_GET['url_election_name']);
}
?>