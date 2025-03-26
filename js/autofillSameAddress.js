/* This javascript should work to autofill a mailing address with an already entered address if the following is true:
    1. The form collecting the data has a checkbox named "same_as_home", 
            allowing users to say their mailing address is their home address
    2. The form's relevant field id's match the id's used in this script
*/

// Wait until the browser has loaded the whole html file becore executing this script
document.addEventListener("DOMContentLoaded", function() {
    // Select the checkbox with the id "same_as_home" and add an event listener to it
    document.getElementById("same-as-home").addEventListener("change", function() {
        /*if the same-as-home checkbox is checked, the home address values need to be
         copied to the mailing address fields */
         if (this.checked) {
            document.getElementById("mailing-address").value = document.getElementById("address").value;
            document.getElementById("mailing-city").value = document.getElementById("city").value;
            document.getElementById("mailing-state").value = document.getElementById("state").value;
            document.getElementById("mailing-zip").value = document.getElementById("zip").value;

            /* Disable the mailing address text box, to prevent manual insertion */
            document.getElementById("mailing-address").disabled = true;
            document.getElementById("mailing-city").disabled = true;
            document.getElementById("mailing-state").disabled = true;
            document.getElementById("mailing-zip").disabled = true;
         }
         /* Otherwise, set the mailing address values to the empty string, and enable manual insertion */
         else {
            /* First, set value to empty string */
            document.getElementById("mailing-address").value = '';
            document.getElementById("mailing-city").value = '';
            document.getElementById("mailing-state").value = '';
            document.getElementById("mailing-zip").value = '';
            
            /* Then enable manual insertion */
            document.getElementById("mailing-address").disabled = false;
            document.getElementById("mailing-city").disabled = false;
            document.getElementById("mailing-state").disabled = false;
            document.getElementById("mailing-zip").disabled = false;
         }
    })
});