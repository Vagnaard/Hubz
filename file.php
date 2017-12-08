<?php

 session_start();

 
 $is_on_site = htmlspecialchars($_GET['onSite']);

if ($_SESSION['connected'] != true && $is_on_site != true) {
		
		include("index.php");
		die();	
}


if ($_SESSION['connected'] != true) {
		die();	
}

include('includes/functions.php');
include('config.php');
if($files_url != ""){ $files_url = "/".$files_url; }
$files_url = $root_url.$files_url.$_SESSION['dir'];


    function get_mime_type($file_name)
{

        
        $PHPprotectV11 = array(
                "pdf"=>"application/pdf"
                ,"exe"=>"application/octet-stream"
                ,"zip"=>"application/zip"
                ,"docx"=>"application/msword"
                ,"doc"=>"application/msword"
                ,"xls"=>"application/excel"
                ,"ppt"=>"application/ms-powerpoint"
                ,"gif"=>"image/gif"
                ,"png"=>"image/png"
                ,"jpeg"=>"image/jpg"
                ,"jpg"=>"image/jpg"
                ,"mp3"=>"audio/mpeg"
                ,"wav"=>"audio/x-wav"
                ,"mpeg"=>"video/mpeg"
                ,"mpg"=>"video/mpeg"
                ,"mpe"=>"video/mpeg"
                ,"mov"=>"video/quicktime"
                ,"avi"=>"video/x-msvideo"
                ,"3gp"=>"video/3gpp"
                ,"css"=>"text/css"
                ,"jsc"=>"application/javascript"
                ,"js"=>"application/javascript"
                ,"php"=>"text/html"
                ,"htm"=>"text/html"
                ,"html"=>"text/html"
                ,"txt"=>"text/plain"
                ,"psd"=>"application/octet-stream"
        );

        $exploded_file = strtolower(end(explode('.',$file_name)));

        return $PHPprotectV11[$exploded_file];
}
	
	$file_name = basename(urldecode(htmlspecialchars($_GET['file'])));

    $padded_files_url = $files_url."/";
	
	$PHPprotectV14 = get_mime_type(htmlspecialchars($_GET['file']));
	
	$PHPprotectV15 = $padded_files_url.htmlspecialchars($_GET['file']);
	
	
	if (!file_exists($PHPprotectV15)){
		
										$PHPprotectV16 = str_replace('/', "-", $_SESSION['dir']);
										$PHPprotectV15 = str_replace('/', "-", $PHPprotectV15);
										$PHPprotectV16 = '/'.$PHPprotectV16.'/';
										$PHPprotectV15 = preg_replace($PHPprotectV16, '', $PHPprotectV15, 1);
										$PHPprotectV15 = str_replace('-', "/", $PHPprotectV15);
	}
	
	
	if (0 === strpos(realpath($PHPprotectV15), $files_url)) {
	
		if ($is_on_site != true) {
	
			$file_name = urldecode(htmlspecialchars($_GET['file']));
			$_SESSION['pendingDownload'] = $file_name;
	
			header('location: '.dirname($_SERVER['SCRIPT_NAME']));
			die();	
		}
	
	}else{
	
		if ($is_on_site != true) {
		
			$_SESSION['deniedAccess'] = true;
		
			header('location: '.dirname($_SERVER['SCRIPT_NAME']));
		
		}
	
		die();	
	}

    if (file_exists($PHPprotectV15))
    {
        
        
        $PHPprotectV18 = file_get_contents($PHPprotectV15);
        
        header('Content-type: '.$PHPprotectV14);
		header("Content-Disposition: attachment; filename=".$file_name);
		header("Pragma: no-cache");
		header("Expires: 0");

        echo $PHPprotectV18;
        
    }


?>