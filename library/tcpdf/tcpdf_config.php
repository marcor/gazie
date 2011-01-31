<?php
//============================================================+
// File name   : tcpdf_config.php
// Begin       : 2004-06-11
// Last Update : 2008-09-26
//
// Description : Congiguration file for TCPDF.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Configuration file for TCPDF.
 * @author Nicola Asuni
 * @copyright 2004-2011 Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @package com.tecnick.tcpdf
 * @version 4.0.014
 * @link http://tcpdf.sourceforge.net
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2004-10-27
 */

// If you define the constant K_TCPDF_EXTERNAL_CONFIG, the following settings will be ignored.


if (!defined("K_TCPDF_EXTERNAL_CONFIG")) {
    /**
     * font path
     */
    define ("K_PATH_FONTS", "../../library/fonts/");

    /**
     * page format
     */
    define ("PDF_PAGE_FORMAT", "gazie");

    /**
     * page orientation (P=portrait, L=landscape)
     */
    define ("PDF_PAGE_ORIENTATION", "P");

    /**
     * document creator
     */
    define ("PDF_CREATOR", "GAzie");

    /**
     * document author
     */
    define ("PDF_AUTHOR", $_SESSION['Login']);

    /**
     * header title
     */
    define ("PDF_HEADER_TITLE", '');

    /**
     * header description string
     */
    define ("PDF_HEADER_STRING", '');

    /**
     * image logo
     */
    define ("PDF_HEADER_LOGO", '');

    /**
     * header logo image width [mm]
     */
    define ("PDF_HEADER_LOGO_WIDTH", 40);


    /**
     *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
     */
    define ("PDF_UNIT", "mm");

    /**
     * header margin
     */
    define ("PDF_MARGIN_HEADER", 5);

    /**
     * footer margin
     */
    define ("PDF_MARGIN_FOOTER", 10);

    /**
     * top margin
     */
    define ("PDF_MARGIN_TOP", 50);

    /**
     * bottom margin
     */
    define ("PDF_MARGIN_BOTTOM", 20);

    /**
     * left margin
     */
    define ("PDF_MARGIN_LEFT", 15);

    /**
     * right margin
     */
    define ("PDF_MARGIN_RIGHT", 15);

    /**
     * main font name
     */
    define ("PDF_FONT_NAME_MAIN", "freesans");

    /**
     * main font size
     */
    define ("PDF_FONT_SIZE_MAIN", 10);

    /**
     * data font name
     */
    define ("PDF_FONT_NAME_DATA", "freeserif");

    /**
     * data font size
     */
    define ("PDF_FONT_SIZE_DATA", 8);

    /**
     * Ratio used to scale the images
     */
    define ("PDF_IMAGE_SCALE_RATIO", 4);

    /**
     * magnification factor for titles
     */
    define("HEAD_MAGNIFICATION", 1);

    /**
     * height of cell repect font height
     */
    define("K_CELL_HEIGHT_RATIO", 1.25);

    /**
     * title magnification respect main font size
     */
    define("K_TITLE_MAGNIFICATION", 1.3);

    /**
     * reduction factor for small font
     */
    define("K_SMALL_RATIO", 2/3);
}

//============================================================+
// END OF FILE
//============================================================+
?>