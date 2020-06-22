export default class AJAX{
    constructor(){
        this.AjaxObject = new XMLHttpRequest();
    }

    POST(url, callbacks, dataobj = null, formname = null ){
        if (dataobj == null && formname == null) return null;

        this.AjaxObject.open("POST", url, true);

        let PostFormData = null;

        if (dataobj == null) {
            if (document.forms[formname] == null) return false;
            PostFormData = new FormData(document.forms[formname]);
        }
        else{
            PostFormData = JSON.stringify(dataobj);
            this.AjaxObject.setRequestHeader('Content-Type', 'application/json');
        }

        this.AjaxObject.onload  = callbacks.onload;
        this.AjaxObject.onerror = callbacks.onerror;
        this.AjaxObject.send(PostFormData);
        return this.AjaxObject.responseText;
    }
}