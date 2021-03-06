<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <benjamin.woester@googlemail.com> wrote this file. As long as you retain
 * this notice you can do whatever you want with this stuff. If we meet some
 * day, and you think this stuff is worth it, you can buy me a beer in return.
 * Benjamin Wöster
 * ----------------------------------------------------------------------------
 */
/**
 * @author     Benjamin Wöster <benjamin.woester@googlemail.com>
 * @package    libIwParsers
 * @subpackage helpers
 */

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class PropertyValueC
{

    /////////////////////////////////////////////////////////////////////////////

    /**
     * function ensureInteger
     *
     * filtert beliebige Zahlen mit Tausendertrennzeichen und maximal 2 Nachkommastellen
     *
     * @param string|int|float $value Zahl zum Filtern
     *
     * @uses   PropertyValueC::ensureFloat
     * @return int gefilterte Zahl
     *
     * @author masel <masel789@googlemail.com>
     */
    public static function ensureInteger($value)
    {
        return (integer)round(PropertyValueC::ensureFloat($value));
    }

    /////////////////////////////////////////////////////////////////////////////

    public static function ensureBoolean($value)
    {
        return (boolean)$value;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * function ensureFloat
     *
     * filtert beliebige Zahlen mit Tausendertrennzeichen und maximal 2 Nachkommastellen
     *
     * @param string|int|float $value Zahl zum Filtern
     *
     * @return float gefilterte Zahl
     *
     * @author masel <masel789@googlemail.com>
     */
    public static function ensureFloat($value)
    {

        $filtered_number = 0;
        if (preg_match('~^\s*(?P<sign>-|\+|)(?P<digit>\d{1,3}(?:(\D?)\d{3})?(?:\3\d{3})*)(?:\D(?P<part>\d{1,2}))?\s*$~', $value, $numberpart)) {
            $filtered_number = preg_replace('~\D~', '', $numberpart['digit']);

            if (isset($numberpart['part'])) {                        //Nachkommastellen vorhanden?
                if (strlen($numberpart['part']) === 2) {             //zwei Nachkommastellen
                    $filtered_number += $numberpart['part'] / 100;
                } else {                                             //eine Nachkommastelle
                    $filtered_number += $numberpart['part'] / 10;
                }
            }

            if ($numberpart['sign'] === '-') {                       //evl. negatives Vorzeichen wieder dazu
                $filtered_number = -$filtered_number;
            }

        }

        return (float)$filtered_number;

    }

    /////////////////////////////////////////////////////////////////////////////

    public static function ensureString($value)
    {
        return (string)$value;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * @deprecated
     *
     * ParserBaseC::setText cares for the necessary replacements. So if
     * the user inputs an IceWars page containing alternative resource names,
     * these will be replaced by the normal resource names before any parser
     * starts doing its work (this is also important to hold the parsers
     * simple).
     *
     * Thus, any resource a parser can find and return should be a 'normal'
     * resource name.
     *
     * To validate that a parser really matched a resource name and not some
     * arbitrary string, you should use:
     *
     * PropertyValueC::ensureEnum( $value, 'eResources' );
     */
    public static function ensureResource($value)
    {
        $value = (string)$value;

        if ($value == "Erdbeeren") {
            $value = "Eisen";
        } else if ($value == "Erdbeermarmelade") {
            $value = "Stahl";
        } else if (preg_match('/Erdbeerkonfit.+re/', $value) > 0) {
            $value = "VV4A";
        } else if ($value == "blubbernde Gallertmasse") {
            $value = "Bevölkerung";
        } else if (preg_match('/Bev.+lkerung/', $value) > 0) {
            $value = "Bevölkerung";
        } else if ($value == "Vanilleeis") {
            $value = "Eis";
        } else if ($value == "Eismatsch") {
            $value = "Wasser";
        } else if ($value == "Traubenzucker") {
            $value = "Energie";
        } else if ($value == "Kekse") {
            $value = "Credits";
        } else if ($value == "Brause") {
            $value = "chem. Elemente";
        }

        return $value;
    }

    /////////////////////////////////////////////////////////////////////////////

    public static function ensureArray($value)
    {
        return (array)$value;
    }

    /////////////////////////////////////////////////////////////////////////////

    public static function ensureObject($value)
    {
        return (object)$value;
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * Checks if the given value is a value of the enum $enumName
     *
     * The method first strips out all characters, that are not allowed in
     * PHP labels. It then checks if the class $enumName contains a constant
     * labeled like the stripped $value, and if it finds such a constant,
     * it returns its value.
     *
     * e.g.
     * If you try to ensure resource names, you'd call it like this:
     *
     * $result = PropertyValueC::ensureEnum( 'Eisen', 'eResources' );
     *
     * Because eResources defines a constant labeled 'Eisen', the method
     * returns its value (which is 'iron').
     *
     * Imagine the following:
     *
     * $result = PropertyValueC::ensureEnum( 'chem. Elemente', 'eResources' );
     *
     * Of course, 'chem. Elemente' can't be a constant of eResources, because
     * they can't contain dots or spaces. So the method strips those characters
     * and searches for a constant labeled 'chemElemente'. It finds the constant
     * and returns its value 'chemicals'.
     */
    public static function ensureEnum($value, $enumName)
    {
        $reflectionClass = new ReflectionClass($enumName);

        //TODO: Find better solution
        //dirty hack for ePlanetObjects:
        //replace --- with noObject, because --- can't be defined as a
        //constant.
        //I also thought about returning an empty string if after stripping
        //characters that are not allowed nothing was left. But I'm not convinced
        //by the behaviour that that would imply (enureEnum would accept empty
        //strings as valid enum values).
        $value = preg_replace('/---/', 'noObject', $value);

        //replace characters that are not allowed
        //see: http://de2.php.net/manual/en/language.variables.basics.php
        //TODO: first character mustn't be a number.
        $value = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '', $value);

        if ($reflectionClass->hasConstant($value)) {
            return $reflectionClass->getConstant($value);
        } else {
            throw new Exception("'$value' is not a valid enumerable value for enum '$enumName'.");
        }
    }

}