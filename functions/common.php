<?php

function alternate(&$a, $b) {
	return $a = ( $a ?? $b );
}

