<?php
class QF {
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
            $rtn = Yii::app()->request->$func($k, $default);
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
}