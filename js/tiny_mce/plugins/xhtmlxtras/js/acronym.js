 /**
 * $Id: acronym.js,v 1.4 2011/01/01 11:07:11 devincen Exp $
 *
 * @author Moxiecode - based on work by Andrew Tetlaw
 * @copyright Copyright © 2004-2011, Moxiecode Systems AB, All rights reserved.
 */

function init() {
    SXE.initElementDialog('acronym');
    if (SXE.currentAction == "update") {
        SXE.showRemoveButton();
    }
}

function insertAcronym() {
    SXE.insertElement('acronym');
    tinyMCEPopup.close();
}

function removeAcronym() {
    SXE.removeElement('acronym');
    tinyMCEPopup.close();
}

tinyMCEPopup.onInit.add(init);