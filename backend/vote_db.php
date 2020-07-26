<?php

require '../vendor/autoload.php';
require 'connection.php';
use RCVvote\Connection as Connection;

try {
    $pdo_handle = Connection::get()->connect();
} catch (\PDOException $e) {
    echo $e->getMessage();
}

// Functions to save and retrieve vote values
// https://www.php.net/manual/en/book.pdo.php

// $pdo_db_file = "/home/daniel/public_html/votedb.sq3";
// $pdo_db_name = "sqlite:".$pdo_db_file;

// // If database doesn't exist then create it
// if (file_exists($pdo_db_file) == false){
//     new_vote_db();
//     echo "Created ".$pdo_db_name." database<br>\n";
// }
// // Database file exists so open it
// else{
//     try {
//         $pdo_handle  = new PDO($pdo_db_name);
//         $pdo_handle->exec( 'PRAGMA foreign_keys = ON;' );
//         $pdo_handle->exec( 'PRAGMA case_sensitive_like=ON;' );
//     }
//     catch (PDOException $e){
//         echo 'Caught exception: ',  $e->getMessage(), "\n";
//     }
// }

// ----------------------------------------------------------------------------
function executesql($sqls,$aa_bind_params = NULL)
// executes dml and ddl. Will also execute Select statement but will not error or return any message about it.
{
    /*
        Anything that takes input from the user should use bind params to avoid errors with quotes and possible code injection.
    */
    global $pdo_handle;

    $stmt = $pdo_handle->prepare($sqls);

    if ($stmt === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL script failed on prepare.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }

    // check if bind params are null to determine what kind of execute to do.
    if (is_null($aa_bind_params)){
        $result = $stmt->execute();
    }
    else{
        $result = $stmt->execute($aa_bind_params);
    }
    
    // if the select statement errored, return an error message.
    if ($result === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL script failed on execute.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }
    else{
        return "OK";
    }
} // end executesql()

// ----------------------------------------------------------------------------
function executeselect($sqls, $ab_fetch_column = NULL, array $aa_bind_params = NULL)
// Executes select statement and returns the data as an array of rows. Each row is an array of the data indexed by column_name. Returns a string if it errors.
{
    /*
    $sqls can be defined "plainly" without any retrieval arguments by leaving the bind params array ($aa_bind_params) null.
    If $aa_bind_params is not null, it will attempt to bind the parameters to the SQL string. Anything that takes input from
    the user should use bind params to avoid errors with quotes and possible code injection.
    See https://www.php.net/manual/en/pdostatement.execute.php for designing select statements with bind params for execute().

    ab_fetch_column can be set to true to change the fetch behavior to return a one-dimensional array useful for fetching just one column. https://phpdelusions.net/pdo/fetch_modes#FETCH_COLUMN
    */

    global $pdo_handle;

    // prepare the passed statement
    $stmt = $pdo_handle->prepare($sqls);

    if ($stmt === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL Select script failed on prepare.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }

    // bind variables if provided
    if (!is_null($aa_bind_params)){
        foreach($aa_bind_params as $bind_var => $bind_val){
            if(array_keys($aa_bind_params) !== range(0, count($aa_bind_params) - 1)){ //check if array is indexed by number or string, allows for '?' parameter binding
                $rtn = $stmt->bindValue($bind_var, $bind_val);
            } else {
                $rtn = $stmt->bindValue($bind_var+1, $bind_val);
            }
            if($rtn === false){
                return "Error: SQL Select script faild on binding parameters to prepared statement.";
            }
        }
    }

    // check if bind params are null to determine what kind of select to do.
    $result = $stmt->execute();
    
    // if the select statement errored, return an error message.
    if ($result === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL Select script failed on execute.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }
    
    // retrieve the data
    if ($ab_fetch_column == false){
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    elseif ($ab_fetch_column == true){
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_COLUMN);
    }

    // check if data retrieval worked
    if ($data === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL Select script failed on fetch.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2];
    }
    else{
        return $data;
    }
} // end executeselect()

function select_scalar($sqls,$aa_bind_params = NULL)
// Executes a select statement that should return one column and one row and then returns that value
// I should probably put in a rowcount check or something but that would require parsing the string to replace the column with count(1)
{
    global $pdo_handle;

    // prepare the passed statement
    $stmt = $pdo_handle->prepare($sqls);

    if ($stmt === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL Select scalar script failed on prepare.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }

    // check if bind params are null to determine what kind of select to do.
    if (is_null($aa_bind_params)){
        $result = $stmt->execute();
    }
    else{
        $result = $stmt->execute($aa_bind_params);
    }
    
    // if the select statement errored, return an error message.
    if ($result === false){
        $err = $pdo_handle->errorInfo();
        return "Error: SQL Select scalar script failed on execute.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }
    
    // retrieve the data
    $data = $stmt->fetchColumn(0);
    // check if data retrieval returned anything.
    if ($data === false){
        return null;
        // $err = $pdo_handle->errorInfo();
        // return "Error: SQL Select scalar script failed on fetch.\r\nError Code: ".$err[1]."\r\nError Message: ".$err[2]."\r\nSQL: ".$sqls;
    }
    else{
        return intval($data);
    }
}

// ----------------------------------------------------------------------------
function selecttotable($data, $table_id = NULL)
// takes result set of select statement and returns it as a string with formatted html table
// potential security risk inserting the database value directly into the html string
{
    $output = "<table id='$table_id'>";
    foreach($data as $key => $row)
    {
        if($key===0) { // first item in array, output the table headers from the array keys as well as the first row of data
            $output .= "<tr id='$table_id|$key'>";
            foreach($row as $col => $val) {
                $output .= "<th id='$col'>$col</th>";
            }
            $output .= "</tr><tr id='$table_id|$key'>";
            foreach($row as $col => $val) {
                $output .= "<td id='$table_id|$col|$key' hearders='$col'>$val</td>";
            }
            $output .= '</tr>';
        }
        else { // output the rest of the rows
            $output .= "<tr id='$table_id|$key'>";
            foreach($row as $col => $val) {
                $output .= "<td id='$table_id|$col|$key' hearders='$col'>$val</td>";
            }
            $output .= '</tr>';
        }
    }
    $output .= '</table>';
    return $output;  
}

// ----------------------------------------------------------------------------
function new_vote_db($type = NULL){
    global $pdo_db_file;
    global $pdo_db_name;
    global $pdo_handle;
    
    // Delete any existing database file
    echo "Delete ".$pdo_db_file." database<br>\n";
    if ($type==='refresh') {
        unlink($pdo_db_file);
    }
    
    // Try to make a new SQLITE database
    try {
        $pdo_handle  = new PDO($pdo_db_name);
        $pdo_handle->exec( 'PRAGMA foreign_keys = ON;' );
        $pdo_handle->exec( 'PRAGMA case_sensitive_like=ON;' );
    }
    catch (PDOException $e){
        echo "Caught exception: ",  $e->getMessage(), "\n";
    }
    // Create the database structure    
    $pdo_handle->exec("CREATE TABLE vote_options
                        (
                          option_id         integer,
                          time_stamp        text,
                          description       text,
                          primary key (option_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_options_time_audit_up
                        after update on vote_options
                        for each row
                        begin
                            update vote_options 
                            set time_stamp = datetime('now','localtime') 
                            where option_id = new.option_id);
                        end");

    $pdo_handle->exec("create trigger tr_vote_options_time_audit_in
                        after insert on vote_options
                        for each row
                        begin
                            update vote_options 
                            set time_stamp = datetime('now','localtime') 
                            where option_id = new.option_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_elections
                        (
                          election_id       integer,
                          time_stamp        DATE,
                          description       text,
                          long_description  text,
                          start_date        date,
                          end_date          date default '2068-12-31',
                          public_private    integer default 0,
                          password_protect  integer default 0,
                          password          text,
                          url_election_name text UNIQUE,
                          allow_write_ins   integer,
                          anon_results      integer,
                          primary key (election_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_elections_time_audit_up
                        after update on vote_elections
                        for each row
                        begin
                            update vote_elections
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id;
                        end");

    $pdo_handle->exec("create trigger tr_vote_elections_time_audit_in
                        after insert on vote_elections
                        for each row
                        begin
                            update vote_elections
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_ballot_options
                        (
                          election_id   integer,
                          option_id     integer,
                          rank          integer,
                          time_stamp    text,
                          primary key (election_id,option_id),
                          foreign key (election_id) references vote_elections(election_id),
                          foreign key (option_id) references vote_options(option_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_ballot_options_time_audit_up
                        after update on vote_ballot_options
                        for each row
                        begin
                            update vote_ballot_options
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and option_id = new.option_id;
                        end");

    $pdo_handle->exec("create trigger tr_vote_ballot_options_time_audit_in
                        after insert on vote_ballot_options
                        for each row
                        begin
                            update vote_ballot_options
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and option_id = new.option_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_voters
                        (
                          voter_id      integer,
                          time_stamp    text,
                          voter_name    text UNIQUE,
                          primary key (voter_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_voters_time_audit_up
                        after update on vote_voters
                        for each row
                        begin
                            update vote_voters
                            set time_stamp = datetime('now','localtime')
                            where voter_id = new.voter_id;
                        end");

    $pdo_handle->exec("CREATE trigger tr_vote_voters_time_audit_in
                        after insert on vote_voters
                        for each row
                        begin
                            update vote_voters
                            set time_stamp = datetime('now','localtime')
                            where voter_id = new.voter_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_cast_ballots
                        (
                          cast_ballot_id    integer,
                          time_stamp        text,
                          election_id       integer,
                          voter_id          integer,
                          option_id         integer,
                          option_rank       integer,
                          primary key (cast_ballot_id),
                          foreign key (election_id) references vote_elections(election_id),
                          foreign key (voter_id) references vote_voters(voter_id),
                          foreign key (option_id) references vote_options(option_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_cast_ballots_time_audit_up
                        after update on vote_cast_ballots
                        for each row
                        begin
                            update vote_cast_ballots
                            set time_stamp = datetime('now','localtime')
                            where cast_ballot_id = new.cast_ballot_id;
                        end");

    $pdo_handle->exec("create trigger tr_vote_cast_ballots_time_audit_in
                        after insert on vote_cast_ballots
                        for each row
                        begin
                            update vote_cast_ballots
                            set time_stamp = datetime('now','localtime')
                            where cast_ballot_id = new.cast_ballot_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_winner_calc
                        (
                          election_id       integer,
                          first_option_id   integer,
                          second_option_id  integer,
                          pref_strength     integer,
                          strongest_path    integer,
                          time_stamp        text,
                          primary key (election_id, first_option_id, second_option_id),
                          foreign key (election_id) references vote_elections(election_id),
                          foreign key (first_option_id) references vote_options(option_id),
                          foreign key (second_option_id) references vote_options(option_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_winner_calc_time_audit_up
                        after update on vote_winner_calc
                        for each row
                        begin
                            update vote_winner_calc
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and first_option_id = new.first_option_id
                            and second_option_id = new.second_option_id;
                        end");

    $pdo_handle->exec("create trigger tr_vote_winner_calc_time_audit_in
                        after insert on vote_winner_calc
                        for each row
                        begin
                            update vote_winner_calc
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and first_option_id = new.first_option_id
                            and second_option_id = new.second_option_id;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_winner_calc_audit
                        (
                          election_id    integer,
                          i              integer,
                          j              integer,
                          k              integer,
                          pref_jk        integer,
                          pref_ji        integer,
                          pref_ik        integer,
                          strongest_path integer,
                          time_stamp     text
                        )");

    $pdo_handle->exec("create trigger tr_vote_winner_calc_audit_time_audit_up
                        after update on vote_winner_calc_audit
                        for each row
                        begin
                            update vote_winner_calc_audit
                            set time_stamp = datetime('now','localtime')
                            where i = new.i
                            and j = new.j
                            and k = new.k;
                        end");

    $pdo_handle->exec("create trigger tr_vote_winner_calc_audit_time_audit_in
                        after insert on vote_winner_calc_audit
                        for each row
                        begin
                            update vote_winner_calc_audit
                            set time_stamp = datetime('now','localtime')
                            where i = new.i
                            and j = new.j
                            and k = new.k;
                        end");

    $pdo_handle->exec("CREATE TABLE vote_election_voter_list
                        (
                          election_id   integer,
                          voter_id      integer,
                          time_stamp    text,
                          primary key (election_id, voter_id),
                          foreign key (election_id) references vote_elections(election_id),
                          foreign key (voter_id) references vote_voters(voter_id)
                        )");

    $pdo_handle->exec("create trigger tr_vote_election_voter_list_time_audit_up
                        after update on vote_election_voter_list
                        for each row
                        begin
                            update vote_election_voter_list
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and voter_id = new.voter_id;
                        end");

    $pdo_handle->exec("create trigger tr_vote_election_voter_list_time_audit_in
                        after insert on vote_election_voter_list
                        for each row
                        begin
                            update vote_election_voter_list
                            set time_stamp = datetime('now','localtime')
                            where election_id = new.election_id
                            and voter_id = new.voter_id;
                        end");
    
    //insert dummy data
    $pdo_handle->exec("INSERT INTO vote_elections (election_id, description, public_private, password_protect, password, url_election_name) 
                        SELECT 1, 'Test Ballot 1', 1, 1, 'arst', 'test_ballot_1' union 
                        select 2, 'Test Ballot 2', null, null, null, 'test_ballot_2' ");

    $pdo_handle->exec("INSERT INTO vote_voters (voter_id, voter_name)
                        SELECT 1,'Daniel'  UNION
                        SELECT 2,'Emil'  UNION
                        SELECT 3,'Jess'  UNION
                        SELECT 4,'Colin' ");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 5,NULL,'Schulze5'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 6,NULL,'Schulze6'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 7,NULL,'Schulze7'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 8,NULL,'Schulze8'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 9,NULL,'Schulze9'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 10,NULL,'Schulze10'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 11,NULL,'Schulze11'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 12,NULL,'Schulze12'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 13,NULL,'Schulze13'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 14,NULL,'Schulze14'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 15,NULL,'Schulze15'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 16,NULL,'Schulze16'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 17,NULL,'Schulze17'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 18,NULL,'Schulze18'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 19,NULL,'Schulze19'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 20,NULL,'Schulze20'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 21,NULL,'Schulze21'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 22,NULL,'Schulze22'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 23,NULL,'Schulze23'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 24,NULL,'Schulze24'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 25,NULL,'Schulze25'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 26,NULL,'Schulze26'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 27,NULL,'Schulze27'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 28,NULL,'Schulze28'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 29,NULL,'Schulze29'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 30,NULL,'Schulze30'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 31,NULL,'Schulze31'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 32,NULL,'Schulze32'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 33,NULL,'Schulze33'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 34,NULL,'Schulze34'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 35,NULL,'Schulze35'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 36,NULL,'Schulze36'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 37,NULL,'Schulze37'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 38,NULL,'Schulze38'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 39,NULL,'Schulze39'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 40,NULL,'Schulze40'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 41,NULL,'Schulze41'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 42,NULL,'Schulze42'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 43,NULL,'Schulze43'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 44,NULL,'Schulze44'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 45,NULL,'Schulze45'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 46,NULL,'Schulze46'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 47,NULL,'Schulze47'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 48,NULL,'Schulze48'");
    $pdo_handle->exec("INSERT INTO vote_voters SELECT 49,NULL,'Schulze49'");

    $pdo_handle->exec("INSERT INTO vote_options (option_id, description)
                        SELECT 1,'Aachi''s Indian'  UNION
                        SELECT 2,'Bowl Lab'  UNION
                        SELECT 3,'Chipotle'  UNION
                        SELECT 4,'Doc Green''s'  UNION
                        SELECT 5,'Einstein''s Bagels'");

    $pdo_handle->exec("INSERT INTO vote_ballot_options (election_id,option_id)
                        SELECT 1, option_id FROM vote_options");

    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 1,NULL,1,1,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 2,NULL,1,1,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 3,NULL,1,1,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 4,NULL,1,1,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 5,NULL,1,1,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 6,NULL,1,2,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 7,NULL,1,2,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 8,NULL,1,2,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 9,NULL,1,2,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 10,NULL,1,2,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 11,NULL,1,3,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 12,NULL,1,3,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 13,NULL,1,3,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 14,NULL,1,3,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 15,NULL,1,3,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 16,NULL,1,4,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 17,NULL,1,4,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 18,NULL,1,4,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 19,NULL,1,4,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 20,NULL,1,4,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 21,NULL,1,5,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 22,NULL,1,5,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 23,NULL,1,5,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 24,NULL,1,5,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 25,NULL,1,5,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 26,NULL,1,6,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 27,NULL,1,6,4,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 28,NULL,1,6,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 29,NULL,1,6,3,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 30,NULL,1,6,2,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 31,NULL,1,7,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 32,NULL,1,7,4,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 33,NULL,1,7,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 34,NULL,1,7,3,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 35,NULL,1,7,2,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 36,NULL,1,8,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 37,NULL,1,8,4,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 38,NULL,1,8,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 39,NULL,1,8,3,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 40,NULL,1,8,2,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 41,NULL,1,9,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 42,NULL,1,9,4,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 43,NULL,1,9,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 44,NULL,1,9,3,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 45,NULL,1,9,2,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 46,NULL,1,10,1,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 47,NULL,1,10,4,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 48,NULL,1,10,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 49,NULL,1,10,3,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 50,NULL,1,10,2,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 51,NULL,1,11,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 52,NULL,1,11,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 53,NULL,1,11,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 54,NULL,1,11,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 55,NULL,1,11,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 56,NULL,1,12,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 57,NULL,1,12,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 58,NULL,1,12,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 59,NULL,1,12,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 60,NULL,1,12,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 61,NULL,1,13,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 62,NULL,1,13,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 63,NULL,1,13,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 64,NULL,1,13,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 65,NULL,1,13,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 66,NULL,1,14,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 67,NULL,1,14,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 68,NULL,1,14,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 69,NULL,1,14,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 70,NULL,1,14,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 71,NULL,1,15,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 72,NULL,1,15,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 73,NULL,1,15,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 74,NULL,1,15,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 75,NULL,1,15,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 76,NULL,1,16,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 77,NULL,1,16,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 78,NULL,1,16,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 79,NULL,1,16,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 80,NULL,1,16,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 81,NULL,1,17,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 82,NULL,1,17,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 83,NULL,1,17,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 84,NULL,1,17,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 85,NULL,1,17,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 86,NULL,1,18,2,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 87,NULL,1,18,5,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 88,NULL,1,18,4,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 89,NULL,1,18,1,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 90,NULL,1,18,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 91,NULL,1,19,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 92,NULL,1,19,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 93,NULL,1,19,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 94,NULL,1,19,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 95,NULL,1,19,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 96,NULL,1,20,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 97,NULL,1,20,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 98,NULL,1,20,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 99,NULL,1,20,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 100,NULL,1,20,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 101,NULL,1,21,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 102,NULL,1,21,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 103,NULL,1,21,2,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 104,NULL,1,21,5,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 105,NULL,1,21,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 106,NULL,1,22,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 107,NULL,1,22,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 108,NULL,1,22,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 109,NULL,1,22,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 110,NULL,1,22,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 111,NULL,1,23,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 112,NULL,1,23,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 113,NULL,1,23,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 114,NULL,1,23,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 115,NULL,1,23,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 116,NULL,1,24,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 117,NULL,1,24,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 118,NULL,1,24,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 119,NULL,1,24,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 120,NULL,1,24,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 121,NULL,1,25,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 122,NULL,1,25,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 123,NULL,1,25,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 124,NULL,1,25,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 125,NULL,1,25,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 126,NULL,1,26,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 127,NULL,1,26,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 128,NULL,1,26,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 129,NULL,1,26,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 130,NULL,1,26,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 131,NULL,1,27,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 132,NULL,1,27,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 133,NULL,1,27,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 134,NULL,1,27,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 135,NULL,1,27,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 136,NULL,1,28,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 137,NULL,1,28,1,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 138,NULL,1,28,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 139,NULL,1,28,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 140,NULL,1,28,4,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 141,NULL,1,29,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 142,NULL,1,29,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 143,NULL,1,29,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 144,NULL,1,29,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 145,NULL,1,29,5,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 146,NULL,1,30,3,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 147,NULL,1,30,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 148,NULL,1,30,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 149,NULL,1,30,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 150,NULL,1,30,5,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 151,NULL,1,31,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 152,NULL,1,31,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 153,NULL,1,31,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 154,NULL,1,31,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 155,NULL,1,31,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 156,NULL,1,32,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 157,NULL,1,32,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 158,NULL,1,32,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 159,NULL,1,32,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 160,NULL,1,32,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 161,NULL,1,33,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 162,NULL,1,33,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 163,NULL,1,33,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 164,NULL,1,33,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 165,NULL,1,33,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 166,NULL,1,34,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 167,NULL,1,34,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 168,NULL,1,34,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 169,NULL,1,34,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 170,NULL,1,34,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 171,NULL,1,35,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 172,NULL,1,35,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 173,NULL,1,35,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 174,NULL,1,35,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 175,NULL,1,35,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 176,NULL,1,36,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 177,NULL,1,36,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 178,NULL,1,36,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 179,NULL,1,36,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 180,NULL,1,36,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 181,NULL,1,37,4,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 182,NULL,1,37,3,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 183,NULL,1,37,5,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 184,NULL,1,37,2,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 185,NULL,1,37,1,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 186,NULL,1,38,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 187,NULL,1,38,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 188,NULL,1,38,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 189,NULL,1,38,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 190,NULL,1,38,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 191,NULL,1,39,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 192,NULL,1,39,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 193,NULL,1,39,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 194,NULL,1,39,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 195,NULL,1,39,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 196,NULL,1,40,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 197,NULL,1,40,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 198,NULL,1,40,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 199,NULL,1,40,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 200,NULL,1,40,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 201,NULL,1,41,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 202,NULL,1,41,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 203,NULL,1,41,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 204,NULL,1,41,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 205,NULL,1,41,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 206,NULL,1,42,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 207,NULL,1,42,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 208,NULL,1,42,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 209,NULL,1,42,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 210,NULL,1,42,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 211,NULL,1,43,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 212,NULL,1,43,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 213,NULL,1,43,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 214,NULL,1,43,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 215,NULL,1,43,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 216,NULL,1,44,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 217,NULL,1,44,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 218,NULL,1,44,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 219,NULL,1,44,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 220,NULL,1,44,3,5");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 221,NULL,1,45,5,1");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 222,NULL,1,45,2,2");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 223,NULL,1,45,1,3");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 224,NULL,1,45,4,4");
    $pdo_handle->exec("INSERT INTO vote_cast_ballots SELECT 225,NULL,1,45,3,5");
    }
?>