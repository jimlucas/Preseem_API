<?php

function alternate(&$a, $b) {
	return $a = ( isset($a) ? $a : $b );
}

