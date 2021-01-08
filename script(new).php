require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php"); 

$BLOCK_ID    = 15;
$STEP        = 2; //количество шагов
$count_csv   = 20; //количество строк в csv файле
$LIMIT       = intdiv($count_csv,$STEP); 


$filePath = $_SERVER["DOCUMENT_ROOT"]."/test.csv"; 
$csvFile = new CCSVData('R', false);
$csvFile->LoadFile($filePath);
$csvFile->SetDelimiter(';'); 
$csvFile->SetFirstHeader(true);


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


//пошаговое сравнение
$i = 0;
$count = 0;
$limit = $LIMIT;

while($i!=$STEP){
	while($count < $limit){
		if($arFields = $csvFile->Fetch()) 
		{	
			$PROP = array(); 
			$PROP['name'] = $arFields[1];
			$PROP['preview_text'] = $arFields[2]; 
			$PROP['detail_text'] = $arFields[3];
			$PROP['prop1'] = $arFields[4];  
			$PROP['prop2'] = $arFields[5];
			$array_csv[$count] = $PROP;
	
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
			$ress[$count] = $result;
			$PRODUCT_ID[$count] = $arFields['ID'];
		}
		$count++;
	}
	if($count==$limit){

		$res_diff = diff($array_csv,$ress);
		if(!empty($res_diff))
			updateUp($res_diff, $PRODUCT_ID, $BLOCK_ID);

		$array_csv = array();
		$ress = array();
		$limit = $count + $LIMIT;
		$i++;
	}
};


//---------------Функции---------------//

// Функция на изменение/добавление элементов
function updateUp($array_diff,$prod_id, $block_id){


	$el = new CIBlockElement;

	$arLoadProductArray = Array(
	  "IBLOCK_ID"      => $block_id,
	  "ACTIVE"         => "Y",	
	);



	foreach($array_diff as $k=>$v){
		$PROP = array();
		if(in_array($array_diff[$k]['name'],$array_diff[$k])) $arLoadProductArray["NAME"] = $array_diff[$k]["name"];
		if(in_array($array_diff[$k]['preview_text'],$array_diff[$k])) $arLoadProductArray["PREVIEW_TEXT"] = $array_diff[$k]["preview_text"];
		if(in_array($array_diff[$k]['detail_text'],$array_diff[$k])) $arLoadProductArray["DETAIL_TEXT"] = $array_diff[$k]["detail_text"];
		if(in_array($array_diff[$k]['prop1'],$array_diff[$k]) || in_array($array_diff[$k]['prop2'],$array_diff[$k])) {
			if(in_array($array_diff[$k]['prop1'],$array_diff[$k]))
			$PROP['prop1'] = $array_diff[$k]['prop1'];
			if(in_array($array_diff[$k]['prop2'],$array_diff[$k]))
			$PROP['prop2'] = $array_diff[$k]['prop2'];
			print_r($PROP);
			}
			CIBlockElement::SetPropertyValuesEx($prod_id[$k], $block_id, $PROP);

		if($res = $el->Update($prod_id[$k], $arLoadProductArray)){
		echo"Я изменил поля";
		} else {
		$arLoadProductArray["PROPERTY_VALUES"] = $PROP;
		if($res = $el->Add($arLoadProductArray))echo"я добавил элeмент";}

	};
};

// Разность двух массивов
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
