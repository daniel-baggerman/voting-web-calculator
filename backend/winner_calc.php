<?php
include 'vote_db.php';

if( isset($_GET['election_id']) ){
    echo f_election_winner_process($_GET['election_id']);
}
else{
    echo "Error tallying votes. Not all variables bound.";
}

function f_election_winner_process( $ai_election_id ){
    /* 
        ai_election_id: election_id argument passed to designate election winner to be calculated
        ls_msg: local string variable for storing messages to return from the function
    */
    
    // start the calc
    $ls_msg = f_initialize( $ai_election_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    $ls_msg = f_strongest_path( $ai_election_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    $ls_msg = f_winner_calc( $ai_election_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    // End winner calc and return success message
    return json_encode(["status" => "Success!",
                        "message" => "Winner Calc successful!",
                        "data" => []
                        ]);
}

function f_initialize( $ai_election_id ){
/* 
    Initialize the database with info about election and setting up rows to store info.

    Variables:
    li_first_option_rank: local integer to hold rank of first option in comparison pair
    li_second_option_rank: local integer to hold rank of second option in comparison pair
    li_preference_strength: local integer to hold prefence strength of first_option over second_option
    option_pairs: local array of option pairs in an election for comparison
    la_voters: local array of voters in the election
*/

    // initialize winner calc with election and options info
    // vote_cast_ballots_temp logic creates a table like vote_cast_ballots but where options not ranked by a voter are placed in dead last tied on their ballot
    // join the cast ballots table to itself on election_id and voter_id to get a crosswise comparison of each option on a voter's ballot
    // then count the number of times option A is ranked more highly than option B.
    // I used sum() instead of count() because count was excluding option pairs where no one ranked X above Y.
    // update the pref_strength and strongest_path to the base preference strength from this count. The strongest path will be updated later.

    $ls_msg = executesql("INSERT INTO vote_winner_calc (election_id, first_option_id, second_option_id, pref_strength, strongest_path)
        WITH vote_cast_ballots_temp AS
        (   SELECT vote_cast_ballots.election_id, vote_cast_ballots.voter_id, vote_cast_ballots.option_id, ifnull(voter_option_ranks.option_rank,16180339) option_rank
                    FROM (SELECT vbo.election_id, option_id, voters.voter_id 
                            FROM vote_ballot_options vbo
                            JOIN (SELECT DISTINCT election_id, voter_id FROM vote_cast_ballots) voters ON vbo.election_id = voters.election_id
                            WHERE vbo.election_id=".$ai_election_id."
                            ) vote_cast_ballots
                    LEFT JOIN vote_cast_ballots voter_option_ranks 
                        ON vote_cast_ballots.option_id    = voter_option_ranks.option_id 
                        AND vote_cast_ballots.election_id = voter_option_ranks.election_id 
                        AND vote_cast_ballots.voter_id    = voter_option_ranks.voter_id
                )
        SELECT DISTINCT a.election_id election_id, a.option_id first_option_id, b.option_id second_option_id,
            sum(case when a.option_rank-b.option_rank<0  then 1
                when a.option_rank-b.option_rank>=0 then 0
            end) over (partition by 1, a.election_id, a.option_id, b.option_id) pref_strength,
            sum(case when a.option_rank-b.option_rank<0  then 1
                when a.option_rank-b.option_rank>=0 then 0
            end) over (partition by 1, a.election_id, a.option_id, b.option_id) strongest_path
        FROM vote_cast_ballots_temp a
        LEFT OUTER JOIN vote_cast_ballots_temp b ON a.election_id = b.election_id AND a.voter_id = b.voter_id
        WHERE a.option_id<>b.option_id
        order by a.voter_id, a.option_id, b.option_id");

    // older version, should be functional for most elections excpet rare circumstances
    // $ls_msg = executesql("INSERT INTO vote_winner_calc (run_id, election_id, first_option_id, second_option_id, pref_strength, strongest_path)
    //     WITH vote_cast_ballots_temp AS
    //     (   SELECT vote_cast_ballots.election_id, vote_cast_ballots.voter_id, vote_cast_ballots.option_id, ifnull(voter_option_ranks.option_rank,16180339) option_rank
    //         FROM (SELECT vbo.election_id, option_id, voters.voter_id 
    //               FROM vote_ballot_options vbo
    //               JOIN (SELECT DISTINCT election_id, voter_id FROM vote_cast_ballots) voters ON vbo.election_id = voters.election_id
    //               WHERE vbo.election_id=".$ai_election_id."
    //               ) vote_cast_ballots
    //         LEFT JOIN vote_cast_ballots voter_option_ranks 
    //             ON vote_cast_ballots.option_id    = voter_option_ranks.option_id 
    //             AND vote_cast_ballots.election_id = voter_option_ranks.election_id 
    //             AND vote_cast_ballots.voter_id    = voter_option_ranks.voter_id
    //     )
    //     SELECT ".$ai_run_id." run_id, a.election_id election_id, a.option_id first_option_id, b.option_id second_option_id, count(1) pref_strength, count(1) strongest_path
    //     FROM vote_cast_ballots_temp a
    //     LEFT OUTER JOIN vote_cast_ballots_temp b ON a.election_id = b.election_id AND a.voter_id = b.voter_id
    //     WHERE a.option_id<>b.option_id
    //     AND a.option_rank<b.option_rank
    //     GROUP BY a.election_id, a.option_id, b.option_id
    //     ");

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id);
        return "Error inserting into vote_winner_calc. Error Message:\r\n".$ls_msg;
    } else {
        return $ls_msg;
    }
}

function f_strongest_path( $ai_election_id ){
/* 
    Calculate the strongest path for each option pair using modified Floyd–Warshall algorithm.

    Variables:
    ls_msg: string for holding error messages or return 'OK' for successful function completion
    la_options: array that stores IDs of options in the election
    pref_jk: stores preference for option j over k
    pref_ji: stores preference for option j over i
    pref_ik: stores preference for option i over k
    strongest_path: stores max preference value during iteration
*/

    $la_options = executeselect("SELECT DISTINCT option_id
                                 FROM vote_cast_ballots
                                 WHERE election_id = ".strval($ai_election_id)." 
                                 ORDER BY option_id"
                                );

    if(empty($la_options) or is_null($la_options)){
        rollback_winner_calc($ai_election_id);
        $ls_msg = "Error: Unable to retrieve options in election: ".strval($ai_election_id);
        return $ls_msg;
    };

    // clear audit table before the calc
    $ls_msg = executesql("DELETE FROM vote_winner_calc_audit WHERE election_id=".$ai_election_id);
    
    if($ls_msg <> "OK"){
        return "Error error deleting from vote_winner_calc_audit. Error message:\r\n".$ls_msg;
    }

    // modified Floyd–Warshall algorithm
    // compares the path from j to k against any alternative route from j to k through i
    for($i = 0; $i < count($la_options); $i++){
        for($j = 0; $j < count($la_options); $j++){
            if($i <> $j){
                for($k = 0; $k < count($la_options); $k++){
                    if($i<>$k and $j<>$k){
                        $pref_max = 0;
                        $pref_min = 0;

                        // fetch the preferences
                        $pref_jk = select_scalar(  "SELECT strongest_path
                                                    FROM vote_winner_calc
                                                    WHERE election_id    = " . strval($ai_election_id) . "
                                                    AND first_option_id  = " . strval($la_options[$j]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$k]['option_id'])
                                                );

                        $pref_ji = select_scalar(  "SELECT strongest_path
                                                    FROM vote_winner_calc
                                                    WHERE election_id    = " . strval($ai_election_id) . "
                                                    AND first_option_id  = " . strval($la_options[$j]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$i]['option_id'])
                                                );

                        $pref_ik = select_scalar(  "SELECT strongest_path
                                                    FROM vote_winner_calc
                                                    WHERE election_id    = " . strval($ai_election_id) . "
                                                    AND first_option_id  = " . strval($la_options[$i]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$k]['option_id'])
                                                );

                        // Compare the preferences. This is where the magic happens.
                        $strongest_path = max( $pref_jk, min( $pref_ji, $pref_ik ) );

                        // save the variables used for calc in audit table in case the processing logic needs to be checked
                        $ls_msg = executesql("INSERT INTO vote_winner_calc_audit (election_id, i,j,k, pref_jk, pref_ji, pref_ik, strongest_path)
                                              VALUES (".$ai_election_id.",".$i.",".$j.",".$k.",".$pref_jk.",".$pref_ji.",".$pref_ik.",".$strongest_path.")");
                    
                        if($ls_msg <> "OK"){
                            rollback_winner_calc($ai_election_id);
                            return "Error inserting into calc audit in strongest path calc. Error message:\r\n".$ls_msg;
                        }
                        
                        // update the strongest path
                        $ls_msg = executesql("UPDATE vote_winner_calc
                                                SET strongest_path    = " . strval($strongest_path) . "
                                                WHERE election_id     = " . strval($ai_election_id) . "
                                                AND first_option_id   = " . strval($la_options[$j]['option_id']) . "
                                                AND second_option_id  = " . strval($la_options[$k]['option_id']) 
                                            );

                        if($ls_msg <> "OK"){
                            rollback_winner_calc($ai_election_id);
                            return "Error updating vote_winner_calc in strongeth path calc. Error message:\r\n".$ls_msg;
                        }
                    }
                }
            }
        } 
    }

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id);
        return "Error during strongest path calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}

function f_winner_calc( $ai_election_id ){
/* 
    Calculate the winner of the election. 

    ls_msg: string for holding error messages or return 'OK' for successful function completion
    pref_ij: stores preference for option i over j
    pref_ji: stores preference for option j over i
    la_options: array that stores IDs of options in the election
    */

    $ls_msg = executesql(" INSERT INTO vote_election_winners (election_id, option_id)
                           SELECT ".$ai_election_id.", option_id
                           FROM vote_ballot_options
                           WHERE election_id=".$ai_election_id);

    if($ls_msg <> "OK"){
        return "Error inserting into vote_election_winners. Error message:\r\n".$ls_msg;
    }

    $la_options = executeselect("SELECT DISTINCT option_id
                                 FROM vote_cast_ballots
                                 WHERE election_id = ".strval($ai_election_id)
                                );

    if(empty($la_options) or is_null($la_options)){
        rollback_winner_calc($ai_election_id);
        $ls_msg = "Unable to retrieve options in election: " . strval($ai_election_id);
        return $ls_msg;
    };

    for($i = 0; $i < count($la_options); $i++){        
        if($ls_msg <> "OK"){
            return "Error updating vote_elections with winner. Error message:\r\n".$ls_msg;
        }

        for($j = 0; $j < count($la_options); $j++){
            if($i<>$j){
                // fetch strongest paths for the options
                $pref_ij = select_scalar(  "SELECT strongest_path
                                            FROM vote_winner_calc
                                            WHERE election_id      = ".strval($ai_election_id)."
                                            AND first_option_id    = ".strval($la_options[$i]['option_id'])."
                                            AND second_option_id   = ".strval($la_options[$j]['option_id'])
                                        ) ;

                $pref_ji = select_scalar(  "SELECT strongest_path
                                            FROM vote_winner_calc
                                            WHERE election_id      = " . strval($ai_election_id) . "
                                            AND first_option_id    = " . strval($la_options[$j]['option_id']) . "
                                            AND second_option_id   = " . strval($la_options[$i]['option_id'])
                                        ) ;

                // if there is an option with a higher strongest path over i then i is not the winner.
                // don't set j to winner because that option will be considered elsewhere in the loop
                if($pref_ji > $pref_ij){
                    $ls_msg = executesql("DELETE FROM vote_election_winners WHERE election_id=".$ai_election_id." AND option_id=".$la_options[$i]['option_id']);
                }
            }
        }
    }

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id);
        return "Error during final winner calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}

function rollback_winner_calc($ai_election_id){
    $ls_msg = executesql( "DELETE FROM vote_winner_calc WHERE election_id=".$ai_election_id);
    if($ls_msg<>"OK"){
        return "Error during rollback of winner calculation. Error Message:\r\n".$ls_msg;
    }
}
?>