<?php
    if(!isset($_GET['id'])){
        echo 'Вас сюда не приглашали :)';
        exit;
    }
    
    $user_id = $_GET['id'];
    
    $users = unserialize(file_get_contents('turn'));
    $room = $users[$user_id];
    unset($users[$user_id]);
    if(file_exists($room))
        unlink($room);
        
    file_put_contents('turn', serialize($users));
    
    echo '<script type="text/javascript">
                        window.location = "index.php?id='.$user_id.'"
                    </script>';