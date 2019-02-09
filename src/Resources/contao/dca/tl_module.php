<?php

/**
 * Contao bundle contao-om-security
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 * @package   contao-om-security
 * @link      http://www.omos.de
 * @license   LGPL 3.0+
 */


/**
 * Table tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['registration'] .= ';{security_legend},,passwordList';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['passwordList'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['passwordList'],
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''"
);
