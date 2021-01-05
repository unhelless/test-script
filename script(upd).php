//запуск делал через php-консоль
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php"); 

$BLOCK_ID = 13;

$filePath = $_SERVER["DOCUMENT_ROOT"]."/test.csv"; 
$csvFile = new CCSVData('R', false);
$csvFile->LoadFile($filePath);
$csvFile->SetDelimiter(';'); 

CModule::IncludeModule("iblock"); 

$el = new CIBlockElement; 
$array_csv = array();
$i=0;
while ($arFields = $csvFile->Fetch()) 
{
	$PROP = array();
    $PROP['id'] = $arFields[0];  
    $PROP['name'] = $arFields[1];
    $PROP['preview_text'] = $arFields[2]; 
    $PROP['detail_text'] = $arFields[3];
    $PROP['prop1'] = $arFields[4];  
    $PROP['prop2'] = $arFields[5];
	$array_csv[$i] = $PROP;
    $i++;
}
unset($array_csv[0]);
sort($array_csv);

$arSelect = Array(
		"ID", 
		"PROPERTY_id", 
		"PROPERTY_name",
		"PROPERTY_preview_text",
		"PROPERTY_detail_text",
		"PROPERTY_prop1",
		"PROPERTY_prop2"); 

$arFilter = Array(
		"IBLOCK_ID"=>$BLOCK_ID, 
		"ACTIVE_DATE"=>"Y", 
		"ACTIVE"=>"Y"); 

$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$ress= array();
$PRODUCT_ID = array();
$i=0;
while($ob = $res->GetNextElement())
{
  $result = array();
  $arFields = $ob->GetFields();
  $result['id'] = $arFields['PROPERTY_ID_VALUE'];
  $result['name'] = $arFields['PROPERTY_NAME_VALUE'];
  $result['preview_text'] = $arFields['PROPERTY_PREVIEW_TEXT_VALUE'];
  $result['detail_text'] = $arFields['PROPERTY_DETAIL_TEXT_VALUE'];
  $result['prop1'] = $arFields['PROPERTY_PROP1_VALUE'];
  $result['prop2'] = $arFields['PROPERTY_PROP2_VALUE'];
  $ress[$i] = $result;
  $PRODUCT_ID[$i] = $arFields['ID'];
  $i++;
}

//функция по сравеннию массивов
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
}
$diff = diff($array_csv, $ress);
print_r($diff);


foreach($diff as $k=>$v){
$res = CIblockElement::GetList([], ["IBLOCK_ID" => $IBLOCK_ID, "NAME" => $diff[$k]['id']], false, false, ["ID"]);
	if(count($array_csv)==count($ress)){
while ($ob = $res->GetNext()) {
    CIblockElement::SetPropertyValuesEx($PRODUCT_ID[$k], $IBLOCK_ID, $diff[$k]);
};}
	else{
  	$arLoadProductArray = Array(
      "IBLOCK_ID"         => $BLOCK_ID, 
      "PROPERTY_VALUES"   => $diff[$k], 
      "NAME"              => $diff[$k]['id'], 
      "ACTIVE"            => "Y", 
    );

    if($res_add = $el->Add($arLoadProductArray)) 
    {
        echo "ID: ".$diff[$k]['id']." добавлен<br>";
		unset($diff[$k]);
    } else 
		echo "Я честно пытался";
}
}

