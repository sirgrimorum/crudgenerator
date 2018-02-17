/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';

    //config.allowedContent = true;
    config.extraAllowedContent = 'div(*)[data-href,data-target,data-toggle,aria-expanded,aria-controls,role]; p(*); h3(*); h4(*); a(*)[data-target,data-toggle,aria-expanded,aria-controls,role]; button(*)[data-target,data-toggle,aria-expanded,aria-controls,role]';
    config.contentsCss = [urlAssets + "js/bootstrap-3.3.7/css/bootstrap.min.css", urlAssets + "css/principal.css"];
    //config.extraAllowedContent = 'div(col-md-*,container*,row, panel*,well*,list*,active*,collapse*,data*,role*),p(list*),h3(list*),h4(list*),a(list*,collapse*)';

};
