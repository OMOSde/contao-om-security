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
 * Table tl_om_security_password
 */
$GLOBALS['TL_DCA']['tl_om_security_password'] = [

    // Config
    'config'   => [
        'dataContainer' => 'Table',
        'ptable'        => 'tl_om_security_password_list',
        'sql'           => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ]
        ]
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['password'],
            'panelLayout'           => 'sort;search,limit',
            'headerFields'          => ['title'],
            'child_record_callback' => ['tl_om_security_password', 'childRecordCallback']
        ],
        'label'             => [
            'fields' => ['password'],
            'format' => '%s',
        ],
        'global_operations' => [
            'import' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_om_security_password']['import'],
                'href'            => 'key=import',
                'icon'            => 'system/themes/flexible/icons/theme_import.svg',
                'button_callback' => ['tl_om_security_password', 'importPasswords']
            ],
            'export' => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password']['export'],
                'href'  => 'key=export',
                'icon'  => 'system/themes/flexible/icons/theme_export.svg',
            ],
            'all'    => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_om_security_password']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_om_security_password']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_om_security_password']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_om_security_password', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_om_security_password']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        'default' => '{password_legend},password;{publish_legend},published'
    ],

    // Fields
    'fields'   => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'password'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_om_security_password']['password'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'attempts'  => [
            'label'   => &$GLOBALS['TL_LANG']['tl_om_security_password']['attempts'],
            'sorting' => true,
            'flag'    => 12,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_om_security_password']['published'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''"
        ]
    ]
];


/**
 * Class tl_om_security_password
 *
 * @copyright OMOS.de 2019 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 */
class tl_om_security_password extends \Backend
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
     * @param $arrRow
     *
     * @return mixed
     */
    public function childRecordCallback($arrRow)
    {
        return sprintf('%s <span style="color:#b3b3b3;padding-left:3px">[%s]</span>', $arrRow['password'], $arrRow['attempts']);
    }


    /**
     * Return the "import theme" link
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     */
    public function importPasswords($href, $label, $title, $class, $attributes)
    {
        return '<a href="' . $this->addToUrl($href) . '" class="' . $class . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a>';
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
            \Controller::redirect(\Controller::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_om_security_passwords::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . \Controller::addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }


    /**
     * Disable/enable an option
     *
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_om_security_passwords::published', 'alexf'))
        {
            \System::log('Not enough permissions to publish/unpublish password ID "' . $intId . '"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_om_security_password', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_om_security_password']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_om_security_password']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_om_security_passwords SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();
        \System::log('A new version of record "tl_om_security_passwords.id=' . $intId . '" has been created' . $this->getParentEntries('tl_om_security_password', $intId), __METHOD__, TL_GENERAL);
    }
}
