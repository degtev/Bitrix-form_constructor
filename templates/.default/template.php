<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
$this->addExternalJS($componentPath."/assets/js/imask.js");
$this->addExternalCss($componentPath."/assets/css/style.css");
CJSCore::Init("ajax");
?>
<form action="<?= POST_FORM_ACTION_URI ?>" method="POST" enctype="multipart/form-data" class="custom-js-form <?=$arParams["FORM_CLASS_NAME"];?>" >
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="componentId" value="<?=$arResult["COMPONENT_ID"]?>">
    <input type="hidden" name="scriptCast" value="<?=base64_encode($arResult["SCRIPT_PATH"])?>">
    <input type="hidden" name="mail_tpl_id" value="<?=$arResult["MESSAGE_ID"];?>">
    <input type="hidden" name="FORM_NAME" value="<?=$arParams["MESSAGE_ID"];?>">

    <?foreach($arResult["FIELDS_UNSERIALIZE"]["ITEMS"] as $item):?>
        <?
        switch ($item["type"]) {
            case "string":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="text" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" placeholder="<?=$item["placeholder"];?>" value="" >
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "hidden":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="hidden" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" value="<?=$item["listOpt"];?>">
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "num":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="number" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" placeholder="<?=$item["placeholder"];?>" value="">
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "email":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="email" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" placeholder="<?=$item["placeholder"];?>" value="">
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "phone":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="tel" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" placeholder="<?=$item["placeholder"];?>" value="">
                    <span class="error-msg"></span>
                </div>
                <?if($item["listOpt"]):?>
                <script type="text/javascript">
                    const maskOptions_<?=$item["id"];?> = {mask: '<?=$item["listOpt"];?>', lazy: false, placeholderChar: '_'};
                    const mask = IMask(document.getElementById('<?=$item["id"];?>'), maskOptions_<?=$item["id"];?>);
                </script>
                <?endif;?>

                <?break;
            case "file":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="file" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" value="">
                    <span class="error-msg"></span>
                    <?if($item["listOpt"]):?>
                    <input type="hidden" name="FORMAT_<?=$item["code"];?>" value="<?=$item["listOpt"];?>">
                    <?endif;?>
                </div>

                <?break;
            case "select":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <select id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>">
                        <option><?=GetMessage("DEFAULT_SELECT_OPTION");?></option>
                        <?$values = explode(";", $item["listOpt"]);?>
                        <?foreach($values as $value):$value = htmlspecialchars($value);?>
                            <option value="<?=$value;?>"><?=$value;?></option>
                        <?endforeach;?>
                    </select>
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "radio":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                <?$values = explode(";", $item["listOpt"]);?>
                <?foreach($values as $key => $value):?>
                        <input type="radio" id="<?=$item["id"]."_".$key;?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" value="<?=$value;?>">
                        <label for="<?=$item["id"]."_".$key;?>"><?=$value;?></label>
                <?endforeach;?>
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "checkbox":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <input type="checkbox" id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" value="<?=$item["name"];?>">
                    <label for="<?=$item["id"];?>"><?=$item["name"];?></label>
                    <span class="error-msg"></span>
                </div>

                <?break;
            case "textarea":?>

                <div class="field-area" id="field-area-<?=$item["id"];?>">
                    <textarea id="<?=$item["id"];?>" class="<?=$item["class"];?>" name="<?=$item["code"];?>" placeholder="<?=$item["placeholder"];?>" value=""></textarea>
                    <span class="error-msg"></span>
                </div>

                <?break;
        }
        ?>
    <?endforeach?>


    <button type="submit" class="submit"><?=$arParams["FORM_BTN_NAME"];?></button>
    <div class="success-message">
        <?=$arParams["FORM_SUCCESS_MESSAGE"];?>
    </div>
</form>