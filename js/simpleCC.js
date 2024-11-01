function simpleCC_message()
{
    // save values from form for error checking
    var firstName = document.forms["simpleCC_form"]["simpleCC_fname"].value;
    var lastName = document.forms["simpleCC_form"]["simpleCC_lname"].value;
    var email = document.forms["simpleCC_form"]["simpleCC_email"].value;

    // test for null or empty values
    if (firstName==null || firstName=="", lastName==null || lastName=="", email==null || email=="")
    {
      document.getElementById('simpleCC_error_message').innerHTML = 'Please fill in all fields';
      return false;
    }
    else
    {
        // clear any errors set success to true and submit form
        document.getElementById('simpleCC_error_message').innerHTML = '';
        document.forms["simpleCC_form"]["simpleCC_submitted"].value = 'success';
        document.forms["simpleCC_form"].submit();
    }
}