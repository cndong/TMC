<?php
class IndexController extends ApiController {
    public $defaultAction = 'publicIndex';
    
    public function actionPublicIndex() {
        echo 'Bye Bye';
    }
}