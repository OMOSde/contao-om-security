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
 * Use
 */
use OMOSde\ContaoOmSecurityBundle\OmSecurityPasswordModel;


/**
 * Table tl_om_security_password_list
 */
$GLOBALS['TL_DCA']['tl_om_security_password_list'] = [

    // Config
    'config'   => [
        'dataContainer' => 'Table',
        'ctable'        => ['tl_om_security_password'],
        'sql'           => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search,limit'
        ],
        'label'             => [
            'fields'         => ['title'],
            'format'         => '%s',
            'label_callback' => ['tl_om_security_password_list', 'labelCallback']
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['edit'],
                'href'  => 'table=tl_om_security_password',
                'icon'  => 'edit.gif'
            ],
            'editHeader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif'
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_om_security_password_list']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle'     => [
                'label'           => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_om_security_password_list', 'toggleIcon']
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title;{publish_legend},published'
    ],

    // Fields
    'fields'   => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['title'],
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_om_security_password_list']['published'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''"
        ]
    ]
];


/**
 * Class tl_om_security_password_list
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class tl_om_security_password_list extends \Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Label callback
     *
     * @param $arrRow
     *
     * @return string
     */
    public function labelCallback($arrRow)
    {
        $intPasswords = OmSecurityPasswordModel::countBy(['pid=?', 'published=1'], $arrRow['id']);

        return $arrRow['title'] . '<span style="color:#b3b3b3;padding-left:3px">[' . $intPasswords . ']</span>';
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_om_security_password_list::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }


    /**
     * Disable/enable a password list
     *
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_om_security_password_list::published', 'alexf'))
        {
            \System::getContainer()->get('monolog.logger.contao')->log(LogLevel::ERROR, 'Not enough permissions to publish/unpublish password list ID "' . $intId . '"');
            $this->redirect('contao/main.php?act=error');
        }

        // update the database
        $this->Database->prepare("UPDATE tl_om_security_password_list SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        // create new version
        $objVersions = new Versions('tl_om_security_password_list', $intId);
        $objVersions->initialize();
        $objVersions->create();

        // create log entry
        \System::getContainer()->get('monolog.logger.contao')->log(LogLevel::INFO, 'A new version of record "tl_om_security_password_list.id=' . $intId . '" has been created');
    }
}
