 /**
 * $Id: abbr.js,v 1.4 2011/01/01 11:07:11 devincen Exp $
 *
 * @author Moxiecode - based on work by Andrew Tetlaw
 * @copyright Copyright © 2004-2012, Moxiecode Systems AB, All rights reserved.
 */

function init() {
    SXE.initElementDialog('abbr');
    if (SXE.currentAction == "update") {
        SXE.showRemoveButton();
    }
}

function insertAbbr() {
    SXE.insertElement(tinymce.isIE ? 'html:abbr' : 'abbr');
    tinyMCEPopup.close();
}

function removeAbbr() {
    SXE.removeElement('abbr');
    tinyMCEPopup.close();
}

tinyMCEPopup.onInit.add(init);