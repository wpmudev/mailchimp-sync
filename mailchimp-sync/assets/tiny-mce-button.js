(function($) {

    tinymce.create('tinymce.plugins.MailChimpShortcode', {
        init : function(ed, url) {

            ed.addButton('mailchimpform', {
                title : 'MailChimp Form',
                cmd : 'insert_mcshortcode',
                image : url + '/tinymceicon.png',
                //onclick : function() {
                  //  mailchimp_sync_shortcode.perform_request( 'load-ui' );
                   // return false;
                //}
            });


            ed.addCommand('insert_mcshortcode', function() {
                ed.windowManager.open({
                    file : ajaxurl + '?action=display_mailchimp_shortcode_admin_form', // file that contains HTML for our modal window
                    inline : 1,
                    width: 450,
                    height: 600,
                    title: 'MailChimp Form'
                }, {
                    plugin_url : url
                });
                //var selected_text = ed.selection.getContent();
                //var return_text = '';
                //return_text = '[mailchimp-form]';
                //ed.execCommand('mceInsertContent', 0, return_text);
            });
            
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {};
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add( 'mailchimpshortcode', tinymce.plugins.MailChimpShortcode );

    var mailchimp_sync_shortcode = {
        in_request: false,
        perform_request: function( action ) {
            if (this.in_request)
                return;

            this.in_request = true;

            var jqXHR = jQuery.ajax(
                ajaxurl,
                {
                    type : 'POST',
                    data : {
                        action : 'display_mailchimp_shortcode_admin_form',
                        security : 'aaa',
                        mailchimp_action : action,
                        mailchimp_params : {}
                    },
                    success : function( response ) {
                        mailchimp_sync_shortcode.action_success( action, response, this );
                        mailchimp_sync_shortcode.in_request = false;
                    },
                    error : function( jqXHR, textStatus, errorThrown ) {
                        //SynvedShortcode.actionFailed(action, params, errorThrown, this);
                        //SynvedShortcode.doingRequest = false;
                    }
                }
            );
            
            return jqXHR;
        },
        action_success: function ( action, response, request ) {
            tb_show( 'MailChimp Form', '#TB_inline' );
            var tb = jQuery("#TB_window");
            if ( tb ) {
                var tb_content = tb.find('#TB_ajaxContent');
                tb_content.html(response);

                tb_content.find('#mailchimp-insert-shortcode').click(function (e) {
                    e.preventDefault();
                    
                    var code = '[mailchimp-form';

                    // Form title
                    var title = '';
                    var title_input = tb_content.find( 'input[name=title]' );
                    code += ' title="' + title_input.val() + '"';

                    // Success text
                    var success = '';
                    var success_input = tb_content.find( 'input[name=success-text]' );
                    code += ' success_text="' + success_input.val() + '"';

                    // Submit button text
                    var button = '';
                    var button_input = tb_content.find( 'input[name=button-text]' );
                    code += ' button_text="' + button_input.val() + '"';

                    // Required fields
                    var require = tb_content.find( 'input[name=require_firstname]' ).attr('checked');
                    if ( ! require )
                        code += ' firstname="0"';
                    else
                        code += ' firstname="1"';

                    require = tb_content.find( 'input[name=require_lastname]' ).attr('checked');
                    if ( ! require )
                        code += ' lastname="0"';
                    else
                        code += ' lastname="1"';

                    code += ']';
                    
                    if (tinyMCE.activeEditor != null && tinyMCE.activeEditor.selection.getSel() != null)
                    {
                        tinyMCE.activeEditor.selection.setContent(code);
                    }
                    else
                    {
                        jQuery('#content').insertAtCaret(code);
                    }
                    
                    tb_remove();
                    
                    return false;
                });
            }
        }
    }
})();