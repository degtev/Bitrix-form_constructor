function FieldEdit(arParams)
{
    if (null != window.jsSettingsListEditor) {
        try {
            window.jsSettingsListEditor.Close();
        } catch (e) {}

        window.jsSettingsListEditor = null;
    }

    window.jsSettingsListEditor = new JSettingsListEditor(arParams);
}

function JSettingsListEditor(arParams) {
    this.arParams = arParams;
    this.jsOptions = this.arParams.data.split('||');

    var obButton = this.arParams.oCont.appendChild(BX.create('BUTTON', {
        html: this.jsOptions[0]
    }));

    obButton.onclick = BX.delegate(this.btnClick, this);

    this.saveData = BX.delegate(this.__saveData, this);
}

JSettingsListEditor.prototype.btnClick = function () {
    this.arElements = this.arParams.getElements();

    if (!this.arElements)
        return false;

    if (null == window.jsPopup_list_editor) {
        var strUrl = this.jsOptions[1]
            + '?lang=' + this.jsOptions[0];

        var strUrlPost = 'LIST_DATA=' + BX.util.urlencode(this.arParams.oInput.value);

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
}

JSettingsListEditor.prototype.Close = function(e) {
    if (false !== e)
        BX.util.PreventDefault(e);

    if (null != window.jsPopup_list_editor) {
        window.jsPopup_list_editor.Close();
    }
}

JSettingsListEditor.prototype.__saveData = function(strData) {
    this.arParams.oInput.value = strData;
    if (null != this.arParams.oInput.onchange)
        this.arParams.oInput.onchange();

    this.Close(false);
}