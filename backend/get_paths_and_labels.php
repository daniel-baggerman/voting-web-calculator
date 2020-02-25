<?php
include 'vote_db.php';

if(isset($_GET['election_id'])){
    echo get_paths_and_labels($_GET['election_id']);
} else {
    echo "Election ID not set.";
}

function get_paths_and_labels($ai_election_id){
    /* example pref_strengths = [
    // d[*,A], d[*,B], d[*,C], d[*,D], d[*,E]
        [null, 20,     26,     30,     22],  // d[A,*]
        [25,   null,   16,     33,     18],  // d[B,*]
        [19,   29,     null,   17,     24],  // d[C,*]
        [15,   12,     28,     null,   14],  // d[D,*]
        [23,   27,     21,     31,     null] // d[E,*]
    ];
    */

    // Fetch the options to loop through, fetch_column to get 1D array of values
    $la_options = executeselect("SELECT option_id 
                                 FROM vote_options 
                                 WHERE option_id IN (SELECT DISTINCT option_id
                                                     FROM vote_cast_ballots
                                                     WHERE election_id = ".$ai_election_id.")
                                 ORDER BY description",
                                 $ab_fetch_column=true
                                );

    // Blank array to start.
    $pref_strengths = [];
    // Add rows for each option
    for( $i=0; $i < count($la_options); $i++ ){
        array_push($pref_strengths, []);
    }

    // Add values to array
    for( $i=0; $i < count($la_options); $i++ ){
        for( $j=0; $j < count($la_options); $j++ ){
            // Fetch the value
            $val = select_scalar("SELECT pref_strength 
                                    FROM vote_winner_calc 
                                    WHERE run_id = (SELECT max(run_id) FROM vote_election_runs WHERE election_id=".$ai_election_id.")
                                    AND election_id = ".$ai_election_id."
                                    AND first_option_id   = ".$la_options[$i]."
                                    AND second_option_id  = ".$la_options[$j]);
            
            if(is_string($val)){ 
                return "Error selecting strongest path from vote_winner_calc. Error Message:\r\n".$val;
            }
            
            // Store the value in the array
            array_push($pref_strengths[$i], $val);
        }
    }

    // Fetch the labels
    $labels = executeselect("SELECT description 
                             FROM vote_options 
                             WHERE option_id IN (SELECT DISTINCT option_id
                                                 FROM vote_cast_ballots
                                                 WHERE election_id = ".$ai_election_id.")
                             ORDER BY description",
                             $ab_fetch_column=true);
    if(is_string($val)){ 
        return "Error selecting node labels. Error Message:\r\n".$val;
    }

    // Fetch the winner
    $winner = executeselect("SELECT description
                             FROM vote_election_winners vew
                             JOIN vote_options vo ON vew.option_id = vo.option_id
                             WHERE vew.run_id = (SELECT max(run_id) FROM vote_election_runs WHERE election_id=".$ai_election_id.")
                             AND vew.election_id = ".$ai_election_id,
                             $ab_fetch_column=true);

    if(is_string($winner)){ 
        return "Error selecting election winner. Error Message:\r\n".$winner;
    }

    $data = json_encode(["status" => "Success!",
                         "message" => "Election data successfully retrieved!",
                         "data" => ["pref_strengths" => $pref_strengths,
                                    "labels" => $labels,
                                    "winner" => $winner]
                         ],JSON_NUMERIC_CHECK);

    if($data === false){
        return "Error encoding array as json.";
    }
    else{
        return $data;
    }
}
?>