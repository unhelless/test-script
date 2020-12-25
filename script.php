//запуск делал через php-консоль
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php"); 

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
		"IBLOCK_ID"=>13, 
		"ACTIVE_DATE"=>"Y", 
		"ACTIVE"=>"Y"); 

$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$ress= array();
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
  $i++;
}


$duff = array_diff_assoc($ress,$array_csv);
if(empty($duff))
	$duff = array_diff_assoc($array_csv,$ress);


print_r($duff);
//если нет расхождений - вывести "Нет изменений.", иначе очистить инфоблок и добавить данные с файла

if (!empty($duff)){
  echo "<br>Файл изменен. Переписываю инфоблок <br>";

$result = CIBlockElement::GetList
(
    array("ID"=>"ASC"),
    array
    (
        'IBLOCK_ID'=>13,
        'SECTION_ID'=>0,
        'INCLUDE_SUBSECTIONS'=>'N'
    )
);

while($element = $result->Fetch())
	CIBlockElement::Delete($element['ID']);

foreach($array_csv as $key => $val)
{
	$arFields = array();
	$arFields = $array_csv[$key];

  	$arLoadProductArray = Array(
      "IBLOCK_ID"         => 13, 
      "PROPERTY_VALUES"   => $arFields, 
      "NAME"              => $arFields['id'], 
      "ACTIVE"            => "Y", 
    );

    if($PRODUCT_ID = $el->Add($arLoadProductArray)) 
    {
        echo "ID: ".$arFields['id']." добавлен<br>";
    } else 
		echo "Error";

}
}
else echo "Изменений нет";