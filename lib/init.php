<?php
if ( !class_exists( 'wtbInit' ) ) {
	class wtbInit
	{
		public $post_type;		
		public $taxonomies;
		public $schotCodeWTB;
		public $request;
		
		function __construct(){
        
			$this->post_type = 'wtb-booking';
			$this->post_type_slug = 'table-booking';
			$this->schotCodeWTB = 'table-booking-sc';
			$this->taxonomies = array();
			$this->incPath       = dirname( __FILE__ );
			$this->functionsPath    = $this->incPath . '/functions/';
			$this->classesPath		= $this->incPath . '/classes/';
			$this->widgetsPath		= $this->incPath . '/widgets/';
			$this->viewsPath		= $this->incPath . '/views/';

			$this->assetsUrl        = WTB_PLUGIN_URL  . '/assets/';

			$this->WTBloadClass( $this->classesPath );
			$this->loadFunctions( $this->functionsPath );			
			// Set up empty request object
			$this->request = new stdClass();
			$this->request->request_processed = false;
			$this->request->request_inserted = false;
			$this->options = array(
			  'settings' => 'wtb_settings'
			);
		}	

		
	/**
	 * nonce Text for booking table
	 * since 1.0
	*/	
	function nonceText(){
		return "wp_table_booking_nonce";
	}		
	/**
	 * Load all required function
	 * since 1.0
	*/
	function loadFunctions( $dir ){
	   $this->loadDirectory( $dir );            
	}
	
	 /**
	 * Include all file from any directory
	 * Create instence of each class and add return all instance as an array
	 */
	function loadDirectory( $dir ){
		if (!file_exists($dir)) return;
		foreach (scandir($dir) as $item) {
			if( preg_match( "/.php$/i" , $item ) ) {
				require_once( $dir . $item );				
			}
		}
		return;
	}
	
	/**
     * @param $dir
     */
	 
	function WTBloadClass($dir){
		if (!file_exists($dir)) return;

            $classes = array();

            foreach (scandir($dir) as $item) {
                if( preg_match( "/.php$/i" , $item ) ) {
                    require_once( $dir . $item );
                    $className = str_replace( ".php", "", $item );
                    $classes[] = new $className;
                }      
            }
            
            if($classes){
            	foreach( $classes as $class )
            	    $this->objects[] = $class;
            }
	}
		
	/**
     * @param $dir
     */
    function loadWidget($dir){
        if (!file_exists($dir)) return;
        foreach (scandir($dir) as $item) {
            if( preg_match( "/.php$/i" , $item ) ) {
                require_once( $dir . $item );
                $class = str_replace( ".php", "", $item );

                if (method_exists($class, 'register_widget')) {
                    $caller = new $class;
                    $caller->register_widget();
                }
                else {
                    register_widget($class);
                }
            }
        }
    }
	
	 /**
	 * Render
	 * @since 1.0
	 * @param $viewName
	 */
	function render( $viewName, $args = array()){
        global $wtbInit;        
        
        $viewPath = $wtbInit->viewsPath . $viewName . '.php';
        if( !file_exists( $viewPath ) ) return;
        
        if( $args ) extract($args);            
        $pageReturn = include $viewPath;
        if( $pageReturn AND $pageReturn <> 1 )
            return $pageReturn;
        if( @$html ) return $html;        
    } 
	
	/**
     * Dynamicaly call any  method from models class
     * by pluginFramework instance
     */
    function __call( $name, $args ){
        if( !is_array($this->objects) ) return;
        foreach($this->objects as $object){
            if(method_exists($object, $name)){
                $count = count($args);
                if($count == 0)
                    return $object->$name();
                elseif($count == 1)
                    return $object->$name($args[0]);
                elseif($count == 2)
                    return $object->$name($args[0], $args[1]);     
                elseif($count == 3)
                    return $object->$name($args[0], $args[1], $args[2]);      
                elseif($count == 4)
                    return $object->$name($args[0], $args[1], $args[2], $args[3]);  
                elseif($count == 5)
                    return $object->$name($args[0], $args[1], $args[2], $args[3], $args[4]);         
                elseif($count == 6)
                    return $object->$name($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);                                                                                             
            }
        }
    } 
		
	}
}
global $wtbInit;
if( !is_object( $wtbInit ) )
    $wtbInit = new wtbInit;		
