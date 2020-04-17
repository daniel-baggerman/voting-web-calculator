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
                                 FROM vote_ballot_options
                                 WHERE election_id = ".$ai_election_id."
                                 AND option_id IN (SELECT DISTINCT option_id
                                                    FROM vote_cast_ballots
                                                    WHERE election_id = ".$ai_election_id.")
                                 ORDER BY rank",
                                 $ab_fetch_column=true
                                );

    // Fetch prefence strengths
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
                                    WHERE election_id = ".$ai_election_id."
                                    AND first_option_id   = ".$la_options[$i]."
                                    AND second_option_id  = ".$la_options[$j]);
            
            if(is_string($val)){ 
                return json_encode(["status" => "Failure :(",
                                    "message" => $val,
                                    "data" => []
                                    ],JSON_NUMERIC_CHECK);
            }
            
            // Store the value in the array
            array_push($pref_strengths[$i], $val);
        }
    }

    // Fetch strongest paths
    $strongest_paths = [];
    // Add rows for each option
    for( $i=0; $i < count($la_options); $i++ ){
        array_push($strongest_paths, []);
    }

    // Add values to array
    for( $i=0; $i < count($la_options); $i++ ){
        for( $j=0; $j < count($la_options); $j++ ){
            // Fetch the value
            $val = select_scalar("SELECT strongest_path 
                                    FROM vote_winner_calc 
                                    WHERE election_id = ".$ai_election_id."
                                    AND first_option_id   = ".$la_options[$i]."
                                    AND second_option_id  = ".$la_options[$j]);
            
            if(is_string($val)){ 
                return json_encode(["status" => "Failure :(",
                                    "message" => $val,
                                    "data" => []
                                    ],JSON_NUMERIC_CHECK);
            }
            
            // Store the value in the array
            array_push($strongest_paths[$i], $val);
        }
    }

    // Fetch the labels
    $labels = executeselect("SELECT DISTINCT description 
                             FROM vote_options vo
                             JOIN vote_ballot_options vbo on vbo.option_id = vo.option_id
                             JOIN vote_cast_ballots vcb on vcb.option_id = vo.option_id and vcb.election_id = vbo.election_id
                             WHERE vbo.election_id = ".$ai_election_id."
                             ORDER BY vbo.rank",
                             $ab_fetch_column=true);
    if(is_string($val)){ 
        return json_encode(["status" => "Failure :(",
                            "message" => $val,
                            "data" => []
                            ],JSON_NUMERIC_CHECK);
    }

    // Fetch the winner
    $winner = executeselect("SELECT vo.description
                             FROM vote_options vo 
                             JOIN vote_ballot_options vbo ON vbo.option_id = vo.option_id
                             WHERE vbo.rank = 1
                             AND vbo.election_id = ".$ai_election_id,
                             $ab_fetch_column=true);

    if(is_string($winner)){ 
        return json_encode(["status" => "Failure :(",
                            "message" => $winner,
                            "data" => []
                            ],JSON_NUMERIC_CHECK);
    }

    $data = json_encode(["status" => "Success!",
                         "message" => "Election data successfully retrieved!",
                         "data" => ["pref_strengths" => $pref_strengths,
                                    "strongest_paths" => $strongest_paths,
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