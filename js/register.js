// Contact Form Scripts
$(function() {

    $("input,textarea").jqBootstrapValidation({
        preventSubmit: true,
        submitError: function($form, event, errors) {
            // additional error messages or events
        },
        submitSuccess: function($form, event) {
            event.preventDefault(); // prevent default submit behaviour
            // get values from FORM
            var name = $("input#name").val();
            var email = $("input#email").val();
            var phone = $("input#phone").val();
            var firstName = name; // For Success/Failure Message
            // Check for white space in name for Success/Fail message
            if (firstName.indexOf(' ') >= 0) {
                firstName = name.split(' ').slice(0, -1).join(' ');
            }
            $.ajax({
                url: "/ajax/register.php",
                type: "POST",
                data: {
                    name: name,
                    phone: phone,
                    email: email
                },
                cache: false,
                success: function(data) {
					switch (data) {
						case "0":
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>Sorry " + firstName + ", it seems there is a problem with our system. Please try again later!</strong>");
							$('#success > .alert-danger').append('</div>');
							$('#contactForm').trigger("reset"); 
						break;
						case "1":
							$('#success').html("<div class='alert alert-success'>");
							$('#success > .alert-success').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-success')
								.append("<strong>Please check your e-mail to complete your registration. </strong>");
							$('#success > .alert-success')
								.append('</div>');
							$('#contactForm').trigger("reset");
						break;
						case "2": // email invalid
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>Plase enter a valid e-mail address.</strong>");
							$('#success > .alert-danger').append('</div>');
						break;
						case "3": // phone invalid
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>Please enter a valid US phone number.</strong>");
							$('#success > .alert-danger').append('</div>');
						break;
						case "4": // name invalid
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>Please enter your first & last name.</strong>");
							$('#success > .alert-danger').append('</div>');
						break;
						case "5": // email duplicate
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>The e-mail address you entered is already registered.</strong>");
							$('#success > .alert-danger').append('</div>');
						break;
						case "6": // phone duplicate
							$('#success').html("<div class='alert alert-danger'>");
							$('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
								.append("</button>");
							$('#success > .alert-danger').append("<strong>The phone number you entered is already registered.</strong>");
							$('#success > .alert-danger').append('</div>');
						break;
					}
                },
                error: function() {
                    // Fail message
                    $('#success').html("<div class='alert alert-danger'>");
                    $('#success > .alert-danger').html("<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×")
                        .append("</button>");
                    $('#success > .alert-danger').append("<strong>Sorry " + firstName + ", it seems there is a problem with our system. Please try again later!");
                    $('#success > .alert-danger').append('</div>');
                    //clear all fields
                    $('#contactForm').trigger("reset");
                },
            })
        },
        filter: function() {
            return $(this).is(":visible");
        },
    });

    $("a[data-toggle=\"tab\"]").click(function(e) {
        e.preventDefault();
        $(this).tab("show");
    });
});