<?php
/**
 * Project: sqlValidator
 * User: Christian Steusloff
 * Date: 17.02.14
 * Time: 13:12
 *
 * only for Frontend
 */

class frontendHelper {


    public function unsetSession(&$session,$stringVariable){
        if(is_array($stringVariable)){
            foreach($stringVariable as $value){
                $this->_unsetSession($session,$value);
            }
        } else {
            $this->_unsetSession($session,$stringVariable);
        }
    }

    private function _unsetSession(&$session,$var){
        $session[$var] = null;
        unset($session[$var]);
    }

}
