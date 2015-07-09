<?php

require_once 'lib/event.php';
require_once 'lib/magic_class.php';
require_once 'lib/aggregate.php';

header("content-type: application/json");

$test = new appti2ude\Aggregate();
$test->test();
$snap = json_decode($test->snapshot());
$mine = appti2ude\bones\MagicClass::hydrate($snap);
echo $mine->snapshot();