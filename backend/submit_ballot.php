<?php
include 'vote_db.php';

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

if(isset($_GET['election_id']) && isset($_GET['voter_id']) && !empty($ga_postdata) ){
    echo post_ballot($_GET['election_id'], $_GET['voter_id']);
}
else{
    echo "Error submitting ballot. Not all variables bound.";
}

function post_ballot($as_election_id, $as_voter_id){
    global $ga_postdata;

    $la_ballot = json_decode($ga_postdata, true);

    if(json_last_error() != 0){
        return json_last_error_msg();
    };

    // delete voter's previous ballot submission
    $rtn = executesql("delete from vote_cast_ballots 
                       where election_id = ".$as_election_id."
                       and voter_id = ".$as_voter_id
                       );
    // error catch
    if($rtn <> 'OK'){
        return $rtn;
    }

    // insert ballot
    foreach($la_ballot as $key => $option){
        // ballot array is in order of ranking
        $rank = intval($key) + 1;

        // run the insert based on the data passed
        $sqls = "insert into vote_cast_ballots 
                    (   cast_ballot_id
                      , election_id
                      , voter_id
                      , option_id
                      , option_rank)
                 values 
                    (   ifnull((select max(cast_ballot_id) from vote_cast_ballots),0)+1
                      , ".$as_election_id."
                      , ".$as_voter_id."
                      , ".strval($option['option_id'])."
                      , ".strval($rank).")";

        $output = executesql($sqls);

        // check for errors
        if($output <> "OK"){
            return htmlspecialchars($sqls."\r\n".$output);
        }
    }

    return json_encode(["status" => "Success!",
                        "message" => "Ballot successfully submitted!",
                        "data" => []
                        ]);
}
?>