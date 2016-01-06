<?php
namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 *
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Sns extends Engine
{
	/**
     * returns the name of the bank
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'SNS';
    }

        /**
     * Overloaded: Rabo has different way of storing account info
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = [];
        // SEPA MT940 Structured
        if (preg_match('/:86:(.*?) /im', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            return $this->sanitizeAccount($results[1]);
        }

       /* if (preg_match('/^:61:.{26}(.{16})/im', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])) {
            return $this->sanitizeAccount($results[1]);
        }*/

        return '';
    }

    /**
     * Overloaded: Rabo has different way of storing account name
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = [];
        // SEPA MT940 Structured
         if (preg_match('/:86:(.*?) (.*?)$/im', $this->getCurrentTransactionData(), $results)
                && !empty($results[2])
        ) {
            return $this->sanitizeAccountName($results[2]);
        }

        return '';
    }



     /**
     * Overloaded: Rabo encapsulates the description with /REMI/ for SEPA
     * @inheritdoc
     */
    protected function sanitizeDescription($string)
    {

    //	echo "string 1: ".$string;
    	$description = stripFirstLine($string);
    	 //   	echo "description 1: ".$description;

        $description = parent::sanitizeDescription($description);
       //     	echo "description 2: ".$description;

        return $description;
    }


}

function stripFirstLine($text){        
  			return substr( $text, strpos($text, "\n")+1 );
  		}

