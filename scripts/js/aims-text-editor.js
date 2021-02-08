// Initializing a class definition
class aimsAPI {

    tinymce = null;
    uniqueId = 0;

    originalImg = null;
    forceRefresh

    constructor() {}

    setTinymce(tinymce){
        this.tinymce = tinymce
    }

    insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    }

    clickAimsData(){

        const ahref = document.querySelectorAll('.aims-link-spot');
        ahref.forEach(el => el.addEventListener('click', event => {
            event.preventDefault();

            const modal = document.createElement("div");
            modal.classList.add('aims-modal')
            modal.setAttribute('id','aims-modal')
            modal.style.display = "block";

            const modalContent = document.createElement("div");
            modalContent.classList.add('aims-modal-content')

            const closebtn = document.createElement("span");
            closebtn.classList.add('close')
            closebtn.innerHTML = '&times;';
            closebtn.addEventListener('click', () =>{
                document.getElementById("aims-modal").remove();
            })

            modalContent.appendChild(closebtn);
            modalContent.appendChild(this.getContentModalLink(event));
            modal.appendChild(modalContent);


            document.getElementsByTagName("BODY")[0].appendChild(modal);
        }))

        const divs = document.querySelectorAll('.aimsData');
        divs.forEach(el => el.addEventListener('dblclick', event => {
            if(!event.target){
                return false;
            }
            let eventObj = this.getParentAimsClassObj(event.target, 'aimsData');

            eventObj = (eventObj ? eventObj : event.target);
            console.log('OOOOOOOOOOOOOO')
            console.log(eventObj)
            this.uniqueId++;
            const textareaId = "aimsEditor" + this.uniqueId;
            const action = this.getAimsAttribute(eventObj,'aimsAction');
            const refresh = this.getAimsAttribute(eventObj,'aimsRefresh');
            const updateField = eventObj.getAttribute('field')
            console.log(updateField);
            console.log( eventObj.innerHTML)
            eventObj.style.display = 'none';

            var newcontent = document.createElement('textarea');
            newcontent.setAttribute('id',textareaId);
            newcontent.innerHTML = eventObj.innerHTML;

            this.forceRefresh = (refresh ? refresh : false);
            this.insertAfter( eventObj,newcontent);
            this.runTinymce(textareaId, eventObj ,action, updateField,this.update,this.delete,this.forceRefresh);

            console.log(action);
        }));

    }

    getAimsAttribute(el, attr){
        console.log(el.tagName);
        if( el.tagName.toLowerCase() === 'body'){
            return false;
        }
        for (var i = 1; i <= 10; i++) {
            parent = el.parentNode;
            if(!parent || (parent.tagName && parent.tagName.toLowerCase() === 'body')){
                return false;
            }
            if(parent.getAttribute(attr)){
                return parent.getAttribute(attr);
            }

            el = parent;
        }
        return false;
    }

    getParentAimsClassObj(el, className){
        if( el.tagName.toLowerCase() === 'body'){
            return false;
        }

        for (var i = 1; i <= 10; i++) {
            parent = el.parentNode;
            if(!parent || parent.tagName.toLowerCase() === 'body'){
                return false;
            }
            if (parent.classList.contains(className)) {
                return parent;
            }
            el = parent;
        }
        return false;
    }



    runTinymce(selectorID, event, actionId, updateField, saveObj, deleteObj, forceRefresh){

        let plugins, toolbar = '';
        const preset = (event.getAttribute('preset') ? event.getAttribute('preset') : 'data');
        const attrPlugins = event.getAttribute('plugins');
        const attrToolbar = event.getAttribute('toolbar');
        let menubar = true;
        if(preset === 'none'){
            plugins = '';
            toolbar = 'code';
            menubar = false;
           // valid_elements: 'p[style],strong,em,span[style],a[href],ul,ol,li',
        }

        if(preset === 'data'){
            plugins = 'advlist autolink lists link image charmap print preview anchor '+
                'searchreplace visualblocks code fullscreen ' +
                'insertdatetime media table paste code help wordcount';

            toolbar = ' undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | code ';
        }

        plugins += (attrPlugins ? attrPlugins : '');
        toolbar += (attrToolbar ? attrToolbar : '');
        toolbar = 'customSave customCancel customDelete |' +  toolbar;

        console.log(preset);
        console.log(plugins);
        console.log(toolbar);
        this.tinymce.init({
            selector: '#' +  selectorID,
            menubar:menubar,

            force_br_newlines : false,
            force_p_newlines : false,
            forced_root_block : '',

            plugins: [plugins],
            toolbar: toolbar,
            toolbar_mode: 'floating',
            setup: function (editor) {

                editor.ui.registry.addButton('customSave', {
                    text: 'OPSLAAN',
                    onAction: ()=> {
                        let content =  editor.getContent()
                        document.getElementById(selectorID).nextSibling.remove()
                        document.getElementById(selectorID).remove();

                        if(preset === 'none'){
                            content = content.replace(/(<([^>]+)>)/gi, "");
                        }

                        event.innerHTML = content
                        event.style.display = 'block';
                        saveObj(actionId,updateField,content);
                    }
                })
                editor.ui.registry.addButton('customCancel', {
                    text: 'CANCEL',
                    onAction: ()=> {
                        document.getElementById(selectorID).nextSibling.remove()
                        document.getElementById(selectorID).remove();
                        event.style.display = 'block';
                    }
                })
                editor.ui.registry.addButton('customDelete', {
                    text: 'DELETE',
                    onAction: ()=> {
                        document.getElementById(selectorID).nextSibling.remove()
                        document.getElementById(selectorID).remove();
                        event.style.display = 'block';
                        deleteObj(actionId);
                    }
                })
            }
        });
    }

    update(actionId,updateField,content,forceRefresh){
        console.log(actionId);
        console.log(updateField);
        console.log(content);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 &&  forceRefresh) {
                location.reload();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage: 'update',
            action: 'update',
            id: actionId,
            field: updateField,
            content: content,
        }));
    }

    delete(actionId) {
        console.log(actionId);
        if (!confirm('Weet u zeker dat u "het gehele block" wilt verwijderen ')) {
            return false
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200) {
                location.reload();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage: 'update',
            action: 'delete',
            id: actionId,
        }));
    }


    getContentModalLink(eventObj){

        const  url = eventObj.target.getAttribute('href');
        const editUrl = url.replace('/admin/','');

        const div = document.createElement("div");
        div.style.clear = 'both';

        const content = document.createElement("div");
        const lable = document.createElement("label");
        const input = document.createElement("input");
        const btnLink = document.createElement("button");
        const btnPage = document.createElement("button");

        lable.innerHTML = 'Link address';
        content.appendChild(lable);

        input.value = editUrl;
        input.classList.add('w100')
        input.style.display = 'none';
        content.appendChild(input);

        const btntxt =  'Bewerk link';
        btnLink.innerHTML = btntxt;
        btnLink.classList.add('w45')
        btnLink.style.marginRight= '5%';
        btnLink.addEventListener('click', () =>{
            if(btnLink.innerHTML === btntxt) {
                input.style.display = 'block';
                btnPage.style.display = 'none';
                btnLink.innerHTML = 'Save Url';
            }else{
                const action = this.getAimsAttribute(eventObj.target,'aimsAction');
                this.update(action,'url',input.value,false)
                eventObj.target.setAttribute('href','/admin/' + input.value );
                document.getElementById("aims-modal").remove();
            }
        })
        content.appendChild(btnLink);

        btnPage.innerHTML = 'open link';
        btnPage.classList.add('w45')
        btnPage.style.marginLeft= '5%';
        btnPage.addEventListener('click', () => {
            const target = event.target.getAttribute('target');
            window.open(url, (target ? target : '_self'));
        })

        content.appendChild(btnPage);
        content.appendChild(div);

        return content;

    }

    updateUrl(){

    }

}

aimsAPI = new aimsAPI();
aimsAPI.setTinymce(tinymce)
aimsAPI.clickAimsData();


