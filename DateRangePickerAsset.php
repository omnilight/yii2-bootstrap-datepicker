<?php

namespace omnilight\assets;

use yii\web\AssetBundle;


/**
 * Class DatePickerAsset
 */
class DatePickerAsset extends AssetBundle
{
    public $sourcePath = '@bower/bootstrap-datepicker';

    public $js = [
        'daterangepicker.js'
    ];
} 