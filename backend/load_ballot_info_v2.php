<?php
include 'vote_db.php';
function load_ballot_info($an_ballot_option_id){
    $bind_params = array($an_ballot_option_id);
    $data = executeselect("Select vo.description description, vo.option_id option_id, 1 enabled
                            from vote_ballot_options vbo 
                            join vote_options vo on vo.option_id = vbo.option_id
                            where vbo.election_id = ?
                            order by vo.description"
                          , false
                          , $bind_params);

    // if error occurred, return the error string
    if(is_string($data)){ 
        return $data;
    }
    // else convert array to json text and return it
    else{
        $output = json_encode($data, JSON_NUMERIC_CHECK);
        if($output === false){
            return "Error encoding sql select array as json.";
        }
        else{
            return $output;
        }
    }
}

if(isset($_GET['election_id'])){
    echo load_ballot_info($_GET['election_id']);
}
?>