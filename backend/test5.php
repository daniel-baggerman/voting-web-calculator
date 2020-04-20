<?php
include 'vote_db.php';
?>

<!DOCTYPE HTML>

<html>

    <head>
        <title>w_sql_command</title>
        <link rel="stylesheet" type="text/css" href="styles.css">
    </head>

    <body>
        <?php echo date('d M Y H:i:s'); ?><br>
        <h3>SQL</h3>

        <form id='sqlinjection' method='POST' action='test5.php'>
            <textarea id='textarea_sql' form='sqlinjection' rows=15 cols=150></textarea>
            <button id='execute' type='execute'>Execute</button>
            <button name='refreshdb'>Refresh DB</button>
            <button name='php' id='php'>php</button>
        </form>

        <div id="response"></div>
        <script>
            document.getElementById('execute').addEventListener('click', f_executeSQL);

            function f_executeSQL(e){
                e.preventDefault();

                var xhr = new XMLHttpRequest();

                var sqls = encodeURIComponent(document.getElementById('textarea_sql').value);

                xhr.open('GET', "execute_sql.php?sqls="+sqls+"&table_id=sqlexecute",true);

                xhr.onload = function(){                    
                    if(this.status == 200){
                        document.getElementById('response').innerHTML = "loading...";
                        setTimeout(() => {
                            document.getElementById('response').innerHTML = this.responseText;    
                        }, 200);
                        document.getElementById('db_refresh_msg').innerHTML = '';
                    }
                    else if(this.status == 404){
                        document.getElementById('response').innerHTML = '<h3>execute_sql.php not found</h3>';
                    }
                }

                xhr.onerror = function(){
                    consol.log('Request Error!');
                    alert('Request Error!');
                }

                xhr.send();
            }
        </script>
        <script>
            document.getElementById('php').addEventListener('click', f_executePHP);

            function f_executePHP(e){
                e.preventDefault();

                var xhr = new XMLHttpRequest();

                xhr.open('GET', "execute_php.php?",true);

                xhr.onload = function(){                    
                    if(this.status == 200){
                        document.getElementById('response').innerHTML = "loading...";
                        setTimeout(() => {
                            document.getElementById('response').innerHTML = this.responseText;    
                        }, 200);
                        document.getElementById('db_refresh_msg').innerHTML = '';
                    }
                    else if(this.status == 404){
                        document.getElementById('response').innerHTML = '<h3>execute_sql.php not found</h3>';
                    }
                }

                xhr.onerror = function(){
                    consol.log('Request Error!');
                    alert('Request Error!');
                }

                xhr.send();
            }
        </script>

        <script>
            document.onkeyup = function(e){
                if (e.ctrlKey && e.which == 13){
                    f_executeSQL(e);
                } 
            }
        </script>
        
        <div id='db_refresh_msg'>
            <?php
                if (isset($_POST['refreshdb'])){
                    new_vote_db('refresh');
                    echo 'Database refreshed.';
                }
            ?>
        </div>
        <br>


        <a href='../index.html'>HOME</a>
    </body>

</html>