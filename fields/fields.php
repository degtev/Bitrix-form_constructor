<?php

use Bitrix\Main\Localization\Loc;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_js.php");

$sDirName = dirname(pathinfo(__FILE__, PATHINFO_DIRNAME));
$sPath = substr($sDirName, strlen($_SERVER['DOCUMENT_ROOT']));
Loc::loadMessages(__FILE__);

CJSCore::Init(['translit']);

$obJSPopup = new CJSPopup('',
    [
        'TITLE' => Loc::getMessage('FIELDS_TITLE'),
        'SUFFIX' => 'form',
        'ARGS' => ''
    ]
);

$arData = [];
if ($_REQUEST['LIST_DATA']) {
    CUtil::JSPostUnescape();
    if (CheckSerializedData($_REQUEST['LIST_DATA'])) {
        $arData = unserialize($_REQUEST['LIST_DATA']);
    }
}

if (!isset($arData['ITEMS']))
    $arData['ITEMS'] = [];

$iItemsCNT = 0;
$arTypes = [
    'string' => Loc::getMessage('FIELDS_TYPE_STRING'),
    'num' => Loc::getMessage('FIELDS_TYPE_NUM'),
    'email' => Loc::getMessage('FIELDS_TYPE_EMAIL'),
    'phone' => Loc::getMessage('FIELDS_TYPE_PHONE'),
    'file' => Loc::getMessage('FIELDS_TYPE_FILE'),
    'select' => Loc::getMessage('FIELDS_TYPE_SELECT'),
    'checkbox' => Loc::getMessage('FIELDS_TYPE_CHECKBOX'),
    'radio' => Loc::getMessage('FIELDS_TYPE_RADIO'),
    'textarea' => Loc::getMessage('FIELDS_TYPE_TEXTAREA'),
    'hidden' => Loc::getMessage('FIELDS_TYPE_HIDDEN'),
];

?>
    <script type="text/javascript">
        var jsFLEditor = {
            arData: null,
            obForm: null,

            __serialize: function (obj) {
                if (typeof (obj) == 'object') {
                    var str = '', cnt = 0;
                    for (var i in obj) {
                        ++cnt;
                        str += jsFLEditor.__serialize(i) + jsFLEditor.__serialize(obj[i]);
                    }
                    str = "a:" + cnt + ":{" + str + "}";
                    return str;
                } else if (typeof (obj) == 'boolean') {
                    return 'b:' + (obj ? 1 : 0) + ';';
                } else if (null == obj) {
                    return 'N;'
                } else if (Number(obj) == obj && obj != '' && obj != ' ') {
                    if (Math.floor(obj) == obj)
                        return 'i:' + obj + ';';
                    else
                        return 'd:' + obj + ';';
                } else if (typeof (obj) == 'string') {
                    obj = obj.replace(/\r\n/g, "\n");
                    obj = obj.replace(/\n/g, "###RN###");

                    var offset = 0;
                    if (window._global_BX_UTF) {
                        for (var q = 0, cnt = obj.length; q < cnt; q++) {
                            if (obj.charCodeAt(q) > 2047) offset += 2;
                            else if (obj.charCodeAt(q) > 127) offset++;
                        }
                    }

                    return 's:' + (obj.length + offset) + ':"' + obj + '";';
                }
            },

            __saveChanges: function () {
                jsFLEditor.arData = Object.create(null);
                jsFLEditor.arData["ITEMS"] = new Array();

                var arItems = Object.create(null);
                var iIndex = 0;

                var arTables = BX.findChildren(
                    BX('bx_settingslist_layout'),
                    {
                        'tag': 'table',
                        'class': 'settingslist-table'
                    },
                    true
                );

                if (!!arTables && (arTables.length > 0)) {
                    for (var iTCnt = 0, iTLen = arTables.length; iTCnt < iTLen; iTCnt++) {
                        var arRows = BX.findChildren(
                            arTables[iTCnt],
                            {
                                'tag': 'tr'
                            },
                            true
                        );

                        if (!!arRows && (arRows.length > 0)) {
                            for (var iRCnt = 0, iRLen = arRows.length; iRCnt < iRLen; iRCnt++) {
                                var arCells = BX.findChildren(
                                    arRows[iRCnt],
                                    {
                                        'tag': 'td'
                                    },
                                    true
                                );

                                if (!!arCells && (arCells.length == 11)) {
                                    var oNumerator = BX.findChild(arCells[0], {
                                        'tag': 'input',
                                        'attribute': {'type': 'hidden', 'name': 'ids[]'}
                                    });
                                    if (oNumerator) {
                                        var iNumber = oNumerator.value;

                                        var oNamesList = BX.findChild(arCells[1], {
                                            'tag': 'input',
                                            'attribute': {'name': 'name_' + iNumber}
                                        });
                                        var oCodesList = BX.findChild(arCells[2], {
                                            'tag': 'input',
                                            'attribute': {'name': 'code_' + iNumber}
                                        });
                                        var oIdList = BX.findChild(arCells[3], {
                                            'tag': 'input',
                                            'attribute': {'name': 'id_' + iNumber}
                                        });
                                        var oTypesList = BX.findChild(arCells[4], {
                                            'tag': 'select',
                                            'attribute': {'name': 'type_' + iNumber}
                                        });
                                        var oListOptList = BX.findChild(arCells[4], {
                                            'tag': 'input',
                                            'attribute': {'name': 'listopt_' + iNumber}
                                        }, true);
                                        var oFilterList = BX.findChild(arCells[4], {
                                            'tag': 'input',
                                            'attribute': {'name': 'filter_' + iNumber}
                                        }, true);
                                        var oSortsList = BX.findChild(arCells[5], {
                                            'tag': 'input',
                                            'attribute': {'name': 'sort_' + iNumber}
                                        });
                                        var oPlaceholderList = BX.findChild(arCells[6], {
                                            'tag': 'input',
                                            'attribute': {'name': 'placeholder_' + iNumber}
                                        });
                                        var oErrorsList = BX.findChild(arCells[7], {
                                            'tag': 'input',
                                            'attribute': {'name': 'error_' + iNumber}
                                        });
                                        var oClassList = BX.findChild(arCells[8], {
                                            'tag': 'input',
                                            'attribute': {'name': 'class_' + iNumber}
                                        });
                                        var oReqsList = BX.findChild(arCells[9], {
                                            'tag': 'input',
                                            'attribute': {'name': 'req_' + iNumber}
                                        });
                                        if (oListOptList != null) {
                                            oListOptListValue = oListOptList.value
                                        } else {
                                            oListOptListValue = ""
                                        }
                                        ;
                                        if (oFilterList != null) {
                                            oFilterListValue = oFilterList.value
                                        } else {
                                            oFilterListValue = ""
                                        }
                                        ;
                                        if (oNamesList && oCodesList && oTypesList && oSortsList) {
                                            arItems[iIndex] = {
                                                'name': oNamesList.value,
                                                'code': oCodesList.value,
                                                'id': oIdList.value,
                                                'type': oTypesList.value,
                                                'listOpt': oListOptListValue,
                                                'filter': oFilterListValue,
                                                'sort': oSortsList.value,
                                                'placeholder': oPlaceholderList.value,
                                                'req': oReqsList.checked,
                                                'error': oErrorsList.value,
                                                'class': oClassList.value,
                                            };
                                        }
                                    }
                                }
                                console.log(oNamesList);
                                iIndex++;
                            }
                        }
                    }
                }

                /* Check the empty */
                var tmpArray = new Array();
                tmpArray = arItems;
                for (var i = arItems.length - 1; i >= 0; i--) {
                    if (tmpArray[i].name.replace(/\s+/, '').length == 0) {
                        tmpArray.splice(i);
                    }
                }

                jsFLEditor.arData["ITEMS"] = tmpArray;


                window.jsSettingsListEditor.saveData(jsFLEditor.__serialize(jsFLEditor.arData));
                return false;
            }
        }
        var jsMess = {
            itemDel: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_ITEM_DELETE"))?>",
            reqTitle: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_REQ_TITLE"))?>",
            errorText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_ERROR_TEXT"))?>",
            listOptText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_LIST_OPTIONS_TEXT"))?>",
            filterText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_LIST_FILTER_TEXT"))?>",
            iblockText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_IBLOCK_TEXT"))?>",
            phoneMaskText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_PHONE_MASK_TEXT"))?>",
            dateTemplateText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_DATE_TEMPLATE_TEXT"))?>",
            hiddenText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_HIDDEN_TEXT"))?>",
            fileText: "<?=CUtil::JSEscape(Loc::getMessage("FIELDS_FILE_FORMAT_TEXT"))?>",
        };


        window._global_BX_UTF = <?=(defined('BX_UTF') && (BX_UTF == true)) ? 'true' : 'false'?>;

        var arTypes = new Array();
        <?php foreach ($arTypes as $id => $arType):?>
        arTypes.push({"id": "<?=$id?>", "name": "<?=$arType?>"});
        <?php endforeach;?>

        function checkType(type, ind) {
            BX('listOpt_' + ind).innerHTML = "";
            BX('filter_' + ind).innerHTML = "";
            if (type == "select") {
                var objParent = BX('listOpt_' + ind);
                var innerHTML = '<input type="text" value="" name="listopt_' + ind + '" style="width:100%" placeholder="' + jsMess.listOptText + '">';
                objParent.appendChild(BX.create('DIV', {html: innerHTML}));
            }
            if (type == "radio") {
                var objParent = BX('listOpt_' + ind);
                var innerHTML = '<input type="text" value="" name="listopt_' + ind + '" style="width:100%" placeholder="' + jsMess.listOptText + '">';
                objParent.appendChild(BX.create('DIV', {html: innerHTML}));
            }
            if (type == "phone") {
                var objParent = BX('listOpt_' + ind);
                var innerHTML = '<input type="text" value="+{7} (000) 000-00-00" name="listopt_' + ind + '" style="width:100%" placeholder="' + jsMess.phoneMaskText + '">';
                objParent.appendChild(BX.create('DIV', {html: innerHTML}));
            }
            if (type == "file") {
                var objParent = BX('listOpt_' + ind);
                var innerHTML = '<input type="text" value="jpg;png;doc" name="listopt_' + ind + '" style="width:100%" placeholder="' + jsMess.fileText + '">';
                objParent.appendChild(BX.create('DIV', {html: innerHTML}));
            }
            if (type == "hidden") {
                var objParent = BX('listOpt_' + ind);
                var innerHTML = '<input type="text" value="" name="listopt_' + ind + '" style="width:100%" placeholder="' + jsMess.hiddenText + '">';
                objParent.appendChild(BX.create('DIV', {html: innerHTML}));
            }
        }

        function translit(string, ind) {
            let target = BX('code_' + ind);
            let id = BX('id_' + ind);
            new Promise(resolve => {
                resolve(BX.translit(string.value, {
                    max_len: 50,
                    change_case: 'U',
                    replace_space: '_',
                    replace_other: '_',
                    delete_repeat_replace: true,
                    use_google: false
                }))
            }).then((translit) => {
                target.value = translit;
                id.value = translit.toLowerCase() + Date.now();
            })
        }

        function getSelectHTML(arItems, sInputName, event) {
            var sResult = '';
            sResult += '<select name="' + sInputName + '" style="width: 100%;"' + event + '>';
            var l = arItems.length;
            for (i = 1; i <= l; i++) {
                sResult += '<option value="' + arItems[i - 1].id + '">';
                sResult += arItems[i - 1].name + '&nbsp;[' + arItems[i - 1].id + ']';
                sResult += '</option>';
            }
            sResult += '</select>';
            return sResult;
        }

        function itemAdd() {
            var obCounter = BX('bx_item_cnt');
            var i = parseInt(obCounter.value);
            obCounter.value = ++i;

            var obPlacement = BX('bx_settingslist_layout').appendChild(BX.create('DIV', {
                props: {
                    className: 'bx-menu-placement',
                    id: 'bx_item_placement_' + i
                }
            }));

            var innerHTML = '<table class="bx-width100 internal settingslist-table"><tbody><tr>' +
                '	<td style="width: 3%;">' +
                '		<input type="hidden" name="ids[]" value="' + i + '" />' + i +
                '	</td>' +
                '	<td valign="top" style="width: 13%;">' +
                '<input type="text" name="name_' + i + '" value="" style="width:100%;" onkeyup="translit(this,\'' + i + '\')"> ' +
                '	</td>' +
                '	<td valign="top" style="width: 7%;">' +
                '<input type="text" name="code_' + i + '" value="" style="width:100%;" id="code_' + i + '">' +
                '	</td>' +
                '	<td valign="top" style="width: 7%;">' +
                '<input type="text" name="id_' + i + '" value="" style="width:100%;" id="id_' + i + '">' +
                '	</td>' +
                '	<td valign="top" style="width: 20%;">' +
                getSelectHTML(arTypes, 'type_' + i, ' onchange="checkType(this.options[this.selectedIndex].value,\'' + i + '\');"') +
                '		<div id="listOpt_' + i + '"></div>' +
                '		<div id="filter_' + i + '"></div>' +
                '	</td>' +
                '	<td valign="top" style="width: 7%;"><input type="text" name="sort_' + i + '" value="100" style="width:100%;"></td>' +
                '	<td valign="top" style="width: 15%;"><input type="text" name="placeholder_' + i + '" value="" style="width:100%;"></td>' +
                '	<td valign="top" style="width: 15%;"><input type="text" name="error_' + i + '" value="" style="width:100%;"></td>' +
                '	<td valign="top" style="width: 15%;"><input type="text" name="class_' + i + '" value="" style="width:100%;"></td>' +
                '	<td valign="top" style="width: 2%;">' +
                '<input type="checkbox" name="req_' + i + '" value="y" title="' + jsMess.reqTitle + '">' +
                '	</td>' +
                '	<td style="width: 3%;"><span onclick="itemDelete(' + i + ')" class="rowcontrol delete" title="' + jsMess.itemDel + '"></span></td>' +
                '</tr></tbody></table>';
            var obRow = obPlacement.appendChild(BX.create('DIV', {
                props: {
                    className: 'bx-edit-menu-item',
                    id: 'bx_item_row_' + i
                }, html: innerHTML
            }));
        }

        function itemDelete(i) {
            var obPlacement = BX('bx_item_row_' + i).parentNode;
            obPlacement.parentNode.removeChild(obPlacement);
        }
    </script>
    <form name="bx_popup_form_settings_list">
        <input type="hidden" name="save" value="Y"/>

        <?php $obJSPopup->ShowTitlebar(); ?>
        <?php $obJSPopup->StartDescription('bx-edit-menu'); ?>
        <p class="note"><?= Loc::getMessage('FIELDS_DESCR') ?></p>
        <?php $obJSPopup->StartContent(); ?>
        <table border="0" cellpadding="2" cellspacing="0" class="bx-width100 internal">
            <thead>
            <tr class="heading">
                <td style="width: 3%;">&nbsp;</td>
                <td style="width: 13%;"><b><?= Loc::getMessage('FIELDS_NAME') ?></b></td>
                <td style="width: 7%;"><b><?= Loc::getMessage('FIELDS_CODE') ?></b></td>
                <td style="width: 7%;"><b><?= Loc::getMessage('FIELDS_ID') ?></b></td>
                <td style="width: 17%;"><b><?= Loc::getMessage('FIELDS_TYPE') ?></b></td>
                <td style="width: 7%;"><b><?= Loc::getMessage('FIELDS_SORT') ?></b></td>
                <td style="width: 14%;"><b><?= Loc::getMessage('FIELDS_PLACEHOLDER_TEXT') ?></b></td>
                <td style="width: 15%;"><b><?= Loc::getMessage('FIELDS_ERROR_TEXT') ?></b></td>
                <td style="width: 15%;"><b><?= Loc::getMessage('FIELDS_CLASSNAME') ?></b></td>
                <td style="width: 2%;"></td>
                <td style="width: 3%;">&nbsp;</td>
            </tr>
            </thead>
        </table>
        <div id="bx_settingslist_layout" class="bx-menu-layout">
            <?php $iCount = 0; ?>
            <?php foreach ($arData['ITEMS'] as $arItem): ?>
                <?php
                $iItemsCNT++;
                $iCount++;
                ?>
                <div class="bx-menu-placement" id="bx_item_placement_<?= $iCount ?>">
                    <div class="bx-edit-menu-item" id="bx_item_row_<?= $iCount ?>">
                        <table id="bx_settingslist_layout_tbl_<?= $iCount ?>"
                               class="bx-width100 internal settingslist-table">
                            <tr>
                                <td style="width: 3%;">
                                    <input type="hidden" name="ids[]" value="<?= $iCount ?>"/>
                                    <?= $iCount ?>
                                </td>

                                <td valign="top" style="width: 13%;">
                                    <input type="text" name="name_<?= $iCount ?>" value="<?= htmlspecialchars($arItem['name']) ?>" style="width:100%;">
                                </td>
                                <td valign="top" style="width: 7%;">
                                    <input type="text" name="code_<?= $iCount ?>" value="<?= htmlspecialchars($arItem['code']) ?>" style="width:100%;" id="code_<?= $iCount ?>">
                                </td>
                                <td valign="top" style="width: 7%;">
                                    <input type="text" name="id_<?= $iCount ?>" value="<?= htmlspecialchars($arItem['id']) ?>" style="width:100%;" id="id_<?= $iCount ?>">
                                </td>
                                <td valign="top" style="width: 20%;">
                                    <select name="type_<?= $iCount ?>" style="width: 100%;"
                                            onchange="checkType(this.options[this.selectedIndex].value,'<?= $iCount ?>');">
                                        <?php foreach ($arTypes as $id => $arType): ?>
                                            <option value="<?= $id ?>"<?= (($id == $arItem['type']) ? " selected=\"selected\"" : "") ?>>
                                                <?= $arType ?>&nbsp;[<?= $id ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php
                                    if (!empty($arItem['listOpt'])) {
                                        $list = '<input type="text" value="' . htmlspecialchars($arItem['listOpt']) . '" name="listopt_' . $iCount . '" style="width:100%" placeholder="">';
                                    } else {
                                        $list = '';
                                    }
                                    ?>
                                    <div id="listOpt_<?= $iCount ?>"><?= $list ?></div>
                                    <?php
                                    if (!empty($arItem['filter'])) {
                                        $filter = '<input type="text" value="' . htmlspecialchars($arItem['filter']) . '" name="filter_' . $iCount . '" style="width:100%" placeholder="">';
                                    } else {
                                        $filter = '';
                                    }
                                    ?>
                                    <div id="filter_<?= $iCount ?>"><?= $filter ?></div>
                                </td>
                                <td valign="top" style="width: 7%;">
                                    <input type="text" name="sort_<?= $iCount ?>" value="<?= $arItem['sort'] ?>" style="width:100%;">
                                </td>
                                <td valign="top" style="width: 15%;">
                                    <input type="text" name="placeholder_<?= $iCount ?>" value="<?= $arItem['placeholder'] ?>" style="width:100%;">
                                </td>
                                <td valign="top" style="width: 15%;">
                                    <input type="text" name="error_<?= $iCount ?>" value="<?= $arItem['error'] ?>" style="width:100%;">
                                </td>
                                <td valign="top" style="width: 15%;">
                                    <input type="text" name="class_<?= $iCount ?>" value="<?= $arItem['class'] ?>" style="width:100%;">
                                </td>
                                <td valign="top" style="width: 2%;">
                                    <input type="checkbox" name="req_<?= $iCount ?>" value="y" <?php if ($arItem['req']): ?>checked<?php endif; ?>title='<?= Loc::getMessage('FIELDS_REQ_TITLE') ?>'>
                                </td>
                                <td style="width: 3%;">
                                    <span onclick="itemDelete(<?= $iCount ?>)" class="rowcontrol delete" title="<?= Loc::getMessage('FIELDS_ITEM_DELETE') ?>"></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <br>
        <input type="button" onClick="itemAdd();" value="<?= Loc::getMessage('FIELDS_ITEM_ADD') ?>"/>
        <input type="hidden" id="bx_item_cnt" value="<?= $iItemsCNT ?>"/>
        <?php $obJSPopup->StartButtons(); ?>
        <input type="submit" value="<?= Loc::getMessage('FIELDS_SUBMIT') ?>"
               onclick="return jsFLEditor.__saveChanges();" class="adm-btn-save"/>
        <?php $obJSPopup->ShowStandardButtons(['cancel']); ?>
        <?php $obJSPopup->EndButtons(); ?>
    </form>
<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin_js.php');
