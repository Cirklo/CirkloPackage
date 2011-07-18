function clickit(id,action,table,resource) {
    var iChars = "!#$%^&=[]\';/{}|\"<>?";
    var iCharsINT = "0123456789";
    var iCharsREAL = ".0123456789";
    var CurForm=eval("document.tableman"+id);
    if (action!='delete') {
        for (i=0;i<CurForm.length;i++) {
            if ((CurForm[i].value=='') && (CurForm[i].lang=='not_null')) {
                CurForm[i].focus();
                alert ("Field " + CurForm[i].name + " required!");
                return;
            }
            if (CurForm[i].title=='string') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iChars.indexOf(CurForm[i].value.charAt(k)) != -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts special characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
            if (CurForm[i].title=='real') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iCharsREAL.indexOf(CurForm[i].value.charAt(k)) == -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts non numerial characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
            if (CurForm[i].title=='int') {
                for (var k = 0; k < CurForm[i].value.length; k++) {
                    if (iCharsINT.indexOf(CurForm[i].value.charAt(k)) == -1) {
                        CurForm[i].focus();
                        alert("Field " + CurForm[i].name + " Containts non numerical characters. \n These are not allowed.\n Please remove them and try again.");
                        return;
                    }
                }
            }
        }
    }
    var resp=confirm('Sure you want to ' + action + ' this record?');
    if (resp) {
        CurForm.action='../agendo/usermanage.php?&action=update&resource=' + resource;
        CurForm[0].disabled=false;
        CurForm.submit();
    } 
}
