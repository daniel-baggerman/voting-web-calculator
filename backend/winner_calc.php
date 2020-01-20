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

    // Get the next run_id
    $ln_run_id = select_scalar("select ifnull(max(run_id),0)+1 from vote_winner_calc where election_id = ".$ai_election_id);
    
    $ls_msg = f_initialize( $ai_election_id, $ln_run_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    $ls_msg = f_strongest_path( $ai_election_id, $ln_run_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    $ls_msg = f_winner_calc( $ai_election_id, $ln_run_id );
    if($ls_msg <> 'OK'){
        return $ls_msg;
    }

    // End winner calc and return success message
    return json_encode(["status" => "Success!",
                        "message" => "Winner Calc successful!",
                        "data" => []
                        ]);
}

function f_initialize( $ai_election_id, $ai_run_id ){
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
    // join the cast ballots table to itself on election_id and voter_id to get a crosswise comparison of each option on a voter's ballot
    // then count the number of times option A is ranked more highly than option B
    // update the pref_strength and strongest_path to the base preference strength from this count. The strongest path will be updated later.

    $ls_msg = executesql(" INSERT INTO vote_winner_calc (run_id, election_id, first_option_id, second_option_id, pref_strength, strongest_path)
                            SELECT ".$ai_run_id." run_id, a.election_id election_id, a.option_id first_option_id, b.option_id second_option_id, count(1) pref_strength, count(1) strongest_path
                            FROM vote_cast_ballots a
                            LEFT OUTER JOIN vote_cast_ballots b ON a.election_id = b.election_id AND a.voter_id = b.voter_id
                            WHERE a.election_id=".$ai_election_id."
                            AND a.option_id<>b.option_id
                            AND a.option_rank<b.option_rank
                            GROUP BY a.election_id, a.option_id, b.option_id");

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id, $ai_run_id);
        return "Error inserting into vote_winner_calc. Error Message:\r\n".$ls_msg;
    } else {
        return $ls_msg;
    }
}

function f_strongest_path( $ai_election_id, $ai_run_id ){
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
                                    WHERE election_id = " . strval($ai_election_id)." 
                                    ORDER BY option_id"
                                );

    if(empty($la_options) or is_null($la_options)){
        rollback_winner_calc($ai_election_id, $ai_run_id);
        $ls_msg = "Error: Unable to retrieve options in election: " . strval($ai_election_id);
        return $ls_msg;
    };

    // modified Floyd–Warshall algorithm
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
                                                    AND run_id           = " . strval($ai_run_id) . "
                                                    AND first_option_id  = " . strval($la_options[$j]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$k]['option_id'])
                                                );

                        $pref_ji = select_scalar(  "SELECT strongest_path
                                                    FROM vote_winner_calc
                                                    WHERE election_id    = " . strval($ai_election_id) . "
                                                    AND run_id           = " . strval($ai_run_id) . "
                                                    AND first_option_id  = " . strval($la_options[$j]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$i]['option_id'])
                                                );

                        $pref_ik = select_scalar(  "SELECT strongest_path
                                                    FROM vote_winner_calc
                                                    WHERE election_id    = " . strval($ai_election_id) . "
                                                    AND run_id           = " . strval($ai_run_id) . "
                                                    AND first_option_id  = " . strval($la_options[$i]['option_id']) . "
                                                    AND second_option_id = " . strval($la_options[$k]['option_id'])
                                                );

                        // Compare the preferences. This is where the magic happens.
                        $strongest_path = max( $pref_jk, min( $pref_ji, $pref_ik ) );

                        $ls_msg = executesql("UPDATE vote_winner_calc
                                                SET strongest_path    = " . strval($strongest_path) . "
                                                WHERE election_id     = " . strval($ai_election_id) . "
                                                AND run_id            = " . strval($ai_run_id) . "
                                                AND first_option_id   = " . strval($la_options[$j]['option_id']) . "
                                                AND second_option_id  = " . strval($la_options[$k]['option_id']) 
                                            );

                        if($ls_msg <> "OK"){
                            rollback_winner_calc($ai_election_id, $ai_run_id);
                            return "Error updating vote_winner_calc in strongeth path calc. Error message:\r\n".$ls_msg;
                        }

                        // save the variables used for calc in audit table in case the processing logic needs to be checked
                        $ls_msg = executesql("INSERT INTO vote_winner_calc_audit (run_id, election_id, i,j,k, pref_jk, pref_ji, pref_ik, strongest_path)
                                                VALUES (".$ai_election_id.", ".$ai_run_id.", ".$i.",".$j.",".$k.",".$pref_jk.",".$pref_ji.",".$pref_ik.",".$strongest_path.")");
                    
                        if($ls_msg <> "OK"){
                            // executesql( "delete from vote_winner_calc_audit where election_id = ".$ai_election_id." AND run_id = ".$ai_run_id )
                            rollback_winner_calc($ai_election_id, $ai_run_id);
                            return "Error inserting into calc audit in strongest path calc. Error message:\r\n".$ls_msg;
                        }
                    }
                }
            }
        } 
    }

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id, $ai_run_id);
        return "Error during strongest path calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}

function f_winner_calc( $ai_election_id, $ai_run_id ){
/* 
    Calculate the winner of the election. 

    ls_msg: string for holding error messages or return 'OK' for successful function completion
    pref_ij: stores preference for option i over j
    pref_ji: stores preference for option j over i
    la_options: array that stores IDs of options in the election
    */
    $la_options = executeselect("SELECT DISTINCT option_id
                                    FROM vote_cast_ballots
                                    WHERE election_id = ".strval($ai_election_id)
                                );

    if(empty($la_options) or is_null($la_options)){
        rollback_winner_calc($ai_election_id, $ai_run_id);
        $ls_msg = "Unable to retrieve options in election: " . strval($ai_election_id);
        return $ls_msg;
    };

    for($i = 0; $i < count($la_options); $i++){
        // start off assuming i is the winner and remove that if a stronger preference over i is found.
        $ls_msg = executesql("UPDATE vote_elections SET election_winner = " . strval($la_options[$i]['option_id']) . " WHERE election_id = " . strval($ai_election_id));
        
        if($ls_msg <> "OK"){
            return "Error updating vote_elections with winner. Error message:\r\n".$ls_msg;
        }

        for($j = 0; $j < count($la_options); $j++){
            if($i<>$j){
                // fetch strongest paths for the options
                $pref_ij = select_scalar(  "SELECT strongest_path
                                            FROM vote_winner_calc
                                            WHERE election_id      = " . strval($ai_election_id) . "
                                            AND run_id             = " . strval($ai_run_id) . "
                                            AND first_option_id    = " . strval($la_options[$i]['option_id']) . "
                                            AND second_option_id   = " . strval($la_options[$j]['option_id'])
                                        ) ;

                $pref_ji = select_scalar(  "SELECT strongest_path
                                            FROM vote_winner_calc
                                            WHERE election_id      = " . strval($ai_election_id) . "
                                            AND run_id             = " . strval($ai_run_id) . "
                                            AND first_option_id    = " . strval($la_options[$j]['option_id']) . "
                                            AND second_option_id   = " . strval($la_options[$i]['option_id'])
                                        ) ;

                // if there is an option with a higher strongest path over i then i is not the winner.
                // don't set j to winner because that option will be considered elsewhere in the loop
                if($pref_ji > $pref_ij){
                    $ls_msg = executesql("UPDATE vote_elections SET election_winner = ".$la_options[$j]['option_id']." WHERE election_id = " . strval($ai_election_id));
                }
            }
        }
    }

    if($ls_msg<>"OK"){
        rollback_winner_calc($ai_election_id, $ai_run_id);
        return "Error during final winner calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}

function rollback_winner_calc($ai_election_id, $ai_run_id){
    $ls_msg = executesql( "DELETE FROM vote_winner_calc WHERE election_id=".strval($ai_election_id)." AND run_id=".strval($ai_run_id) );
    if($ls_msg<>"OK"){
        return "Error during rollback of winner calculation. Error Message:\r\n".$ls_msg;
    }
}
?>