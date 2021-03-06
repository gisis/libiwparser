<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <martin@martimeo.de> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Martin Martimeo
 * ----------------------------------------------------------------------------
 */
/**
 * @author     Martin Martimeo <martin@martimeo.de>
 * @author     Mac <MacXY@herr-der-mails.de>
 * @package    libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parser for the schiff overview
 *
 * This parser is responsible for parsing the schiff overview at economy
 *
 * Its identifier: de_mil_schiff_uebersicht
 */
class ParserMilSchiffUebersichtC extends ParserBaseC implements ParserI
{

    /////////////////////////////////////////////////////////////////////////////

    public function __construct()
    {
        parent::__construct();

        $this->setIdentifier('de_mil_schiff_uebersicht');
        $this->setName('Schiffsübersicht');
        $this->setRegExpCanParseText('/Milit.{1,3}r[\s\S]*Schiff.{1,3}bersicht[\s\S]*Schiffs.{1,3}bersicht/');
        $this->setRegExpBeginData('/Schiffs.{1,3}bersicht/sm');
        $this->setRegExpEndData('');
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * @see ParserI::parseText()
     */
    public function parseText(DTOParserResultC $parserResult)
    {
        $parserResult->objResultData = new DTOParserMilSchiffUebersichtResultC();
        $retVal =& $parserResult->objResultData;

        $this->stripTextToData();

        $regExp  = $this->getRegularExpression();
        $aResult = array();
        $fRetVal = preg_match_all($regExp, $this->getText(), $aResult, PREG_SET_ORDER);

        if ($fRetVal !== false && $fRetVal > 0) {
            $parserResult->bSuccessfullyParsed = true;

            $aKolos = array();
            foreach ($aResult as $result) {
                $strKoloLine  = $result['kolo_line'];
                $strDataLines = $result['data_lines'];

                if (empty($aKolos)) {
                    $regExpKolo = $this->getRegularExpressionKolo();

                    $aResultKolo = array();

                    preg_match_all($regExpKolo, $strKoloLine, $aResultKolo, PREG_SET_ORDER);

                    foreach ($aResultKolo as $resultKolo) {
                        $strKoloType = $resultKolo['kolo_type'];
                        $strCoords   = $resultKolo['coords'];
                        $iCoordsGal  = PropertyValueC::ensureInteger($resultKolo['coords_gal']);
                        $iCoordsSol  = PropertyValueC::ensureInteger($resultKolo['coords_sol']);
                        $iCoordsPla  = PropertyValueC::ensureInteger($resultKolo['coords_pla']);
                        $aCoords     = array(
                            'coords_gal' => $iCoordsGal,
                            'coords_sol' => $iCoordsSol,
                            'coords_pla' => $iCoordsPla
                        );

                        $retVal->aKolos[$strCoords]                = new DTOParserMilSchiffUebersichtKoloResultC;
                        $retVal->aKolos[$strCoords]->aCoords       = $aCoords;
                        $retVal->aKolos[$strCoords]->strCoords     = PropertyValueC::ensureString($strCoords);
                        $retVal->aKolos[$strCoords]->strObjectType = PropertyValueC::ensureString($strKoloType);

                        $aKolos[] = $strCoords;
                    }

                }

                $regExpSchiff  = $this->getRegularExpressionSchiff();
                $aDataLines    = array();

                preg_match_all($regExpSchiff, $strDataLines, $aDataLines, PREG_SET_ORDER);

                foreach ($aDataLines as $strDataLine) {
                    $aDataLine = explode("\t", $strDataLine["anz"]);

                    $schiff                = new DTOParserMilSchiffUebersichtSchiffResultC;
                    $schiff->strSchiffName = PropertyValueC::ensureString($strDataLine["schiff"]);

                    $schiff->iCountGesamt = PropertyValueC::ensureInteger(array_pop($aDataLine));
                    $schiff->iCountStat   = PropertyValueC::ensureInteger(array_pop($aDataLine));
                    $schiff->iCountFlug   = PropertyValueC::ensureInteger(array_pop($aDataLine));

                    if (empty($schiff->iCountGesamt) || $schiff->iCountGesamt == 0) {
                        continue;
                    }
                    foreach ($aDataLine as $i => $strData) {
                        $schiff->aCounts[$aKolos[$i]] = PropertyValueC::ensureInteger($strData);
                    }

                    $retVal->aSchiffe[] = $schiff;

                }

            }

        } else {
            $parserResult->bSuccessfullyParsed = false;
            $parserResult->aErrors[]           = 'Unable to match the de_mil_schiff_uebersicht pattern.';
        }

    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpression()
    {
        $reKoloTypes  = $this->getRegExpKoloTypes();
        $reKoloCoords = $this->getRegExpKoloCoords();

        $regExp  = '/^';
        $regExp .= '\s?';
        $regExp .= '(?P<kolo_line>' . $reKoloCoords;
        $regExp .= '    (?:\n+\(' . $reKoloTypes . '\)\s' . $reKoloCoords . ')*';
        $regExp .= '    \n+\(' . $reKoloTypes . '\)';
        $regExp .= '    \s+';
        $regExp .= '    Im\sFlug\sStat\sGesamt';
        $regExp .= ')';
        $regExp .= '(?P<data_lines>';
        $regExp .= '    (?:';
        $regExp .= '        \n+';
        $regExp .= '        [^\n]+';
        $regExp .= '    )*';
        $regExp .= ')';
        $regExp .= '$/mx';

        return $regExp;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionSchiff()
    {
        $reSchiff = $this->getRegExpSingleLineText3();

        $regExpSchiffe = '/';
        $regExpSchiffe .= '(?:^(?P<schiff>' . $reSchiff . ')\t(?P<anz>(?:[^\s]*\t?)+)' . '\s*$)+';
        $regExpSchiffe .= '/mx';

        return $regExpSchiffe;
    }

    /////////////////////////////////////////////////////////////////////////////

    private function getRegularExpressionKolo()
    {
        $reKoloTypes  = $this->getRegExpKoloTypes();

        $regExpKolo  = '/';
        $regExpKolo .= '(?P<coords>(?P<coords_gal>\d{1,2})\:(?P<coords_sol>\d{1,3})\:(?P<coords_pla>\d{1,2}))';
        $regExpKolo .= '\n+';
        $regExpKolo .= '\((?P<kolo_type>' . $reKoloTypes . ')\)';
        $regExpKolo .= '/mx';

        return $regExpKolo;
    }

}