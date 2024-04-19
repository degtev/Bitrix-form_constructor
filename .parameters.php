<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

$site = ($_REQUEST["site"] <> ""? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ""? $_REQUEST["src_site"] : false));

if($site !== false)
    $arFilter["LID"] = $site;


$sDirName = dirname(__FILE__);
$componentFolder = substr($sDirName, strlen($_SERVER["DOCUMENT_ROOT"]));

$arComponentParameters = array(
    "PARAMETERS" => array(
        "FORM_CLASS_NAME" => array(
            "NAME" => Loc::getMessage("PARAM_PROP_FORM_CLASSNAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "class-name",
            "PARENT" => "BASE",
        ),
        "FORM_NAME" => array(
            "NAME" => Loc::getMessage("RAPAM_FORM_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => Loc::getMessage("RAPAM_FORM_NAME_DEFAULT"),
            "PARENT" => "BASE",
        ),
        "FORM_BTN_NAME" => array(
            "NAME" => Loc::getMessage("RAPAM_FORM_BTN_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => Loc::getMessage("RAPAM_FORM_BTN_NAME_DEFAULT"),
            "PARENT" => "BASE",
        ),
        "FORM_SUCCESS_MESSAGE" => array(
            "NAME" => Loc::getMessage("RAPAM_FORM_SUCCESS_MESSAGE"),
            "TYPE" => "STRING",
            "DEFAULT" => Loc::getMessage("RAPAM_FORM_SUCCESS_MESSAGE_DEFAULT"),
            "PARENT" => "BASE",
        ),
        "MESSAGE_EVENT_TYPE" => array(
            "NAME" => Loc::getMessage("PARAM_PROP_EVENT_TYPE_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => "NEW_EVENT",
            "PARENT" => "BASE",
            "REFRESH" => "N",
        ),
        "FIELDS" => array(
            "NAME" => Loc::getMessage("PARAM_PROP_FORM_FIELDS"),
            "TYPE" => "CUSTOM",
            "JS_FILE" => $componentFolder . "/fields/fields.js",
            "JS_EVENT" => "FieldEdit",
            "JS_DATA" => Loc::getMessage("PARAM_PROP_FORM_FIELDS_SET") . "||" . $componentFolder . "/fields/fields.php",
            "DEFAULT" => null,
            "PARENT" => "BASE",
            "REFRESH" => "Y"
        ),
        "FORM_USE_IN_COMPONENT" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("FORM_USE_IN_COMPONENT"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
            "REFRESH" => "Y"
        ],
        "COMPONENT_TEMPLATE_FOLDER" => array(
            "NAME" => Loc::getMessage("COMPONENT_TEMPLATE_FOLDER"),
            "TYPE" => "STRING",
            "DEFAULT" => "",
            "PARENT" => "BASE",
            "HIDDEN" => $arCurrentValues["FORM_USE_IN_COMPONENT"] == "Y" ? "N" : "Y",
        ),
//        "EVENT_MESSAGE_ID" => Array(
//            "NAME" => Loc::getMessage("FORM_EMAIL_TEMPLATES"),
//            "TYPE"=>"LIST",
//            "VALUES" => getMessageEvents($site),
//            "DEFAULT"=>"",
//            "MULTIPLE"=>"Y",
//            "COLS"=>25,
//            "PARENT" => "BASE",
//            "HIDDEN" => "Y"
//        ),
    )
);

//if ($arCurrentValues["MESSAGE_EVENT_TYPE"]) {
//    $arComponentParameters["PARAMETERS"]["EVENT_MESSAGE_ID"]["VALUES"] = getMessageEvents($site, $arCurrentValues["MESSAGE_EVENT_TYPE"]);
//    $arComponentParameters["PARAMETERS"]["EVENT_MESSAGE_ID"]["HIDDEN"] = "N";
//}
//
//function getMessageEvents($site, $messageEventType = "") {
//    $arFilter = array("TYPE_ID" => "", "ACTIVE" => "Y");
//    if ($site !== false) {
//        $arFilter["LID"] = $site;
//    }
//    if ($messageEventType !== "") {
//        $arFilter["TYPE_ID"] = $messageEventType;
//    }
//
//    $arEvent = [];
//    $dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
//    while($arType = $dbType->GetNext())
//        $arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];
//    return $arEvent;
//}
?>