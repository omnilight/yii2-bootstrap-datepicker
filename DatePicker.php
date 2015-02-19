<?php

namespace omnilight\widgets;
use omnilight\assets\DateRangePickerBootstrap2Asset;
use omnilight\assets\DateRangePickerBootstrap3Asset;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use yii\helpers\Html;


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
        echo $this->renderWidget() ."\n";

        $containerID = $this->options['id'];
        $language = $this->language ? $this->language : Yii::$app->language;

        if (strncmp($this->dateFormat, 'php:', 4) === 0) {
            $format = substr($this->dateFormat, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($this->dateFormat, 'datetime', $language);
        }
        $this->clientOptions['format'] = $this->convertDateFormat($format);
        $this->clientOptions['timePicker'] = $this->timePicker;
        $this->clientOptions['timePicker12Hour'] = $this->timePicker12Hour;
        $this->clientOptions['separator'] = $this->separator;
        if ($this->defaultRanges && ArrayHelper::getValue($this->clientOptions, 'range') === null) {
            $this->clientOptions['ranges'] = [
                'Today' => new JsExpression('[new Date(), new Date()]'),
                'Yesterday' => new JsExpression('[moment().subtract("days", 1), moment().subtract("days", 1)]'),
                'Last 7 Days' => new JsExpression('[moment().subtract("days", 6), new Date()]'),
                'Last 30 Days' => new JsExpression('[moment().subtract("days", 29), new Date()]'),
                'This Month' => new JsExpression('[moment().startOf("month"), moment().endOf("month")]'),
                'Last Month' => new JsExpression('[moment().subtract("month", 1).startOf("month"), moment().subtract("month", 1).endOf("month")]'),
            ];
        }


        $this->registerClientOptions('daterangepicker', $containerID);
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

    /**
     * Automatically convert the date format from PHP DateTime to Moment.js DateTime format
     * as required by bootstrap-daterangepicker plugin.
     *
     * @see http://php.net/manual/en/function.date.php
     * @see http://momentjs.com/docs/#/parsing/string-format/
     *
     * @param string $format the PHP date format string
     *
     * @return string
     * @author Kartik Visweswaran, Krajee.com, 2014
     */
    protected static function convertDateFormat($format)
    {
        return strtr($format, [
            // meridian lowercase remains same
            // 'a' => 'a',
            // meridian uppercase remains same
            // 'A' => 'A',
            // second (with leading zeros)
            's' => 'ss',
            // minute (with leading zeros)
            'i' => 'mm',
            // hour in 12-hour format (no leading zeros)
            'g' => 'h',
            // hour in 12-hour format (with leading zeros)
            'h' => 'hh',
            // hour in 24-hour format (no leading zeros)
            'G' => 'H',
            // hour in 24-hour format (with leading zeros)
            'H' => 'HH',
            //  day of the week locale
            'w' => 'e',
            //  day of the week ISO
            'W' => 'E',
            // day of month (no leading zero)
            'j' => 'D',
            // day of month (two digit)
            'd' => 'DD',
            // day name short
            'D' => 'DDD',
            // day name long
            'l' => 'DDDD',
            // month of year (no leading zero)
            'n' => 'M',
            // month of year (two digit)
            'm' => 'MM',
            // month name short
            'M' => 'MMM',
            // month name long
            'F' => 'MMMM',
            // year (two digit)
            'y' => 'YY',
            // year (four digit)
            'Y' => 'YYYY',
            // unix timestamp
            'U' => 'X',
        ]);
    }
}