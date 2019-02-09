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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{security_legend},passwordList';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['passwordList'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_settings']['passwordList'],
    'inputType'  => 'checkboxWizard',
    'foreignKey' => 'tl_om_security_password_list.title',
    'eval'       => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr']
];
