/**
 * Thanks to https://stackoverflow.com/users/1070129
 * 
 * https://stackoverflow.com/a/469362/9229515
 * 
 * Use like this
 * 
 *  setInputFilter(document.getElementById("myTextBox"), function(value) {
 *      return /^\d*\.?\d*$/.test(value); // Allow digits and '.' only, using a RegExp
 *  });
 * 
 */
if (typeof window['setInputFilter'] !== 'function') {
    // Restricts input for the given textbox to the given inputFilter function.
    function setInputFilter(textbox, inputFilter) {
        ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
            textbox.addEventListener(event, function() {
                if (inputFilter(this.value)) {
                    this.oldValue = this.value;
                    this.oldSelectionStart = this.selectionStart;
                    this.oldSelectionEnd = this.selectionEnd;
                } else if (this.hasOwnProperty("oldValue")) {
                    this.value = this.oldValue;
                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                } else {
                    this.value = "";
                }
            });
        });
    }
}