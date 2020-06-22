import AJAX from "/resources/scripts/ajax.js";

function displayError(errorcode){
    let ErrorTD = document.getElementById('ErrorTD');
    let ErrorCodeBar = document.getElementById('ErrorStatus');

    if (errorcode !== 200){
        ErrorTD.style.display = 'table-cell';
        ErrorCodeBar.innerText = errorcode.toString();
    }
    else
        ErrorTD.style.display = 'none';
}

window.refreshTable = function () {
    let AJAXM = new AJAX();
    let PostData = {
        'Action' : 'getkeyslist',
        'ToValue' : document.getElementById('Refresh_ToValue').value,
        'FromValue' : document.getElementById('Refresh_FromValue').value
    }

    AJAXM.POST("/resources/scripts/producthandle.php",{
        onload: function () {
            let JsonData = null;
            let ErrorCode = 0;

            console.log(this.responseText);

            try{
                JsonData = JSON.parse(this.responseText);
                let TableData = JsonData.Comment;
                let NewTable = document.createElement('tbody');

                if (JsonData.Code === 200) {
                    TableData.forEach(row => {
                        let DomRow = document.createElement('tr');
                        let DomCells = [
                            document.createElement('td'),
                            document.createElement('td'),
                            document.createElement('td'),
                            document.createElement('td'),
                        ]
                        let KeyText = document.createTextNode(row[3]);
                        DomCells[0].appendChild(KeyText);
                        let ControlInfoText = document.createTextNode(row[4]);
                        DomCells[1].appendChild(ControlInfoText);
                        DomCells[2].innerText = row[2] === '1' ? "True" : "False";
                        DomCells[2].className = row[2] === '1' ? "cellfree" : "cellnotfree";
                        let Button = document.createElement('input');
                        Button.type = 'button';
                        Button.value = 'Delete';
                        Button.setAttribute('onClick', 'deleteRecord(this)');
                        DomCells[3].appendChild(Button);
                        DomCells.forEach(Cell => {
                            DomRow.appendChild(Cell);
                        });
                        NewTable.appendChild(DomRow);
                    });

                    let DomTable = document.getElementById('tablecontent');
                    DomTable.innerHTML = NewTable.innerHTML;
                }
                displayError(JsonData.Code);
            }
            catch (e) {
                displayError("001");
            }
        },
        onerror: function () {ё
            console.log('Error!');
        }
    }, PostData);
}

window.deleteRecord = function(callerElement) {
    const ParentTr = callerElement.parentElement.parentElement;
    let Cells = ParentTr.getElementsByTagName('td');
    Cells = [...Cells].slice(0,Cells.length-1);
    let CellsValue = [];
    Cells.forEach(element => {
        CellsValue.push(element.innerText);
    });

    let AJAXM = new AJAX();

    let PostData = {
        'Action'        : 'deletekey',
        'ProductKey'    : CellsValue[0]
    }

    AJAXM.POST("/resources/scripts/producthandle.php",{
        onload: function () {
            let ErrorCode = 200;
            console.log(this.responseText);
            try{
                let JsonData = JSON.parse(this.responseText);

                if (JsonData.Code === 200){
                    alert("New key has been successfully removed from database");
                    window.refreshTable();
                }

                ErrorCode = JsonData.Code;
            }
            catch (e) {
                ErrorCode = "001";
            }

            displayError(ErrorCode);
        },
        onerror: function () {
            console.log('Error!');
        }
    }, PostData);

}

window.insertRecord  = function(){
    let AJAXM = new AJAX();

    let PostData = {
        'Action'        : 'addkey',
        'ProductKey'    : document.getElementById('NewKey_ProductKey').value,
        'ControlInfo'   : document.getElementById('NewKey_ControlInfo').value
    }

    AJAXM.POST("/resources/scripts/producthandle.php",{
        onload: function () {
            let ErrorCode = 200;
            try{
                let JsonData = JSON.parse(this.responseText);

                if (JsonData.Code === 200){
                    alert("New key has been successfully added in database")
                    window.refreshTable();
                }

                ErrorCode = JsonData.Code;
            }
            catch (e) {
                ErrorCode = "001";
            }

            displayError(ErrorCode);
        },
        onerror: function () {
            console.log('Error!');
        }
    }, PostData);
};