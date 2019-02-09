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
 * Namespace
 */
namespace OMOSde\ContaoOmSecurityBundle;


/**
 * Export passwords
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class Export extends \Backend
{
    /**
     * Export passwords as csv
     *
     * @return mixed
     */
    public function exportPasswords()
    {
        // load password list
        $objPasswordList = OmSecurityPasswordListModel::findByPk(\Input::get('id'));
        if (!$objPasswordList)
        {
            return $this->generateError('noPasswordListFound');
        }

        // load passwords
        $objPasswords = OmSecurityPasswordModel::findByPid(\Input::get('id'));
        if (!$objPasswords)
        {
            return $this->generateError('noPasswordsFound');
        }

        // variables
        $strLogVersion = '';
        $strFilename = sprintf('Export-Passwortliste-%s-%s-utf8.csv', $objPasswordList->title, date('Y-m-d'));

        // send header
        header('Content-Type: text/comma-separated-values');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="' . $strFilename . '"');

        if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', getenv("HTTP_USER_AGENT"), $strLogVersion)) // check for IE
        {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        else
        {
            header('Pragma: no-cache');
        }

        // write csv
        $out = fopen('php://output', 'w');
        foreach ($objPasswords as $password)
        {
            fputcsv($out, [$password->password], ',');
        }

        fclose($out);
        exit;
    }


    /**
     * @param string $strError
     *
     * @return mixed
     */
    protected function generateError($strError = 'undefinedError')
    {
        return $GLOBALS['TL_LANG']['OM_SECURITY']['ERR'][$strError];
    }
}
