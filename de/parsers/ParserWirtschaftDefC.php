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
 * @author Mac <MacXY@herr-der-mails.de>
 * @package libIwParsers
 * @subpackage parsers_de
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

/**
 * Parser for the defence overview
 *
 * This parser is responsible for parsing the defence overview at economy
 *
 * Its identifier: de_wirtschaft_def
 */
class ParserWirtschaftDefC extends ParserBaseC implements ParserI
{

  /////////////////////////////////////////////////////////////////////////////

  public function __construct()
  {
    parent::__construct();

    $this->setIdentifier('de_wirtschaft_def');
    $this->setName('Verteidigungs&uuml;bersicht');
    $this->setRegExpCanParseText('/Geb.{1,3}ude.{1,3}bersicht\s*Forschungs.{1,3}bersicht\s*Werft.{1,3}bersicht\s*Defence.{1,3}bersicht.*Verteidigungs.{1,3}bersicht(?:.*Verteidigungs.{1,3}bersicht)?/sm');
    $this->setRegExpBeginData( '/Defence.{1,3}bersicht.*Verteidigungs.{1,3}bersicht/sm' );
    $this->setRegExpEndData( '' );
  }

  /////////////////////////////////////////////////////////////////////////////

  /**
   * @see ParserI::parseText()
   */
  public function parseText(DTOParserResultC $parserResult) 
  {
        $parserResult->objResultData = new DTOParserWirtschaftDefResultC();
        $retVal = & $parserResult->objResultData;
        $fRetVal = 0;

        $this->stripTextToData();

        $regExp = $this->getRegularExpression();
        $aResult = array();
        $fRetVal = preg_match_all($regExp, $this->getText(), $aResult, PREG_SET_ORDER);
        $aKolos = array();

        if ($fRetVal !== false && $fRetVal > 0) {
            $parserResult->bSuccessfullyParsed = true;

            foreach ($aResult as $result) {

                $strKoloLine = $result['kolo_line'];
                $strSlotLine = $result['slot_line'];
                $strDataLines = $result['data_lines'];

                if (empty($aKolos)) {
                    $regExpKolo = $this->getRegularExpressionKolo();

                    $aResultKolo = array();
                    $fRetValKolo = preg_match_all($regExpKolo, $strKoloLine, $aResultKolo, PREG_SET_ORDER);

                    foreach ($aResultKolo as $resultKolo) {
                        $strKoloType = $resultKolo['kolo_type'];
                        $strCoords = $resultKolo['coords'];
                        $iCoordsGal = PropertyValueC::ensureInteger($resultKolo['coords_gal']);
                        $iCoordsSol = PropertyValueC::ensureInteger($resultKolo['coords_sol']);
                        $iCoordsPla = PropertyValueC::ensureInteger($resultKolo['coords_pla']);
                        $aCoords = array('coords_gal' => $iCoordsGal, 'coords_sol' => $iCoordsSol, 'coords_pla' => $iCoordsPla);

                        $retVal->aKolos[$strCoords] = new DTOParserWirtschaftDefKoloResultC;
                        $retVal->aKolos[$strCoords]->aCoords = $aCoords;
                        $retVal->aKolos[$strCoords]->strCoords = PropertyValueC::ensureString($strCoords);
                        $retVal->aKolos[$strCoords]->strObjectType = PropertyValueC::ensureString($strKoloType);

                        $aKolos[] = $strCoords;
                    }
                }

                $aSlotLines = explode("\n", $strSlotLine);
                foreach ($aSlotLines as $strSlotLine) {
                    $aData = explode(" ", trim($strSlotLine));
                    $aData = array_filter($aData, function($val){return ($val !== "/");});  //! alle / rausfiltern
                    $aData = array_values($aData);                                          //! keys korrigieren

                    $strDefenceType = array_shift($aData);
                    array_shift($aData); // legende entfernen
                    if (strpos($strDefenceType,"orb") !== FALSE)
                        $strDefenceType = "orbital";
                    else if (strpos($strDefenceType,"pla") !== FALSE)
                        $strDefenceType = "planetar";
                                        
                    $slot = new DTOParserWirtschaftDefSlotResultC;
                    $slot->strSlotType = PropertyValueC::ensureString($strDefenceType);

                    if (empty($strDefenceType))
                        continue;
                    for ($i=0; $i<=count($aData)-3; $i+=3) {
                        $slot->aAvailable[$aKolos[$i/3]] = PropertyValueC::ensureInteger($aData[$i]);
                        $slot->aUsed[$aKolos[$i/3]] = PropertyValueC::ensureInteger($aData[$i+1]);
                        $slot->aTotal[$aKolos[$i/3]] = PropertyValueC::ensureInteger($aData[$i+2]);
                    }
                    $retVal->aSlots[] = $slot;
                }
                
                $aResultDefence = array();
                $fRetValDefence = preg_match_all($this->getRegularExpressionBuildDefence(), $strDataLines, $aResultDefence, PREG_SET_ORDER);

                foreach ($aResultDefence as $resultDef) {
                    $strDefenceName = $resultDef['name'];
                    $defence = new DTOParserWirtschaftDefDefenceResultC;
                    $defence->strDefenceName = PropertyValueC::ensureString($strDefenceName);

                    if (empty($strDefenceName))
                        continue;
                    $resultDef['anz'] = str_replace("\t", " ", $resultDef["anz"]);
                    $aData = explode(" ", trim($resultDef['anz']));
                    $aData = array_filter($aData, function($val){return ($val !== "/");});  //! alle / rausfiltern
                    $aData = array_values($aData);                                          //! keys korrigieren
                    for ($i=0; $i<=count($aData)-2; $i+=2) {
                        $defence->aCounts[$aKolos[$i/2]] = PropertyValueC::ensureInteger($aData[$i]);
                        $defence->aMaxCounts[$aKolos[$i/2]] = PropertyValueC::ensureInteger($aData[$i+1]);
                    }
                    
                $retVal->aDefences[] = $defence;
                }
            }
        } else {
            $parserResult->bSuccessfullyParsed = false;
            $parserResult->aErrors[] = 'Unable to match the pattern.';
        }
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getRegularExpression()
  {
    /**
    */

    $reKoloTypes        = $this->getRegExpKoloTypes();
    $reKoloCoords       = $this->getRegExpKoloCoords();
    $reNumber           = $this->getRegExpDecimalNumber();
    $reDefence          = $this->getRegExpDefence();
    
    $regExp = '&';
    $regExp .= '(?P<kolo_line>';
    $regExp .=     $reKoloCoords;
    $regExp .= '   (?:';
    $regExp .= '     [\n\r]+';
    $regExp .= '     \('.$reKoloTypes.'\)';
    $regExp .= '     \s+';
    $regExp .= '     '.$reKoloCoords.'';
    $regExp .= '   )+';
    $regExp .= '   [\n\r]+';
    $regExp .= '   \('.$reKoloTypes.'\)';
    $regExp .= ')';
    $regExp .= '\s+';
    $regExp .= '^Verteidigungsslots\s';
    $regExp .= '(?P<slot_line>';
    $regExp .= '   (?:(?:pla|orb)\sfrei/belegt/gesamt(?:\s+' . $reNumber . "\s/\s" . $reNumber . "\s/\s" . $reNumber . ')*[\n\r]+';
    $regExp .= '   )+';
    $regExp .= ')';
    $regExp .= '\s*';
    $regExp .= '^Verteidigungsanlagen\s\(Anzahl/Baubar\)';
    $regExp .='(?P<data_lines>(?:';
    $regExp .='      [\n\r]+';    //! Zeilenumbruch
    $regExp .='     ' . $reDefence;
    $regExp .='      (?:[\t|\s]\d*\s/\s\d*)+';   //! Bundle von (Nr / Nr)
    $regExp .='      )+';
    $regExp .= ')\s+';
    $regExp .= '&mx';

    return $regExp;
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getRegularExpressionKolo()
  {
    /**
    */

    $reKoloTypes           = $this->getRegExpKoloTypes();
    $reKoloCoords        = $this->getRegExpKoloCoords();

    $regExpKolo  = '/
          (?P<coords>(?P<coords_gal>\d{1,2})\:(?P<coords_sol>\d{1,3})\:(?P<coords_pla>\d{1,2}))
          [\n\r]+
          \((?P<kolo_type>'.$reKoloTypes.')\)
    /mx';

    return $regExpKolo;
  }

  /////////////////////////////////////////////////////////////////////////////

  private function getRegularExpressionBuildDefence()
  {
    /**
    */

    $reDefence        = $this->getRegExpDefence();

    $regExp = '~';
    $regExp .= '(?P<name>' . $reDefence . ')';
    $regExp .= '(?P<anz>(?:[\t|\s]\d*\s/\s\d*)+)';   //! Bundle von (Nr / Nr)
    $regExp .= '~mx';

    return $regExp;
  }
  
  /////////////////////////////////////////////////////////////////////////////

  /**
   * For debugging with "The Regex Coach" which doesn't support named groups
   */
  private function getRegularExpressionWithoutNamedGroups()
  {
    $retVal = $this->getRegularExpression();

    $retVal = preg_replace( '/\?P<\w+>/', '', $retVal );

    return $retVal;
  }

  /////////////////////////////////////////////////////////////////////////////

}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////