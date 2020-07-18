
class Loader{
    constructor(){
        this._addhtml();
        this._addstyle();
    }

    start(){
        document.getElementById("loaderbody").style.display = "flex";
        document.getElementById("loader").style.zindex = 21474836479;
    }
//html追加
    _addhtml(){
        var body = document.body || document.getElementsByTagName('body');
        var div = document.createElement('div');
        var div2 = document.createElement('div');
        div.id="loaderbody";
        div2.id="loader";
        body.appendChild(div);
        div.appendChild(div2);
    }
//css追加
    _addstyle() {
        var css = `
#loader {
    border: 16px solid #f3f3f3;
    border-top: 16px solid #3498db;
    border-radius: 50%;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;

}
#loaderbody{
    position: absolute;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(100, 100, 100, .8);
    z-index: -1;
    display: none;
    align-items: center;
    justify-content: center
}
@keyframes spin{
    0%{ transform: rotate(0deg);}
    100%{ transform: rotate(360deg);}
}
`;

        var head = document.head || document.getElementsByTagName('head')[0];
        var style = document.createElement('style');
        head.appendChild(style);
        style.type = 'text/css';
        if (style.styleSheet){
            // This is required for IE8 and below.
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }
    }
}
