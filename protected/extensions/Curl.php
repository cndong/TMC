<?php
/*
 * 随永杰 2015-09-09
 */
class Curl {
	public $key;
	public $header = array();//只有Set-Cookie和Cookie的值是数组'Cookie' => array('name' => 'username', 'value' => 'xxx')
	public $handler = Null;
	public $autoSetCookie = True;
	
	public $postDataType = 'concat';
	
	const POST_DATA_TYPE_BUILD = '';
	const POST_DATA_TYPE_OWN_CONCAT = 'concat';
	
	const STATUS_RIGHT = 0;
	const STATUS_JSON_ERROR = 1001;
	
	private static $_instances = array(); 
	public static function getInstance($key = 'curl', $headerConfig = array(), $curlConfig = array()) {
	    if (empty(self::$_instances[$key])) {
	        self::$_instances[$key] = new Curl($key, $headerConfig, $curlConfig);
	    }
	    
	    return self::$_instances[$key];
	}
    
    public static function mergeArray() {
    	$args = func_get_args();
		$res = array_shift($args);
		while (!empty($args)) {
			$next = array_shift($args);
			foreach ($next as $k => $v) {
				if (is_integer($k)) {
					isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
				} else if (is_array($v) && isset($res[$k]) && is_array($res[$k]))
					$res[$k] = self::mergeArray($res[$k], $v);
				else
					$res[$k] = $v;
			}
		}
		return $res;
    }
    
	public static function getStatus($status) {
	    return is_array($status) ? $status['status'] : $status;
	}
	
	public static function isCorrect($status) {
	    return self::STATUS_RIGHT == self::getStatus($status);
	}
	
	public static function isRequestError($status) {
	    $status = self::getStatus($status);
	    return $status < 0 || ($status > 0 && $status < 100);
	}
	
	public static function isJsonError($status) {
	    return self::STATUS_JSON_ERROR == self::getStatus($status);
	}
	
	public function debug() {
	    if (Q::isLocalEnv()) {
	       $this->initHandler(array(CURLOPT_PROXY => '127.0.0.1:8888'));
	    }
	    
	    return $this;
	}
	
	public function setHeaderContentTypeEncode() {
	    $this->initHeader(array('Content-Type' => 'application/x-www-form-urlencoded'));
	    return $this;
	}
	
	public function setHeaderContentTypeDecode() {
	    $this->initHeader(array('Content-Type' => 'multipart/form-data'));
	    return $this;
	}
	
	public function setPostDataType($postDataType) {
	    $this->postDataType = $postDataType;
	    return $this;
	}
	
	public static function postDataOwnConcat($data) {
	    $rtn = array();
	    foreach ($data as $k => $v) {
	        $rtn[] = "{$k}={$v}";
	    }
	    return implode('&', $rtn);
	}
	
	public function __construct($key = '', $headerConfig = array(), $curlConfig = array()) {
		$this->key = $key;
		$this->handler = curl_init();
		
		$headerConfig = self::mergeArray(self::getDefaultHeaderInit(), $headerConfig);
		$curlConfig = self::mergeArray(self::getDefaultHandlerInit(), $curlConfig);
		
		$this->initHeader($headerConfig);
		$this->initHandler($curlConfig);
	}
	
	//获取浏览器User-Agent
	public static function getUserAgent($type = '') {
		$config = array(
			'Safari_MAC' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
			'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)',
			'IE8' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
			'IE9' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;',
			'IE10' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
			'Firefox4.0.1' => 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1',
			'Firefox5.0.1' => 'Mozilla/5.0 (Windows NT 6.1; rv:34.0) Gecko/20100101 Firefox/34.0',
			'Opera11.11_MAC' => 'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11',
			'Opera11.11_WIN' => 'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11'
		);
		$type = !empty($type) && isset($config[$type]) ? $type : array_rand($config);
		return $config[$type];
	}
	
	//获取默认的请求头信息
	public static function getDefaultHeaderInit() {
		return array(
			'Accept' => '*/*',
			//'Accept-Encoding' => 'gzip, deflate',
			'Accept-Language' => 'zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
			'Cache-Control' => 'no-cache',
			'Connection' => 'keep-alive',
			'If-Modified-Since' => '0',
			'User-Agent' => self::getUserAgent(),
			//'X-Forwarded-For' => mt_rand(58, 61) . '.' . mt_rand(10, 200) . '.' . mt_rand(10, 200) . '.' . mt_rand(10, 200)
		);
	}
	
	//获取默认的curl配置信息
	public static function getDefaultHandlerInit() {
		return array(
			CURLINFO_HEADER_OUT => True,
			CURLOPT_SSL_VERIFYPEER => False,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 30,
		);
	}
	
	//配置请求的header头信息
	public function initHeader($headerConfig, $isMerge = True) {
		if ($isMerge) {
			$this->header = self::mergeArray($this->header, $headerConfig);
		} else {
			foreach ($headerConfig as $k => $v) {
				$this->header[$k] = $v;
			}
		}
		return $this;
	}
	
	public function initHandler($name, $value = Null) {
	    if (is_array($name)) {
	        curl_setopt_array($this->handler, $name);
	    } else {
	        curl_setopt($this->handler, $name, $value);
	    }
		return $this;
	}
	
	//把响应头中的set-cookie字符串(aaa=xxx;path=xxx;) 每个set-cookie需要转一次
	public static function headerSetCookieStr2Arr($header) {
		$cookie = explode(';', $header);
		$kv = explode('=', trim($cookie[0]));
		return array(
			'name' => trim($kv[0]),
			'value' => trim($kv[1])
		);
	}
	
	//把请求头中的cookie字符串(aaaa=xxxx;bbbb=yyyy;...)转为通用header中的cookie数组
	public static function headerCookieStr2Arr($header) {
		$rtn = array();
		
		$cookies = explode(';', $header);
		foreach ($cookies as $cookie) {
			$tmp = explode('=', $cookie);
			$rtn[trim($tmp[0])] = array(
				'name' => trim($tmp[0]),
				'value' => trim($tmp[1])
			);
		}
		
		return $rtn;
	}
	
	//把通用header中的cookie数组转为请求头中的cookie字符串(aaaa=xxxx;bbbb=yyyy;...)
	public static function headerCookieArr2Str($cookies) {
		$rtn = array();
		foreach ($cookies as $cookie) {
			$rtn[] = "{$cookie['name']}={$cookie['value']}";
		}
		return implode(';', $rtn);
	}
	
	//把请求或相应的header头转换为数组(本curl类的header头通用数组), 其中header头的Set-Cookie可能重复多次
	public function headerStr2Arr($content) {
		$rtn = array();
		$headers = explode("\r\n", $content);
		foreach ($headers as $header) {
			$header = explode(':', $header, 2);
			if (count($header) < 2) {
				continue;
			}
	
			if ($header[0] == 'Set-Cookie') {
				$tmp = self::headerSetCookieStr2Arr($header[1]);
				$rtn[$header[0]][$tmp['name']] = $tmp;
			} elseif ($header[0] == 'Cookie') {
				$rtn[$header[0]] = self::headerCookieStr2Arr($header[1]);
			} else {
				$rtn[trim($header[0])] = trim($header[1]);
			}
		}
		return $rtn;
	}
	
	//获取curl_setopt设置header时的值
	public function getCurlHeader() {
		$rtn = array();
		foreach ($this->header as $k => $v) {
			if ($k == 'Cookie') {
				$v = self::headerCookieArr2Str($v);
			}
			$rtn[] = $k . ':' . $v;
		}
		return $rtn;
	}
	
	//设置是否自动更新cookie
	public function setAutoSetCookie($status) {
		$this->autoSetCookie = $status;
	}
	
	public static function addQuote($k) {
	    return '<{' . $k . '}>';
	}
	
	private function _initUrl($url, $urlData, $isUrlEncode) {
	    if (ctype_digit(key($urlData))) {
	        array_unshift($urlData, $url);
	        $url = count($urlData) > 1 ? call_user_func_array('sprintf', $urlData) : $url;
	    } else {
	        $count = 0;
	        foreach ($urlData as $k => $v) {
	            $tmp = self::addQuote($k);
	            if (strpos($url, $tmp) !== False) {
	                $count++;
	            }
	            
	            $url = str_replace($tmp, $v, $url);
	        }
	        
	        if ($count <= 0 && $urlData) {
	            $queryString = array();
	            foreach ($urlData as $k => $v) {
	                $v = $isUrlEncode ? urlencode($v) : $v;
	                $queryString[] = "{$k}={$v}";
	            }
	            $url .= implode('&', $queryString);
	        }
	    }
	    
	    return $url;
	}
	
	//发送HTTP请求及处理
	private function _request($urlType, $urlData = array(), $isUrlEncode = False) {
		$rtn = array('status' => self::STATUS_RIGHT, 'error_msg'=>'', 'data' => '', 'info' => array(), 'qheader' => array(), 'pheader' => array(), 'urlType' => array());
		
		if (is_array($urlType)) {
			$urlConfig = $urlType;
		} else {
			$urlConfig = array('reqUrl' => $urlType);
		}
		
		$rtn['urlType'] = $urlConfig;
		
		$urlConfig = self::mergeArray(array('reqLogUrl' => False, 'reqLogPData' => False, 'reqLogQData' => True), $urlConfig);
		
		$url = $this->_initUrl($urlConfig['reqUrl'], $urlData, $isUrlEncode);

		$this->initHandler(array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $this->getCurlHeader()
		));
        
		$content = curl_exec($this->handler);
		$content = explode("\r\n\r\n", $content, 2);
		$rtn['data'] = count($content) < 2 ? $content[0] : $content[1];
		if (($errno = curl_errno($this->handler)) != 0) {
		    $rtn['status'] = $errno;
		    $rtn['error_msg'] = curl_error($this->handler);
		    return $rtn;
		}
		
		$rtn['info'] = curl_getinfo($this->handler);
		if (empty($rtn['info']['request_header'])) {
			$rtn['info']['request_header'] = '';
		}
		$rtn['qheader'] = $this->headerStr2Arr($rtn['info']['request_header']);

		if ($rtn['info']['http_code'] != 200) {
		    $rtn['status'] = $rtn['info']['http_code'];
			$rtn['error_msg'] = curl_error($this->handler);
			return $rtn;
		}
		
		if (count($content) < 2) {
			$rtn['data'] = $content[0];
		} else {
			//在uuwise中的上传验证码步骤 返回两个头 其中第一个为 HTTP/1.1 100 Continue
			$tmp = explode("\r\n", $content[1]);
			if (preg_match('/\s*?HTTP\/1.[01]\s+?\d{3}\s+?\w+?\s*?/i', $tmp[0])) {
				$content = explode("\r\n\r\n", $content[1], 2);
			}
			$rtn['pheader'] = $this->headerStr2Arr($content[0]);
			$rtn['data'] = count($content) < 2 ? $content[0] : $content[1];
			
			if ($this->autoSetCookie && !empty($rtn['pheader']['Set-Cookie'])) {
				$this->initHeader(array('Cookie' => $rtn['pheader']['Set-Cookie']));
			}
		}
		
		return $rtn;
	}
	
	public function get($urlType, $urlData = array(), $isUrlEncode = False) {
		$this->initHandler(array(
			CURLOPT_POST => 0
		));
		return $this->_request($urlType, $urlData, $isUrlEncode);
	}
	
	public function post($urlType, $postData = array(), $urlData = array(), $isUrlEncode = False) {
	    Q::log($postData, 'curl.post.request: '.$urlType);
		$ifHaveFile = False;
		if (is_array($postData)) {
    		foreach ($postData as $k => $v) {
    			if (is_string($v) && isset($v{0}) && $v{0} == '@' && file_exists(substr($v, 1))) {
    				$ifHaveFile = True;
    				break;
    			}
    		}
		}
		if ($ifHaveFile || is_string($postData)) {
		    $this->setHeaderContentTypeDecode();
		} else {
		    $this->setHeaderContentTypeEncode();
		}
		
		if (is_array($postData)) {
		    $postData = $this->postDataType == self::POST_DATA_TYPE_OWN_CONCAT ? self::postDataOwnConcat($postData) : http_build_query($postData);
		}
		
		$this->initHandler(array(
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $postData
		));
		$res = $this->_request($urlType, $urlData, $isUrlEncode);
		Q::log($res['status'].'|'.$res['error_msg'].'|'.$res['data'], 'curl.post.response');
		return $res;
	}
	
	public function getC($urlType, $urlData = array(), $isUrlEncode = False) {
		return $this->get($urlType, $urlData, $isUrlEncode);
	}
	
	public function getJ($urlType, $urlData = array(), $isUrlEncode = False) {
		$rtn = $this->get($urlType, $urlData, $isUrlEncode);
		if ($rtn['status'] == self::STATUS_RIGHT) {
			if (($rtn['data'] = json_decode($rtn['data'], True)) === Null) {
			    $rtn['status'] = self::STATUS_JSON_ERROR;
			}
		}
		return $rtn;
	}
	
	public function postC($urlType, $postData = array(), $urlData = array(), $isUrlEncode = False) {
		return $this->post($urlType, $postData, $urlData, $isUrlEncode);
	}
	
	public function postJ($urlType, $postData = array(), $urlData = array(), $isUrlEncode = False) {
		$rtn = $this->post($urlType, $postData, $urlData, $isUrlEncode);
		if ($rtn['status'] == self::STATUS_RIGHT) {
            if (($rtn['data'] = json_decode($rtn['data'], True)) === Null) {
			    $rtn['status'] = self::STATUS_JSON_ERROR;
			}
		}
		return $rtn;
	}
}