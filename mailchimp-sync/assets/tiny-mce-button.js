( function() {
    tinymce.PluginManager.add( 'mailchimpshortcode', function( editor, url ) {

        // Add a button that opens a window
        editor.addButton( 'mailchimpform', {

            icon: false,
            title : 'Add MailChimp Subscription Form',
            image : url + '/tinymceicon.png',
            onclick: function() {
                // Open window
                editor.windowManager.open( {
                    title: 'Mailchimp Form',
                    body: [
                        {
                            type: 'textbox',
                            name: 'form_title',
                            label: 'Form title',
                            value: 'Subscribe to our MailChimp list.'
                        },
                        {
                            type: 'textbox',
                            name: 'subscribed_text',
                            label: 'Text displayed when a user subscribes',
                            value: 'Thank you, your email has been added to the list.'
                        },
                        {
                            type: 'textbox',
                            name: 'button_text',
                            label: 'Text displayed in the submit button',
                            value: 'Subscribe!'
                        },
                        {
                            type: 'checkbox',
                            name: 'require_firstname',
                            label: 'Require First Name'
                        },
                        {
                            type: 'checkbox',
                            name: 'require_lastname',
                            label: 'Require Last Name'
                        }
                    ],
                    onsubmit: function( e ) {
                        var code;

                        code = "[mailchimp-form";
                        // Form title
                        var title = '';
                        var title_input = e.data.form_title;
                        code += ' title="' + title_input + '"';

                        // Success text
                        var success = '';
                        var success_input = e.data.subscribed_text;
                        code += ' success_text="' + success_input + '"';

                        // Submit button text
                        var button = '';
                        var button_input = e.data.button_text;
                        code += ' button_text="' + button_input + '"';

                        // Required fields
                        var require = e.data.require_firstname;
                        if ( ! require )
                            code += ' firstname="0"';
                        else
                            code += ' firstname="1"';

                        require = e.data.require_lastname;
                        if ( ! require )
                            code += ' lastname="0"';
                        else
                            code += ' lastname="1"';

                        code += ']';

                        // Insert content when the window form is submitted
                        editor.insertContent( code );
                    }

                } );
            }

        } );

    } );

} )();




