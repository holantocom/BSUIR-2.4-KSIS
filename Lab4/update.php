<?php
        /*$game = array(
            array(100, 101),
            "Last" => 2,
            array(
                array(0,0,0),
                array(0,0,0),
                array(0,0,0)
                )
            );
            
        file_put_contents('game', serialize($game));   */
        
    function isOver($board)
	{
		
		//top row
		if ($board[0][0] && $board[0][0] == $board[0][1] && $board[0][1] == $board[0][2])
			return $board[0][0];
			
		//middle row
		if ($board[1][0] && $board[1][0] == $board[1][1] && $board[1][1] == $board[1][2])
			return $board[1][0];
			
		//bottom row
		if ($board[2][0] && $board[2][0] == $board[2][1] && $board[2][1] == $board[2][2])
			return $board[2][0];
			
		//first column
		if ($board[0][0] && $board[0][0] == $board[1][0] && $board[1][0] == $board[2][0])
			return $board[0][0];
			
		//second column
		if ($board[0][1] && $board[0][1] == $board[1][1] && $board[1][1] == $board[2][1])
			return $board[0][1];
			
		//third column
		if ($board[0][2] && $board[0][2] == $board[1][2] && $board[1][2] == $board[2][2])
			return $board[0][2];
			
		//diagonal 1
		if ($board[0][0] && $board[0][0] == $board[1][1] && $board[1][1] == $board[2][2])
			return $board[0][0];
			
		//diagonal 2
		if ($board[0][2] && $board[0][2] == $board[1][1] && $board[1][1] == $board[2][0])
			return $board[0][2];
        
        $arr = array_merge($board[0], $board[1], $board[2]);
        foreach($arr as $el){
            if(intval($el) == 0)
                return 0;
        }	
        
        return 3;
	}
	
        
        $user_id = $_GET['id'];
        $c = $_GET['c'];
        
        $users = unserialize(file_get_contents('turn'));
        $room = $users[$user_id];
        $game = unserialize(file_get_contents($room));
        
        $key = array_search($user_id, $game[0]) + 1;
        
        if($c == 1){
            if($game["Last"] == $key)
                $array = array(404);
            else 
                $array = array(200);
            
            $array = array_merge($array, $game[1][0], $game[1][1], $game[1][2]);
            
            $current = isOver($game[1]);
            if(($current == 1) or ($current == 2))
                $array[0] = ($key == $current) ? 201 : 202;
            if($current == 3)
                $array[0] = 203;
            
                
            echo json_encode($array);
            exit;
        }
        
        if(($c == 2) and ($game["Last"] != $key)){
                
            $col = $_POST['col'];
            $row = $_POST['row'];
            
            $game[1][$row][$col] = $key;
            $game['Last'] = $key;
            file_put_contents($room, serialize($game));
            
        }