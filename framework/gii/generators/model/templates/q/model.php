<?php echo "<?php\n"; ?>
class <?php echo $modelClass; ?> extends QActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
<?php if($connectionId!='db'):?>
    public function getDbConnection() {
        return Yii::app()-><?php echo $connectionId ?>;
    }
<?php endif?>
    public function tableName() {
        return '<?php echo $tableName; ?>';
    }

    public function rules() {
        return array(
<?php foreach($rules as $rule): ?>
            <?php echo $rule.",\n"; ?>
<?php endforeach; ?>
            array('<?php echo implode(', ', array_keys($columns)); ?>', 'safe', 'on' => 'search'),
        );
    }
}