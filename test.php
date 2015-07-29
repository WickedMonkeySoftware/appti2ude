<html>
<body>
<h2>Original Array</h2>
<pre>
<?php
$arr = [];
for($i = 0; $i < 5; $i++) {
    $val = new stdClass();
    $val->value = $i + 1;
    $arr[] = $val;
}

var_dump($arr);

?>
</pre>
<h2>Reversed Array</h2>
<pre>
<?php
    function reverse(&$arr) {
        reset($arr);
        end($arr);
        while(key($arr) != null) {
            yield current($arr);
            prev($arr);
        }
        yield current($arr);
    }

    foreach (reverse($arr) as $value) {
        echo "$value->value\n";
    }
    ?>
</pre>
</body>
</html>
