<?php
/**
 * User: Jens Wiemann
 * Date: 09.12.13
 * Time: 21:43
 */

require_once("classes/PHPSQLParser.php");
//https://code.google.com/p/php-sql-parser/
/*
echo("<pre>");
$qT = new queryTranslator();
print_r($qT->translate("
(
  select 
    cname 
  from 
    cocktail
) minus (
  select 
    cname 
  from 
    cocktail natural 
    join zutat_cocktail
)
", "Jens_"));
*/


/**
 * Class translation
 */
class translation
{
    /**
     * @var null
     */
    public $query = null;
    /**
     * @var bool
     */
    public $has_OrderBy = false;
    /**
     * @var null
     */
    public $OrderBy = null;
    /**
     * @var null
     */
    public $exception = null;
}


class queryTranslator
{


    /**
     * Add $username in front of every tablename of $inputquery
     * @param $inputquery
     * @param $username
     * @return Exception|mixed|string
     */
    public function translate($inputquery, $username)
    {
        $translation = new translation();
        $result_Query = null;
        if (stristr($inputquery, "MINUS") === false) {
            try {
                $parser = new PHPSQLParser();
                $parsed = $parser->parse($inputquery, TRUE);
                //print_r($parsed);
                $positions = $this->nameSearch($parsed);
                $result_Query = $inputquery;
                $i = 0;
                foreach ($positions as $pos) {
                    $result_Query = substr_replace($result_Query, $username, $pos + strlen($username) * $i, 0);
                    ++$i;
                }
            } catch (Exception $e) {
                $translation->exception = $e;
            }
            //$result_Query = $this->extractOrder($inputquery, $translation);
            $translation->query = $result_Query;
        } else {
            $this->translate_Minus($inputquery, $username, $translation);
        }
        //TODO: return whole translation
        return $translation->query;
    }

    /**
     * @param $inputquery
     * @param $translation
     * @return null
     */
    private function extractOrder($inputquery, $translation)
    {
        if (stripos($inputquery, "ORDER BY") === false) {
            return $inputquery;
        } else {
            $translation->has_OrderBy = true;
            //TODO: complete
           /* try {
                $parser = new PHPSQLParser();
                $parsed = $parser->parse($inputquery, TRUE);
                //print_r($parsed);
                $positions = $this->nameSearch($parsed);
                $result_Query = $inputquery;
                $i = 0;
                foreach ($positions as $pos) {
                    $result_Query = substr_replace($result_Query, "", $pos + strlen($username) * $i, 0);
                    ++$i;
                }
            } catch (Exception $e) {
                $translation->exception = $e;
            }

*/
            return $inputquery;
        }

    }


    /**
     * @param $inputquery
     * @param $username
     * @return Exception|mixed|string
     */
    private function translate_Minus($inputquery, $username, $translation)
    {
        $first = true;
        $inputquery = str_ireplace("minus", "MINUS", $inputquery);
        $parts = explode("MINUS", $inputquery);
        foreach ($parts as $part) {
            //Remove double white spaces
            $part = preg_replace('/\s+/', ' ', $part);
            //Remove superfluous brackets
            $supBrackets = false;
            $position1 = strpos($part, "(");
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
                //print_r($parsed);
                $positions = $this->nameSearch($parsed);

                $i = 0;
                foreach ($positions as $pos) {
                    $part = substr_replace($part, $username, $pos + strlen($username) * $i, 0);
                    ++$i;
                }
            } catch (Exception $e) {
                $translation->exception = $e;
                return;
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

        $translation->query = $answer;
    }
    //Search for Names in Query
    /**
     * @param $parsedarray
     * @return array|ArrayObject
     */
    private function nameSearch($parsedarray)
    {

        //Change if other type required
        //$tempresult = search($parsedarray, "expr_type", "table");
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
     * @param $array
     * @param $key
     * @return array
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
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    private function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search($subarray, $key, $value));
        }
        return $results;
    }
} 