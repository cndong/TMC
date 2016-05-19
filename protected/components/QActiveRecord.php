<?php
class QActiveRecord extends CActiveRecord {
    protected $_cacheSaveTime = 86400;
    protected $_deleted = self::DELETED_F;
    
    const DELETED_T = 1;
    const DELETED_F = 0;
    
    public function beforeSave() {
        if ($this->isNewRecord) {
            if ($this->hasAttribute('ctime') && !isset($this->attributes['ctime'])) {
                $this->ctime = $this->getMetaData()->columns['ctime']->type == 'integer' ? Q_TIME : date('Y-m-d H:i:s', Q_TIME);
            }
            
            if ($this->hasAttribute('deleted') && !isset($this->attributes['deleted'])) {
                $this->deleted = $this->getMetaData()->columns['deleted']->type == 'integer' ? $this->_deleted : strval($this->_deleted);
            }
        }
        if ($this->hasAttribute('utime')) {
            $this->utime = $this->getMetaData()->columns['utime']->type == 'integer' ? Q_TIME : date('Y-m-d H:i:s', Q_TIME);
        }
        
        return True;
    }
    
    public function isDeleted() {
        return isset($this->deleted) ? $this->deleted == self::DELETED_T : False;
    }
    
    public function getCacheKey($pk = array(), $isRead = True) {
        $cacheKey = get_called_class();
        if (!($pkFields = $this->getMetaData()->tableSchema->primaryKey)) {
            return $cacheKey;
        }
        
        $tmp = array();
        if ($isRead) {
            $tmp = is_array($pk) ? $pk : array($pk);
        } else {
            $pkFields = is_array($pkFields) ? $pkFields : array($pkFields);
            foreach ($pkFields as $k => $v) {
                $tmp[] = $this->$v;
            }
        }
        
        return $cacheKey . '_' . implode('_', $tmp);
    }
    
    public function read($pk, $reload = False) {
        $cacheKey = $this->getCacheKey($pk, True);
        if ($reload || !($obj = Yii::app()->cache->get($cacheKey))) {
            if ($obj = self::readone($pk)) {
                Yii::app()->cache->set($cacheKey, $obj, $this->_cacheSaveTime);
            }
        }
        
        return $obj;
    }
    
    public function write($runValidation = True, $attributes = Null) {
        if ($rtn = $this->save($runValidation, $attributes)) {
            Yii::app()->cache->set($this->getCacheKey(array(), False), $this, $this->_cacheSaveTime);
        }
        
        return $rtn;
    }
    
    public static function readone($pk) {
        if (!($obj = call_user_func(array(get_called_class(), 'model'))->findByPk($pk))) {
            return Null;
        }
        
        if ($obj->isDeleted()) {
            return Null;
        }
        
        return $obj;
    }
}