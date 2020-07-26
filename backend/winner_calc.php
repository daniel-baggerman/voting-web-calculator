<?php
require 'vote_db.php';

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

    global $pdo_handle;
    
    $pdo_handle->beginTransaction();
    
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

    $pdo_handle->commit();

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
    $ls_msg = executesql( "DELETE from winner_calc where election_id = ".$ai_election_id );

    if($ls_msg<>"OK"){
        $pdo_handle->rollBack();
        return "Error clearing into winner_calc. Error Message:\r\n".$ls_msg;
    }

    // initialize winner calc with election and options info
    // cast_ballots_temp logic creates a table like cast_ballots but where options not ranked by a voter are placed in dead last tied on their ballot
    // join the cast ballots temp table (cast_ballots_temp) to itself on election_id and voter_id to get a crosswise comparison of each option on a voter's ballot
    // then count the number of times option A is ranked more highly than option B.
    // I used sum() instead of count() because count was excluding option pairs where no one ranked X above Y.
    // update the pref_strength and strongest_path to the base preference strength from this count. The strongest path will be updated later.

    $ls_msg = executesql("INSERT INTO winner_calc (election_id, first_option_id, second_option_id, pref_strength, strongest_path)
        WITH cast_ballots_temp AS
        (   SELECT cast_ballots.election_id, cast_ballots.voter_id, cast_ballots.option_id, coalesce(voter_option_ranks.option_rank,16180339) option_rank
            FROM (SELECT vbo.election_id, option_id, voters.voter_id 
                    FROM ballot_options vbo
                    JOIN (SELECT DISTINCT election_id, voter_id FROM cast_ballots) voters ON vbo.election_id = voters.election_id
                    WHERE vbo.election_id = ".$ai_election_id."
                    ) cast_ballots
            LEFT JOIN cast_ballots voter_option_ranks 
                ON cast_ballots.option_id    = voter_option_ranks.option_id 
                AND cast_ballots.election_id = voter_option_ranks.election_id 
                AND cast_ballots.voter_id    = voter_option_ranks.voter_id
        )
        SELECT DISTINCT a.election_id election_id, a.option_id first_option_id, b.option_id second_option_id,
            sum(case when a.option_rank-b.option_rank<0  then 1
                when a.option_rank-b.option_rank>=0 then 0
            end) over (partition by 1, a.election_id, a.option_id, b.option_id) pref_strength,
            sum(case when a.option_rank-b.option_rank<0  then 1
                when a.option_rank-b.option_rank>=0 then 0
            end) over (partition by 1, a.election_id, a.option_id, b.option_id) strongest_path
        FROM cast_ballots_temp a
        LEFT OUTER JOIN cast_ballots_temp b ON a.election_id = b.election_id AND a.voter_id = b.voter_id
        WHERE a.option_id<>b.option_id
        ORDER BY a.option_id, b.option_id");

    if($ls_msg<>"OK"){
        $pdo_handle->rollBack();
        return "Error inserting into winner_calc. Error Message:\r\n".$ls_msg;
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
                                 FROM cast_ballots
                                 WHERE election_id = ".strval($ai_election_id)." 
                                 ORDER BY option_id"
                                );

    if(empty($la_options) or is_null($la_options)){
        $pdo_handle->rollBack();
        $ls_msg = "Error: Unable to retrieve options in election: ".$ai_election_id;
        return $ls_msg;
    };

    // clear audit table before the calc
    $ls_msg = executesql("DELETE FROM winner_calc_audit WHERE election_id=".$ai_election_id);
    
    if($ls_msg <> "OK"){
        return "Error error deleting from winner_calc_audit. Error message:\r\n".$ls_msg;
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
                                                    FROM winner_calc
                                                    WHERE election_id    = " . $ai_election_id . "
                                                    AND first_option_id  = " . $la_options[$j]['option_id'] . "
                                                    AND second_option_id = " . $la_options[$k]['option_id']
                                                );

                        $pref_ji = select_scalar(  "SELECT strongest_path
                                                    FROM winner_calc
                                                    WHERE election_id    = " . $ai_election_id . "
                                                    AND first_option_id  = " . $la_options[$j]['option_id'] . "
                                                    AND second_option_id = " . $la_options[$i]['option_id']
                                                );

                        $pref_ik = select_scalar(  "SELECT strongest_path
                                                    FROM winner_calc
                                                    WHERE election_id    = " . $ai_election_id . "
                                                    AND first_option_id  = " . $la_options[$i]['option_id'] . "
                                                    AND second_option_id = " . $la_options[$k]['option_id']
                                                );

                        // Compare the preferences. This is where the magic happens.
                        $strongest_path = max( $pref_jk, min( $pref_ji, $pref_ik ) );

                        // save the variables used for calc in audit table in case the processing logic needs to be checked
                        $ls_msg = executesql("INSERT INTO winner_calc_audit (election_id, i,j,k, pref_jk, pref_ji, pref_ik, strongest_path)
                                              VALUES (".$ai_election_id.",".$i.",".$j.",".$k.",".$pref_jk.",".$pref_ji.",".$pref_ik.",".$strongest_path.")");
                    
                        if($ls_msg <> "OK"){
                            $pdo_handle->rollBack();
                            return "Error inserting into calc audit in strongest path calc. Error message:\r\n".$ls_msg;
                        }
                        
                        // update the strongest path
                        $ls_msg = executesql("UPDATE winner_calc
                                                SET strongest_path    = " . $strongest_path . "
                                                WHERE election_id     = " . $ai_election_id . "
                                                AND first_option_id   = " . $la_options[$j]['option_id'] . "
                                                AND second_option_id  = " . $la_options[$k]['option_id']
                                            );

                        if($ls_msg <> "OK"){
                            $pdo_handle->rollBack();
                            return "Error updating winner_calc in strongeth path calc. Error message:\r\n".$ls_msg;
                        }
                    }
                }
            }
        } 
    }

    if($ls_msg<>"OK"){
        $pdo_handle->rollBack();
        return "Error during strongest path calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}

function f_winner_calc( $ai_election_id ){
    /* 
    Calculate the winner of the election and the ranking of all the options.

    ls_msg: string for holding error messages or return 'OK' for successful function completion
    pref_ij: stores strongest path for option i over j
    pref_ji: stores strongest path for option j over i
    la_options: array that stores IDs of options in the election
    */

    $la_options = executeselect( "SELECT DISTINCT option_id FROM cast_ballots WHERE election_id = ".$ai_election_id );

    if(empty($la_options) or is_null($la_options)){
        $pdo_handle->rollBack();
        $ls_msg = "Unable to retrieve options in election: ".$ai_election_id;
        return $ls_msg;
    };

    // After an option is declared a winner in the for loop over rank below, it is added to the removed list so that the next loop will compare all options except the ones that have won already.
    $la_removed_options = [];
    $rank = 1;

    // Start by finding the first winner, rank 1.    
    while($rank <= count($la_options)){
        
        // Loop through each option
        for($i = 0; $i < count($la_options); $i++){

            // Skip this option, i, if it has been removed already.
            if(in_array($la_options[$i]['option_id'],$la_removed_options)){
                continue;
            }

            // Assume they are a winner until defeated later.
            $winner = true;

            // Compare i to every other option, j.
            for($j = 0; $j < count($la_options); $j++){
                if($i<>$j){

                    // Skip this comparison if option j has been removed already.
                    if(in_array($la_options[$j]['option_id'],$la_removed_options)){
                        continue;
                    }

                    // Fetch strongest paths for the options
                    $pref_ij = select_scalar(  "SELECT strongest_path
                                                FROM winner_calc
                                                WHERE election_id      = ".$ai_election_id."
                                                AND first_option_id    = ".$la_options[$i]['option_id']."
                                                AND second_option_id   = ".$la_options[$j]['option_id']
                                            );

                    $pref_ji = select_scalar(  "SELECT strongest_path
                                                FROM winner_calc
                                                WHERE election_id      = ".$ai_election_id."
                                                AND first_option_id    = ".$la_options[$j]['option_id']."
                                                AND second_option_id   = ".$la_options[$i]['option_id']
                                            );

                    // If j beats i, then i is not the winner.
                    if($pref_ji > $pref_ij){
                        $winner = false;
                    }
                }
            }

            // If i survived elimination, i is the winner 
            if($winner){
                array_push($la_removed_options,$la_options[$i]['option_id']);

                $ls_msg = executesql("UPDATE ballot_options
                                      SET rank = ".$rank."
                                      WHERE election_id = ".$ai_election_id."
                                      AND option_id = ".$la_options[$i]['option_id']);
                if($ls_msg <> "OK"){
                    $pdo_handle->rollBack();
                    return "Error updating option rank. Error message:\r\n".$ls_msg;
                }

                $rank++;
            }
        }
    }

    if($ls_msg<>"OK"){
        $pdo_handle->rollBack();
        return "Error during final winner calculation. Error Message:\r\n".$ls_msg;
    }
    else{
        return $ls_msg;
    }
}
?>