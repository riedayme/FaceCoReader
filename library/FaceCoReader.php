<?php 

/**
* FaceCoReader Class
* 16 Februari 2022
* Made by FaanTeyki
*/
class FaceCoReader
{

	protected $base = "https://mbasic.facebook.com/";
	protected $apibase = "https://graph.facebook.com/";
	protected $debug = true;

	protected $headers = [
	'Authority: mbasic.facebook.com',
	'Cache-Control: max-age=0',
	'Sec-Ch-Ua: ?0',
	'Sec-Ch-Ua-Mobile: ?0',
	'Sec-Ch-Ua-Platform: Windows',
	'Upgrade-Insecure-Requests: 1',
	'User-Agent: Mozilla/5.0 (Windows NT 6.1, Win64, x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
	'Sec-Fetch-Site: none',
	'Sec-Fetch-Mode: navigate',
	'Sec-Fetch-User: ?1',
	'Sec-Fetch-Dest: document',
	'Accept-Language: en-GB,en-US,q=0.9,en,q=0.8,id,q=0.7'
	];

	public $login = [];

	protected $proxy = false;

	function __construct($data = []) 
	{
		if (array_key_exists('cookie', $data)) {
			$this->headers = array_merge($this->headers, ['Cookie: '.$data['cookie']]);
		}

		if (array_key_exists('proxy', $data)) {
			$this->proxy = $data['proxy'];
		}
	}
	
	/**
	 * Helper
	 */
	protected function Fetch($url, $postdata = 0, $header = 0, $cookie = 0, $useragent = 0, $proxy = array(), $followlocation = 0) 
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followlocation);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_HEADER, 1);

		// for facebook url & api
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		if($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		}

		if($postdata) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($postdata != 'empty') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			}
		}

		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		}

		if ($useragent) {
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		}

		if (!empty($proxy['proxy']['ip'])){
			curl_setopt($ch, CURLOPT_PROXY, $proxy['proxy']['ip']);
		}

		if (!empty($proxy['proxy']['userpwd'])){
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['proxy']['userpwd']);
		}

		if (!empty($proxy['proxy']['socks5'])){
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}

		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch);

		if (curl_errno($ch)) {
			return [
			'status' => false,
			'response' => 'Connection 404'
			];
		}		

		if(!$httpcode) 
		{
			curl_close($ch);	
			
			return [
			'status' => false,
			'response' => 'HttpCode 404'
			];
		}
		else
		{
			$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
			$body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

			curl_close($ch);

			if (!$httpcode['http_code']) {
				return [
				'status' => false,
				'response' => 'HttpCode 404'
				];
			}elseif (strstr($header, 'login.php') or $httpcode['http_code'] == '404') {
				return [
				'status' => false,
				'response' => 'Has Kick to Lobby'
				];
			}

			return [
			'status' => true,
			'header' => $header,
			'body' => $body,
			'code' => $httpcode['http_code']
			];
		}
	}	

	protected function FindStringOnArray($arr, $string) 
	{
		return array_filter($arr, function($value) use ($string) {
			return strstr($value, $string) !== false;
		});
	}

	protected function GetStringBetween($string,$start,$end)
	{
		$str = explode($start,$string);
		if (empty($str[1])) return false;
		$str = explode($end,$str[1]);
		return $str[0];
	}

	protected function innerHTML($node)
	{
		$doc = new \DOMDocument();
		foreach ($node->childNodes as $child) 
		{
			$doc->appendChild($doc->importNode($child, true));
		}
		return $doc->saveHTML();
	}	

	protected function GetDom($html)
	{

		$previous_value = libxml_use_internal_errors(TRUE);
		$dom = new \DOMDocument;
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_use_internal_errors($previous_value);

		return $dom;
	}

	protected function GetXpath($dom)
	{

		$xpath = new \DOMXPath($dom);
		return $xpath;
	}	

	public function isJson($string) {
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}	

	/**
	 * Cookie
	 */	
	public function ReadEditThisCookie($data)
	{
		$cookies = '';
		foreach (json_decode($data,TRUE) as $read) {
			$cookies .= "{$read['name']}={$read['value']};";
		}

		return $cookies;
	}


	/**
	 * Auth
	 */

	public function Auth($cookie)
	{

		// set cookie on header for login
		$this->headers = array_merge($this->headers, ['Cookie: '.$cookie]);

		$connect = $this->Fetch($this->base, false , $this->headers , false, false, $this->proxy);
		if (!$connect['status']) return $connect;

		if (!strstr($connect['body'], 'mbasic_logout_button')) {
			return [
			'status' => false,
			'response' => 'Cookie Invalid'
			];	
		}

		$this->GetAccessToken();
		$this->GetUserInfoUseToken();

		return [
		'status' => true,
		'response' => array_merge($this->login,['cookie' => $cookie])
		];	
	}

	protected function GetAccessToken()
	{

		$url = "https://business.facebook.com/creatorstudio/home";

		$connect = $this->Fetch($url, false , $this->headers , false, false, $this->proxy);
		if (!$connect['status']) return $connect;

		$accesstoken = $this->GetStringBetween($connect['body'],'"userAccessToken":"','","rightsManagerVersion"');	
		if (!$accesstoken) {
			return [
			'status' => false,
			'response' => 'Can\'t Get AccessToken'
			];	
		}

		$this->login = [
		'accesstoken' => $accesstoken
		];
	}	

	protected function GetUserInfoUseToken()
	{

		$url = $this->apibase."me?fields=name,picture.type(large)&access_token=".$this->login['accesstoken'];

		$connect = $this->Fetch($url, false , $this->headers , false, false, $this->proxy);
		if (!$connect['status']) return $connect;

		$response = json_decode($connect['body'],true);

		if (array_key_exists('error', $response)) {
			return [
			'status' => false,
			'response' => 'Fail Get User Information'
			];	
		}else{
			$this->login = array_merge($this->login, [
				'userid' => $response['id'],
				'username' => $response['name'],
				'photo' => $response['picture']['data']['url']
				]);
		}
	}

	/**
	 * Comment
	 */
	public function ReadComment($postid,$deep = false) 
	{
		if ($deep) {
			if (filter_var($deep, FILTER_VALIDATE_URL)) { 
				$url = $deep;
			}else{
				$url = $this->base.$deep;
			}
		}else{
			$url = $this->base.$postid;
		}
		
		$connect = $this->Fetch($url, false , $this->headers , false, false, $this->proxy, true);
		if (!$connect['status']) {return $connect;}		

		$dom = $this->GetDom($connect['body']);
		$xpath = $this->GetXpath($dom);

		$GetDeepURL = $xpath->query('//div[@id="ufi_'.$postid.'"]/div/div[5]/div[contains(@id,"see_next")]/a/@href');
		// if xpath see next not found
		if($GetDeepURL->length < 1) {
			// use see prev
			$GetDeepURL = $xpath->query('//div[@id="ufi_'.$postid.'"]/div/div[4]/div[contains(@id,"see_prev")]/a/@href');
		}

		$deep = false;
		if ($GetDeepURL->length > 0) {
			$deep = $GetDeepURL[0]->value;
		}

		/**
		 * Extract
		 */
		$XpathCommentList = $xpath->query('//div[@id="ufi_'.$postid.'"]/div/div[5]/div');
		// if xpath see next not found
		if($XpathCommentList->length < 1) {
			// use see prev
			$XpathCommentList = $xpath->query('//div[@id="ufi_'.$postid.'"]/div/div[4]/div');
		}

		$extract = array();
		if($XpathCommentList->length > 0) 
		{

			foreach ($XpathCommentList as $key => $node) 
			{

				$commentid = $node->getAttribute('id');

				if (is_numeric($commentid)) {

					$build_commentid = "{$postid}_{$commentid}";
					$profilexpath = $xpath->query('//div[@id="'.$commentid.'"]/div/h3/a',$node)[0];
					$username = $profilexpath->nodeValue;
					$userurl = $profilexpath->getAttribute('href');

					$message = $xpath->query('//div[@id="'.$commentid.'"]/div/div[1]',$node)[0]->nodeValue;

					$media = $xpath->query('//div[@id="'.$commentid.'"]/div/div[2]/div/a',$node);
					if ($media->length > 0) {
						$media = $this->innerHTML($media[0]);
					}else{
						$media = false;
					}

					$CheckReplyTag = $xpath->query('//div[contains(@id,"'.$build_commentid.'")]/div/a',$node);

					$reply = false;
					if ($CheckReplyTag->length > 0) {
						$reply = $CheckReplyTag[0]->getAttribute('href');
					}

					$extract[] = [
					'userurl' => $userurl,
					'username' => $username,
					'commentid' => $build_commentid,
					'message' => $message,
					'media' => $media,
					'reply_url' => $reply
					];

				}

			}
		}

		return [
		'status' => true,
		'response' => $extract,
		'deep' => $deep
		];
	}

	public function ReadCommentReply($reply_url,$deep = false) 
	{
		if ($deep) {
			if (filter_var($deep, FILTER_VALIDATE_URL)) { 
				$url = $deep;
			}else{
				$url = $this->base.$deep;
			}
		}else{
			$url = $this->base.$reply_url;
		}
		
		$connect = $this->Fetch($url, false , $this->headers , false, false, $this->proxy);
		if (!$connect['status']) {return $connect;}		

		$dom = $this->GetDom($connect['body']);
		$xpath = $this->GetXpath($dom);

		$GetDeepURL = $xpath->query('/html/body/div/div/div[2]/div/div[1]/div[2]/div[1]/a/@href');

		$deep = false;
		if ($GetDeepURL->length > 0) {
			$deep = $GetDeepURL[0]->value;
		}

		/**
		 * Extract
		 */
		$XpathCommentList = $xpath->query('//div[@id="objects_container"]/div/div[1]/div[2]/div');
		$extract = array();
		if($XpathCommentList->length > 0) 
		{

			foreach ($XpathCommentList as $ked => $node) 
			{

				$commentid = $node->getAttribute('id');

				if (is_numeric($commentid)) {
					$build_commentid = "{$commentid}";
					$profilexpath = $xpath->query('//div[@id="'.$commentid.'"]/div/h3/a',$node)[0];
					$username = $profilexpath->nodeValue;
					$userurl = $profilexpath->getAttribute('href');

					$message = $xpath->query('//div[@id="'.$commentid.'"]/div/div[1]',$node)[0]->nodeValue;

					$media = $xpath->query('//div[@id="'.$commentid.'"]/div/div[2]/div/a',$node);
					if ($media->length > 0) {
						$media = $this->innerHTML($media[0]);
					}else{
						$media = false;
					}

					$CheckReplyTag = $xpath->query('//div[contains(@id,"'.$build_commentid.'")]/div/a',$node);

					$reply = false;
					if ($CheckReplyTag->length > 0) {
						$reply = $CheckReplyTag[0]->getAttribute('href');
					}

					$extract[] = [
					'userurl' => $userurl,
					'username' => $username,
					'commentid' => $build_commentid,
					'message' => $message,
					'media' => $media,
					'reply_url' => $reply
					];
				}
			}
		}

		return [
		'status' => true,
		'response' => $extract,
		'deep' => $deep
		];

	}
}