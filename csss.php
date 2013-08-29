<?php

  /**
    by iBcker 2013 8 29 
  **/

	define(FILE_DIR_ARR, 'css/,Public/css/');
	define(DEF_DIR_ARR, 'css/');

	define(CACHE_DIR,dirname(__FILE__).'/cache/');

  	define(IS_SAE,isset($_SERVER['HTTP_APPNAME']));
	
	header("Content-Type: text/css; charset=UTF-8");

	$params=trim($_GET['files']);//get js file list
	$list=explode(',',$params);
	foreach ($list as $key => &$value) {
		$value=trim($value,'./ 	
			');
		if (!$value||!preg_match('/\.css$/', $value)) {
			unset($list[$key]);
		}else{
			if (strpos($value,'/')===false) {
				$value=DEF_DIR_ARR.$value;
	    	}
			ddotfilter($value);
		}
	}
	
	// var_dump($list);
	// echo '<hr/>';
    $product;
    if (count($list)==1) {
        $product=$list[0];
    }else if(count($list)>1){
        $product=json_encode($list);//create product file name for cache
    }else {
        return;
    }

	if ($contents=readCache($product)) {//if exist product cache
		echo $contents;
	}else{
		$contents='';
		foreach ($list as $file) {
			// echo '------>'.$file.'<hr/>';
			if ($con=readCache($file)) {//if exist file cache
				$contents.=$con.';';
			}else{
				if($con=readJsFile($file)){
					$temp=CssMin::minify($con);
					write2cache($file,$temp);
					$contents.=$temp.';';
				}
			}
		}
		echo $contents;
		if ($contents)write2cache($product,$contents);
	}




	function getDirList($str){
		$list=explode(',', $str);
		return array_values(array_diff($list, array('',NULL)));
	}

	//过滤路径中的..，防止非法访问上层目录
	function ddotfilter(&$value){
		$value=preg_replace('/\.+\//', '', $value);
		$value=preg_replace('/\/+\//', '/', $value);
		return $path;
	}

	function hashJsName($str){
		return md5($str).'.css';
	}

	function readJsFile($path){
		ddotfilter($path);
		if (checkSafeDir($path)) {
			$localPath=dirname(__FILE__).'/'.$path;
			// echo 'read file:'.$localPath.'<br/>';
			$c=file_get_contents($localPath);
			if ($c) {
				return trim($c);
			}else{
				return false;
			}
		}else{
			// echo 'not safe file:'.$path.'<br/>';
			return false;
		}
	}

	function checkSafeDir($path){
		$path=trim($path);
		$dirs=getDirList(FILE_DIR_ARR);
		if (!is_array($dirs)||empty($dirs)) {
			return false;
		}else{
			foreach ($dirs as $item) {
				if (strpos($path,$item)===0) {
					return true;
		    	}
			}
			return false;
		}
	}

	function readCache($path){
		$name=hashJsName($path);
		// echo 'read cache :'.$name.'<br/>';
		if (IS_SAE) {
			return memcache($name);
		}else{
			return filecache($name);
		}
	}

	function write2cache($path,$contents){
		$name=hashJsName($path);
		// echo 'write cache :'.$name.'<br/>';
		if (IS_SAE) {
			return memcache($name,$contents);
		}else{
			return filecache($name,$contents);
		}
	}

	function memcache($path,$contents=NULL){
		$mmc=memcache_init();
		if($mmc==false)die("mc init failed");
		if ($contents) {
      // echo '写入缓存'.$path.'<br>';
			return memcache_set($mmc,$path,$contents);
		}else{
      // if (memcache_get($mmc,$path)) {
      //   echo '读取到缓存'.$path.'<br>';
      // }else{
      //   echo '读取不到缓存'.$path.'<br>';
      // }
			return memcache_get($mmc,$path);
		}
	}


	function filecache($path,$contents=NULL){
		if ($contents) {//write
			$fh=fopen(CACHE_DIR.$path,'w');
			// echo CACHE_DIR.$path;
			if ($fh) {
				$res=fwrite($fh,$contents);
				fclose($fh);
				return $res;
			}
			return false;
		}else{//read
			$dataPath=CACHE_DIR.$path;
			// echo 'read file:'.$dataPath.'<br/>';
			$c=@file_get_contents($dataPath);
			if ($c) {
				return trim($c);
			}else{
				return false;
			}
		}

	}

  class CssMin {

    private $con;

    public static function minify($con){
      $e=new CssMin($con);
      return $e->min();
    }

    function __construct($con){
      $this->con=$con;
    }

    public function min(){
      $this->con=preg_replace("/[	\n\r]/", '',$this->con);//去掉换行空格等
      $this->con=preg_replace("/  /", '',$this->con);//去掉换行空格等
      $this->con=preg_replace("/: /", ':',$this->con);//去掉换行空格等
      $this->con=preg_replace("/, /", ',',$this->con);//去掉换行空格等
      $this->con=preg_replace("/ ,/", ',',$this->con);//去掉换行空格等
      $this->con=preg_replace("/; /", ';',$this->con);//去掉换行空格等
      $this->con=preg_replace("/ ;/", ';',$this->con);//去掉换行空格等
      $this->con=preg_replace("/ {/", '{',$this->con);//去掉换行空格等
      $this->con=preg_replace("/{ /", '{',$this->con);//去掉换行空格等
      $this->con=preg_replace("/} /", '}',$this->con);//去掉换行空格等
      $this->con=preg_replace("/ }/", '}',$this->con);//去掉换行空格等
      $this->con=preg_replace("/\/\*.+?\*\//", '',$this->con);//去掉注释
      $this->con=preg_replace("/;}/", '}',$this->con);//去掉换行空格等
      $this->con=preg_replace("/#ffffff/", '#fff',$this->con);//去掉换行空格等
      return $this->con;
    }
  }
?>