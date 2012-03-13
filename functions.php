<?php 


function makeUrl($sUrl)
{
	if( preg_match('|^https?://|',$sUrl) )
	{
		// nothing .
	}
	else if( substr($sUrl,0,1)=='/' )
	{
		global $sUrlRootPath ;
		$sUrl = $sUrlRootPath . $sUrl ;
	}
	else
	{
		global $sUrlPath ;
		$sUrl = $sUrlPath . $sUrl ;
	}
	
	return 'http://'.$_SERVER['HTTP_HOST'] . '/proxy.php?url=' . urlencode(encodeUrl($sUrl)) ;
}

function encodeUrl($sUrl)
{
	$i = 3 ;
	while($i--)
	{
		$sUrl = 'dd'.base64_encode($sUrl) ;
	}
	return $sUrl ;
}
function decodeUrl($sUrl)
{
	$i = 3 ;
	while($i--)
	{
		$sUrl = base64_decode(substr($sUrl,2)) ;
	}
	return $sUrl ;
}