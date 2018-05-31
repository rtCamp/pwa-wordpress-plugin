var pwa_ready =  {

	init: function(  ) {
		if ('serviceWorker' in navigator) {
			window.addEventListener( 'load', function() {
				navigator.serviceWorker.register('/?pwa_wp_plugin_sw=1').then( function ( registration  ) {
					console.log( 'Service worker registered' );
				}).catch( function( registrationError ) {
					console.log( 'Service worker registration failed' );
				});
			});
		}
	}
};

pwa_ready.init();
