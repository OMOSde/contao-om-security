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
namespace OMOSde\om_security;


/**
 * Advanced front end module "registration".
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class ModuleRegistration extends \ModuleRegistration
{

    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var \PageModel $objPage */
        global $objPage;

        $GLOBALS['TL_LANGUAGE'] = $objPage->language;

        \System::loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');

        // Call onload_callback (e.g. to check permissions)
        if (is_array($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]();
                }
                elseif (is_callable($callback))
                {
                    $callback();
                }
            }
        }

        // Activate account
        if (\Input::get('token') != '')
        {
            $this->activateAcount();

            return;
        }

        if ($this->memberTpl != '')
        {
            /** @var \FrontendTemplate|object $objTemplate */
            $objTemplate = new \FrontendTemplate($this->memberTpl);

            $this->Template = $objTemplate;
            $this->Template->setData($this->arrData);
        }

        $this->Template->fields = '';
        $this->Template->tableless = $this->tableless;
        $objCaptcha = null;
        $doNotSubmit = false;

        // Predefine the group order (other groups will be appended automatically)
        $arrGroups = array
        (
            'personal' => array(),
            'address'  => array(),
            'contact'  => array(),
            'login'    => array(),
            'profile'  => array()
        );

        // Captcha
        if (!$this->disableCaptcha)
        {
            $arrCaptcha = array
            (
                'id' => 'registration',
                'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
                'type' => 'captcha',
                'mandatory' => true,
                'required' => true,
                'tableless' => $this->tableless
            );

            /** @var \FormCaptcha $strClass */
            $strClass = $GLOBALS['TL_FFL']['captcha'];

            // Fallback to default if the class is not defined
            if (!class_exists($strClass))
            {
                $strClass = 'FormCaptcha';
            }

            /** @var \FormCaptcha $objCaptcha */
            $objCaptcha = new $strClass($arrCaptcha);

            if (\Input::post('FORM_SUBMIT') == 'tl_registration')
            {
                $objCaptcha->validate();

                if ($objCaptcha->hasErrors())
                {
                    $doNotSubmit = true;
                }
            }
        }

        $arrUser = array();
        $arrFields = array();
        $hasUpload = false;
        $i = 0;

        // Build form
        foreach ($this->editable as $field)
        {
            $arrData = $GLOBALS['TL_DCA']['tl_member']['fields'][$field];

            // Map checkboxWizard to regular checkbox widget
            if ($arrData['inputType'] == 'checkboxWizard')
            {
                $arrData['inputType'] = 'checkbox';
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!class_exists($strClass))
            {
                continue;
            }

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = $arrData['eval']['mandatory'];

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $arrData['default'], '', '', $this));

            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

            // Increase the row count if its a password field
            if ($objWidget instanceof \FormPassword)
            {
                $objWidget->rowClassConfirm = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
            }

            // Validate input
            if (\Input::post('FORM_SUBMIT') == 'tl_registration')
            {
                $objWidget->validate();
                $varValue = $objWidget->value;

                // Check whether the password matches the username
                if ($objWidget instanceof \FormPassword && $varValue == \Input::post('username'))
                {
                    $objWidget->addError($GLOBALS['TL_LANG']['ERR']['passwordName']);
                }

                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim')))
                {
                    try
                    {
                        $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                        $varValue = $objDate->tstamp;
                    }
                    catch (\OutOfBoundsException $e)
                    {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($arrData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue('tl_member', $field, $varValue))
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $field));
                }

                // Save callback
                if ($objWidget->submitInput() && !$objWidget->hasErrors() && is_array($arrData['save_callback']))
                {
                    foreach ($arrData['save_callback'] as $callback)
                    {
                        try
                        {
                            if (is_array($callback))
                            {
                                $this->import($callback[0]);
                                $varValue = $this->$callback[0]->$callback[1]($varValue, null);
                            }
                            elseif (is_callable($callback))
                            {
                                $varValue = $callback($varValue, null);
                            }
                        }
                        catch (\Exception $e)
                        {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                // Store the current value
                if ($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }
                elseif ($objWidget->submitInput())
                {
                    // Set the correct empty value (see #6284, #6373)
                    if ($varValue === '')
                    {
                        $varValue = $objWidget->getEmptyValue();
                    }

                    $arrUser[$field] = $varValue;
                }
            }

            if ($objWidget instanceof \uploadable)
            {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            $arrFields[$arrData['eval']['feGroup']][$field] .= $temp;

            ++$i;
        }

        // Captcha
        if (!$this->disableCaptcha)
        {
            $objCaptcha->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
            $strCaptcha = $objCaptcha->parse();

            $this->Template->fields .= $strCaptcha;
            $arrFields['captcha']['captcha'] .= $strCaptcha;
        }

        $this->Template->rowLast = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->hasError = $doNotSubmit;

        // Create new user if there are no errors
        if (\Input::post('FORM_SUBMIT') == 'tl_registration' && !$doNotSubmit)
        {
            $this->createNewUser($arrUser);
        }

        $this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_member']['loginDetails'];
        $this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_member']['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_member']['contactDetails'];
        $this->Template->personalData = $GLOBALS['TL_LANG']['tl_member']['personalData'];
        $this->Template->captchaDetails = $GLOBALS['TL_LANG']['MSC']['securityQuestion'];

        // Add the groups
        foreach ($arrFields as $k=>$v)
        {
            $this->Template->$k = $v; // backwards compatibility

            $key = $k . (($k == 'personal') ? 'Data' : 'Details');
            $arrGroups[$GLOBALS['TL_LANG']['tl_member'][$key]] = $v;
        }

        $this->Template->categories = $arrGroups;
        $this->Template->formId = 'tl_registration';
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['register']);
        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->captcha = $arrFields['captcha']['captcha']; // backwards compatibility
    }
}
