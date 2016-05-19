<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/static/plugins/bootstrap/css/bootstrap.min.css'); ?>
    <?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/static/css/pc/boss/sb-admin-2.css'); ?>
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	 <title>后台管理系统</title>
</head>
<body>
    <div id="wrapper">
        <?php 
            if ($breadCrumbs = $this->getRenderParams('breadCrumbs', array())) {
        ?>
        <div class="row">
            <div class="col-lg-12">
                <h5 class="page-header text-danger">
            <?php 
                foreach ($breadCrumbs as $index => $breadCrumb) {
                    echo $index > 0 ? ' / ' . $breadCrumb : $breadCrumb;
                }
                ?>
                </h5>
            </div>
        </div>
        <?php
            }
        ?>
        <?php echo $content; ?>
    </div>
</body>
</html>
