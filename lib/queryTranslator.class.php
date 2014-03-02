<?php

/**
 * @package    SqlValidator
 * @author     Christian Steusloff
 * @author     Jens Wiemann
 */

require_once("classes/PHPSQLParser.php");
//php-sql-browser
//https://code.google.com/p/php-sql-parser/

/**
 * Class queryTranslator
 */
class queryTranslator
{
    /**
     * Add $username in front of every tablename of $inputquery
     *
     * @param string $inputquery Query to be translated
     * @param string $username to be placed infront of every tablename
     * @return Exception|mixed|string Returns translated Query or Exception
     */
    public function translate($inputquery, $username)
    {
        $result_Query = $inputquery;
        if (stristr($inputquery, "MINUS") === false) {
            try {
                $parser = new PHPSQLParser();
                $parsed = $parser->parse($inputquery, TRUE);
                $positions = $this->nameSearch($parsed);
                $i = 0;
                foreach ($positions as $pos) {
                    $result_Query = substr_replace($result_Query, $username, $pos + strlen($username) * $i, 0);
                    ++$i;
                }
            } catch (Exception $e) {
                return $e;
            }
        } else {
            $result_Query = $this->translate_Minus($inputquery, $username);
        }
        return $result_Query;
    }

    /**
     * Add $username in front of every tablename of $inputquery for queries with 'MINUS'
     * because the php-sql-parser does not parse the 2. MINUS part correctly
     *
     * @param string $inputquery
     * @param string $username
     * @return Exception|mixed|string
     */
    private function translate_Minus($inputquery, $username)
    {
        $answer = null;
        $first = true;
        $inputquery = str_ireplace("minus", "MINUS", $inputquery);
        $parts = explode("MINUS", $inputquery);
        foreach ($parts as $part) {
            //Remove double white spaces
            $part = preg_replace('/\s+/', ' ', $part);
            //Remove superfluous brackets
            $supBrackets = false;
            $position1 = strpos($part, "(");
            $position2 = null;
            if ($position1 === 0 || $position1 === 1 || $position1 === 2 || $position1 === 3) {
                $position2 = strrpos($part, ")");
                if ($position2 != 0) {
                    $supBrackets = true;
                    $part = substr_replace($part, "", $position1, 1);
                    $part = substr_replace($part, "", $position2 - 1, 1);
                }
            }
            try {
                $parser = new PHPSQLParser();
                $parsed = $parser->parse($part, TRUE);
                $positions = $this->nameSearch($parsed);

                $i = 0;
                foreach ($positions as $pos) {
                    $part = substr_replace($part, $username, $pos + strlen($username) * $i, 0);
                    ++$i;
                }
            } catch (Exception $e) {
                return $e;
            }
            //Insert removed Brackets
            if ($supBrackets) {
                $part = substr_replace($part, "(", $position1, 0);
                $part = substr_replace($part, ")", $position2 + strlen($username) * $i, 0);
            }

            if ($first) {
                $answer = $part;
                $first = false;
            } else
                $answer .= "MINUS" . $part;
        }

        return $answer;
    }

    /**
     * Searches the tablenames out of the Array produced by the php-sql-parser
     *
     * @param array $parsedarray from php-sql-parser
     * @return array|ArrayObject with the positions of tablenames in the Query
     */
    private function nameSearch($parsedarray)
    {
        //Change if other type required
        $tempresult = $this->search2($parsedarray, "table");
        $answer = new ArrayObject();

        //Returns ownly the positions matching to the tables
        foreach ($tempresult as $key => $val) {
            foreach ($tempresult[$key] as $key2 => $val2) {
                if ($key2 == "position")
                    $answer[] = $val2;
            }
        }
        return $answer;
    }

    /**
     * Searches for arrays containing $key in $array; recursively
     *
     * @param array $array
     * @param string $key
     * @return array that contain arrays, containing $key
     */
    private function search2($array, $key)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]))
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search2($subarray, $key));
        }
        return $results;
    }

    /**
     * Alternative to search2() that searches for arrays containing $key AND $value; recursively
     *
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array that contain arrays, containing $key = $value
     */
    private function search($array, $key, $value)
    {
        $results = array();
        //Accept only arrays with $key=$value
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search($subarray, $key, $value));
        }
        return $results;
    }
} 