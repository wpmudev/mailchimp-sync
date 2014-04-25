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

    
})();