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
        header('HTTP/1.1 200 OK');
        exit;
    } else {
        header('HTTP/1.1 444 Folder Not Specified');
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
    header('HTTP/1.1 445 Second Folder Not Specified');
    exit;
}
 
    
if (!file_exists($urls[0])){
    
    $paths = pathinfo($urls[0]);
    
    if ((!is_dir($paths['dirname'])) and (($method == "PUT") or ($method == "POST"))){
        mkdir($paths['dirname'], 0777, true);
    } else {
        header('HTTP/1.1 404 File Not Found');
        exit;
    }    
    
}  

switch ($method) {
    case 'GET':
        
        $myfile = fopen($urls[0], "r");
        header('HTTP/1.1 200 OK');
        echo fread($myfile,filesize($urls[0]));
        fclose($myfile);
        
        break;
        
    case 'PUT':
        
        $content = file_get_contents('php://input');
        $result = file_put_contents($urls[0], $content);
        if($result === FALSE)
            header('HTTP/1.1 512 Rewriting Error');
        else 
            header('HTTP/1.1 200 OK');
        
        break;
        
    case 'POST':
        
        $content = file_get_contents('php://input');
        $result = file_put_contents($urls[0], $content, FILE_APPEND);
        if($result === FALSE)
            header('HTTP/1.1 512 Adding Error');
        else 
            header('HTTP/1.1 200 OK');
        
        break;
        
    case 'DELETE':
        
        if(!unlink($urls[0]))
            header('HTTP/1.1 512 Delete Error');
        else
            header('HTTP/1.1 200 OK');
        
        break;
        
    case 'COPY':
        
        $paths = pathinfo($urls[1]);
    
        if (!is_dir($paths['dirname']))
            mkdir($paths['dirname'], 0777, true);
        
        if (!copy($urls[0], $urls[1]))
            header('HTTP/1.1 512 Copy Error');
        else
            header('HTTP/1.1 200 OK');
        
        break;
        
    case 'MOVE':
        
        $paths = pathinfo($urls[1]);
    
        if (!is_dir($paths['dirname']))
            mkdir($paths['dirname'], 0777, true);
        
        if (!copy($urls[0], $urls[1]))
            header('HTTP/1.1 512 Move Error');
        else
            if(!unlink($urls[0]))
                header('HTTP/1.1 512 Move Error');
            else
                header('HTTP/1.1 200 OK');
            
        break;

    default:
        
        header('HTTP/1.1 400 Bad Request');
        
        break;    
}