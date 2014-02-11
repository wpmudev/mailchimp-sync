(function($) {
    tinymce.create('tinymce.plugins.MailChimpShortcode', {
        init : function(ed, url) {

            ed.addButton('mailchimpform', {
                title : 'MailChimp Form',
                cmd : 'insert_mcshortcode',
                image : url + '/tinymceicon.png'
            });

            ed.addCommand('insert_mcshortcode', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                return_text = '[mailchimp-form]';
                ed.execCommand('mceInsertContent', 0, return_text);
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