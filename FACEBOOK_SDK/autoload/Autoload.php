<?php
function autoload($class)
{
	$paths = explode(PATH_SEPARATOR, get_include_path());
	$paths[]= "./src/";	
	$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
	$file = str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\")).".php";
	foreach($paths as $path)
	{
	      $combined = ".".$path.DIRECTORY_SEPARATOR.$file;
          
           echo $path.'<br>';
	       
		if(file_exists($combined))
		{
			include_once($combined);
			return;
		}
	}
	throw new Exception("{$class} the class was not found");
}
class Autoloader 
{
	public static function autoload($class)
	{
		autoload($class);
	}
}
spl_autoload_register('autoload');
spl_autoload_register(array('autoloader', 'autoload'));
?>