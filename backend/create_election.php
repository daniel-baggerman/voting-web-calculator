<?php
require 'vote_db.php';

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
    http_response_code(400);
    echo json_encode(["status" => "Failure :(",
                        "message" => "No election data found.",
                        "data" => []
                        ]);
}

function post_new_election($election_data){
    global $pdo_handle;
    
    $pdo_handle->beginTransaction();

    $election_data = json_decode($election_data, true);

    if(json_last_error() != 0){
        http_response_code(500);
        return json_encode(["status" => "Failure :(",
                            "message" => json_last_error_msg(),
                            "data" => []
                            ]);
    };

    // Commented out to allow duplicate election names for now.
    // // insert election
    // // check that the election name is unique
    // $unique_check = select_scalar("SELECT count(1) FROM elections WHERE description = ?"
    //                                 , array($election_data['name']));

    // if(is_string($unique_check)){
    //     $pdo_handle->rollBack();
    //     http_response_code(500);
    //     return json_encode(["status" => "Failure :(",
    //                         "message" => "Error selecting election count for unique check. Error Message: \r\n".$unique_check,
    //                         "data" => []
    //                         ]);
    // };
    
    // if($unique_check !== 0){
    //     http_response_code(400);
    //     return json_encode(["status" => "Failure :(",
    //                         "message" => "Cannot complete operation. Election name must be unique.",
    //                         "data" => []
    //                         ]);
    // };

    // define the election description to be used in the URL.
    $url_election_name = $election_data['name'];
    $url_election_name = str_replace(' ','_',$url_election_name); // Replace spaces with underscores
    $url_election_name = str_replace(str_split(';,/?:@&=+$'),'',$url_election_name); // Remove special characters
    $url_election_name = strtolower(rawurlencode($url_election_name)); // Encode anything left and lower it

    // check that the url encoding is unique. If it's not unique, then try to add numbers to the end of it until it is unique.
    $unique_check = select_scalar("SELECT count(1) FROM elections WHERE url_election_name = ?",[$url_election_name]);

    if($unique_check !== 0){
        $i=0;
        while($i<100){
            $i++;
            $unique_check = select_scalar("SELECT count(1) FROM elections WHERE url_election_name = ?",[$url_election_name.$i]);
            if($unique_check===0){
                $url_election_name = $url_election_name.$i;
                break;
            }
            if($i==9){
                return json_encode(["status" => "Failure :(",
                                    "message" => "Pick a new election name.",
                                    "data" => []
                                    ]); // The user is so unoriginal that 99 other elections with the same name have been created.
            }
        }
    };

    // insert the data into the elections table
    $sqls = "INSERT INTO elections 
                (     election_id
                    , description
                    , start_date
                    , end_date
                    , long_description
                    , public_private
                    , password_protect
                    , password
                    , url_election_name)
                VALUES 
                (     nextval('election_id_seq')
                    , ?
                    , to_date(?,'YYYY-MM-DD')
                    , to_date(?,'YYYY-MM-DD')
                    , ?
                    , ?
                    , ?
                    , ?
                    , ?
                )";

    $rtn = executesql($sqls,
                        [   $election_data['name'],
                            (array_key_exists('start_date',$election_data) ? $election_data['start_date'] : '1970-01-01'),
                            $election_data['end_date'],
                            $election_data['desc'],
                            ($election_data['public_private'] == 'public' ? '1' : '0'),
                            (array_key_exists('password_protect',$election_data) ? ($election_data['password_protect'] ? 1 : 0) : 0 ),
                            ($election_data['public_private'] == 'public' ?
                                (array_key_exists('password_protect',$election_data) ?
                                    ($election_data['password_protect'] ?
                                        (array_key_exists('password',$election_data) ? 
                                            $election_data['password'] 
                                            : '' ) 
                                        : '')
                                    : '')
                                : '' ),
                            $url_election_name
                        ]
                    );

    if($rtn <> "OK"){
        $pdo_handle->rollBack();
        return json_encode(["status" => "Failure :(",
                            "message" => "Error inserting into elections. Error Message: \r\n".$rtn,
                            "data" => []
                            ]);
    }

    // insert options

    $new_election_id = select_scalar('SELECT election_id FROM elections WHERE url_election_name = ?',[$url_election_name]);

    // explode the options from $election_data into an array based on the ';' character, then use the strlen() function
    // to filter out any elements of the array that are empty or null. Then trim the resulting elements.
    $options = array_map('trim', array_filter( explode(';', $election_data['options']) ,'strlen') );

    foreach($options as $option){
        // check that option description is already in use. 
        // If it's not already in use, make a new one, else use the old one.
        $option_id = select_scalar('SELECT option_id FROM options WHERE description = ?',[$option]);

        if( empty($option_id) ){
            // insert the option into options
            $rtn = executesql( "INSERT INTO options (option_id, description)
                                VALUES (nextval('option_id_seq'), ?)" , [$option]);
            if($rtn <> 'OK'){
                $pdo_handle->rollBack();
                return json_encode(["status" => "Failure :(",
                                    "message" => "Error inserting into options. Error Message: \r\n".$rtn,
                                    "data" => []
                                    ]);
            }

            $option_id = select_scalar("SELECT option_id FROM options WHERE description = ?",[$option]);   
        }

        // make the option available on the ballot for the new election
        $rtn = executesql( 'INSERT INTO ballot_options (election_id, option_id)
                            SELECT '.$new_election_id.', '.$option_id.'
                            WHERE not exists (SELECT 1 FROM ballot_options WHERE election_id = '.$new_election_id.' and option_id = '.$option_id.')' );
        if($rtn <> 'OK'){
            $pdo_handle->rollBack();
            return json_encode(["status" => "Failure :(",
                                "message" => "Error inserting new option into ballot_options. Error Message: \r\n".$rtn,
                                "data" => []
                                ]);
        }
    }

    $pdo_handle->commit();

    // TODO: do something with email list passed

    $return_election_details = executeselect("  SELECT  description, 
                                                        long_description, 
                                                        to_char(start_date,'YYYY-MM-DD') start_date, 
                                                        to_char(end_date,'YYYY-MM-DD') end_date, 
                                                        public_private, 
                                                        password_protect, 
                                                        url_election_name
                                                FROM elections
                                                WHERE election_id = ".$new_election_id );

    $return_options = executeselect("   SELECT o.description option_name
                                        FROM ballot_options bo
                                        JOIN elections e on bo.election_id = e.election_id
                                        JOIN options o on o.option_id = bo.option_id
                                        WHERE bo.election_id = ".$new_election_id."
                                        ORDER BY o.description", true );

    return json_encode(["status" => "Success!",
                        "message" => "Election successfully created!",
                        "data" => ["election" => $return_election_details[0],
                                    "options" => $return_options]
                        ]);
}
?>