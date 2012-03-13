<?php 
include_once dirname(__FILE__).'/functions.php' ;

error_reporting(E_ALL & ~E_DEPRECATED) ;

if( empty($_REQUEST['url']) )
{
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>代理入口</title>
	
<script language="javascript">
    var base64encodechars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var base64decodechars = new Array(
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
    -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
    52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
    -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
    15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
    -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
    41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);
    function base64encode(str) {
        var out, i, len;
        var c1, c2, c3;
        len = str.length;
        i = 0;
        out = "";
        while (i < len) {
            c1 = str.charCodeAt(i++) & 0xff;
            if (i == len) {
                out += base64encodechars.charAt(c1 >> 2);
                out += base64encodechars.charAt((c1 & 0x3) << 4);
                out += "==";
                break;
            }
            c2 = str.charCodeAt(i++);
            if (i == len) {
                out += base64encodechars.charAt(c1 >> 2);
                out += base64encodechars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xf0) >> 4));
                out += base64encodechars.charAt((c2 & 0xf) << 2);
                out += "=";
                break;
            }
            c3 = str.charCodeAt(i++);
            out += base64encodechars.charAt(c1 >> 2);
            out += base64encodechars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xf0) >> 4));
            out += base64encodechars.charAt(((c2 & 0xf) << 2) | ((c3 & 0xc0) >> 6));
            out += base64encodechars.charAt(c3 & 0x3f);
        }
        return out;
    }
    function base64decode(str) {
        var c1, c2, c3, c4;
        var i, len, out;
        len = str.length;
        i = 0;
        out = "";
        while (i < len) {
           
            do {
                c1 = base64decodechars[str.charCodeAt(i++) & 0xff];
            } while (i < len && c1 == -1);
            if (c1 == -1)
                break;
           
            do {
                c2 = base64decodechars[str.charCodeAt(i++) & 0xff];
            } while (i < len && c2 == -1);
            if (c2 == -1)
                break;
            out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
           
            do {
                c3 = str.charCodeAt(i++) & 0xff;
                if (c3 == 61)
                    return out;
                c3 = base64decodechars[c3];
            } while (i < len && c3 == -1);
            if (c3 == -1)
                break;
            out += String.fromCharCode(((c2 & 0xf) << 4) | ((c3 & 0x3c) >> 2));
           
            do {
                c4 = str.charCodeAt(i++) & 0xff;
                if (c4 == 61)
                    return out;
                c4 = base64decodechars[c4];
            } while (i < len && c4 == -1);
            if (c4 == -1)
                break;
            out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
        }
        return out;
    }
    function utf16to8(str) {
        var out, i, len, c;
        out = "";
        len = str.length;
        for (i = 0; i < len; i++) {
            c = str.charCodeAt(i);
            if ((c >= 0x0001) && (c <= 0x007f)) {
                out += str.charAt(i);
            } else if (c > 0x07ff) {
                out += String.fromCharCode(0xe0 | ((c >> 12) & 0x0f));
                out += String.fromCharCode(0x80 | ((c >> 6) & 0x3f));
                out += String.fromCharCode(0x80 | ((c >> 0) & 0x3f));
            } else {
                out += String.fromCharCode(0xc0 | ((c >> 6) & 0x1f));
                out += String.fromCharCode(0x80 | ((c >> 0) & 0x3f));
            }
        }
        return out;
    }
    function utf8to16(str) {
        var out, i, len, c;
        var char2, char3;
        out = "";
        len = str.length;
        i = 0;
        while (i < len) {
            c = str.charCodeAt(i++);
            switch (c >> 4) {
                case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                    // 0xxxxxxx
                    out += str.charAt(i - 1);
                    break;
                case 12: case 13:
                    // 110x xxxx   10xx xxxx
                    char2 = str.charCodeAt(i++);
                    out += String.fromCharCode(((c & 0x1f) << 6) | (char2 & 0x3f));
                    break;
                case 14:
                    // 1110 xxxx  10xx xxxx  10xx xxxx
                    char2 = str.charCodeAt(i++);
                    char3 = str.charCodeAt(i++);
                    out += String.fromCharCode(((c & 0x0f) << 12) |
                       ((char2 & 0x3f) << 6) |
                       ((char3 & 0x3f) << 0));
                    break;
            }
        }
        return out;
    }
    function encodeUrl(sUrl)
    {
    	sUrl = utf16to8(sUrl) ;

    	var i = 3 ;
    	while(i--)
    	{
    		sUrl = 'dd'+base64encode(sUrl)
    	}
    	return sUrl ;
    }
</script>
</head>

<body>
	<input id="url" type="text" value="http://" name="url" style="width: 400px" />
	<input type="submit" value="访问" onclick="location='?url='+encodeUrl(document.getElementById('url').value)" />

	<div>
		<a href="<?php echo makeUrl('http://www.facebook.com') ;?>">to fb</a>
		<a href="<?php echo makeUrl('http://www.youtube.com') ;?>">to yt</a>
		<a href="<?php echo makeUrl('http://www.twitter.com') ;?>">to tt</a>
	</div>
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
if( !defined('__DIR__') )
{
	define('__DIR__',dirname(__FILE__)) ;
}
$sUserSessionFile = __DIR__."/cookies/{$_COOKIE['proxy_session_uid']}.txt" ;
if( !is_dir(__DIR__."/cookies") )
{
	mkdir(__DIR__."/cookies",0777) ;
}

$_REQUEST['url'] = decodeUrl($_REQUEST['url']) ;


$aAccess = curl_init() ;

// --------------------
// 请求
// set URL and other appropriate options
curl_setopt($aAccess, CURLOPT_URL, $_REQUEST['url']);
curl_setopt($aAccess, CURLOPT_HEADER, @$_REQUEST['debug']?true:false);
curl_setopt($aAccess, CURLOPT_RETURNTRANSFER, true);
curl_setopt($aAccess, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($aAccess, CURLOPT_SSL_VERIFYPEER, false);  
curl_setopt($aAccess, CURLOPT_SSL_VERIFYHOST, false);  
curl_setopt($aAccess, CURLOPT_TIMEOUT, 30);
curl_setopt($aAccess, CURLOPT_COOKIEJAR, $sUserSessionFile);
curl_setopt($aAccess, CURLOPT_COOKIEFILE, $sUserSessionFile);
if(@$_REQUEST['bin'])
{
	curl_setopt($aAccess, CURLOPT_BINARYTRANSFER, true);
}

if(@$_REQUEST['debug'])
{
	echo $_REQUEST['url'], "\r\n" ;
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
		//'HTTP_ACCEPT_LANGUAGE'=>CURLOPT_ENCODING ,
		//'HTTP_COOKIE'=>CURLOPT_COOKIE ,
	) as $sHeaderName=>$sHeaderConst)
{
	if( !empty($_SERVER[$sHeaderName]) )
	{
		curl_setopt($aAccess,$sHeaderConst,$_SERVER[$sHeaderName]) ;
	}
}

curl_setopt($aAccess,CURLOPT_HTTPHEADER,array(
		'Accept','text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' ,
		//'User-Agent','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1' ,
		'Accept-Language','zh-CN,zh;q=0.8' ,
		'Accept-Charset','GBK,utf-8;q=0.7,*;q=0.3' ,
)) ;

if( $_SERVER['REQUEST_METHOD']=='POST' )
{
	curl_setopt($aAccess, CURLOPT_POST, 1);
	curl_setopt($aAccess, CURLOPT_POSTFIELDS, $_POST);
}


// grab URL and pass it to the browser
//if(empty($_REQUEST['debug']))
{
	$sResponse = curl_exec($aAccess);
}


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

// head code
header($arrResponseHeader['CURLINFO_HTTP_CODE']) ;

// location redirect 
if( @$arrResponseHeader['location'] )
{
	header('Location: '.makeUrl($arrResponseHeader['location'])) ;
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
	$sPath = makeUrl($arrMatche[4]) ;
	if( strtolower($arrMatche[1])=='img' )
	{
		$sPath.= '&bin=1' ;
	}
	
	return "<{$arrMatche[1]} {$arrMatche[2]} {$arrMatche[3]}=\"{$sPath}\"" ;
}

// close cURL resource, and free up system resources
curl_close($aAccess);

if(@$_REQUEST['debug'])
{
	echo '<pre>' ;
	echo htmlentities($sResponse) ;
	echo '</pre>' ;
}
else
{
	echo $sResponse ;
}
