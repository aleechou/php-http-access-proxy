<?php 
include_once dirname(__FILE__).'/functions.php' ;

if(empty($_SERVER['HTTP_REFERER']))
{
	$arrUrlInfo = parse_url($_SERVER['HTTP_REFERER']) ;
	if( $arrUrlInfo['host']==$_SERVER['HTTP_HOST'] )
	{
		parse_str($arrUrlInfo['query'],$arrQuery) ;
		if( !empty($arrQuery['url']) )
		{
			$sRefererUrl = decodeUrl($arrQuery['url']) ;

			$arrUrlInfo = parse_url($sRefererUrl) ;
			$sRequestUrl = $arrUrlInfo['scheme'] . '://' . $arrUrlInfo['host'] . $_SERVER['REQUEST_URI'] ;

			//echo makeUrl($sRequestUrl) ;
			header('Location: '.makeUrl($sRequestUrl)) ;
			exit() ;
		}
	}
}

echo 'nothing for you<pre>' ;
print_r($_SERVER) ;
echo '</pre>' ;

if( function_exists('apache_request_headers') )
{
	echo '<hr />headers<pre>' ;
	print_r(apache_request_headers()) ;
	echo '</pre>' ;
}