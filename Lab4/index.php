<?php

    function run($user_id){
        $users = array();
        $users = unserialize(file_get_contents('turn'));
        
        if(!empty($users) and array_key_exists($user_id, $users) and ($users[$user_id] != 'WAIT')){
            echo '<script type="text/javascript">
                    window.location = "game.php?id='.$user_id.'"
                </script>'; 
            exit;    
        }
        
        if(empty($users) or (array_search('WAIT', $users) === FALSE) or (array_keys($users, "WAIT")[0] == $user_id)){
        
            echo "Ожидайте очереди";
            if(!isset($_GET['id']))
                echo '<script type="text/javascript">
                        window.location = "index.php?id='.$user_id.'"
                    </script>';
                    
            $users[$user_id] = 'WAIT';
            file_put_contents('turn', serialize($users));
        
        } else {
        
            $key = array_search('WAIT', $users);
            $room = uniqid('gm_');
            
            $users[$user_id] = $room;
            $users[$key] = $room;
        
            $game = array( array($key, $user_id), "Last" => 2,
                array(array(0,0,0), array(0,0,0), array(0,0,0)) );
        
            file_put_contents('turn', serialize($users));
            file_put_contents($room, serialize($game));
            
            echo '<script type="text/javascript">
                    window.location = "game.php?id='.$user_id.'"
                </script>'; 
        
        }
    }
    
    
    if(isset($_POST['button'])):
        
        if(!isset($_GET['id'])) {
            $user_id = uniqid('us_');
            //header('Location: index.php?id='.$user_id);
        } else {
            $user_id = $_GET['id'];
        }
        
        run($user_id);
    
    endif;
?>    
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Онлайн крестики-нолики</title>
</head>	
<body>
<div></div>
<form method="post">
    <input type="submit" name="button" class="button" value="Start game" />
</form>
</body>
</html> 
    
