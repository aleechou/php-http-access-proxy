<?php 
 
if( empty($_REQUEST['url']) )
{
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>代理入口</title>
</head>

<body>
	<form method="get">
		<input type="text" value="http://" name="url" style="width: 400px" />
		<input type="submit" value="访问" />
	</form>
</body>
</html>
<?php
	exit() ;
}

if( empty($_COOKIE['proxy_session_uid']) )
{
	$_COOKIE['proxy_session_uid'] = md5(microtime(true).rand(0,9999999999)) ;
	setcookie('proxy_session_uid',$_COOKIE['proxy_session_uid'],time()+24*60*60*365*10) ;
}
$sUserSessionFile = __DIR__."/cookies/{$_COOKIE['proxy_session_uid']}.txt" ;
if( !is_dir(__DIR__."/cookies") )
{
	mkdir(__DIR__."/cookies",0777) ;
}

$_REQUEST['url'] = trim($_REQUEST['url']) ;

$aAccess = curl_init() ;

// --------------------
// 请求
// set URL and other appropriate options
curl_setopt($aAccess, CURLOPT_URL, $_REQUEST['url']);
curl_setopt($aAccess, CURLOPT_HEADER, false);
curl_setopt($aAccess, CURLOPT_RETURNTRANSFER, true);
curl_setopt($aAccess, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($aAccess, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($aAccess, CURLOPT_TIMEOUT, 30);
curl_setopt($aAccess, CURLOPT_COOKIEJAR, $sUserSessionFile);
curl_setopt($aAccess, CURLOPT_COOKIEFILE, $sUserSessionFile);
if(@$_REQUEST['bin'])
{
	curl_setopt($aAccess, CURLOPT_BINARYTRANSFER, true);
}

// 来路
$arrUrlInfo = parse_url($_SERVER['HTTP_REFERER']) ;
if( $arrUrlInfo['host']==$_SERVER['HTTP_HOST'] and $arrUrlInfo['path']==$_SERVER['SCRIPT_NAME'] )
{
	parse_str($arrUrlInfo['query'],$arrUrlQuery) ;
	if( !empty($arrUrlQuery['url']) )
	{
		curl_setopt($aAccess,CURLOPT_REFERER,$arrUrlQuery['url']) ;
	}
}

// 其它 http header
foreach(array(
		'HTTP_USER_AGENT'=>CURLOPT_USERAGENT ,
		'HTTP_ACCEPT_LANGUAGE'=>CURLOPT_ENCODING ,
		//'HTTP_COOKIE'=>CURLOPT_COOKIE ,
	) as $sHeaderName=>$sHeaderConst)
{
	if( !empty($_SERVER[$sHeaderName]) )
	{
		curl_setopt($aAccess,$sHeaderConst,$_SERVER[$sHeaderName]) ;
	}
}

if( $_SERVER['REQUEST_METHOD']=='POST' )
{
	curl_setopt($aAccess, CURLOPT_POST, 1);
	curl_setopt($aAccess, CURLOPT_POSTFIELDS, $_POST);
}


// grab URL and pass it to the browser
$sResponse = curl_exec($aAccess);


// --------------------
// 回应 
$arrResponseHeader = curl_getinfo($aAccess) ;
foreach($arrResponseHeader as $sHeaderName=>$sHeaderLine)
{
	$arrSlice = explode('_',$sHeaderName) ;
	$arrSlice = array_map('ucfirst',$arrSlice) ;
	$sHeaderName = implode('-',$arrSlice) ;
	
	header("{$sHeaderName}: {$sHeaderLine}") ;
}

// 替换html中的路径
if(empty($_REQUEST['bin']))
{
	$arrUrlInfo = parse_url($_REQUEST['url']) ;
	$sUrlRootPath = $arrUrlInfo['scheme'] . '://' . $arrUrlInfo['host'] ;
	$sUrlPath = $sUrlRootPath . '/' . dirname($arrUrlInfo['path']) ;
	if( !substr($sUrlPath,-1,1)=='/' )
	{
		$sUrlPath.= '/' ;
	}
	
	$arrTagAttrs = array(
		'img' => 'src' ,
		'script' => 'src' ,
		'a' => 'href' ,
		'link' => 'href' ,
		'form' => 'action' ,
	) ;
	
	$sTags = implode('|',array_keys($arrTagAttrs)) ;
	$sAttrs = implode('|',$arrTagAttrs) ;
	$sRegExp = "`<({$sTags})\\s+(.*?)({$sAttrs})\\s*=\\s*['\"]?([^'\"]+)['\"]?`i" ;
	
	$sResponse = preg_replace_callback($sRegExp,'replace_html_path',$sResponse) ;
}

function replace_html_path($arrMatche)
{	
	$sPath = $arrMatche[4] ;
	
	if( preg_match('|^https?://|',$sPath) )
	{
		// nothing .
	}
	else if( substr($sPath,0,1)=='/' )
	{
		global $sUrlRootPath ;
		$sPath = $sUrlRootPath . $sPath ;
	}
	else
	{
		global $sUrlPath ;
		$sPath = $sUrlPath . $sPath ;
	}
	
	$sPath = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'] . '?url=' . urlencode($sPath) ;
	if( strtolower($arrMatche[1])=='img' )
	{
		$sPath.= '&bin=1' ;
	}
	
	return "<{$arrMatche[1]} {$arrMatche[2]} {$arrMatche[3]}=\"{$sPath}\"" ;
}

// close cURL resource, and free up system resources
curl_close($aAccess);


echo $sResponse ;
