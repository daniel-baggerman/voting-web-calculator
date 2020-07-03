<?php
require 'vote_db.php';
require 'security.php';

// Make sure params are passed before starting anything else.
if(!isset($_GET['election_id'])){
    echo json_encode([  "status" => "Failure :(",
                        "message" => "No election_id passed.",
                        "data" => []
                        ],JSON_NUMERIC_CHECK);
    exit();
}

// $myfile = fopen("test.txt", "w");
// $headers = apache_request_headers();
// foreach ($headers as $header => $value) {
//     file_put_contents("test.txt","$header: $value \n", FILE_APPEND | LOCK_EX);
// }
// fclose($myfile);

// Get token from headers
$token_string = array_key_exists('Authorization',getallheaders()) ? substr(getallheaders()['Authorization'],7) : null;

// Verify token
$valid = validate_token_to_election($token_string,$_GET['election_id']);

// Do the stuff
if($valid){
    echo load_ballot_info($_GET['election_id']);
} else {
    http_response_code(401);
    echo json_encode(["status" => "Failure :(",
                        "message" => "Request not authentic.",
                        "data" => []
                        ],JSON_NUMERIC_CHECK);
    exit();
}

function load_ballot_info($an_election_id){
    $data = executeselect("SELECT o.description description, o.option_id option_id, 1 enabled
                            from ballot_options bo 
                            join options o on o.option_id = bo.option_id
                            where bo.election_id = ?
                            order by o.description"
                          , false
                          , [$an_election_id]);

    // if error occurred, return the error data
    if(is_string($data)){ 
        return json_encode(["status" => "Failure :(",
                            "message" => "Error: ".$data,
                            "data" => []
                            ],JSON_NUMERIC_CHECK);
    }
    // else convert array to json text and return it
    else{
        $output = json_encode(["status" => "Success!",
                                "message" => "Election options successfully retrieved!",
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
?>