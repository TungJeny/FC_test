<?php
/**
 * Is HTTPS?
 *
 * Determines if the application is accessed via an encrypted
 * (HTTPS) connection.
 *
 * @return	bool
 */
function is_https()
{
	//Cloudflare
	if ( ! empty($_SERVER['HTTP_CF_VISITOR']))
	{	
			$visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
			if ($visitor !== NULL)
			{
				return $visitor->scheme == 'https';
			}
	}
	
	if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
	{
		return TRUE;
	}
	elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	{
		return TRUE;
	}
	elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
	{
		return TRUE;
	} elseif (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
		return TRUE;
	}
	return FALSE;
}

function is_on_phppos_host()
{
    return false;
	if ( isset($_SERVER['CI_PHPPOS_HOST']))
	{
		return $_SERVER['CI_PHPPOS_HOST'];
	}
	
	return strpos($_SERVER['HTTP_HOST'],'phppointofsale.com') !== FALSE || strpos($_SERVER['HTTP_HOST'],'phppointofsalestaging.com') !== FALSE;
}
