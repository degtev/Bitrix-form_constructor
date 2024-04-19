<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;

class AllForm extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {

        try {
            $this->onIncludeComponentLang();
            $this->addMailEvents();
            $this->arResult["COMPONENT_ID"] = $this->componentId();
            $this->arResult["COMPONENT_PATH"] = $this->getComponentPlace();
            $this->arResult["THIS_FORM_INTO_ANOTHER_COMPONENT"] = $this->formUseIntoAnotherComponent();
            if($this->arResult["THIS_FORM_INTO_ANOTHER_COMPONENT"] == "Y"){
                if(file_exists($_SERVER["DOCUMENT_ROOT"].$this->arResult["COMPONENT_PATH"])){
                    $this->arResult["COMPONENT_PATH_ERROR"] = false;
                    $this->arResult["SCRIPT_PATH"] = $this->arResult["COMPONENT_PATH"];
                    $this->includeComponentTemplate();
                }else{
                    $this->arResult["COMPONENT_PATH_ERROR"] = true;
                    ShowError(Loc::getMessage("ERROR_FILE_PATH"));
                }
            }else{
                $this->arResult["SCRIPT_PATH"] = $_SERVER["SCRIPT_NAME"];
                $this->includeComponentTemplate();
            }

        } catch (LoaderException $e) {
            ShowError($e->getMessage());
        }

    }

    protected function componentId()
    {
        $entryId = "customform";
        $m = null;
        if (preg_match(
            "/^bx_(.*)_" . $entryId . "$/",
            $this->getEditAreaId($entryId),
            $m
        )) {
            return $m[1];
        }
    }

    protected function getComponentPlace()
    {
        return $this->arParams["COMPONENT_TEMPLATE_FOLDER"];
    }
    protected function formUseIntoAnotherComponent()
    {
        return $this->arParams["FORM_USE_IN_COMPONENT"];
    }

    protected static function pageComponents($scriptPath)
    {
        if (is_file($absPath = $_SERVER["DOCUMENT_ROOT"] . $scriptPath)) {
            $arCounter = [];
            $fileContent = file_get_contents($absPath);
            $arComponents = \PHPParser::ParseScript($fileContent);
            foreach ($arComponents as &$r) {
                $arCounter[$r["DATA"]["COMPONENT_NAME"]]++;
                $r["ID"] = abs(crc32($r["DATA"]["COMPONENT_NAME"] . "_" . $arCounter[$r["DATA"]["COMPONENT_NAME"]]));
            }
            return $arComponents;
        }
        throw new \Exception("File [" . $scriptPath . "] not found");
    }

    protected static function componentData($componentId, $scriptPath)
    {
        if ($componentId && ($arComponents = self::pageComponents($scriptPath))) {
            foreach ($arComponents as $arData)
                if ($componentId == $arData["ID"])
                    return $arData;
        }
    }


    public function configureActions()
    {
        return [
            "ajax" => [
                "prefilters" => [],
                "postfilters" => []
            ]
        ];
    }

    public function ajaxAction($componentId, $scriptCast)
    {
        if (($scriptPath = base64_decode($scriptCast)) && ($arComponentData = self::componentData($componentId, $scriptPath))) {
            $componentParams = $arComponentData["DATA"]["PARAMS"];
        }

        $status = true;
        $data = unserialize($componentParams["FIELDS"]);
        $dataFields = [];
        $eventMessageType = $componentParams["MESSAGE_EVENT_TYPE"];
        $eventMessageId = $_POST["MAIL_TPL_ID"];
        foreach ($data["ITEMS"] as $arItem) {

            if ($arItem["req"] == 1) {

                switch ($arItem["type"]) {
                    case "email":
                        $checkField = check_email($_POST[$arItem["code"]]) ? true : false;
                        if (!$checkField) {
                            $field = [
                                "CODE" => $arItem["code"],
                                "ID" => $arItem["id"],
                                "ERROR_TEXT" => $arItem["error"],
                            ];
                            $dataFields[] = $field;
                        }
                        break;
                    case "phone":
                        if ($_POST[$arItem["code"]] == "" || strpos($_POST[$arItem["code"]], "_") !== false) {
                            $field = [
                                "CODE" => $arItem["code"],
                                "ID" => $arItem["id"],
                                "ERROR_TEXT" => $arItem["error"],
                            ];
                            $dataFields[] = $field;
                        }
                        break;
                    default:
                        if ($_POST[$arItem["code"]] == "") {
                            $field = [
                                "CODE" => $arItem["code"],
                                "ID" => $arItem["id"],
                                "ERROR_TEXT" => $arItem["error"],
                            ];
                            $dataFields[] = $field;
                        }
                }

            }

        }

        if ($_FILES) {
            $filesArray = [];
            foreach ($_FILES as $key => $files) {
                $element = [
                    "id" => $key,
                    "error" => $files["error"],
                    "full_path" => $files["full_path"],
                    "name" => $files["name"],
                    "size" => $files["size"],
                    "tmp_name" => $files["tmp_name"],
                    "type" => $files["type"]
                ];
                if ($_POST["FORMAT_" . $key]) {
                    $allowedFormat = $_POST["FORMAT_" . $key];
                    $element["allowed_format"] = $allowedFormat;
                }
                $filesArray[] = $element;
            }

            $checkStatusFile = [];
            $arrFiles = [];
            foreach ($filesArray as $file) {
                if ($file["size"] > 0) {

                    $allowedFormats = explode(";", $file["allowed_format"]);
                    $targetDir = $_SERVER["DOCUMENT_ROOT"] . "/custom_form_maildir/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $targetFile = $targetDir . basename($file["name"]);
                    $fileExtension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

                    if ($allowedFormats) {
                        if (in_array($fileExtension, $allowedFormats) && $file["error"] == 0) {
                            move_uploaded_file($file["tmp_name"], $targetFile);
                            $fileArray = CFile::MakeFileArray($targetFile);
                        } elseif ($file["error"] == 1 || $file["error"] == 2 || $file["error"] == 3 || $file["error"] == 4) {
                            $resultResponce["text"] = Loc::getMessage("ERROR_FILE_UPLOAD");
                            $status = false;
                        } else {
                            $resultResponce["code"] = $file["id"];
                            $resultResponce["text"] = Loc::getMessage("ERROR_FILE_FORMAT") . implode(", ", $allowedFormats);
                            $status = false;
                        }
                        $checkStatusFile[] = $resultResponce;
                    } else {
                        if ($file["error"] == 0) {
                            move_uploaded_file($file["tmp_name"], $targetFile);
                            $fileArray = CFile::MakeFileArray($targetFile);
                        } elseif ($file["error"] == 1 || $file["error"] == 2 || $file["error"] == 3 || $file["error"] == 4) {
                            $resultResponce["code"] = $file["id"];
                            $resultResponce["text"] = Loc::getMessage("ERROR_FILE_UPLOAD");
                            $status = false;
                        }
                        $checkStatusFile[] = $resultResponce;
                    }

                    $arrFiles[] = $fileArray["tmp_name"];
                }
            }

        }


        if (count($dataFields) == 0) {
            $dataFields = false;
        } else {
            $status = false;
        }

        if ($status == true) {
            $_POST["serializefields"] = "";
            \Bitrix\Main\Mail\Event::send([
                "EVENT_NAME" => $eventMessageType,
                "MESSAGE_ID" => $eventMessageId,
                "LID" => "s1",
                "C_FIELDS" => $_POST,
                "FILE" => $arrFiles,
            ]);
        }

        foreach ($arrFiles as $file) {
            unlink($file);
        }
        return [
            "status" => $status,
            "error_fields" => "",
            "required_fields" => $dataFields,
            "check_file_status" => $checkStatusFile,
        ];
    }

    protected function addMailEvents()
    {
        $this->addMailEventType();
        $this->addMailEventMessage();
    }


    protected function addMailEventType()
    {

        $this->arResult["FIELDS_UNSERIALIZE"] = unserialize($this->arParams["~FIELDS"]);

        $arEventType = \CEventType::GetByID(
            $this->arParams["MESSAGE_EVENT_TYPE"],
            SITE_ID
        )->Fetch();
        if (!is_array($arEventType)) {
            $sEventDesc = "";
            foreach ($this->arResult["FIELDS_UNSERIALIZE"]["ITEMS"] as $arField) {
                if($arField["type"] == "file")continue;
                $sEventDesc .= "#" . $arField["code"] . "# - " . $arField["name"] . "\n";
            }

            $oEventType = new \CEventType;
            $arEventFields = [
                "LID" => SITE_ID,
                "EVENT_NAME" => $this->arParams["MESSAGE_EVENT_TYPE"],
                "NAME" => Loc::getMessage(
                    "FORM_C_EVENT_TYPE_NAME",
                    ["#FORM_NAME#" => $this->arParams["FORM_NAME"]]
                ),
                "DESCRIPTION" => $sEventDesc
            ];
            $arEventFields["LID"] = ($this->arResult["SITE"]["LANGUAGE_ID"] == "ru" ? "en" : "ru");
            $oEventType->Add($arEventFields);

            $arType = array(
                "DESCRIPTION" => $sEventDesc,
            );
            $oEventType->Update(array("EVENT_NAME" => $this->arParams["MESSAGE_EVENT_TYPE"]), $arType);

        }
    }

    protected function addMailEventMessage()
    {
        $arMessAdmin = \CEventMessage::GetList(
            $by = "id",
            $order = "desc",
            [
                "TYPE_ID" => $this->arParams["MESSAGE_EVENT_TYPE"]
            ]
        )->Fetch();

        if (!is_array($arMessAdmin)) {
            $sFormTitle = Loc::getMessage("FORM_C_EVENT_MESSAGE_SUBJECT");
            $sMessageBody = "";
            foreach ($this->arResult["FIELDS_UNSERIALIZE"]["ITEMS"] as $arField) {
                $sMessageBody .= $arField["name"] . ": " . "#" . $arField["code"] . "#</br>\n";
            }

            $sMessage = $sMessageBody;

            $oEventMessage = new \CEventMessage;
            $arMessAdmin = [];

            $arMessAdmin["ID"] = $oEventMessage->Add([
                "ACTIVE" => "Y",
                "EVENT_NAME" => $this->arParams["MESSAGE_EVENT_TYPE"],
                "LID" => SITE_ID,
                "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                "EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
                "BCC" => "",
                "SUBJECT" => Loc::getMessage("FORM_C_EVENT_MESSAGE_SUBJECT"),
                "BODY_TYPE" => "html",
                "MESSAGE" => $sMessage,
            ]);
        }
        $this->arResult["MESSAGE_ID"] = $arMessAdmin["ID"];
        $this->arResult["EVENT_NAME"] = $this->arParams["MESSAGE_EVENT_TYPE"] ?: "";
    }


}