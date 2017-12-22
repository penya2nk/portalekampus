<?php
class PDPTService {  
    /**
     * @param string $symbol the symbol of the stock
     * @return float the stock price
     * @soapmethod
     */
    public function getPrice($symbol)
    {
        switch ($symbol) {
            case 'us' :
                $value=2.5;
            break;
            default :
                $value=0.00;
        }
        return $value;
    }
}