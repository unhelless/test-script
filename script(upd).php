
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php"); 

$BLOCK_ID = 15;
$LIMIT    = 6;
$COUNT    = 0;

$filePath = $_SERVER["DOCUMENT_ROOT"]."/test.csv"; 
$csvFile = new CCSVData('R', false);
$csvFile->LoadFile($filePath);
$csvFile->SetDelimiter(';'); 

CModule::IncludeModule("iblock"); 

$el = new CIBlockElement; 
$array_csv = array();

$arSelect = Array(
		"ID", 
		"DETAIL_TEXT", 
		"PROPERTY_PROP1",
		"PROPERTY_PROP2",
		"NAME",
		"PREVIEW_TEXT"); 

$arFilter = Array(
		"IBLOCK_ID"=>$BLOCK_ID, 
		"ACTIVE_DATE"=>"Y", 
		"ACTIVE"=>"Y"); 

$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$ress= array();
$PRODUCT_ID = array();

$res_diff = array();
#сделать подсчет максимальной длины массивов, пока i<277

for($i = 0; $i<=277; $i++){
	if($arFields = $csvFile->Fetch()) 
	{	
		$PROP = array(); 
		$PROP['name'] = $arFields[1];
		$PROP['preview_text'] = $arFields[2]; 
		$PROP['detail_text'] = $arFields[3];
		$PROP['prop1'] = $arFields[4];  
		$PROP['prop2'] = $arFields[5];
		$array_csv[$i] = $PROP;
	};
	if($ob = $res->GetNextElement())
	{	
		$result = array();
		$arFields = $ob->GetFields();
		$result['name'] = $arFields['NAME'];
		$result['preview_text'] = $arFields['PREVIEW_TEXT'];
		$result['detail_text'] = $arFields['DETAIL_TEXT'];
		$result['prop1'] = $arFields['PROPERTY_PROP1_VALUE'];
		$result['prop2'] = $arFields['PROPERTY_PROP2_VALUE'];
		$ress[$i] = $result;
		$PRODUCT_ID[$i] = $arFields['ID'];
	}

	$res_diff = diff($array_csv[$i],$ress[$i]);

	if(!empty($res_diff)){
		updateUp($res_diff, $PRODUCT_ID[$i], $BLOCK_ID);
	} else echo"";
};

function updateUp($array_diff,$prod_id, $block_id){
	$el = new CIBlockElement;
	$keys = array_keys($array_diff);
	$arLoadProductArray = Array(
	  "IBLOCK_ID"      => $block_id,
	  "ACTIVE"         => "Y",
	);
	$PROP = array();
	foreach($keys as $k=>$v){
	if($keys[$k] == 'name') $arLoadProductArray["NAME"] = $array_diff["name"];
	if($keys[$k] == 'preview_text') $arLoadProductArray["PREVIEW_TEXT"] = $array_diff["preview_text"];
	if($keys[$k] == 'detail_text') $arLoadProductArray["DETAIL_TEXT"] = $array_diff["detail_text"];
	if(($keys[$k] == 'prop1') || ($keys[$k] == 'prop2')) {
		if($keys[$k] == 'prop1')
		$PROP['prop1'] = $array_diff['prop1'];
		if($keys[$k] == 'prop2')
		$PROP['prop2'] = $array_diff['prop2'];
		CIBlockElement::SetPropertyValuesEx($prod_id, $block_id, $PROP);
	}
};
		if($res = $el->Update($prod_id, $arLoadProductArray)){
		echo"Я изменил";
		} else {
			$arLoadProductArray["PROPERTY_VALUES"] = $PROP;
			print_r($arLoadProductArray);
			$res_add = $el->Add($arLoadProductArray);
		}
};



function diff($arr1,$arr2){
$diff = array();
foreach($arr1 as $key => $value)
{
    if(is_array($value))
    {
        if(!isset($arr2[$key]))
        {
            $diff[$key] = $value;
        }
        elseif(!is_array($arr2[$key]))
        {
            $diff[$key] = $value;
        }
        else
        {
            $new_diff = array_diff($value, $arr2[$key]);
            if($new_diff != FALSE)
            {
                $diff[$key] = $new_diff;
            }
        }
    }
    elseif(!isset($arr2[$key]) || $arr2[$key] != $value)
    {
        $diff[$key] = $value;
    }
}
return $diff;
};

