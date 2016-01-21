<?php
if ( !class_exists( 'wtbInit' ) ) {
	class wtbInit
	{
		public $post_type;		
		public $taxonomies;
		
		function __construct(){
        
			$this->post_type = 'wtb-booking';
			$this->post_type_slug = 'table-booking';			
			$this->taxonomies = array();
			$this->incPath       = dirname( __FILE__ );
			$this->functionsPath    = $this->incPath . '/functions/';
			$this->classesPath		= $this->incPath . '/classes/';
			$this->widgetsPath		= $this->incPath . '/widgets/';
			$this->viewsPath		= $this->incPath . '/views/';

			$this->assetsUrl        = WTB_PLUGIN_URL  . '/assets/';

			$this->WTBloadClass( $this->classesPath ); 

			$this->options = array(
					'settings' => 'wtb_settings'
				);
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
	 * Format date
	 * @since 1.0
	 */
	public function format_date( $date ) {
		$date = mysql2date( get_option( 'date_format' ) , $date);
		return apply_filters( 'get_the_date', $date );
	}
	
	/**
	 * Format time
	 * @since 1.0
	 */
	public function format_time( $time ) {
		$time = mysql2date( get_option( 'time_format' ), $time);
		return apply_filters( 'get_the_date', $time );
	}
		
	}
}
global $wtbInit;
if( !is_object( $wtbInit ) )
    $wtbInit = new wtbInit;		
