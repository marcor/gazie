
// directory of where all the images are
var cmThemeGazieBase = '../../js/jscookmenu/';

// the follow block allows user to re-define theme base directory
// before it is loaded.
try
{
    if (myThemeGazieBase)
    {
        cmThemeGazieBase = myThemeGazieBase;
    }
}
catch (e)
{
}

var cmThemeGazie =
{
    prefix: 'ThemeGazie',
    // main menu display attributes
    //
    // Note.  When the menu bar is horizontal,
    // mainFolderLeft and mainFolderRight are
    // put in <span></span>.  When the menu
    // bar is vertical, they would be put in
    // a separate TD cell.

    // HTML code to the left of the folder item
    mainFolderLeft: '',
    // HTML code to the right of the folder item
    mainFolderRight: '<img alt="" src="' + cmThemeGazieBase + 'arrowdown.png">',
    // HTML code to the left of the regular item
    mainItemLeft: '',
    // HTML code to the right of the regular item
    mainItemRight: '',

    // sub menu display attributes

    // 0, HTML code to the left of the folder item
    folderLeft: '',
    // 1, HTML code to the right of the folder item
    folderRight: '<img alt="" src="' + cmThemeGazieBase + 'arrow.png">',
    // 2, HTML code to the left of the regular item
    itemLeft: '<img alt="" src="' + cmThemeGazieBase + 'blank.png">',
    // 3, HTML code to the right of the regular item
    itemRight: '',
    // 4, cell spacing for main menu
    mainSpacing: 0,
    // 5, cell spacing for sub menus
    subSpacing: 0,

    // move 1st lvl submenu for horizontal menus up a bit to avoid double border
    offsetHMainAdjust:  [0, -1],
    offsetVMainAdjust:  [-1, 0],
    // offset according to Opera, which is correct.
    offsetSubAdjust:    [1, 0]
    // rest use default settings
};

// for horizontal menu split
var cmThemeGazieHSplit = [_cmNoClick, '<td class="ThemeGazieMenuItemLeft"></td><td colspan="2"><div class="ThemeGazieMenuSplit"></div></td>'];
var cmThemeGazieMainHSplit = [_cmNoClick, '<td class="ThemeGazieMainItemLeft"></td><td colspan="2"><div class="ThemeGazieMenuSplit"></div></td>'];
var cmThemeGazieMainVSplit = [_cmNoClick, '|'];