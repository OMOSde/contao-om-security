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
 * Reads and writes passwords
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class OmSecurityPasswordModel extends \Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_om_security_password';


    /**
     * Find published passwords by multiple pid´s
     *
     * @param $strPassword
     * @param $arrPids
     *
     * @return \Contao\Model\Collection|OmSecurityPasswordModel|null
     */
    public static function findPublishedPasswordsByMultiplePids($strPassword, $arrPids)
    {
        if (!is_array($arrPids) || empty($arrPids))
        {
            return null;
        }

        $arrPids = implode(',', array_map('intval', $arrPids));
        $strTable = static::$strTable;
        $objDatabase = \Database::getInstance();

        return static::findBy(["$strTable.password = '" . $strPassword . "' AND published=1 AND $strTable.pid IN(" . $arrPids . ")"], null, ['order' => $objDatabase->findInSet("$strTable.id", $arrPids)]);
    }
}
