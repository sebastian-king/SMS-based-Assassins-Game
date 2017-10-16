;(function(){

	var app = {
		init: function(){

			$('#showEmailForm').on('click', function(e){ // When the "Email" button is clicked...
				e.preventDefault(); // Stop the page from jumping to the top
				$('.actions a, .actions span').fadeOut(500, function(){ // Fade out our links & "OR" seperator
					$('.actions form').fadeIn(500); // Fade in our email form
				});
			});

			$('#emailForm').on('submit', function(e){ // When the email form is submitted
				e.preventDefault();

				var $this = $(this),
					email = $this.find('[name="email"]').val();

				$('.actions, .initial-content').fadeOut(500, function(){ // Fade out our links & "OR" seperator
					$('.thanks').fadeIn(500); // Fade in thank you content
				});

				app.handleEmail({email: email}); // This is the method you'd use to handle the user's email address (i.e save it to a database, etc)... 
			});
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