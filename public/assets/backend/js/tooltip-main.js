            // for form validate tooltip start

// realTime error message active
$('#basic-form').checkify({

// specify the trigger element
// useful for multiple forms on the page
container: null,

// realtime validation
// default: false
realTime: true

});

// customize error message
$('#basic-form').checkify({

  message: {

    inactive: false,
    // if true. error message won't be shown for required cases
    inactiveForRequired: true,
    // horizontal gap for error message box
    hGap: null,
    // vertical gap for error message box
    vGap: null,
    // can be right or left
    position: 'left',
    required: 'This field is required.',
    email: 'Please enter a valid email address.',
    regex: 'Invalid data.',
    number: 'Please enter a valid number.',
    maxlen: 'Please enter no more than {count} characters.',
    minlen: 'Please enter at least {count} characters.',
    maxChecked: 'Maximum {count} options allowed.',
    minChecked: 'Please select at least {count} options.',
    notEqual: 'Please enter the same value again.',
    different: 'Fields cannot be the same as each other'

  }

});


// // specific the selector css
// $('#basic-form').checkify({

//   trigger: null

// });

// // Available callbacks which will be fired when the form is valid or invalid/
// $('#basic-form').checkify({

//   onError: function () {
//     console.log('error');
//   },
//   onValid: function (e, x) {
//     console.log(e, x);
//     e.preventDefault();

//     console.log('valid');
//   }

// });

// for form validator end

