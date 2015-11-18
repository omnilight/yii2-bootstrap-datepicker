<?php

namespace omnilight\widgets;

use omnilight\assets\DatePickerAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\AssetBundle;
use yii\web\View;
use yii\widgets\InputWidget;


/**
 * Class DatePicker
 */
class DatePicker extends InputWidget
{
    /**
     * @var string
     */
    public $dateFormat;
    /**
     * @var string
     */
    public $language;
    /**
     * @var array the options for the underlying js widget.
     */
    public $clientOptions = [];
    /**
     * @var array the events for widget
     */
    public $clientEvents = [];
    /**
     * If true, no conflict option will be used that gives ability to use it with jquery ui
     * If string - it should be name of the asset with witch we do not want conflict
     * @var bool | string
     */
    public $noConflict = false;

    public function init()
    {
        parent::init();
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->formatter->dateFormat;
        }
    }


    public function run()
    {
        echo $this->renderWidget() . "\n";
        if (is_string($this->noConflict)) {
            /** @var AssetBundle $asset */
            $asset = $this->noConflict;
            $asset::register($this->view);
        }
        $asset = DatePickerAsset::register($this->view);

        $containerID = $this->options['id'];
        $language = $this->language ? $this->language : Yii::$app->language;

        $this->view->registerJsFile($asset->baseUrl.'/locales/bootstrap-datepicker.'.$language.'.min.js', [
            'depends' => ['yii\web\JqueryAsset', 'omnilight\assets\DatePickerAsset'],
        ]);

        if (strncmp($this->dateFormat, 'php:', 4) === 0) {
            $format = substr($this->dateFormat, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($this->dateFormat, 'datetime', $language);
        }
        $this->clientOptions['format'] = $this->convertDateFormat($format);
        $this->clientOptions['language'] = $language;

        if ($this->noConflict) {
            $this->registerNoConflict();
            $this->registerClientOptions('datepickerNoConflict', $containerID);
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }
    }

    protected function renderWidget()
    {
        $options = $this->options;

        if (!isset($options['value']) || empty($options['value'])) {
            if ($this->hasModel()) {
                $value = Html::getAttributeValue($this->model, $this->attribute);
            } else {
                $value = $this->value;
            }
            $options['value'] = $value;
        }

        if ($this->hasModel()) {
            $contents[] = Html::activeTextInput($this->model, $this->attribute, $options);
        } else {
            $contents[] = Html::textInput($this->name, $this->value, $options);
        }

        return implode("\n", $contents);
    }

    /**
     * Automatically convert the date format from PHP DateTime to Bootstrap datepicker format
     *
     * @return string
     */
    protected static function convertDateFormat($format)
    {
        return strtr($format, [
            // Days
            'j' => 'd', // 2
            'd' => 'dd', // 02
            'D' => 'D', // Mon
            'l' => 'DD', // Monday
            // Month
            'n' => 'm', // 9
            'm' => 'mm', // 09
            'M' => 'M', // Sep
            'F' => 'MM', // September
            // Year
            'y' => 'yy', // 15
            'Y' => 'yyyy', // 2015
        ]);
    }

    /**
     * Registers a specific jQuery UI widget options
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget
     */
    protected function registerClientOptions($name, $id)
    {
        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::encode($this->clientOptions);
            $js = "jQuery('#$id').$name($options)";
            foreach ($this->clientEvents as $name => $handler) {
                $js .= ".on('{$name}', {$handler})";
            }
            $js .= ';';
            $this->getView()->registerJs($js);
        }
    }

    private function registerNoConflict()
    {
        $js =<<<JS
(function() {
    var datepicker = $.fn.datepicker.noConflict();
    $.fn.datepickerNoConflict = datepicker;
})();
JS;
        $this->view->registerJs($js, View::POS_END, 'bootstrap-datepicker-no-conflict');
    }
}