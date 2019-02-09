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
 * Backend modules
 */
$GLOBALS['BE_MOD']['om_security'] = [
    'password_list' => [
        'tables' => ['tl_om_security_password_list', 'tl_om_security_password'],
        'import' => ['OMOSde\ContaoOmSecurityBundle\Import', 'importPasswords'],
        'export' => ['OMOSde\ContaoOmSecurityBundle\Export', 'exportPasswords'],
    ]
];


/**
 * Frontend modules
 */
//$GLOBALS['FE_MOD']['user']['registration'] = 'OMOSde\ContaoOmSecurityBundle\ModuleRegistration';


/**
 * Additional frontend form fields
 */
$GLOBALS['TL_FFL']['password'] = 'OMOSde\ContaoOmSecurityBundle\OmSecurityFormPassword';


/**
 * Additional backend form fields
 */
$GLOBALS['BE_FFL']['password'] = 'OMOSde\ContaoOmSecurityBundle\OmSecurityWidgetPassword';


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_om_security_password_list'] = 'OMOSde\ContaoOmSecurityBundle\OmSecurityPasswordListModel';
$GLOBALS['TL_MODELS']['tl_om_security_password'] = 'OMOSde\ContaoOmSecurityBundle\OmSecurityPasswordModel';