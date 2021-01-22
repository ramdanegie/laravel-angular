

function getColorChart() {
    return   [
        "#FF6384", "#4BC0C0", "#FFCE56",
        "#ffff9c", "#36A2EB", '#7cb5ec', '#75b2a3', '#9ebfcc', '#acdda8', '#d7f4d2', '#ccf2e8',
        '#468499', '#088da5', '#00ced1', '#3399ff', '#00ff7f',
        '#b4eeb4', '#a0db8e', '#999999', '#6897bb', '#0099cc', '#3b5998',
        '#000080', '#191970', '#8a2be2', '#31698a', '#87ff8a', '#49e334',
        '#13ec30', '#7faf7a', '#408055', '#09790e'
    ]
}
function colorNyieun() {
     return ['#7cb5ec', '#75b2a3', '#9ebfcc', '#acdda8', '#d7f4d2', '#ccf2e8',
        '#468499', '#088da5', '#00ced1', '#3399ff', '#00ff7f',
        '#b4eeb4', '#a0db8e', '#999999', '#6897bb', '#0099cc', '#3b5998',
        '#000080', '#191970', '#8a2be2', '#31698a', '#87ff8a', '#49e334',
        '#13ec30', '#7faf7a', '#408055', '#09790e'];
}
function colors() {
   return Highcharts.getOptions().colors;
}

function isLoading(bool) {
    if(bool){
        $('#isLoading').show()
    }else{
        $('#isLoading').hide()
    }
}
function add_toast(message,type){
    // debugger

    toast = document.createElement("toast");
    toast.classList.add(type);
    toast.innerHTML = message;
    toast.onclick = function(){vobj = this;$(this).hide(300,function(){
        $(vobj).remove();
    })};
    $("#toast").append(toast);
    // document.getElementById("toastTone"+type).play();
    setTimeout(function(){
        toast = document.getElementById("toast");
        $(toast).find("toast").eq(0).css("animation-name","floatRight");
        setTimeout(function(){
            while (toast.children[0].tagName.toLowerCase() != "toast") {
                toast.removeChild(toast.children[0]);
            }
            toast.removeChild(toast.children[0]);
            $("#toastScript").remove();
        },400);
    },8000);
}
