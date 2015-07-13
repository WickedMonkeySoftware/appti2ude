<?php
class baseClass {
	var $vars = [];
	function &__get($var) {
		echo "got $var<br>";

		echo "<pre>";
		var_dump($this->vars[$var]);

		echo "</pre><br> END GET<br>";
		if (is_array($this->vars[$var])) {
			return $this->vars[$var];
		}

		return $this->vars[$var];
	}
	function __set($var, $value) {
		echo "set $var = $value<br>";
		$this->vars[$var] = $value;
		echo "<pre>";
		if (!isset($var)) {
			echo "$var is not set \n";
		}
		var_dump($this->vars[$var]);
		echo "</pre><br> END SET<br>";
	}
}

$base = new baseClass();
$base->base_class = [];
$base->wonk = 5;
$t = &$base->base_class;
$t['test'] = "hi"; // this doesn't work!
var_dump($base->wonk);
var_dump($base->base_class);
?>