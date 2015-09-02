<?php

namespace omnilight\assets;

use yii\web\AssetBundle;


/**
 * Class DatePickerAsset
 */
class DatePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datepicker/dist';

    public $js = [
        'js/bootstrap-datepicker.min.js',
    ];
    public $css = [
        'css/bootstrap-datepicker3.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}