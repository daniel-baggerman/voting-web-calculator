<?php
include 'vote_db.php';

$ga_postdata = file_get_contents('php://input');

/* example of expected postdata
{
    desc: "This is a long description."
    end_date: "2020-04-09"
    name: "Test Ballot 3"
    options: "a;b;c;d"
    start_date: "2020-04-07" // optional
    public_private: "public" || "private"
    // these two if public
    password_protect: false
    password: arst // only if password_protect = true
    // this if private
    email: "adam@test.com,barb@test.com"
}
*/

if( !empty($ga_postdata) ){
    echo post_new_election($ga_postdata);
}
else{
    echo "No election data found.";
}

function post_new_election($election_data){
    $election_data = json_decode($election_data, true);

    if(json_last_error() != 0){
        return json_last_error_msg();
    };

    // insert election
    // check that the election name is unique
    $unique_check = select_scalar("select count(1) from vote_elections where description = ?"
                                    , array($election_data['name']));

    if(is_string($unique_check)){
        rollback_create_election($new_election_id);
        return "Error selecting election count for unique check. Error Message: \r\n".$unique_check;
    }

    if($unique_check !== 0){
        return "Cannot complete operation. Election name must be unique.";
    };
    
    // get new election_id
    $new_election_id = select_scalar("select max(election_id)+1 from vote_elections");

    // define the election description to be used in the URL.
    $url_election_name = $election_data['name'];
    $url_election_name = str_replace(' ','_',$url_election_name); // Replace spaces with underscores
    $url_election_name = str_replace(str_split(';,/?:@&=+$'),'',$url_election_name); // Remove special characters
    $url_election_name = strtolower(rawurlencode($url_election_name)); // Encode anything left and lower it

    // check that the url encoding is unique. If it's not unique, then try to add numbers to the end of it until it is unique.
    $unique_check = select_scalar("select count(1) from vote_elections where url_election_name = '".$url_election_name."'");

    if($unique_check !== 0){
        $i=0;
        while($i<10 and $break!=true){
            $i++;
            $unique_check = select_scalar("select count(1) from vote_elections where url_election_name = '".$url_election_name.$i."'");
            if($unique_check===0){
                $url_election_name = $url_election_name.$i;
                $break = true;
            }
            if($i==9){
                return "Pick a new election name."; // The user is so unoriginal that 9 other elections with the same name have been created.
            }
        }
    };

    // insert the data into the vote_elections table
    $sqls = "INSERT INTO vote_elections 
                (     election_id
                    , description
                    , start_date
                    , end_date
                    , long_description
                    , public_private
                    , password
                    , anon_results
                    , url_election_name)
                VALUES 
                (     ?
                    , ?
                    , date(?)
                    , date(?)
                    , ?
                    , ?
                    , ?
                    , ?
                    , ?
                )";

    $rtn = executesql($sqls,
                        [
                            $new_election_id,
                            $election_data['name'],
                            (array_has_key('start_date',$election_data) ? $election_data['start_date'] : ''),
                            $election_data['end_date'],
                            $election_data['desc'],
                            ($election_data['radioPublicPrivate'] == 'public' ? '1' : '0'),
                            ($election_data['radioPublicPrivate'] == 'public' ?
                                (array_has_key('password_protect',$election_data) ?
                                    ($election_data['password_protect'] ?
                                        (array_has_key('password',$election_data) ? 
                                            $election_data['password'] 
                                            : '' ) 
                                        : '')
                                    : '')
                                : '' ),
                            (array_has_key('anon',$election_data) ? ($election_data['anon'] ? '1' : '0') : '0'),
                            $url_election_name
                        ]
                    );

    if($rtn <> "OK"){
        rollback_create_election($new_election_id);
        return "Error inserting into vote_elections. Error Message: \r\n".$rtn;
    }

    // insert options

    // explode the options from $election_data into an array based on the ';' character, then use the strlen() function
    // to filter out any elements of the array that are empty or null. Then trim the resulting elements.
    $options = array_map('trim', array_filter( explode(';', $election_data['options']) ,'strlen') );

    foreach($options as $option){
        // check that option description is already in use. 
        // If it's not already in use, make a new one, else use the old one.
        $option_id = select_scalar('select option_id from vote_options where description=?',array($option));

        if( empty($option_id) ){
            // insert the option into vote_options
            $max_id = select_scalar('select max(option_id)+1 from vote_options');
            $rtn = executesql( 'insert into vote_options (option_id, description)
                                values (?, ?)' , array($max_id, $option));
            if($rtn <> 'OK'){
                rollback_create_election($new_election_id);
                return "Error inserting into vote_options. Error Message: \r\n".$rtn;
            }

            // make the option available on the ballot for the new election
            $rtn = executesql( 'insert into vote_ballot_options (election_id, option_id)
                                select '.$new_election_id.', '.$max_id.'
                                where not exists (select 1 from vote_ballot_options where election_id = '.$new_election_id.' and option_id = '.$max_id.')' );
            if($rtn <> 'OK'){
                rollback_create_election($new_election_id);
                return "Error inserting new option into vote_ballot_options. Error Message: \r\n".$rtn;
            }
        }
        else {
            // make the option available on the ballot for the new election
            $rtn = executesql( 'insert into vote_ballot_options (election_id, option_id)
                                select '.$new_election_id.', '.$option_id.'
                                where not exists (select 1 from vote_ballot_options where election_id = '.$new_election_id.' and option_id = '.$option_id.')' );
            if($rtn <> 'OK'){
                rollback_create_election($new_election_id);
                return "Error inserting into vote_ballot_options. Error Message: \r\n".$rtn;
            }
        }
    }

    // TODO: do something with email list passed

    return json_encode(["status" => "Success!",
                        "message" => "Election successfully created!",
                        "data" => []
                        ]);
}

function rollback_create_election($ai_election_id){
    $rtn = executesql('delete from vote_ballot_options where election_id = '.$ai_election_id);
    if($rtn <> 'OK'){
        return "Error during election creation rollback. Unable to delete from vote_ballot_options. Error Message: \r\n".$rtn;
    }

    $rtn = executesql('delete from vote_elections where election_id = '.$ai_election_id);
    if($rtn <> 'OK'){
        return "Error during election creation rollback. Unable to delete from vote_elections. Error Message: \r\n".$rtn;
    }
}
?>