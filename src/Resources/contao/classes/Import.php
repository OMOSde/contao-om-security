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
 * Class Import
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class Import extends \Backend
{
    /**
     *
     */
    public function importPasswords()
    {
        $this->import('BackendUser', 'User');
        $strClassName = $this->User->uploader;

        // See #4086 and #7046
        if (!class_exists($strClassName) || $strClassName == 'DropZone')
        {
            $strClassName = 'FileUpload';
        }

        /** @var \FileUpload $objUploader */
        $objUploader = new $strClassName();

        // import csv
        if (\Input::post('FORM_SUBMIT') == 'importPasswords')
        {
            try
            {
                $arrUploaded = $objUploader->uploadTo('system/tmp');
            } catch (\Exception $e)
            {
                \Message::addError($GLOBALS['TL_LANG']['OM_SECURITY']['ERR']['invalidTargetPath']);
                $this->reload();
            }

            if (empty($arrUploaded))
            {
                \Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
                $this->reload();
            }

            $arrCsvPasswords = [];
            foreach ($arrUploaded as $strCsvFile)
            {
                try
                {
                    $objFile = new \File($strCsvFile, true);
                } catch (\Exception $e)
                {
                    \Message::addError(sprintf($GLOBALS['TL_LANG']['OM_SECURITY']['ERR']['cantCreateFile']));
                    continue;
                }

                if ($objFile->extension != 'csv')
                {
                    \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                    continue;
                }

                $resFile = $objFile->handle;
                while (($arrRow = @fgetcsv($resFile, null, ';')) !== false)
                {
                    $arrCsvPasswords[] = $arrRow['0'];
                }
            }

            if (!empty($arrCsvPasswords))
            {
                $objPasswords = OmSecurityPasswordModel::findByPid(\Input::get('id'));

                $arrNewPasswords = ($objPasswords) ? array_diff($arrCsvPasswords, $objPasswords->fetchEach('password')) : $arrCsvPasswords;

                foreach ($arrNewPasswords as $newPassword)
                {
                    $arrPassword = [
                        'pid'       => \Input::get('id'),
                        'tstamp'    => time(),
                        'password'  => $newPassword,
                        'published' => 1
                    ];

                    $objPassword = new OmSecurityPasswordModel();
                    $objPassword->setRow($arrPassword);
                    $objPassword->save();
                }
            }

            $this->redirect(str_replace('&key=import', '', \Environment::get('request')));
        }

        // return form
        return '<div id="tl_buttons">
                    <a href="' . ampersand(str_replace('&key=import',
                '',
                \Environment::get('request'))) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
                </div>
                ' . \Message::generate() . '
                <form action="' . ampersand(\Environment::get('request'), true) . '" id="importPasswords" class="tl_form tl_edit_form" method="post" enctype="multipart/form-data">
                    <div class="tl_formbody_edit">
                        <input type="hidden" name="FORM_SUBMIT" value="importPasswords">
                        <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
                        <input type="hidden" name="MAX_FILE_SIZE" value="' . \Config::get('maxFileSize') . '">
                        <div class="tl_tbox"> 
                            <div class="widget">
                                <h3>' . $GLOBALS['TL_LANG']['MSC']['source'][0] . '</h3>' . $objUploader->generateMarkup() . (isset($GLOBALS['TL_LANG']['MSC']['source'][1]) ? '
                                <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['source'][1] . '</p>' : '') . '
                            </div>
                        </div>
                    </div>
                    <div class="tl_formbody_submit">
                        <div class="tl_submit_container">
                            <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['lw_import'][0]) . '">
                        </div>
                    </div>
                </form>';
    }
}
