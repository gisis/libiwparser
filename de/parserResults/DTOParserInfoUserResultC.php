<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <MacXY@herr-der-mails.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Mac
 * ----------------------------------------------------------------------------
 */
/**
 * @author     Mac <MacXY@herr-der-mails.de>
 * @package    libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Result DTO of parser de_info_universium
 */
class DTOParserInfoUserResultC
{
    /**
     * @soap
     * @var string $strUserName the user of the object
     */
    public $strUserName;

    /**
     * @soap
     * @var string $strUserAlliance the alliance of the user
     */
    public $strUserAlliance;

    /**
     * @soap
     * @var string $strUserAllianceTag
     */
    public $strUserAllianceTag;

    /**
     * @soap
     * @var string $strUserAllianceJob
     */
    public $strUserAllianceJob;

    /**
     * @soap
     * @var string $bAccType
     */
    public $strAccType;

    /**
     * @soap
     * @var string $strPlanetName name of the object
     */
    public $strPlanetName;

    /**
     * @soap
     * @var array $aCoords
     */
    public $aCoords = array();

    /**
     * @soap
     * @var array $strCoords
     */
    public $strCoords = null;

    /**
     * @soap
     * @var int $iEntryDate
     */
    public $iEntryDate;

    /**
     * @soap
     * @var int $iGebPkt
     */
    public $iGebPkt;

    /**
     * @soap
     * @var int $iFP
     */
    public $iFP;

    /**
     * @soap
     * @var int $iHSPos
     */
    public $iHSPos;

    /**
     * @soap
     * @var int $iHSChange
     */
    public $iHSChange;

    /**
     * @soap
     * @var int $iEvo
     */
    public $iEvo;

    /**
     * @soap
     * @var string $strStaatform
     */
    public $strStaatsform;

    /**
     * @soap
     * @var string $strTitel
     */
    public $strTitel;

    /**
     * @soap
     * @var string $strDescr
     */
    public $strDescr;

    /**
     * @soap
     * @var string $strMisc
     */
    public $strMisc;

}