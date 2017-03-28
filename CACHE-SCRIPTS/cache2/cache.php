
    <?php

    ini_set('memory_limit','4000M');
    /*
    removeHours()
    return the date given but with the given number of hours removed
    */
    function removeHours($date, $hours_added){
        $total_seconds = 3600*$hours_added;// 3600 seconds in an hour
        $date = strtotime($date);
        $new_date = $date-$total_seconds;
        $new_date = date('Y\-m\-d H\:i\:s',$new_date);
        return $new_date;
    }

    class Enum {
    protected $self = array();
    public function __construct( /*...*/ ) {
        $args = func_get_args();
        for( $i=0, $n=count($args); $i<$n; $i++ )
            $this->add($args[$i]);
    }
   
    public function __get( /*string*/ $name = null ) {
        return $this->self[$name];
    }
   
    public function add( /*string*/ $name = null, /*int*/ $enum = null ) {
        if( isset($enum) )
            $this->self[$name] = $enum;
        else
            $this->self[$name] = end($this->self) + 1;
    }
}

class FlagsEnum extends Enum {
    public function __construct( /*...*/ ) {
        $args = func_get_args();
        for( $i=0, $n=count($args), $f=0x1; $i<$n; $i++, $f *= 0x2 )
            $this->add($args[$i], $f);
    }

    public function __set_state(){

    }
}


echo "get object<br>";
$service = new \Redis();
$service->connect("127.0.0.1", "6379");



    /*
    cache example
    */
     
    // cache filename variables
    $cache_filename = 'object_data.inc';
    $cachefile_full_filename = $_SERVER['DOCUMENT_ROOT'].'/TEST/cache2/cache/'.$cache_filename;
     
    // check for cache, if it exists and is less than 1 hour old grab it
    if(file_exists($cachefile_full_filename) && filemtime($cachefile_full_filename) > strtotime(removeHours(date('Y-m-d H:i:s'), 1))){  
        
        echo "<br>";
        $starttime = microtime(true);
        $object_data = unserialize(file_get_contents($cachefile_full_filename));
        $difftime = microtime(true)-$starttime;
        echo $difftime."<br>";
        echo "from cache<br>";
      
    } // end if
    // cache is missing or too old
    else{
    // Initialise object
    //$object_data = new FlagsEnum("HAS_ADMIN", "HAS_SUPER", "HAS_POWER", "HAS_GUEST");

     $data = array_fill(0, 1000000, "hi");
     
     foreach($data as $value) {
        $object_data[] = new FlagsEnum($value);
     }
    // Create the cache for future use
        file_put_contents($cachefile_full_filename, serialize($object_data));
        $service->set('my_key', $object_data);
    } // end else
     
    // Use $object_data variables for whatever you want.

