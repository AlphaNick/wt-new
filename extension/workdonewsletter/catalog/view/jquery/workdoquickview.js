
(function( $ ) {
     
	$.fn.workdoQuickview = function( initvar ) {
 
		/**
		 * Create List Layers By JSON Data.
		 */
		this.price = '';
		this.ischanged = false; 

		this.work = function(  ){
			var _this = this;

			$( "body" ).delegate( ".product-layout", "mouseover", function(){  

				if( $(this).find(".workdo-quickview").length > 0 ){

				}else {
					$( this ).append( $("<div class=\"workdo-ownstyle workdo-quickview\"><a href=\"#\"><i class=\"fa fa-eye\"></i></a></div>") );
				}
			} );

			$("body").delegate( ".workdo-quickview" , "click", function() {
				var tmp = $(this).parent().find("[onclick]").attr("onclick");
				var id = /\d+/.exec(tmp);
				if( !isNaN(id) ){
				 	_this.open( id );
				}
				return false;
			} );
 		},

 		this.open = function( id ){
 			var url = 'index.php?route=extension/opencart/module/workdoquickview|show&product_id='+id;
 			$.magnificPopup.open({
			  items: {
			    src: url, // can be a HTML string, jQuery object, or CSS selector
			    type: 'iframe',
			    width:"80%"
			  }
			});
 		}
		//THIS IS VERY IMPORTANT TO KEEP AT THE END
		return this;
	};
 	
 	$(document).ready( function(){
       
 		$(document).workdoQuickview().work();
 	} );
})( jQuery );
/***/