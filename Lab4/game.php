<?php
    if(!isset($_GET['id'])){
        echo 'Вас сюда не приглашали :)';
        exit;
    }
        
    
?>    
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Крестики-нолики</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<style>
	table{
	    border-collapse: collapse;
	    border: 2px solid white;
	}
	td{
	    width: 80px;
	    height: 80px;
	    border: 1px solid maroon;
	    text-align: center;
	}
	</style>
</head>	
<body>
<table id="table">
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>  
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr> 
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr> 
</table>  
<div class="status"></div>
<script>

$('td').click(function(){
    var col = $(this).index();
    var $tr = $(this).closest('tr');
    var row = $tr.index();
    if (!jQuery(this).has('img').length) {
        $.post("update.php?c=2&id=<?=$_GET['id']?>", {row: row, col: col})
    }
});  

var timer = setInterval(function show() {
    
    $.get("update.php?c=1&id=<?=$_GET['id']?>")
    .done(function( data ) {
        var table = jQuery.parseJSON(data);
        var i = 1;
        
        $("td").each(function() {
            if(table[i] == 1){
                $(this).html('<img src="images/X.jpg">');
            }
            if(table[i] == 2){
                $(this).html('<img src="images/O.jpg">');
            }
            i++;
        });
        switch (table[0]) {
            case 200:
                $(".status").html('You turn');
                break;
            case 404:
                $(".status").html('Wait');
                break;
            case 201:
                $(".status").html('End game. You win :)');
                clearInterval(timer);
                window.setTimeout(function(){
                    window.location.href = "exit.php?id=<?=$_GET['id']?>";
                }, 5000);
                break;
            case 202:
                $(".status").html('End game. You loose :(');
                clearInterval(timer);
                window.setTimeout(function(){
                    window.location.href = "exit.php?id=<?=$_GET['id']?>";
                }, 5000);
                break;
            case 203:
                $(".status").html('End game. Draw :|');
                clearInterval(timer);
                window.setTimeout(function(){
                    window.location.href = "exit.php?id=<?=$_GET['id']?>";
                }, 5000);
                break;    
        }
    });
    
}, 1000);
</script>
</body>
</html>