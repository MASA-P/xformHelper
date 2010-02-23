<?php

/*
 * XFormHelper
 *
 * On confirm screen, each method just show value of post data
 *  insted of making form input tag.
 *
 * On confirm screen, controller needs to set following,
 *  $this->params['xformHelperConfirmFlag'] = true;
 *  or  
 *  XformHelper::confirmScreenFlag = true;
 *
 * If you want to mask a password field on confirm screen,
 *  use password() insted of input().
 *
 * If you want to change separator of datetime,
 *  set separator value on the changeDatetimeSeparator property.
 */

App::import('helper', 'Form');
class XformHelper extends FormHelper {

	var $confirmScreenFlag = false;

	// Do htmlescapechar() on confirm screen.
	var $doHtmlEscape = true;

	// Do nl2br on confirm screen.
	var $doNl2br = true;

	// If set true and change $doHtmlEcpane or $doNl2br properties,
	// these properties are not changed by default value after output.
	var $escapeBrPermanent = false;

	// The field has array data like checkbox(),
	// thease array values join with this separator.
	var $confirmJoinSeparator = ', ';


	/*
	 *   var $changeDatetimeSeparator = array(
     *       'datefmt' => array(
     *           'year' => ' / ',
     *           'month' => ' / ',
     *           'day' => '',
     *           'afterDateTag' => '&nbsp;&nbsp;&nbsp;',
     *           ),
     *       'timefmt' => array(
     *           'hour' => ' : ',
     *           'min' => '',
     *           'meridian' => '',
     *           )
     *       );
	 */
	var $changeDatetimeSeparator = null;


	function __construct($config = null) {

		foreach($config as $key => $val) {
			$this->{$key} = $val;
		}	
		parent::__construct();
	}

	function input($fieldName, $options = array()) {

		$defaults = array('label' => false, 'error' => false, 'div' => false);
		$options = array_merge($defaults, $options);

		return parent::input($fieldName, $options);
	}


	function error($field, $text = null, $options = array()) {
		$defaults = array('wrap' => true);
		$options = array_merge($defaults, $options);
		return parent::error($field, $text, $options);
	}	


	function dateTime($fieldName, $dateFormat = 'DMY', $timeFormat = '12', $selected = null, $attributes = array(), $showEmpty = true) {

        if($this->checkConfirmScreen()) {
			$args = func_get_args();
            return $this->getConfirmDatetime($fieldName, $args);
        }

		if(empty($attributes['monthNames'])){
			$attributes['monthNames'] = false;
		}


		$separator = (!empty($attributes['separator'])) ? $attributes['separator'] : '-';
		$datefmt = array(
				'year' => $separator,
				'month' => $separator,
				'day' => '',
				'afterDateTag' => '',
				);
		$timefmt = array(
				'hour' => ':',
				'min' => '',
				'meridian' => '',
				);

		if(!empty($this->changeDatetimeSeparator)) {
			$datefmt = $this->changeDatetimeSeparator['datefmt'];
			$timefmt = $this->changeDatetimeSeparator['timefmt'];
		}

		$out = $out_date = $out_time = null;
		if(!empty($dateFormat) && $dateFormat !== 'NONE') {
			$tmp_separator = (!empty($attributes['separator'])) ? $attributes['separator'] : null;
			$attributes['separator'] = '__/__';
			$out_date = parent::datetime($fieldName, $dateFormat, 'NONE', $selected, $attributes, $showEmpty);
			$attributes['separator'] = $tmp_separator;
		}

		if(!empty($timeFormat) && $timeFormat !== 'NONE') {
			$out_time = parent::datetime($fieldName, 'NONE', $timeFormat, $selected, $attributes, $showEmpty);
		}

		if(!empty($out_date)){
			$pattern = '#^(.+?)__/__(.+?)__/__(.+?)$#is';
			$out .= preg_replace($pattern, '$1' . $datefmt['year']. ' $2'.$datefmt['month']. ' $3'. $datefmt['day'], $out_date);
			$out .= $datefmt['afterDateTag'];
		}

		if(!empty($out_time) && $timeFormat == 24) {
			$pattern = '#^<select(.*?)</select>:<select(.*?)$#is' ;
			$replace = '<select$1</select>' . $timefmt['hour'] . ' <select$2' . $timefmt['min'];
			$out .= preg_replace($pattern, $replace, $out_time);
		}

		if(!empty($out_time) && $timeFormat == 12) {
			$pattern = '#^<select(.*?)</select>:<select(.*?)</select> <select(.*?)$#is' ;
			$replace = '<select$1</select>' . $timefmt['hour'] . ' <select$2</select>' . $timefmt['min'] . '<select$3';
			$out .= preg_replace($pattern, $replace, $out_time);
		}

		return $out;
	}

	function password($fieldName) {
        if($this->checkConfirmScreen()) {
			$value = $this->getConfirmInput($fieldName);
			if(!empty($value)) {
            	return '*****';
			}else {
				return '';
			}
        }

		$args = func_get_args();
		$args[1]['value'] =  ''; //password value clear if show input form.
		return call_user_func_array( array($this, 'parent::password'), $args);
	}


	function textarea($fieldName) {
        if($this->checkConfirmScreen()) {
            return $this->getConfirmInput($fieldName);
        }

		$args = func_get_args();
		return call_user_func_array( array($this, 'parent::textarea'), $args);
	}

	function text($fieldName) {
        if($this->checkConfirmScreen()) {
            return $this->getConfirmInput($fieldName);
        }

		$args = func_get_args();
		return call_user_func_array( array($this, 'parent::text'), $args);
	}

	function radio($fieldName) {
        if($this->checkConfirmScreen()) {
            return $this->getConfirmInput($fieldName);
        }
		$args = func_get_args();
		return call_user_func_array( array($this, 'parent::radio'), $args);
		
	}


	function select($fieldName) {
        if($this->checkConfirmScreen()) {
            return $this->getConfirmInput($fieldName);
        }
		$args = func_get_args();
		return call_user_func_array( array($this, 'parent::select'), $args);
		
	}

	function checkbox($fieldName) {
        if($this->checkConfirmScreen()) {
            return $this->getConfirmInput($fieldName);
        }
		$args = func_get_args();
		return call_user_func_array( array($this, 'parent::checkbox'), $args);
	}

	function checkConfirmScreen() {
		if(!empty($this->params['xformHelperConfirmFlag']) && $this->params['xformHelperConfirmFlag'] === true) {
			return true;
		}

		if($this->confirmScreenFlag === true) {
			return true;
		}
		return false;
	}


	function _confirmValueOutput($data) {
		if($this->doHtmlEscape) {
			$data = h($data);
		}

		if($this->doNl2br) {
			$data = nl2br($data);
		}

		if($this->escapeBrPermanent === false) {
			$this->doHtmlEscape = true;
			$this->doNl2br = true;
		}

		return $data;
	}


	function _getFieldData($fieldName) {
		$modelname = current($this->params['models']);

		// for Model.field pattern
		$model_field = explode('.', $fieldName);
		if(!empty($model_field[1])) {
			$fieldName = $model_field[1];
			$data = $this->data[$model_field[0]];

		}else{
			if(empty($modelname)) {
				$data = current($this->data);
			}else {
				$data = $this->data[$modelname];
			}
		}

		if(empty($data[$fieldName])) {
			return false;
		}
		
		return $data;
	}


	
	function getConfirmInput($fieldName) {

		if($data = $this->_getFieldData($fieldName)) {

			if(is_array($data[$fieldName])) {
				$out = join($this->confirmJoinSeparator, $data[$fieldName]);
			}else {
				$out = $data[$fieldName];
			}
			return $this->_confirmValueOutput($out);
		} 

		return '';
	}


	function getConfirmDatetime($fieldName, $options = array()) {
		if($data = $this->_getFieldData($fieldName)) {
			if(is_array($data[$fieldName])) {
				$nothing = true;
				foreach($data[$fieldName] as $key => $val) {
					if(!empty($val)){
						$nothing = false;
					}
				}

				if($nothing) {
					return '';
				}

				$separator = (!empty($options[4]['separator'])) ? $options[4]['separator'] : '-';
				$datefmt = array(
						'year' => $separator,
						'month' => $separator,
						'day' => '',
						'afterDateTag' => '',
						);
				$timefmt = array(
						'hour' => ':',
						'min' => '',
						'meridian' => '',
						);


				$out = null;

				if(!empty( $this->changeDatetimeSeparator )){ 
					$datefmt = $this->changeDatetimeSeparator['datefmt'];
					$timefmt = $this->changeDatetimeSeparator['timefmt'];
				}


				foreach($datefmt as $key => $val) {
					$out .= (isset($data[$fieldName][$key]) ? $data[$fieldName][$key] . $val : '');
				}
				if(!empty($options[2]) && $options[2] !== 'NONE') {
					$out .= ' ';
					foreach($timefmt as $key => $val) {
						$sprintf_fmt = (isset($data[$fieldName][$key]) && is_numeric($data[$fieldName][$key])) ? '%02d' :'%s';
						$out .= (isset($data[$fieldName][$key]) ? sprintf($sprintf_fmt ,$data[$fieldName][$key]) .$val : '');
					}
				}


			}else {
				$out = $data[$fieldName];
			}

			return $this->_confirmValueOutput($out);

		}
		return '';
	}


}
?>