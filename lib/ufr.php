<?php
define("ONLINE", 0);
define("UFR1", 1);
define("UFR2", 2);
define("BARRIER", 3);
define("ACT1", 2);
define("ACT2", 1);
class Ufr
{
    private $serialNumber;
    private $cardId;
    private $reader;
    private $onlineSerialNumber;
    private $data;
    
	private $ufr0Response;
    private $ufr1Response;
    private $ufr2Response;
    private $ufr3Response;

    public function __construct()
    {

        if (isset($_POST["SN"]) && !empty($_POST["SN"]) && isset($_POST["UID"]) && !empty($_POST["UID"]) && isset($_POST["online"]) && !empty($_POST["online"])) {
            $this->serialNumber = $_POST["SN"];
            $this->cardId = $_POST["UID"];
            $this->reader = $_POST["online"];
            $this->onlineSerialNumber = $_POST["OSN"];
            $this->data = "0";
	    $this->ufr0Response = "0";
            $this->ufr1Response = "0";
            $this->ufr2Response = "0";
            $this->ufr3Response = "0";

        }
        else if(isset($_POST["OSN"]) && !empty($_POST["OSN"]) && isset($_POST["DATA"]) && !empty($_POST["DATA"]))
        {
            $this->serialNumber = "0";
            $this->cardId = "0";
            $this->reader = "0";
            $this->onlineSerialNumber = $_POST["OSN"];
            $this->data = $_POST["DATA"];
	    $this->ufr0Response = "0";
            $this->ufr1Response = "0";
            $this->ufr2Response = "0";
            $this->ufr3Response = "0";       
 
        }
        else {
            $this->serialNumber = "0";
            $this->cardId = "0";
            $this->reader = "0";
            $this->onlineSerialNumber = "0";
            $this->data = "0";
	    $this->ufr0Response = "0";
            $this->ufr1Response = "0";
            $this->ufr2Response = "0";
            $this->ufr3Response = "0";
  	
        }
    }

    protected function byteArray2Hex($byteArray) {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
      }

    protected function calculateChecksum($data, $len)
    {
        $checksum = 0;
        for ($x = 0; $x < $len; $x++) {
            $checksum ^= $data[$x];
        }    
        return ($checksum+7) & 0xFF;
    }

    protected function addResponse($readerNumber, $response)
    {
		if($readerNumber == 0)
         {
             if($this->ufr0Response == "0")
             {
                $this->ufr0Response = $this->byteArray2Hex($response);    
             }
             else
             {
                $this->ufr0Response .= " ";
                $this->ufr0Response .= $this->byteArray2Hex($response);
             }   
         }
         else if($readerNumber == 1)
         {
             if($this->ufr1Response == "0")
             {
                $this->ufr1Response = $this->byteArray2Hex($response);    
             }
             else
             {
                $this->ufr1Response .= " ";
                $this->ufr1Response .= $this->byteArray2Hex($response);
             }   
         }   
         elseif($readerNumber == 2)
         {
            if($this->ufr2Response == "0")
            {
               $this->ufr2Response = $this->byteArray2Hex($response);    
            }
            else
            {
               $this->ufr2Response .= " ";
               $this->ufr2Response .= $this->byteArray2Hex($response);
            }   
         }
         elseif($readerNumber == 3)
         {
            if($this->ufr3Response == "0")
            {
               $this->ufr3Response = $this->byteArray2Hex($response);    
            }
            else
            {
               $this->ufr3Response .= " ";
               $this->ufr3Response .= $this->byteArray2Hex($response);
            }   
         }

    }

    function getSerialNumber()
    {
        return $this->serialNumber;
    }

    function getCardId()
    {
        return $this->cardId;
    }

    function getReader()
    {
        return $this->reader;
    }
    
    function getData()
    {
        return $this->data;
    }

    function getInputs()
    {
	$json = json_decode($this->data, true);
        return array_values($json["Input"]);
    }
    
    function isCard()
    {
	if(strcmp($this->data, '0') == 0)
       {
	  return true;
       }
       else
       {
           return false;
       }
    }

    function sendResponse()
    {
  
        echo $this->ufr0Response . "\n" . $this->ufr1Response . "\n" . $this->ufr2Response . "\n" . $this->ufr3Response;
    }

    function readerUISignal($readerNumber, $light, $beep)
    {
        $data = array(0x55, 0x26, 0xAA, 0x00, 0xFF, 0xFF, 0xFF);
        $data[4] = $light;
        $data[5] = $beep;
        $data[6] = $this->calculateChecksum($data, 6);
        $this->addResponse($readerNumber, $data);
        return 1;
    }
    
    function setTemplate($readerNumber, $template)
    {
        $data = array(0x55, 0x26, 0xAA, 0x00, 0xFF, 0xFF, 0xFF);
        $data[4] = $template;
        $data[5] = 0;
        $data[6] = $this->calculateChecksum($data, 6);
        $this->addResponse($readerNumber, $data);
        return 1;
    }
    
    function lockOpen($readerNumber, $lockNumber, $duration)
    {
        $data = array(0x55, 0x60, 0xAA, 0x00, 0xFF, 0xFF, 0xFF);
        $data[4] = $duration & 0xFF;
        if($lockNumber == 1)
        {
            $data[5] = $duration >> 8;
        }
        else
        {
            $data[5] = ($duration >> 8) | 0x80;
        }        
        $data[6] = $this->calculateChecksum($data, 6);
        $this->addResponse($readerNumber, $data);
        return 1;
    }

    function ledRingRGB($readerNumber, $red, $green, $blue)
    {		
        $data = array(0x55, 0x72, 0xAA, 0x49, 0x48, 0x00, 0x93);
        for ($x = 0; $x < 72; $x+=3) {
            $data[$x+7] = $green;
            $data[$x+8] = $red;
            $data[$x+9] = $blue;
        } 
        $data[79] = 0x07;      
           
        $this->addResponse($readerNumber, $data);
        return 1;
    }

    function ledRingArray($readerNumber, $array)
    {
        $data = array(0x55, 0x72, 0xAA, 0x49, 0x48, 0x00, 0x93);
        for ($x = 0; $x < 72; $x++) {
            $data[$x+7] = $array[$x];
       
        } 
        $data[79] =  $this->calculateChecksum($array, 72);   
           
        $this->addResponse($readerNumber, $data);
        return 1;
    }
	
	function onlineRGB($readerNumber, $red, $green, $blue, $duration)
    {		
        $data = array(0x55, 0xF8, 0xAA, 0x07, 0x00, 0x00, 0x07);
		$data[4] = $duration & 0xFF;
		$data[5] = $duration >> 8;
		$data[6] =  $this->calculateChecksum($data, 6);   

        for ($x = 0; $x < 6; $x+=3) {
            $data[$x+7] = $red;
            $data[$x+8] = $green;
            $data[$x+9] = $blue;
        } 
        $data[13] = 0x07;      
           
        $this->addResponse($readerNumber, $data);
        return 1;
    }
	
	function onlineRGBDual($readerNumber, $red, $green, $blue, $red1, $green1, $blue1, $duration)
    {		
        $data = array(0x55, 0xF8, 0xAA, 0x07, 0x00, 0x00, 0x07);
		$data[4] = $duration & 0xFF;
		$data[5] = $duration >> 8;
		$data[6] =  $this->calculateChecksum($data, 6);   

        $data[7] = $red;
        $data[8] = $green;
        $data[9] = $blue;
		
		$data[10] = $red1;
        $data[11] = $green1;
        $data[12] = $blue1;
        
        $data[13] = 0x07;      
           
        $this->addResponse($readerNumber, $data);
        return 1;
    }

}
