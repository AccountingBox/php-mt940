<?php

namespace Kingsquare\Parser\Banking\Mt940\Engine;

use Kingsquare\Parser\Banking\Mt940\Engine;

/**
 * @package Kingsquare\Parser\Banking\Mt940\Engine
 * @author Kingsquare (source@kingsquare.nl)
 * @license http://opensource.org/licenses/MIT MIT
 */
class Abn extends Engine
{
    /**
     * returns the name of the bank
     * @return string
     */
    protected function parseStatementBank()
    {
        return 'ABN';
    }

    /**
     * Overloaded: ABN Amro shows the GIRO
     * includes fix for 'for GIRO 1234567 TEST 201009063689 CLIEOP 21-9' and translates that into 1234567
     * @inheritdoc
     */
    protected function parseTransactionAccount()
    {
        $results = parent::parseTransactionAccount();

        if (empty($results)) {
            $giroMatch = $ibanMatch = [];
         preg_match('/IBAN:(.*)/m', $this->getCurrentTransactionData(),$ibanMatch);
      //  print_r($ibanMatch);
            if (preg_match('/^:86:GIRO(.{9})/im', $this->getCurrentTransactionData(), $giroMatch)
                    && !empty($giroMatch[1])
            ) {
                $results = $giroMatch[1];
            }else
            if (preg_match('/IBAN:(.*)/m', $this->getCurrentTransactionData(), $ibanMatch)
                    && !empty($ibanMatch[1])
            ) {
               // echo $this->getCurrentTransactionData();
    
                $results = $ibanMatch[1];
               //  echo $ibanMatch[1];

            }else

            if (preg_match('!^:86:/.*/IBAN/(.*?)/!m', $this->getCurrentTransactionData(), $ibanMatch)
                    && !empty($ibanMatch[1])
            ) {
                $results = $ibanMatch[1];
               //  echo $ibanMatch[1];

            }else
            if (strpos($this->getCurrentTransactionData(), ':86:BEA') !== false){
            $results = "Betaalautomaat";
            }else if (strpos($this->getCurrentTransactionData(), ':86:GEA') !== false){
            $results = "Geldautomaat";
            }
        }

      //  echo $results."\n";

        return $this->sanitizeAccount($results);
    }

    /**
     * Overloaded: ABN Amro shows the GIRO and fixes newlines etc
     * @inheritdoc
     */
    protected function parseTransactionAccountName()
    {
        $results = parent::parseTransactionAccountName();
        if ($results !== '') {
            return $results;
        }

        $results = [];
   //     echo strpos($this->getCurrentTransactionData(), 'NAAM:')."\n";
        //echo preg_match('/NAAM:(.*?)(OMSCHRIJVING|MACHTIGING)/s', $this->getCurrentTransactionData(), $results),"\n";
             if (strpos($this->getCurrentTransactionData(), 'NAAM:') !== false && preg_match('/NAAM:(.*?)(OMSCHRIJVING|MACHTIGING)/s', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
           //     echo $results[1];
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

           if (preg_match('#/NAME/(.*?)/(REMI|ADDR)/#ms', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            $accountName = trim($results[1]);
            if (!empty($accountName)) {
                return $this->sanitizeAccountName($accountName);
            }
        }

        if (preg_match('/:86:(GIRO|BGC\.)\s+[\d]+ (.+)/', $this->getCurrentTransactionData(), $results)
                && !empty($results[2])
        ) {
            return $this->sanitizeAccountName($results[2]);
        }

        /*if (preg_match('/:86:.+\n(.*)\n/', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            return $this->sanitizeAccountName($results[1]);
        }
*/
        return '';
    }

    /**
     * Overloaded: ABNAMRO uses the :61: date-part of the field for two values:
     * Valuetimestamp (YYMMDD) and Entry date (book date) (MMDD)
     *
     * @return int
     */
    protected function parseTransactionEntryTimestamp()
    {
        $results = [];
        if (preg_match('/^:61:\d{6}(\d{4})[C|D]/', $this->getCurrentTransactionData(), $results)
                && !empty($results[1])
        ) {
            return $this->sanitizeTimestamp($results[1], 'md');
        }

        return 0;
    }

    /**
     * Overloaded: ABN encapsulates the description with /REMI/ for SEPA
     * @inheritdoc
     */
    protected function sanitizeDescription($string)
    {
        $description = parent::sanitizeDescription($string);
           if (strpos($description, 'OMSCHRIJVING:') !== false
                && preg_match('/.*OMSCHRIJVING:(.*?)(KENMERK|IBAN):/', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }
        if (strpos($description, '/REMI/') !== false
                && preg_match('#/REMI/(.*?)/(EREF|IBAN)/#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }
        if (strpos($description, '/EREF/') !== false
                && preg_match('#/EREF/(.*?)/(ORDP)/#s', $description, $results) && !empty($results[1])
        ) {
            return $results[1];
        }

        return $description;
    }
}
