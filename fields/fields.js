function FieldEdit(arParams) {
    closeSettingsListEditor();
    window.jsSettingsListEditor = new JSettingsListEditor(arParams);
}

function JSettingsListEditor(arParams) {
    const { data, oCont, oInput, getElements } = arParams;

    const obButton = oCont.appendChild(BX.create('BUTTON', {
        html: data.split('||')[0]
    }));

    obButton.onclick = () => this.btnClick(arParams);

    this.saveData = () => this.__saveData(oInput);
}

JSettingsListEditor.prototype.btnClick = function (arParams) {
    const arElements = arParams.getElements();

    if (!arElements) return false;

    if (!window.jsPopup_list_editor) {
        const [buttonName, baseUrl] = arParams.data.split('||');
        const strUrl = `${baseUrl}?lang=${buttonName}`;
        const strUrlPost = 'LIST_DATA=' + BX.util.urlencode(arParams.oInput.value);

        window.jsPopup_list_editor = new BX.CDialog({
            'content_url': strUrl,
            'content_post': strUrlPost,
            'height': 500,
            'resizable': false,
            'width': 1200,
        });
    }

    window.jsPopup_list_editor.Show();
    window.jsPopup_list_editor.PARAMS.content_url = '';

    return false;
};


JSettingsListEditor.prototype.closeSettingsListEditor = function () {
    if (window.jsPopup_list_editor) {
        window.jsPopup_list_editor.Close();
    }
};


JSettingsListEditor.prototype.__saveData = function (oInput) {
    oInput.value = strData;
    if (oInput.onchange) {
        oInput.onchange();
    }
    this.closeSettingsListEditor();
};