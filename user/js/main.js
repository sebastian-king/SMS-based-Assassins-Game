;(function(){

	var app = {
		init: function(){
		},
		handleEmail: function(data){
			/* Handle the user's email address here... */
			console.log(data);
		},
		phone: {
			/* Animations for the phone */
			init: function(){
				$('.logo').delay(1000).animate({
					top: 78
				}, 1000);

				$('.phone-countdown').delay(1500).fadeIn(1000, function(){
					if($('body').width() > 979){
						$('.phone').animate({
							right: '29%'
						}, 750);
					}

					$('.main-content').delay(450).animate({
						opacity: 1
					}, 500);
				});
			}
		},
		domReady: function(){},
		windowLoad: function(){
			$('.phone').fadeIn(500, app.phone.init); // When the window has loaded, fade in the phone...
		}
	};

	app.init();
	$(function(){
		app.domReady();

		$(window).load(app.windowLoad);
	});

})(jQuery)