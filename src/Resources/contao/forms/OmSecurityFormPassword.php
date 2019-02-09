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
 * Run in a custom namespace
 */
namespace OMOSde\ContaoOmSecurityBundle;


/**
 * Class FormOmSecurityPassword
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class OmSecurityFormPassword extends \FormPassword
{
    /**
     * Add password list validation
     *
     * @param mixed $varInput The user input
     *
     * @return mixed The validated user input
     */
    protected function validator($varInput)
    {
        // validate against selected password lists
        if ($this->passwordList)
        {
            $objPasswords = OmSecurityPasswordModel::findPublishedPasswordsByMultiplePids($varInput, explode(',', $this->passwordList));
            if ($objPasswords)
            {
                // add error
                $this->addError($GLOBALS['TL_LANG']['OM_SECURITY']['ERR']['onPasswordList']);

                // increment attempts
                foreach ($objPasswords as $objPassword)
                {
                    $objPassword->attempts++;
                    $objPassword->save();
                }
            }


        }

        // use also parent validator
        return parent::validator($varInput);
    }
}
