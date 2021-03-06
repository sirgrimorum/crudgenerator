/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.contentsLanguage = 'fr';
    config.skin = 'office2013'; //flat, office2013, kama, moono, minimalist, icy_orange, atlas, prestige
    //config.uiColor = '#bbbbbb';
    config.toolbarCanCollapse = true;
    config.extraPlugins = 'ckawesome';
    //config.allowedContent = true;

    config.extraAllowedContent = 'div(*)[id,data-href,data-parent,data-target,data-toggle,aria-expanded,aria-controls,role,data-ride,style]; ol(*); ul(*)[class]; li(*)[data-target,data-slide-to,style]; p(*); h3(*); h4(*); a(*)[data-slide,data-target,data-parent,data-toggle,aria-expanded,aria-controls,role]; button(*)[data-target,data-parent,data-toggle,aria-expanded,aria-controls,role]; span(*)[aria-hidden,style];iframe(*)[width,height,src,frameborder,allow,allowfullscreen,class];*[id,class]';
    config.contentsCss = urlAssetsCkEditor;
    config.fontawesomePath = urlAssetsCkEditor;

    //config.extraAllowedContent = 'div(col-md-*,container*,row, panel*,well*,list*,active*,collapse*,data*,role*),p(list*),h3(list*),h4(list*),a(list*,collapse*)';


    config.toolbarGroups = [
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing'] },
        { name: 'forms', groups: ['forms'] },
        '/',
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi', 'paragraph'] },
        '/',
        { name: 'styles', groups: ['styles'] },
        { name: 'colors', groups: ['colors'] },
        '/',
        { name: 'links', groups: ['links'] },
        { name: 'insert', groups: ['insert', 'ckawesome'] },
        { name: 'tools', groups: ['tools'] },
        { name: 'others', groups: ['others'] },
        { name: 'about', groups: ['about'] }
    ];

    config.removeButtons = 'Save,NewPage,Print,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,BidiLtr,BidiRtl,Flash,PageBreak,About';

};