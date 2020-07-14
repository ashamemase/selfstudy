///////////////////////////////////////////////////////////////////////////////////////////////////
//
//      TTS.js TTSデータのlocalstorageへのセーブ、ロード、音声の発声を行う
//
//
class myLocalStorage extends Array{
    LoadFromStorage(key){
        this.length=0;
        let str=localStorage.getItem(key);
        let json=JSON.parse(str);
        Object.keys(json).forEach(function(k){
            this[k]=json[k];
        },this);
    }
    SaveToStorage(key){
        let str=JSON.stringify(Object.assign({}, this));
        localStorage.setItem(key,str);
    }
}

class TTS{
    speed;
    pitch;
    volume;
    voice;
    onvoicechanged;//音声が遅延で追加されるブラウザ用です。このクラスがイベントを処理してしまうので、追加の処理がある場合に使ってください。
    _ready;
    _fromlocalstorage=false;
    _key="TTS_DATA";
    _voicename;
    set voicename(name){
        this.setVoiceByName(name);
    }
    get voicename(){
        return this._voicename;
    }
    constructor(){
        this.speed=1.0;
        this.pitch=1.0;
        this.volume=1.0;
        this.voicename=null;
        this.voice=null;
        this._ready=false;
        this.onvoicechanged=null;
        if(localStorage.getItem(this._key)===null){
            this._setDefault();
        }else{
            this._fromlocalstorage=true;
            this._loadStorage();
        }
        speechSynthesis.onvoiceschanged = e => {
            this._appendVoices();
        } 
    }
    //音声名からTTSを設定します。返り値は成否です。
    setVoiceByName(name){
        if(this._voicename==name) return this._ready;
        this._ready=false;
        this.voice=null;
        this._voicename=name;
        this.voice=this._getVoiceFromName(this.voicename);
        if(this.voice!=null) this._ready=true;
        return this._ready;
    }
    //クロム用に遅延音声追加をします。
    _appendVoices(){
        if(this.onvoicechanged!=null) this.onvoicechanged();
        if(!this._fromlocalstorage){
            this._setDefault();
        }else{
            this.voice=this._getVoiceFromName(this.voicename);
        }
        if(this.voice!=null) this._ready=true;
    }
    //デフォルトの音声を設定します。
    _setDefault(){
        let voices=speechSynthesis.getVoices();
        voices.forEach(v=>{
            if(!v.lang.match('ja')) return;
            this.voice=v;
            this._voicename=v.name;
        });
        if(this.voice!=null)this._ready=true;
    }
    //ローカルストレージから音声データを取得します
    _loadStorage(){
        try{
            let json = new myLocalStorage();
            json.LoadFromStorage(this._key);
            this.speed=json["speed"];
            this.pitch=json["pitch"];
            this.volume=json["volume"];
            this._voicename=json["voice"];
            this.voice=this._getVoiceFromName(this.voicename);
        }catch(e){
            return;
        }
        if(this.voice!=null) this._ready=true;
    }
    //ローカルストレージへデータをセーブします
    saveStorage(){
        if(this._ready==false)return;
        try{
            let json =new myLocalStorage();
            json["speed"]=this.speed;
            json["pitch"]=this.pitch;
            json["volume"]=this.volume;
            json["voice"]=this.voice.name;
            json.SaveToStorage(this._key);
        }catch(e){
            return;
        }
    }
    //音声名から音声オブジェクトを取得します
    _getVoiceFromName(name){
        return speechSynthesis.getVoices().filter(voice => voice.name === name)[0];
    }

    //準備ができている場合、音声を発声します
    speech(text){
        if(!this._ready)return;
        const uttr=new SpeechSynthesisUtterance(text);
        uttr.voice=this.voice;
        uttr.rate=this.speed;
        uttr.volume=this.volume;
        uttr.pitch=this.pitch;
        window.speechSynthesis.speak(uttr);
    }
}
