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

		// @todo Add to home screen using deferred event.
		window.addEventListener('beforeinstallprompt', (e) => {
			e.prompt();
			e.userChoice
				.then((choiceResult) => {
					if (choiceResult.outcome === 'accepted') {
						console.log('User accepted the A2HS prompt');
					} else {
						console.log('User dismissed the A2HS prompt');
					}
				e = null;
			});
		});
	}
};

pwa_ready.init();
