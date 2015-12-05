<?php
/**
 *##  TbSelect2 class file.
 *
 * @author Antonio Ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

/**
 *## Select2 wrapper widget
 *
 * @see http://ivaynberg.github.io/select2/
 *
 * @package booster.widgets.forms.inputs
 */
class TbSelect2 extends CInputWidget {

	/**
	 * @var TbActiveForm when created via TbActiveForm.
	 * This attribute is set to the form that renders the widget
	 * @see TbActionForm->inputRow
	 */
	public $form;

	/**
	 * @var array @param data for generating the list options (value=>display)
	 */
	public $data = array();

	/**
	 * @var string[] the JavaScript event handlers.
	 */
	public $events = array();

	/**
	 * @var
	 */
	public $options;

	/**
	 * @var bool
	 * @since 2.1.0
	 */
	public $disabled = false;

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		$this->normalizeData();
		$this->normalizeOptions();
		$this->normalizePlaceholder();

		// disabled
		if (!empty($this->htmlOptions['disabled'])) {
			$this->disabled = true;
		}
	}

	/**
	 * Runs the widget.
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if ($this->hasModel()) {
			if ($this->form) {
				echo $this->form->dropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);
			} else {
				echo CHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);
			}
		} else {
			echo CHtml::dropDownList($name, $this->value, $this->data, $this->htmlOptions);
		}

		$this->registerClientScript($id);
	}

	/**
	 * Registers required client script for bootstrap select2. It is not used through bootstrap->registerPlugin
	 * in order to attach events if any
	 * @param $id
	 * @throws CException
	 */
	public function registerClientScript($id)
	{
		Booster::getBooster()->registerPackage('select2');

		if(empty($this->options['language']) && empty($this->htmlOptions['lang']))
			$this->options['language'] = Yii::app()->language;

		$options = CJavaScript::encode($this->options);

		if(!empty($this->value) || $this->value===0 || $this->value==='0') {
			if(is_array($this->value)) {
				$data = CJSON::encode($this->value);
			} else {
				$data = $this->value;
			}

			//trigger maybe removed
			$defValue = ".val($data).trigger('change')";
		}
		else
			$defValue = '';

		if ($this->disabled) {
			$defValue .= ".prop('disabled', true)";
		}

		ob_start();
		echo "jQuery('#{$id}').select2({$options})";
		foreach ($this->events as $event => $handler) {
			echo ".on('{$event}', " . CJavaScript::encode($handler) . ")";
		}
		echo ';';
		if(!empty($defValue)) {
			echo "jQuery('#{$id}')".$defValue.';';
		}

		Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean());
	}

	private function normalizeData()
	{
		if (!$this->data)
			$this->data = array();
	}

	private function normalizeOptions()
	{
		if (empty($this->options)) {
			$this->options = array();
		}
	}

	private function normalizePlaceholder()
	{
		if (!empty($this->htmlOptions['placeholder']))
			$this->options['placeholder'] = $this->htmlOptions['placeholder'];

		if (!empty($this->options['placeholder']) && empty($this->htmlOptions['multiple']))
			$this->data = array('' => '') + $this->data;
	}
}
