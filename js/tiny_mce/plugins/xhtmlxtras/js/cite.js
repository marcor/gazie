 /**
 * $Id: cite.js,v 1.4 2011/01/01 11:07:11 devincen Exp $
 *
 * @author Moxiecode - based on work by Andrew Tetlaw
 * @copyright Copyright © 2004-2012, Moxiecode Systems AB, All rights reserved.
 */

function init() {
    SXE.initElementDialog('cite');
    if (SXE.currentAction == "update") {
        SXE.showRemoveButton();
    }
}

function insertCite() {
    SXE.insertElement('cite');
    tinyMCEPopup.close();
}

function removeCite() {
    SXE.removeElement('cite');
    tinyMCEPopup.close();
}

tinyMCEPopup.onInit.add(init);