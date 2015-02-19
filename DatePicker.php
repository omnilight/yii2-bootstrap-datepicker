<?php

namespace omnilight\widgets;

use omnilight\assets\DatePickerAsset;
use Yii;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\Json;
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


        $this->registerClientOptions('datepicker', $containerID);
    }

    protected function renderWidget()
    {
        if ($this->hasModel()) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $value = $this->value;
        }

        $options = $this->options;
        $options['value'] = $value;

        if ($this->hasModel()) {
            $contents[] = Html::activeTextInput($this->model, $this->attribute, $options);
        } else {
            $contents[] = Html::textInput($this->name, $value, $options);
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
            $js = "jQuery('#$id').$name($options);";
            $this->getView()->registerJs($js);
        }
    }
}