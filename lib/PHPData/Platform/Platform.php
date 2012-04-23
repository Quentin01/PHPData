<?php

namespace PHPData\Platform;

interface Platform {
	function supportLimit();
	function supportOffset();
	function limit($limit, $offset = null);
}
