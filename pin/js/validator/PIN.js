(function($) {
    $.fn.bootstrapValidator.validators.PIN = {
        enableByHtml5: function($field) {
            return ('text' == $field.attr('type'));
        },
        validate: function(validator, $field, options) {
            var value = $field.val();
            if (value == '') {
                return true;
            }
            var pinRegExp = /^\d{4}$/;
            return pinRegExp.test(value);
        }
    }
}(window.jQuery));
