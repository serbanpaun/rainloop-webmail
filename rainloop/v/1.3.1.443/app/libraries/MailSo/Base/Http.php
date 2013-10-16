<?php

namespace MailSo\Base;

/**
 * @category MailSo
 * @package Base
 */
class Http
{
	/**
	 * @var bool
	 */
	private $bIsMagicQuotesOn;

	/**
	 * @access private
	 */
	private function __construct()
	{
		$this->bIsMagicQuotesOn = (bool) @\ini_get('magic_quotes_gpc');
	}

	/**
	 * @return \MailSo\Base\Http
	 */
	public static function NewInstance()
	{
		return new self();
	}

	/**
	 * @staticvar \MailSo\Base\Http $oInstance;
	 *
	 * @return \MailSo\Base\Http
	 */
	public static function SingletonInstance()
	{
		static $oInstance = null;
		if (null === $oInstance)
		{
			$oInstance = self::NewInstance();
		}

		return $oInstance;
	}

	/**
	 * @param string $sKey
	 *
	 * @return bool
	 */
	public function HasQuery($sKey)
	{
		return isset($_GET[$sKey]);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mDefault = null
	 * @param bool $bClearPercZeroZero = true
	 *
	 * @return mixed
	 */
	public function GetQuery($sKey, $mDefault = null, $bClearPercZeroZero = true)
	{
		return isset($_GET[$sKey]) ? \MailSo\Base\Utils::StripSlashesValue($_GET[$sKey], $bClearPercZeroZero) : $mDefault;
	}

	/**
	 * @return array|null
	 */
	public function GetQueryAsArray()
	{
		return isset($_GET) && \is_array($_GET) ? \MailSo\Base\Utils::StripSlashesValue($_GET, true) : null;
	}

	/**
	 * @param string $sKey
	 *
	 * @return bool
	 */
	public function HasPost($sKey)
	{
		return isset($_POST[$sKey]);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mDefault = null
	 * @param bool $bClearPercZeroZero = false
	 *
	 * @return mixed
	 */
	public function GetPost($sKey, $mDefault = null, $bClearPercZeroZero = false)
	{
		return isset($_POST[$sKey]) ? \MailSo\Base\Utils::StripSlashesValue($_POST[$sKey], $bClearPercZeroZero) : $mDefault;
	}

	/**
	 * @return array|null
	 */
	public function GetPostAsArray()
	{
		return isset($_POST) && \is_array($_POST) ? \MailSo\Base\Utils::StripSlashesValue($_POST, false) : null;
	}

	/**
	 * @param string $sKey
	 *
	 * @return bool
	 */
	public function HasRequest($sKey)
	{
		return isset($_REQUEST[$sKey]);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mDefault = null
	 *
	 * @return mixed
	 */
	public function GetRequest($sKey, $mDefault = null)
	{
		return isset($_REQUEST[$sKey]) ? \MailSo\Base\Utils::StripSlashesValue($_REQUEST[$sKey]) : $mDefault;
	}

	/**
	 * @param string $sKey
	 *
	 * @return bool
	 */
	public function HasServer($sKey)
	{
		return isset($_SERVER[$sKey]);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mDefault = null
	 *
	 * @return mixed
	 */
	public function GetServer($sKey, $mDefault = null)
	{
		return isset($_SERVER[$sKey]) ? $_SERVER[$sKey] : $mDefault;
	}

	/**
	 * @param string $sKey
	 *
	 * @return bool
	 */
	public function HasEnv($sKey)
	{
		return isset($_ENV[$sKey]);
	}

	/**
	 * @param string $sKey
	 * @param mixed $mDefault = null
	 *
	 * @return mixed
	 */
	public function GetEnv($sKey, $mDefault = null)
	{
		return isset($_ENV[$sKey]) ? $_ENV[$sKey] : $mDefault;
	}

	/**
	 * @return string
	 */
	public function ServerProtocol()
	{
		return $this->GetServer('SERVER_PROTOCOL', 'HTTP/1.0');
	}

	/**
	 * @return string
	 */
	public function GetMethod()
	{
		return $this->GetServer('REQUEST_METHOD', '');
	}

	/**
	 * @return bool
	 */
	public function IsPost()
	{
		return ('POST' === $this->GetMethod());
	}

	/**
	 * @return bool
	 */
	public function IsGet()
	{
		return ('GET' === $this->GetMethod());
	}

	/**
	 * @return string
	 */
	public function GetQueryString()
	{
		return $this->GetServer('QUERY_STRING', '');
	}

	/**
	 * @return bool
	 */
	public function CheckLocalhost($sServer)
	{
		return \in_array(\strtolower(\trim($sServer)), array(
			'localhost', '127.0.0.1', '::1', '::1/128', '0:0:0:0:0:0:0:1'
		));
	}

	/**
	 * @return bool
	 */
	public function IsLocalhost()
	{
		return $this->CheckLocalhost($this->GetServer('REMOTE_ADDR', ''));
	}

	/**
	 * @return string
	 */
	public function GetRawBody()
	{
		static $sRawBody = null;
		if (null === $sRawBody)
		{
			$sBody = @\file_get_contents('php://input');
			$sRawBody = (false !== $sBody) ? $sBody : '';
		}
		return $sRawBody;
	}

	/**
	 * @param string $sHeader
	 *
	 * @return string
	 */
	public function GetHeader($sHeader)
	{
		$sResultHeader = '';
		$sServerKey = 'HTTP_'.\strtoupper(\str_replace('-', '_', $sHeader));
		$sResultHeader = $this->GetServer($sServerKey, '');

		if (0 === \strlen($sResultHeader) &&
			\MailSo\Base\Utils::FunctionExistsAndEnabled('apache_request_headers'))
		{
			$sHeaders = \apache_request_headers();
			if (isset($sHeaders[$sHeader]))
			{
				$sResultHeader = $sHeaders[$sHeader];
			}
		}

		return $sResultHeader;
	}

	/**
	 * @return string
	 */
	public function GetScheme()
	{
		return ('on' === \strtolower($this->GetServer('HTTPS'))) ? 'https' : 'http';
	}

	/**
	 * @return bool
	 */
	public function IsSecure()
	{
		return ('https' === $this->GetScheme());
	}

	/**
	 * @param bool $bWithRemoteUserData = false
	 * @param bool $bRemoveWWW = true
	 *
	 * @return string
	 */
	public function GetHost($bWithRemoteUserData = false, $bRemoveWWW = true)
	{
		$sHost = $this->GetServer('HTTP_HOST', '');
		if (0 === \strlen($sHost))
		{
			$sScheme = $this->GetScheme();
			$sName = $this->GetServer('SERVER_NAME');
			$iPort = (int) $this->GetServer('SERVER_PORT');

			$sHost = (('http' === $sScheme && 80 === $iPort) || ('https' === $sScheme && 443 === $iPort))
				? $sName : $sName.':'.$iPort;
		}

		if ($bRemoveWWW)
		{
			$sHost = 'www.' === \substr(\strtolower($sHost), 0, 4) ? \substr($sHost, 0, 4) : $sHost;
		}

		if ($bWithRemoteUserData)
		{
			$sUser = \trim($this->HasServer('REMOTE_USER') ? $this->GetServer('REMOTE_USER', '') : '');
			$sHost = (0 < \strlen($sUser) ? $sUser.'@' : '').$sHost;
		}

		return $sHost;
	}

	/**
	 * @param bool $bCheckProxy = true
	 *
	 * @return string
	 */
	public function GetClientIp($bCheckProxy = true)
	{
		$sIp = '';
		if ($bCheckProxy && null !== $this->GetServer('HTTP_CLIENT_IP', null))
		{
			$sIp = $this->GetServer('HTTP_CLIENT_IP', '');
		}
		else if ($bCheckProxy && null !== $this->GetServer('HTTP_X_FORWARDED_FOR', null))
		{
			$sIp = $this->GetServer('HTTP_X_FORWARDED_FOR', '');
		}
		else
		{
			$sIp = $this->GetServer('REMOTE_ADDR', '');
		}

		return $sIp;
	}

	/**
	 * @param string $sUrl
	 * @param array $aPost = array()
	 * @param string $sCustomUserAgent = 'MaiSo Http User Agent (v1)'
	 * @param int $iCode = 0
	 * @param \MailSo\Log\Logger $oLogger = null
	 *
	 * @return string|bool
	 */
	public function SendPostRequest($sUrl, $aPost = array(), $sCustomUserAgent = 'MaiSo Http User Agent (v1)', &$iCode = 0, $oLogger = null)
	{
		$aOptions = array(
			CURLOPT_URL => $sUrl,
			CURLOPT_HEADER => false,
			CURLOPT_FAILONERROR => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $aPost,
			CURLOPT_TIMEOUT => 20
		);

		if (0 < \strlen($sCustomUserAgent))
		{
			$aOptions[CURLOPT_USERAGENT] = $sCustomUserAgent;
		}

		$oCurl = \curl_init();
		\curl_setopt_array($oCurl, $aOptions);

		if ($oLogger)
		{
			$oLogger->Write('cURL: Send post request: '.$sUrl);
		}

		$mResult = \curl_exec($oCurl);

		$iCode = (int) \curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
		$sContentType = (string) \curl_getinfo($oCurl, CURLINFO_CONTENT_TYPE);

		if ($oLogger)
		{
			$oLogger->Write('cURL: Post request result: (Status: '.$iCode.', ContentType: '.$sContentType.')');
			if (false === $mResult || 200 !== $iCode)
			{
				$oLogger->Write('cURL: Error: '.\curl_error($oCurl), \MailSo\Log\Enumerations\Type::WARNING);
			}
		}

		if (\is_resource($oCurl))
		{
			\curl_close($oCurl);
		}

		return $mResult;
	}
	
	/**
	 * @param string $sUrl
	 * @param resource $rFile
	 * @param string $sCustomUserAgent = 'MaiSo Http User Agent (v1)'
	 * @param string $sContentType = ''
	 * @param int $iCode = 0
	 * @param \MailSo\Log\Logger $oLogger = null
	 *
	 * @return bool
	 */
	public function SaveUrlToFile($sUrl, $rFile, $sCustomUserAgent = 'MaiSo Http User Agent (v1)', &$sContentType = '', &$iCode = 0, $oLogger = null)
	{
		if (!is_resource($rFile))
		{
			if ($oLogger)
			{
				$oLogger->Write('cURL: input resource invalid.', \MailSo\Log\Enumerations\Type::WARNING);
			}
			
			return false;
		}

		$aOptions = array(
			CURLOPT_URL => $sUrl,
			CURLOPT_HEADER => false,
			CURLOPT_FAILONERROR => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FILE => $rFile,
			CURLOPT_TIMEOUT => 10
		);

		if (0 < \strlen($sCustomUserAgent))
		{
			$aOptions[CURLOPT_USERAGENT] = $sCustomUserAgent;
		}

		$oCurl = \curl_init();
		\curl_setopt_array($oCurl, $aOptions);

		if ($oLogger)
		{
			$oLogger->Write('cURL: Send request: '.$sUrl);
		}

		$bResult = \curl_exec($oCurl);
		
		$iCode = (int) \curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
		$sContentType = (string) \curl_getinfo($oCurl, CURLINFO_CONTENT_TYPE);
		
		if ($oLogger)
		{
			$oLogger->Write('cURL: Request result: '.($bResult ? 'true' : 'false').' (Status: '.$iCode.', ContentType: '.$sContentType.')');
			if (!$bResult || 200 !== $iCode)
			{
				$oLogger->Write('cURL: Error: '.\curl_error($oCurl), \MailSo\Log\Enumerations\Type::WARNING);
			}
		}

		if (\is_resource($oCurl))
		{
			\curl_close($oCurl);
		}
		
		return $bResult;
	}

	/**
	 * @param string $sUrl
	 * @param string $sCustomUserAgent = 'MaiSo Http User Agent (v1)'
	 * @param string $sContentType = ''
	 * @param int $iCode = 0
	 * @param \MailSo\Log\Logger $oLogger = null
	 *
	 * @return string|bool
	 */
	public function GetUrlAsString($sUrl, $sCustomUserAgent = 'MaiSo Http User Agent (v1)', &$sContentType = '', &$iCode = 0, $oLogger = null)
	{
		$rMemFile = \MailSo\Base\ResourceRegistry::CreateMemoryResource();
		if ($this->SaveUrlToFile($sUrl, $rMemFile, $sCustomUserAgent, $sContentType, $iCode, $oLogger) && \is_resource($rMemFile))
		{
			\rewind($rMemFile);
			return \stream_get_contents($rMemFile);
		}
		
		return false;
	}

	/**
	 * @param int $iExpireTime
	 * @param bool $bSetCacheHeader = true
	 * @param string $sEtag = ''
	 *
	 * @return bool
	 */
	public function ServerNotModifiedCache($iExpireTime, $bSetCacheHeader = true, $sEtag = '')
	{
		$bResult = false;
		if (0 < $iExpireTime)
		{
			$iUtcTimeStamp = \time();
			$sIfModifiedSince = $this->GetHeader('If-Modified-Since', '');
			if (0 === \strlen($sIfModifiedSince))
			{
				if ($bSetCacheHeader)
				{
					\header('Cache-Control: public', true);
					\header('Pragma: public', true);
					\header('Last-Modified: '.\gmdate('D, d M Y H:i:s', $iUtcTimeStamp - $iExpireTime).' UTC', true);
					\header('Expires: '.\gmdate('D, j M Y H:i:s', $iUtcTimeStamp + $iExpireTime).' UTC', true);

					if (0 < strlen($sEtag))
					{
						\header('Etag: '.$sEtag, true);
					}
				}
			}
			else
			{
				$this->StatusHeader(304);
				$bResult = true;
			}
		}

		return $bResult;
	}

	/**
	 * @param int $iStatus
	 *
	 * @return void
	 */
	public function StatusHeader($iStatus, $sCustomStatusText = '')
	{
		switch ($iStatus)
		{
			default:
				\header('Status: '.$iStatus, true, $iStatus);
				break;
			case 304:
				\header($this->ServerProtocol().' 304 '.(0 === \strlen($sCustomStatusText) ? 'Not Modified' : $sCustomStatusText), true, $iStatus);
				break;
			case 200:
				\header($this->ServerProtocol().' 200 '.(0 === \strlen($sCustomStatusText) ? 'OK' : $sCustomStatusText), true, $iStatus);
				break;
			case 401:
				\header($this->ServerProtocol().' 401 '.(0 === \strlen($sCustomStatusText) ? 'Please sign in' : $sCustomStatusText), true, $iStatus);
				break;
			case 404:
				\header($this->ServerProtocol().' 404 '.(0 === \strlen($sCustomStatusText) ? 'Not Found' : $sCustomStatusText), true, $iStatus);
				break;
		}
	}

	/**
	 * @return string
	 */
	public function GetPath()
	{
		$sUrl = \ltrim(\substr($this->GetServer('SCRIPT_NAME', ''), 0, \strrpos($this->GetServer('SCRIPT_NAME', ''), '/')), '/');
		return '' === $sUrl ? '/' : '/'.$sUrl.'/';
	}

	/**
	 * @return string
	 */
	public function GetUrl()
	{
		return '/'.$this->GetServer('REQUEST_URI', '');
	}

	/**
	 * @return string
	 */
	public function GetFullUrl()
	{
		return $this->GetScheme().'://'.$this->GetHost(true, false).$this->GetPath();
	}

	/**
	 * @return string
	 */
	public function GetFullUrlWithQuery()
	{
		return $this->GetScheme().'://'.$this->GetHost(true, false).$this->GetUrl();
	}
}