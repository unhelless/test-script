require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php"); 

$BLOCK_ID = 4;


$filePath = $_SERVER["DOCUMENT_ROOT"]."/test.csv"; 
$csvFile = new CCSVData('R', false);
$csvFile->LoadFile($filePath);
$csvFile->SetDelimiter(';'); 
$csvFile->SetFirstHeader(true);


CModule::IncludeModule("iblock"); 

$arrCsv = array();
$i = 0;
while ($arFields = $csvFile->Fetch()){
    $arr = array();
    $arr['name'] = $arFields[1];
    $arr['preview_text'] = $arFields[2];
    $arr['detail_text'] = $arFields[3];
    $arr['prop1'] = $arFields[4];
    $arr['prop2'] = $arFields[5];
    $arrCsv[$i] = $arr;
    $i++;
}

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

$elem = new CIBlockElement;

$getBlock = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

$delete = $update = $set = $add = array();

while($obj = $getBlock->GetNextElement()){
    $result = $arrBlock = array();
    $arFields = $obj->GetFields();
    $result['name'] = $arFields['NAME'];
    $result['preview_text'] = $arFields['PREVIEW_TEXT'];
    $result['detail_text'] = $arFields['DETAIL_TEXT'];
    $result['prop1'] = $arFields['PROPERTY_PROP1_VALUE'];
    $result['prop2'] = $arFields['PROPERTY_PROP2_VALUE'];
    $PRODUCT_ID[$i] = $arFields['ID'];
    foreach($arrCsv as $key => $item){
		#Если все элементы равны
		if($result == $item)  {
			echo"\n";
			unset($PRODUCT_ID[$i]);
			unset($arrCsv[$key]);}
		#Если поля (хотя бы одно поле) равны Set
		elseif(($result['name'] == $item['name']) || ($result['detail_text'] == $item['detail_text']) || ($result['preview_text'] == $item['preview_text']))
		{
			$set[$key] = array_diff($item,$result);
			CIBlockElement::SetPropertyValuesEx($PRODUCT_ID[$i], $BLOCK_ID, $set[$key]);
			unset($PRODUCT_ID[$i]);
			unset($arrCsv[$key]);}
		#Если свойства равны
		elseif(($result['prop1'] == $item['prop1']) || ($result['prop2'] == $item['prop2'])){
			$update[$key] = array_diff($item,$result);
			updateUp($update[$key],$PRODUCT_ID[$i],$BLOCK_ID);
			unset($PRODUCT_ID[$i]);
			unset($arrCsv[$key]);
		}

}
	if(!empty($PRODUCT_ID)){
		CIBlockElement::Delete($PRODUCT_ID[$i]);
		unset($PRODUCT_ID[$i]);
	}
	$i++;

}
if(!empty($set)){
	echo"set \n";
	print_r($set);
}
if(!empty($update)){
	echo"update \n";
	print_r($update);
}
if(!empty($PRODUCT_ID)){
	echo"delete \n";
	print_r($PRODUCT_ID);
}


if(!empty($arrCsv)){
	//добавить элемент в инфоблок;
	foreach($arrCsv as $i=>$item){
		print_r($item);
		$PROP = array();
		$PROP['prop1'] = $item['prop1'];
		$PROP['prop2'] = $item['prop2'];
		print_r($PROP);
		$arLoadProductArray = Array(
			"IBLOCK_ID"      => $BLOCK_ID,
			"ACTIVE"         => "Y",
			"NAME"           =>$item['name'],
			"DETAIL_TEXT"    =>$item['detail_text'],
			"PREVIEW_TEXT"   =>$item['preview_text'],
			"PROPERTY_VALUES"=>$PROP
			);
		print_r($arLoadProductArray);
		if($obj = $elem->Add($arLoadProductArray)){
			echo "Элемент с именем: ".$item['name']." успешно добавлен \n";			
		}
	}
}




//---------------Функции---------------//

// Функция на изменение/добавление элементов
function updateUp($array_diff,$prod_id, $block_id){


$el = new CIBlockElement;

$arLoadProductArray = Array(
"IBLOCK_ID"      => $BLOCK_ID,
"ACTIVE"         => "Y",
);

if(in_array($array_diff['name'],$array_diff)) 
	{$arLoadProductArray["NAME"] = $array_diff["name"];}
if(in_array($array_diff['preview_text'],$array_diff)) 
	{$arLoadProductArray["PREVIEW_TEXT"] = $array_diff["preview_text"];}
if(in_array($array_diff['detail_text'],$array_diff)) 
	{$arLoadProductArray["DETAIL_TEXT"] = $array_diff["detail_text"];}

if($res = $el->Update($prod_id, $arLoadProductArray)){
	echo"Я изменил поля \n";
	} else {
		echo"Я не смог :с \n";
	}
};

//добавление элементов в инфоблок
function addIBlock($file, $limit, $block_id, $step = 4){
	$el = new CIBlockElement;
	$i     = 0;
	$count = 0;
	$limit_array = $limit;
	$arrCsv = $PROP = array();
	while($i != $step){
		while($count < $limit_array){
			if($arFields = $file->Fetch()) 
			{	
				$arr = array(); 
				$arr['name'] = $arFields[1];
				$arr['preview_text'] = $arFields[2]; 
				$arr['detail_text'] = $arFields[3];
				$PROP['PROP1'] = $arFields[4];  
				$PROP['PROP2'] = $arFields[5];
				$arrCsv[$count] = $arr;

			};
			$count++;
		}
		//элемент добавления
			foreach($arrCsv as $k=>$v){
			$arLoadProductArray = Array(
				"IBLOCK_ID"      => $block_id,
				"ACTIVE"         => "Y",
				"NAME"           =>$arrCsv[$k]['name'],
				"DETAIL_TEXT"    =>$arrCsv[$k]['detail_text'],
				"PREVIEW_TEXT"   =>$arrCsv[$k]['preview_text'],
				"PROPERTY_VALUES"=>$PROP
			);
				if($obj = $el->Add($arLoadProductArray)){
				echo "Элемент с именем: ".$arrCsv[$k]['name']." успешно добавлен \n";			
				print_r($arrCsv[$k]+$PROP);
				unset($arrCsv[$k]);
				unset($PROP);
				}
				if(empty($arrCsv[$k])){
					$limit_array = $count + $limit;
					$i++;
				}
			}
		}
};


//очистка инфоблока
function clearIBlock($block_id){
	$el = CIBlockElement::GetList
	(
		array("ID"=>"ASC"),
		array
		(
			'IBLOCK_ID'=>$block_id,
			'SECTION_ID'=>0,
			'INCLUDE_SUBSECTIONS'=>'N'
		)
	);
	
	
	while($arr = $el->Fetch()){
		echo"ID: ".$arr['ID']." успешно удален \n";
		CIBlockElement::Delete($arr['ID']);
	}
}
