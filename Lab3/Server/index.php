<?php

/*
    GET - чтение файла /filename 
    PUT - перезапись файла /filename + BODY
    POST - добавление в конец файла /filename + BODY
    DELETE - удаление файла /filename
    COPY - копирование файла /filename/filename1
    MOVE - перемещение файла /filename/filename1
    FILES - список файлов
*/
        
define("BUFFER", 10485760);

function glob_recursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir){
       $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    
    return $files;
}


$method = $_SERVER['REQUEST_METHOD'];
chdir('users');

if(isset($_GET['q'])){
    
    $url = $_GET['q'];
    
} else {
    
    if($method == 'FILES'){
        
        $files = glob_recursive("*");
        
        foreach($files as $file)
            if(!is_dir($file))
                echo $file."\n";
                
        exit;
        
    } else {
        
        header('HTTP/1.1 444 File Not Specified');
        exit;
        
    }
    
}

$url = rtrim($url, '/');
$urls = explode('/', $url);

foreach($urls as &$url){
    
    $url = str_replace(":", "/", $url);
    $url = str_replace("../", "", $url);
    
}

if((($method == "COPY") or ($method == "MOVE")) and !(count($urls) == 2)){
    
    header('HTTP/1.1 445 Second File Not Specified');
    exit;
    
}
 
    
if (!file_exists($urls[0])){
     
    $paths = pathinfo($urls[0]);
    
    if ((!is_dir($paths['dirname'])) and (($method == "PUT") or ($method == "POST"))){
        
        mkdir($paths['dirname'], 0777, true);
        
    } else {
        
        if(!(($method == "PUT") or ($method == "POST"))){
            
            header('HTTP/1.1 404 File Not Found');
            exit;
            
        }    
        
    }    
    
}  

switch ($method) {
    case 'GET':
        
        $myfile = fopen($urls[0], "r");
        
        if(isset($_SERVER['HTTP_CURRENT'])){
            
            fseek($myfile, BUFFER*(intval($_SERVER['HTTP_CURRENT']-1)));
            echo fread($myfile, BUFFER);
            fseek($myfile, 0);
            
        } else { 
            
            header('Blocks: '.ceil(filesize($urls[0])/BUFFER));
            echo fread($myfile, BUFFER);
            fseek($myfile, 0);
            
        }
        
        fclose($myfile);
        
        break;
        
    case 'PUT':
        
        $content = file_get_contents('php://input');
        $result = file_put_contents($urls[0], $content);
        if($result === FALSE)
            header('HTTP/1.1 520 Rewriting Error');
        else 
            header('HTTP/1.1 220 Successfully Rewritten');
        
        break;
        
    case 'POST':
        
        $content = file_get_contents('php://input');
        $result = file_put_contents($urls[0], $content, FILE_APPEND);
        if($result === FALSE)
            header('HTTP/1.1 521 Adding Error');
        else 
            header('HTTP/1.1 221 Successfully Added');
        
        break;
        
    case 'DELETE':
        
        if(!unlink($urls[0]))
            header('HTTP/1.1 522 Delete Error');
        else
            header('HTTP/1.1 222 Successfully Deleted');
        
        break;
        
    case 'COPY':
        
        $paths = pathinfo($urls[1]);
    
        if (!is_dir($paths['dirname']))
            mkdir($paths['dirname'], 0777, true);
        
        if (!copy($urls[0], $urls[1]))
            header('HTTP/1.1 523 Copy Error');
        else
            header('HTTP/1.1 223 Successfully Copied');
        
        break;
        
    case 'MOVE':
        
        $paths = pathinfo($urls[1]);
    
        if (!is_dir($paths['dirname']))
            mkdir($paths['dirname'], 0777, true);
        
        if (!copy($urls[0], $urls[1]))
            header('HTTP/1.1 524 Move Error');
        else
            if(!unlink($urls[0]))
                header('HTTP/1.1 524 Move Error');
            else
                header('HTTP/1.1 224 Successfully Moved');
            
        break;

    default:
        
        header('HTTP/1.1 400 Bad Request');
        
        break;    
}
