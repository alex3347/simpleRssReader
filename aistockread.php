<?php
set_time_limit(0);//设置超时时间为0
function object_array($array) {//对象数组转换为数组
	if (is_object($array)) {
		$array = (array)$array;
	}
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = object_array($value);
		}
	}
	return $array;
}
function stripContentTag($v){ //函数用于去掉content中的html标签
$v = str_replace('<br />', '', $v); 
$v = preg_replace('%(<span\s*[^>]*>(.*)</span>)%Usi', '\2', $v); 
$v = preg_replace('%(<span>(.*)</span>)%Usi', '\2', $v); 
$v = preg_replace('%(<span\s*[^>]*>)%Usi', '', $v); 
$v = str_replace('</span>', '', $v); 
$v = preg_replace('%(<a\s*[^>]*>(.*)</a>)%Usi', '\2', $v); 
$v = preg_replace('%(<p\s*[^>]*>(.*)</p>)%Usi', '\2', $v); 
$v = preg_replace('%(<p[^>]*>)%Usi', '', $v); 
$v = str_replace('<p>', '', $v); 
$v = str_replace('</p>', '', $v); 
$v = str_replace('&nbsp;', '', $v); 
$v = str_replace('&amp;', '', $v); 
$v = preg_replace('%(<b\s*[^>]*>(.*)</b>)%Usi', '\2', $v); 
$v = preg_replace('%(<strong\s*[^>]*>(.*)</strong>)%Usi', '\2', $v); 
$v = preg_replace('%(<div[^>]*>)%Usi', '', $v); 
$v = str_replace('<div>', '', $v); 
$v = str_replace('</div>', '', $v); 
$v = preg_replace('%(<img[^>]*>)%Usi', '', $v); 
$v = preg_replace('%(\s*)%Usi', '', $v); //去掉所有空格
  return $v; 
} 
function del_DirAndFile($dirName){//删除文件
    if(is_dir($dirName)){
        if ( $handle = opendir( "$dirName" ) ) {  
          while ( false !== ( $item = readdir( $handle ) ) ) {  
              if ( $item != "." && $item != ".." ) {  
                  if ( is_dir( "$dirName/$item" ) ) {  
                      del_DirAndFile( "$dirName/$item" );  
                  } else {  
                      unlink( "$dirName/$item" );  
                  }  
              }  
          }  
      	closedir( $handle );  
        }
    }
}
function addFileToZip($path,$zip){//压缩文件函数
    $handler=opendir($path); //打开当前文件夹由$path指定。
    while(($filename=readdir($handler))!==false){
        if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                addFileToZip($path."/".$filename, $zip);
            }else{ //将文件加入zip对象
                $zip->addFile($path."/".$filename);
            }
        }
    }
    @closedir($path);
}
$timestart = $_GET['timestart'];
$timeend = $_GET['timeend'];
$now = $_GET['now'];
if($timeend == 0 || $timestart == $timeend){
	if ($timestart == $now) {
		$timestartTemp = $timestart-1;
		$contents = file_get_contents('http://www.ourkp.com/newsapi/his?day='.$timestartTemp);
		$decodeContents = json_decode($contents);//解码
		$arrayContents = object_array($decodeContents);//对象数组转换为数组
		$data = $arrayContents['data'];//获取数组中的data项
		$nid = $data[count($data)-1]['nid'];//获取最后一项nid的值
		$myfile = fopen($now.".html", "w") or die("Unable to open file!");//删除之前有过的爬取
		fclose($myfile);
		sleep(2);
		for ($i = 0; $i < 20; $i++) {
			$nid = $nid+10;
			$contentsTemp = file_get_contents('http://www.ourkp.com/live/newslist/?type=1&lastid='.$nid);
			$arrayContentsTemp = json_decode($contentsTemp);//对象转换为对象数组
			$arrayContents = object_array($arrayContentsTemp);
			$dataTemp = $arrayContents["data"];
		
			$myfile = fopen($now.".html", "a") or die("Unable to open file!");
			$count = count($dataTemp);
			for ($j = 0; $j < $count; $j++) {
			
				$content = "\xEF\xBB\xBF".stripContentTag($dataTemp[$j]['content']);
				$pub_time = "\xEF\xBB\xBF".$dataTemp[$j]['pub_time']."<br>";
				fwrite($myfile, $content);
				fwrite($myfile, $pub_time);
			
			}
			fclose($myfile);
		}
		
	} else {
		$contents = file_get_contents('http://www.ourkp.com/newsapi/his?day='.$timestart);
		$decodeContents = json_decode($contents);
		$arrayContents = object_array($decodeContents);
		$data = $arrayContents['data'];
		$myfile = fopen($timestart.".html", "w") or die("Unable to open file!");
		$count = count($data);
		for ($i = 0; $i < $count; $i++) {
		
			$content = "\xEF\xBB\xBF".stripContentTag($data[$i]['content']);
			$pub_time = "\xEF\xBB\xBF".$data[$i]['pub_time']."<br>";
			fwrite($myfile, $content);
			fwrite($myfile, $pub_time);
		
		}
		fclose($myfile);
		
	}
	
}else{
	$begintime = strtotime($timestart);
	$endtime = strtotime($timeend);
	del_DirAndFile('ZIP');
	for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
	    $numStart = date("Ymd", $start);
		$contents = file_get_contents('http://www.ourkp.com/newsapi/his?day='.$numStart);
		$decodeContents = json_decode($contents);
		$arrayContents = object_array($decodeContents);
		$data = $arrayContents['data'];
		$myfile = fopen("ZIP/".$numStart.".html", "w") or die("Unable to open file!");
		$count = count($data);
		for ($i = 0; $i < $count; $i++) {
		
			$content = "\xEF\xBB\xBF".stripContentTag($data[$i]['content']);
			$pub_time = "\xEF\xBB\xBF".$data[$i]['pub_time']."<br>";//word \r\n
			fwrite($myfile, $content);
			fwrite($myfile, $pub_time);
		
		}
		fclose($myfile);
	}
	
	$zip=new ZipArchive();
	if($zip->open('doc.zip', ZipArchive::OVERWRITE)=== TRUE){
	    addFileToZip('ZIP', $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
	    $zip->close(); //关闭处理的zip文件
	}
}




$callback = $_GET['callback'];
exit($callback."('complete')");

?>