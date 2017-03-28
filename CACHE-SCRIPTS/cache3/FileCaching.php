<?php
define("RAM_DISK", "R:");
/*$CacheLife this way is not ideal, since it checks for the life of the cache only if you really want to know it (or is it a good thing maybe?) PS: i am trying to make this as efficient as possible, so even maybe saving some microseconds skippping filemtime, when not required (since i guess "if($CacheLife != 0 ||(time() - @filemtime($cacheFile) < $CacheLife))" would check for the filemtime as well*/
function getCache($CacheKey, $CacheLife = 0) {
	$cacheFile = RAM_DISK . "/tmp/" . $CacheKey[0] . "/" . $CacheKey[1]. "/" . $CacheKey;
	if (!$CacheLife) @include($cacheFile);
	elseif (time() - @filemtime($cacheFile) < $CacheLife) @include($cacheFile);
	return isset($val) ? $val : false;
}


function setCache($CacheKey, $CacheVal = "") {
	$firstDive = RAM_DISK . "/tmp/" . $CacheKey[0] . "/";//The whole dive part could be improved
	$secondDive = $firstDive . $CacheKey[1]. "/";
	if (!file_exists($secondDive)) {
		if (!file_exists($firstDive)) {
			if (!file_exists(RAM_DISK . "/tmp/")) mkdir(RAM_DISK . "/tmp/", 0777, true);
			mkdir($firstDive, 0777, true);
		}
		mkdir($secondDive, 0777, true);
	}
	file_put_contents($secondDive . $CacheKey, '<?php $val = ' . str_replace('stdClass::__set_state', '(object)', var_export($CacheVal, true)) . ';');
	return $CacheVal;
}
function delCache($CacheKey) {
 return @unlink(RAM_DISK . "/tmp/" . $CacheKey[0] . "/" . $CacheKey[1] . "/" . $CacheKey);
}
