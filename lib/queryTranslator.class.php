<?php
/**
 * User: Jens Wiemann
 * Date: 09.12.13
 * Time: 21:43
 */
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php 
require_once("classes/PHPSQLParser.php");
//https://code.google.com/p/php-sql-parser/
/*
echo("<pre>");
$qT = new queryTranslator();
print_r($qT->translate("
(
SELECT CNAME
FROM COCKTAIL
)
MINUS
(
SELECT CNAME
FROM COCKTAIL
)
", "Jens_"));
*/
class queryTranslator {
	
	//Add $username in front of every tablename of $inputquery
    public function translate($inputquery, $username){
        try{
			$parser = new PHPSQLParser();
			$parsed = $parser->parse($inputquery, TRUE);
			//print_r($parsed);
			$positions = $this->nameSearch($parsed);
		
			$i=0;
			foreach($positions as $pos){
				$inputquery = substr_replace($inputquery, $username, $pos+strlen($username)*$i, 0);
				++$i;
			}
		}
		catch( Exception $e)
		{
			return $e;
		}
		return $inputquery;			
    }
	//Search for Names in Query
	private function nameSearch($parsedarray){	

		//Change if other type required
		//$tempresult = search($parsedarray, "expr_type", "table");
		$tempresult = $this->search2($parsedarray,"table");
		$answer = new ArrayObject();
		
		//Returns ownly the positions matching to the tables
		foreach($tempresult as $key => $val){
			foreach($tempresult[$key] as $key2 => $val2){
				if ($key2 == "position")	
					$answer[] = $val2;
				}
		}
		return $answer;
	}

    private function search2($array, $key) {
        $results = array();

        if (is_array($array)){
            if (isset($array[$key]))
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search2($subarray, $key));
        }
        return $results;
    }

    private function search($array, $key, $value) {
        $results = array();

        if (is_array($array)){
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, $this->search($subarray, $key, $value));
        }
        return $results;
    }

    //TODO: check for forbidden commands
    private function checkForbidden(){

    }
} 