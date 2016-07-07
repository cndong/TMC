<?php
class F {
    public static $return = array(
        'rc' => RC::RC_SUCCESS,
        'msg' => '',
        'data' => array()
    );
    public static function corReturn($data = array(), $msg = '') {
        $rtn = self::$return;
        $rtn['data'] = $data;
        $rtn['msg'] = $msg;
    
        return $rtn;
    }
    
    public static function errReturn($rc, $msg = '') {
        $rtn = self::$return;
        $rtn['rc'] = $rc;
        $rtn['msg'] = $msg ? $msg : RC::getMsg($rc);
        $rtn['data'] = new stdClass();
    
        return $rtn;
    }
    
    public static function isCorrect($res) {
        return $res['rc'] === RC::RC_SUCCESS;
    }
    
    public static function getCurlError($res) {
        if (Curl::isRequestError($res)) {
            return RC::RC_EXT_CURL_REQUEST_ERROR;
        }
        if (Curl::isJsonError($res)) {
            return RC::RC_EXT_CURL_JSON_ERROR;
        }
        
        return RC::RC_EXT_CURL_SERVER_ERROR;
    }
    
    private static function _getParam($key, $default = Null, $isGet = True) {
        $func = $isGet ? 'getQuery' : 'getPost';
        if (is_array($key)) {
            $rtn = array();
            foreach ($key as $k => $v) {
                if (is_int($k)) {
                    $rtn[$v] = Yii::app()->request->$func($v, $default);
                } else {
                    $rtn[$k] = Yii::app()->request->$func($k, $v);
                }
            }
        } else {
            $rtn = Yii::app()->request->$func($key, $default);
        }
    
        return $rtn;
    }
    
    public static function getQuery($key, $default = Null) {
        return self::_getParam($key, $default, True);
    }
    
    public static function getPost($key, $default = Null) {
        return self::_getParam($key, $default, False);
    }
    
    public static function arrayGetByKeys($params, $keys) {
        if (is_int(key($keys))) {
            $keys = array_fill_keys($keys, True);
        }
    
        $rtn = array();
        foreach ($keys as $key => $v) {
            if ((is_array($params) && isset($params[$key])) || (is_object($params) && isset($params->$key))) {
                $value = is_array($params) ? $params[$key] : $params->$key;
                $rtn[$key] = is_array($v) ? self::arrayGetByKeys($value, $v) : $value;
            } else {
                $rtn[$key] = Null;
            }
        }
    
        return $rtn;
    }
    
    public static function arrayAddField($lines, $field) {
        $rtn = array();
        foreach ($lines as $v) {
            $rtn[$v[$field]] = $v;
        }
    
        return $rtn;
    }
    
    public static function arrayGetField($lines, $field, $isUnique = False) {
        $rtn = array();
        foreach ($lines as $line) {
            if (is_object($line)) {
                $v = isset($line->$field) ? $line->$field : Null;
            } else {
                $v = isset($line[$field]) ? $line[$field] : Null;
            }
            if ($isUnique) {
                $rtn[$v] = 1;
            } else {
                $rtn[] = $v;
            }
        }
    
        return $isUnique ? array_keys($rtn) : $rtn;
    }
    
    public static function arrayChangeKeys($params, $k2k, $isFilter = True) {
        $rtn = array();
        foreach ($params as $k => $v) {
            if (is_array($params)) {
                $v = isset($params[$k]) ? $params[$k] : Null;
            } else {
                $v = isset($params->$k) ? $params->$k : Null;
            }
    
            if (isset($k2k[$k])) {
                $rtn[$k2k[$k]] = $v;
            } elseif (!$isFilter) {
                $rtn[$k] = $v;
            }
        }
    
        return $rtn;
    }
    
    public static function arrayCalculateKeys($lines, $key, $func) {
        $data = array();
        foreach ($lines as $line) {
            $rtn[] = is_object($line) ? $line->$key : $line[$key];
        }
        
        return call_user_func($func, $data);
    }
    
    public static function isJson($str) {
        json_decode($str);
        return json_last_error() == JSON_ERROR_NONE;
    }
    
    public static function isTime($str, $format = 'H:i:s') {
        return self::isDateTime($str, $format);
    }
    
    public static function isDate($str, $format = 'Y-m-d') {
        return self::isDateTime($str, $format);
    }
    
    public static function isDateTime($str, $format = 'Y-m-d H:i:s'){
        return date($format, strtotime($str)) == $str;
    }
    
    public static function isTimestamp($str) {
        return strtotime(date('Y-m-d H:i:s', $str)) > strtotime('1970-01-01 00:00:01');
    }
    
    public static function checkParamCall($params, $k, $format) {
        return is_string($format) ? self::staticEvaluateExpression($format . '($params[$k])', array('params' => $params, 'k' => $k)) : call_user_func($format, $params[$k]);
    }
    
    public static function checkParam($params, $k, $format) {
        if ($isNot = !strncmp($format, '!', 1)) {
            $format = substr($format, 1);
        }
    
        if (!($isCanRef = in_array(substr($format, -5), array('isset', 'empty'))) && !isset($params[$k])) {
            return $isNot ^ False;
        }
    
        if ($isArr = substr($format, 0, 6) == 'array(') {
            $format = self::staticEvaluateExpression($format);
        }
    
        if (is_callable($format) || $isCanRef) {//isset, empty等不能用is_callable
            return $isNot ^ self::checkParamCall($params, $k, $format);
        } elseif (!strncmp($format, '/', 1) && !strncmp($format{strlen($format) - 1}, '/', 1)) {
            return $isNot ^ preg_match($format, $params[$k]);
        } elseif ($params[$k] !== $format) {
            return $isNot ^ False;
        }
    
        return $isNot ^ False;
    }
    
    public static function checkParams($params, $formats, $isRtnFormatKeys = True) {
        $rtn = $isRtnFormatKeys ? array() : $params;
        foreach ($formats as $k => $format) {
            $isOr = $isAssign = False;
            if (strstr($format, '&&')) {
                $formatArr = explode('&&', $format);
            } elseif (strstr($format, '||')) {
                $formatArr = explode('||', $format);
                $isOr = True;
            } else if (strstr($format, '--')) {
                $formatArr = explode('--', $format);
                $isAssign = True;
            } else {
                $formatArr = array($format);
            }
    
            $indexMax = count($formatArr) - 1;
            foreach ($formatArr as $index => $subFormat) {
                if (self::checkParam($params, $k, $subFormat)) {
                    if ($isAssign) {//是则赋值, 否则表示值正确
                        $rtn[$k] = $formatArr[$index + 1];
                        break;
                    } else {
                        $rtn[$k] = $params[$k];
                        if ($isOr) {
                            break;
                        }
                    }
                } else {
                    if ($isOr && $index < $indexMax) {
                        continue;
                    } elseif ($isAssign) {
                        $rtn[$k] = $params[$k];
                        break;
                    } elseif (!$isAssign) {
                        $str = isset($params[$k]) ? $params[$k] : '';
                        $log = $k.'_'.$format.'__'.$str;
                        Q::log('Qmy::checkParams Error' . $log);
                        //echo $k, '<br />', $format, '<br />', isset($params[$k]) ? $params[$k] : Null;exit;
                        return False;
                    }
                }
            }
        }
    
        return $rtn;
    }
    
    public static function staticEvaluateExpression($_expression_, $_data_=array()) {
        extract($_data_);
        return eval('return ' . $_expression_ . ';');
    }
    
    public static function getHash($length, $type = 'alnum') {
        $list = array();
    
        if (in_array($type, array('num', 'alnum', 'lalnum', 'balnum'))) {
            $list[] = array('0', '9');
        }
        if (in_array($type, array('lal', 'alnum', 'lalnum'))) {
            $list[] = array('a', 'z');
        }
        if (in_array($type, array('bal', 'alnum', 'balnum'))) {
            $list[] = array('A', 'Z');
        }
    
        $rtn = '';
        for ($i = 0; $i < $length; $i++) {
            list($min, $max) = $list[array_rand($list)];
            $rtn .= chr(rand(ord($min), ord($max)));
        }
    
        return $rtn;
    }
    
    public static function urlencode($data) {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $k => $v) {
                if (is_scalar($v)) {
                    if (is_array($data)) {
                        $data[$k] = urlencode($v);
                    } else {
                        $data->$k = $v;
                    }
                } elseif (is_array($v)) {
                    $data[$k] = self::urlencode($v);
                } elseif (is_object($v)) {
                    $data->$k = self::urlencode($v);
                }
            }
        } else {
            $data = urlencode($data);
        }
    
        return $data;
    }
    
    public static function unicodeDecode($data) {
        if (!function_exists('replaceUnicodeEscapeSequence')) {
            function replaceUnicodeEscapeSequence($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }
        }
    
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replaceUnicodeEscapeSequence', $data);
    }
    
    public static function chunkSplit($str, $len, $end = '\n', $encode = 'UTF-8') {
        $rtn = array();
        $strLength = strlen($str);
        $i = 0;
        while ($i < $strLength) {
            $rtn[] = mb_substr($str, $i, $len, $encode);
            $i += $len;
        }
        
        return implode($end, $rtn);
    }
    
    public static function trim($str, $startRegex, $endRegex, $encode = 'utf-8') {
        if (($posStart = mb_strpos($str, $startRegex, 0, $encode)) === False || ($posEnd = mb_strrpos($str, $endRegex, 0, $encode)) === False) {
            return '';
        }
        
        $posStart = $posStart + mb_strlen($startRegex, $encode);
        $length = $posEnd - $posStart;
        
        return mb_substr($str, $posStart, $length, $encode);
    }
    
    public static  function getClientIP() {
        static $ip = Null;
        if ($ip) {
            return $ip;
        }
    
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (False !== ($pos = array_search('unknown', $arr))) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    
        return False !== ip2long($ip) ? $ip : '0.0.0.0';
    }
    
    public static function getSignature($params, $key, $isDelSign = True) {
        if ($isDelSign) {
            unset($params['sign']);
        }
        
        ksort($params);
        $queryString = self::buildQuery($params);
        
        return md5($queryString . '&' . $key);
    }
    
    public static function buildQuery($params, $isUrlEncode = False) {
        $queryString = array();
        foreach ($params as $k => $v) {
            $v = $isUrlEncode ? urlencode($v) : $v;
            $queryString[] = $k . '=' . $v;
        }
        $queryString = implode('&', $queryString);
        
        return $queryString;
    }
    
    public static function mergeArrayInt() {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach($next as $k => $v) {
                if(is_array($v) && isset($res[$k]) && is_array($res[$k]))
                    $res[$k]=self::mergeArrayInt($res[$k],$v);
                else
                    $res[$k]=$v;
            }
        }
        return $res;
    }
    
    public static function jsonFormatProtect(&$val){
        if($val !== True && $val !== False && $val !== Null){
            $val = urlencode($val);
        }
    }
    
    public static function jsonFormat($data, $indent = Null){
        array_walk_recursive($data, array('F', 'jsonFormatProtect'));
        $data = urldecode(json_encode($data));
        
        $rtn = '';
        $pos = 0;
        $length = strlen($data);
        $indent = isset($indent)? $indent : '    ';
        $newline = "\n";
        $prevchar = '';
        $outofquotes = True;
    
        for ($i = 0; $i <= $length; $i++) {
            $char = substr($data, $i, 1);
    
            if ($char == '"' && $prevchar != '\\') {
                $outofquotes = !$outofquotes;
            } elseif (($char == '}' || $char==']') && $outofquotes) {
                $rtn .= $newline;
                $pos --;
                for($j = 0; $j < $pos; $j++){
                    $rtn .= $indent;
                }
            }
    
            $rtn .= $char;
            if (($char == ',' || $char == '{' || $char == '[') && $outofquotes) {
                $rtn .= $newline;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
    
                for($j = 0; $j < $pos; $j++){
                    $rtn .= $indent;
                }
            }
    
            $prevchar = $char;
        }
    
        return $rtn;
    }
    
    public static function removeXss($string) {
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
        
        $tags1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $tags2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    
        $tags = array_merge($tags1, $tags2); 
    	for ($i = 0; $i < sizeof($tags); $i++) { 
    		$pattern = '/'; 
    		for ($j = 0; $j < strlen($tags[$i]); $j++) { 
    			if ($j > 0) { 
    				$pattern .= '('; 
    				$pattern .= '(&#[x|X]0([9][a][b]);?)?'; 
    				$pattern .= '|(&#0([9][10][13]);?)?'; 
    				$pattern .= ')?'; 
    			}
    			$pattern .= $tags[$i][$j]; 
    		}
    		$pattern .= '/i';
    		$string = preg_replace($pattern, ' ', $string);
    	}
    	
    	return $string;
    }
    
    public static $encryptWithBase64TR = array(
        '+' => '-',
        '/' => '_'
    );
    
    public static function encryptWithBase64($data, $key) {
        return strtr(base64_encode(Yii::app()->securityManager->encrypt($data, $key)), self::$encryptWithBase64TR);
    }
    
    public static function decryptWithBase64($data, $key) {
        return Yii::app()->securityManager->decrypt(base64_decode(strtr($data, array_flip(self::$encryptWithBase64TR))), $key);
    }
    
    public static function addQuote($str) {
        return '<{' . $str . '}>';
    }
    
    public static function trQuoteTemplate($template, $arr) {
        $tr = array();
        foreach ($arr as $k => $v) {
            $tr[self::addQuote($k)] = $v;
        }
        
        return strtr($template, $tr);
    }
    
    public static  function changeArrKey($array, $do='lcfirst'){
   		if(!is_array($array)) return $array;
   		$tempArray=array();
   		foreach ($array as $key=>$value){
   			$key=$do($key);
   			if(is_array($value)){
   				$value=self::changeArrKey($value);
   			}
   			$tempArray[$key]=$value;
   		}
   		return $tempArray;
   }
    
}