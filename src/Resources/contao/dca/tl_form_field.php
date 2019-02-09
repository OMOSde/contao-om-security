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
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['password'] .= ';{security_legend},passwordList';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['passwordList'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_form_field']['passwordList'],
    'inputType'  => 'checkboxWizard',
    'foreignKey' => 'tl_om_security_password_list.title',
    'eval'       => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr'],
    'sql'        => "text NULL"
];
