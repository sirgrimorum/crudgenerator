/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.contentsLanguage = 'fr';
    config.skin='office2013';//flat, office2013, kama, moono, minimalist, icy_orange, atlas, prestige
    //config.uiColor = '#bbbbbb';
    config.toolbarCanCollapse = true;


    //config.allowedContent = true;
    
    config.extraAllowedContent = 'div(*)[data-href,data-target,data-toggle,aria-expanded,aria-controls,role]; p(*); h3(*); h4(*); a(*)[data-target,data-toggle,aria-expanded,aria-controls,role]; button(*)[data-target,data-toggle,aria-expanded,aria-controls,role]';
    config.contentsCss = urlAssetsCkEditor;
    
    //config.extraAllowedContent = 'div(col-md-*,container*,row, panel*,well*,list*,active*,collapse*,data*,role*),p(list*),h3(list*),h4(list*),a(list*,collapse*)';
    
    
    config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		'/',
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];

	config.removeButtons = 'Save,NewPage,Print,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,BidiLtr,BidiRtl,Flash,PageBreak,About';

};
