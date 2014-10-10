/**
 * Password Strength Indicator calculator
 * 
 * @param   String password - the password value to check.
 * @returns var             - returns the label string value to use in a result span.
 */
function checkStrength(password) {
 
    // init
    var strength = 0;
 
    // length is too short
    if (password.length < 6) {
        $('#result').removeClass();
        $('#result').addClass('label label-danger');
        return lang.pwStrengthTooShort;
    }
 
    // length is 8 characters or more
    if (password.length > 7) strength += 1;
 
    // contains both lower and uppercase characters
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
 
    // has numbers and characters
    if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1;
 
    // has at one special character
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
 
    // has two special characters
    if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,",%,&,@,#,$,^,*,?,_,~])/)) strength += 1;
 
    //if value is less than 2
    if (strength < 2 ) {
        $('#result').removeClass();
        $('#result').addClass('label label-warning');
        return lang.pwStrengthWeak;
    } else if (strength == 2 ) {
        $('#result').removeClass();
        $('#result').addClass('label label-success');
        return lang.pwStrengthGood;
    } else {
        $('#result').removeClass();
        $('#result').addClass('label label-success');
        return lang.pwStrengthStrong;
    }
}
