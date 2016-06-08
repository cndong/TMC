<?php
class TestController extends QController {
    public function actionTaobao() {
        $order = FlightCNOrder::model()->findByPk(9);
        var_dump($order->getTicketsWithRouteType());exit;
        var_dump(FlightCNOrder::createOrder($_POST));
        Yii::app()->end();
        exit;
        //echo F::decryptWithBase64('aR4M--Y3amXUc2DFUaP7QHy0pOKupdIDipCXwU6QW_MYPec9K6u9APYo1D4cf8G2uzRtXrCSSmBDN-lUps97_LtzCNnm5kvfNbgmFhP20XZEf3Sb0k36ZH-VLnTy942iSEVksLjrprCY3TrdtMp3HuQzj_4Hnlu_LN5FRn6gutXG8xZeJgvfWmXD9W_D6lkCpvoXxsEWRE5j0xKzndAsKa8OALaorX40TixT-NO2mxoMOSLGd3GtJYTJMbIw7Zeq9jNPiN-sy149iOpA3gJEuouyBcA7DTFHXwptsoCRpIi3pDjTSGPtK_JAwmhaZYAot8x47mMTyYg8ZE-ROD8Wy7d3AsDwJk8gnWujUxQjUnFGA5V5R1fl8z3xqqQmGUeYIjrE_MPmW0nPnjfU10dEN7LGeQVaQYoa3oXV5Gl8obMwZlx5SgvEdHiHRnNz_ua350mktb9QElNU706hJ8a5z8tthr-zBAwhk0ePxxZyMSulHzWPkQiK4-FaVv9y8LQg0CBRgNzCqAW-d0HHB70g2nmIWRo49rc64mxSG7wCyPbeyzYQxfIg0E-idORKh5wXd30bRo4hp25OG2BVBlu_GTWmEf_sJjqujMKZw7ivTBkWYWU9l3FqqQZoRcMkeidEGt5WtyqOW8eDujesiLTPUNqsWheUVK4HeC0RVvtax37xvV_i562bAA6OExHf3GvpNn6Q6pxEdxzbC3LqIYpD1lBYVyzN8-d52GMkfeu8l_AuQGIYDnHCoItkCPer0IIuMadZtHL9H7C-q_ErkF5LTVRF1w-f7FNW6C8Tj45o6nep1KDmUvSRiCoRzdQ1VC8VcAf5DawRfuv96fmL-2TG1eOISw_BUQbk', QEnv::$orderParamsKey[Dict::BUSINESS_FLIGHT]);
        //exit;
        //echo '<meta charset="utf-8">';
        $res = ProviderF::getCNFlightList(array('departCityCode' => 'BJS', 'arriveCityCode' => 'SHA', 'departDate' => '2016-05-20'));
        //$res = ProviderF::getCNFlightDetail(array('departCityCode' => 'BJS', 'arriveCityCode' => 'CAN', 'departDate' => '2016-05-22', 'flightNo' => 'MU5181'));
        //$res = ProviderF::getCNFlightDetail(array('departCityCode' => 'BJS', 'arriveCityCode' => 'SHA', 'departDate' => '2016-05-20', 'flightNo' => 'HU7609'));
        echo json_encode($res);exit;
        var_dump($res['data']);exit;
        var_dump(json_decode($res['data']['items']['at_n_search_item_v_o'][0]['attributes'], True));exit;
    }
}