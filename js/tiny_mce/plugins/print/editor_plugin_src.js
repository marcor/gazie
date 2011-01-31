/**
 * $Id: editor_plugin_src.js,v 1.5 2011/01/01 11:07:11 devincen Exp $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2011, Moxiecode Systems AB, All rights reserved.
 */

(function() {
    tinymce.create('tinymce.plugins.Print', {
        init : function(ed, url) {
            ed.addCommand('mcePrint', function() {
                ed.getWin().print();
            });

            ed.addButton('print', {title : 'print.print_desc', cmd : 'mcePrint'});
        },

        getInfo : function() {
            return {
                longname : 'Print',
                author : 'Moxiecode Systems AB',
                authorurl : 'http://tinymce.moxiecode.com',
                infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/print',
                version : tinymce.majorVersion + "." + tinymce.minorVersion
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('print', tinymce.plugins.Print);
})();