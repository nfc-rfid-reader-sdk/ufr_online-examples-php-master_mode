<?php
header_remove("X-Powered-By"); //Removing this header is mandatory
header("Content-Type:");  //Adding this header is mandatory
include 'lib/ufr.php';

$ufr = new Ufr; //New object of class Ufr

/*
    $ufr->getReader(); //Get reader number. Only for multiple readers.
    $ufr->getCardId(); //Get CardID in HEX representation
    $ufr->getSerialNumber(); //Get Reader Serial number
*/

if($ufr->getSerialNumber() == "UN123456") //Get Reader serial number
{
    $ufr->readerUISignal(UFR1, 1, 1); //ReaderUISignal 1 1  sent to uFR Online 1
}
else
{
    $ufr->readerUISignal(UFR1, 2, 2); //ReaderUISignal 2 2  sent to uFR Online 1
}

$ufr->sendResponse(); //Sending HTTP response to uFR Online


?>