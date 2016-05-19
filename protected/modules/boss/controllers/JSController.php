<?php
class JSController extends QController {
    public function actionCarType() {
        echo 'var GCarTypes = ' . json_encode(Dict::$carTypes) . ';';
    }
    
    public function actionProviders() {
        echo 'var GProviders = ' . json_encode(Dict::$providers) . ';';
    }
    
    public function actionTFTypes() {
        echo 'var GTFTypes = ' . json_encode(Dict::$tfTypes) . ';';
    }
    
    public function actionRFTypes() {
        echo 'var GRFTypes = ' . json_encode(Dict::$rfTypes) . ';';
    }
    
    public function actionCities() {
        echo 'var GCities = ' . json_encode(Dict::$cities) . ';';
    }
}