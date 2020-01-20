<?php
include 'vote_db.php';

function execute_sql($sqls,$table_id = NULL){
    // if string texarea string is empty, warn user and do nothing
    if ($sqls == "")
        echo "<h3>SQL statement is blank.</h3>";
    // else check if it's a select statement or dml/ddl and pass it to the right function
    elseif (strpos(trim(strtolower($sqls)),"select") === 0){
        $data = executeselect($sqls);
        
        // executeselect returns error string if it errors and data array if it doesn't (shut up I know that's bad design)
        if (is_string($data) == true) {
            //if the data is a string it is an error message
            echo $data;
        }
        else {
            //if it's not, it's real data to be converted to an html table
            echo selecttotable($data, $table_id);
        }
    }
    else{
        // execute the dml/ddl
        $result = executesql($sqls);
        if ($result <> "OK")
            echo $result;
        else
            echo "<h3>SQL run successfully.</h3>";
    }
}

if ( isset($_GET['sqls']) and isset($_GET['table_id']) ){
    execute_sql($_GET['sqls'], $_GET['table_id']);
}
else if (isset($_GET['sqls']) and !isset($_GET['table_id'])){
    execute_sql($_GET['sqls']);
}

?>