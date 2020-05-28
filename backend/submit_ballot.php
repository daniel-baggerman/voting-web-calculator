<?php
include 'vendor/autoload.php';
include 'vote_db.php';
include 'security.php';
use Lcobucci\JWT\Parser;

//////////////////
//  Validations
//////////////////

if(!isset($_GET['election_id'])){
    http_response_code(500);
    echo json_encode([  "status" => "Failure :(",
                        "message" => "No election_id passed.",
                        "data" => []
                        ]);
    exit();
}

// Get token from headers
$token_string = array_key_exists('Authorization',getallheaders()) ? substr(getallheaders()['Authorization'],7) : null;

// Verify token
$valid = validate_token_to_election($token_string,$_GET['election_id']);

if(!$valid){
    http_response_code(401);
    echo json_encode(["status" => "Unauthenticated request",
                      "message" => "User is not authenticated. Ballot not submitted.",
                      "data" => []
                     ]);
    exit();
}

// Get the voter_id from the token since we validated it
$token = (new Parser())->parse((string) $token_string);

$voter_id = $token->getClaim('vid');

// Check that the end_date has not passed
$end_date = executeselect('SELECT end_date FROM vote_elections WHERE election_id = ?',
                            true,[$_GET['election_id']])[0];

$end_date_utc = date_create_from_format('Y-m-d',$end_date);
$now = gmdate('Y-m-d');

if($now > $end_date_utc){
    http_response_code(409);
    echo json_encode(["status" => "End date passed",
                        "message" => "The end date for this poll has passed and ballots can no longer be submitted.",
                        "data" => []
                    ]);
    exit();
}

// Do the stuff
$ga_postdata = file_get_contents('php://input');

/* example of expected postdata
{   
    "0":{   "description":"Aachis Indian",
            "option_id":1,
            "enabled":0},
    "1":{   "description":"Bowl Lab",
            "option_id":2,
            "enabled":0}
}
*/

if(isset($_GET['election_id']) && !empty($ga_postdata) && $valid ){
    echo post_ballot($_GET['election_id'], $voter_id);
}
else{
    http_response_code(500);
    echo json_encode(["status" => "Incorrect Parameters",
                      "message" => "Error submitting ballot. Not all variables bound.",
                      "data" => []
                     ]);
}

function post_ballot($as_election_id, $as_voter_id){
    global $ga_postdata;

    $la_ballot = json_decode($ga_postdata, true);

    if(json_last_error() != 0){
        http_response_code(500);
        return json_encode(["status" => "Error decoding request body.",
                            "message" => json_last_error_msg();,
                            "data" => []
                            ]);
    };

    // delete voter's previous ballot submission
    // maybe I should just prevent them from voting if their voter ID already exists in vote_cast_ballots
    $rtn = executesql("DELETE FROM vote_cast_ballots 
                       WHERE election_id = ?
                       AND voter_id = ?", [$as_election_id,$as_voter_id]
                       );
    // error catch
    if($rtn <> 'OK'){
        http_response_code(500);
        return json_encode(["status" => "Error overwriting previous cast vote.",
                            "message" => $rtn,
                            "data" => []
                            ]);
        return $rtn;
    }

    // insert ballot
    foreach($la_ballot as $key => $option){
        // ballot array is in order of ranking
        $rank = intval($key) + 1;

        // run the insert based on the data passed
        $sqls = "INSERT into vote_cast_ballots 
                    (   cast_ballot_id
                        , election_id
                        , voter_id
                        , option_id
                        , option_rank)
                  VALUES 
                    (   ifnull((select max(cast_ballot_id) from vote_cast_ballots),0)+1
                        , ?
                        , ?
                        , ?
                        , ?)";

        $output = executesql($sqls,[$as_election_id,$as_voter_id,$option['option_id'],$rank]);

        // check for errors
        if($output <> "OK"){
            // Rollback changes
            $rtn = executesql("DELETE FROM vote_cast_ballots 
                                WHERE election_id = ?
                                AND voter_id = ?", [$as_election_id,$as_voter_id]
                                );
            // error catch
            if($rtn <> 'OK'){
                http_response_code(500);
                return json_encode(["status" => "Error rolling back.",
                                    "message" => $rtn,
                                    "data" => []
                                    ]);
                return $rtn;
            }

            // Real error
            http_response_code(500);
            return json_encode(["status" => "Error recording cast ballot.",
                                "message" => $output,
                                "data" => []
                                ]);
        }
    }

    $submitted_ballot = executeselect('SELECT vcb.option_id, vcb.option_rank, vo.description option_description
                                        FROM vote_cast_ballots vcb
                                        JOIN vote_options vo ON vo.option_id = vcb.option_id
                                        WHERE election_id = ?
                                        AND voter_id = ?
                                        ORDER BY vcb.option_rank', false, [$as_election_id,$as_voter_id]);

    // Return a success message and a copy of their submitted ballot
    http_response_code(200);
    return json_encode(["status" => "Success!",
                        "message" => "Ballot successfully submitted!",
                        "data" => ["ballot" => $submitted_ballot]
                        ]);
}
?>