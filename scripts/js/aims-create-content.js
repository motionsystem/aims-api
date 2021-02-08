// Initializing a class definition
class aimsCreateContent {

    page = null;
    newContentAction = null;
    countBlocks = 0;

    constructor(action,page) {
        this.newContentAction = action
        this.page = page
    }

    countAimsAction(el){
        this.countBlocks = 0;
        this.findAimsAction(el);
    }


    getAimsAttribute(el, attr){
        if( el.tagName.toLowerCase() === 'body'){
            return false;
        }
        for (var i = 1; i <= 10; i++) {
            parent = el.parentNode;
            if(!parent || parent.tagName.toLowerCase() === 'body'){
                return false;
            }
            if(parent.getAttribute(attr)){
                return parent.getAttribute(attr);
            }

            el = parent;
        }
        return false;
    }

    findAimsAction(el){
        const children = el.childNodes;
        for(let child in children){
            if(!children[child].tagName){
               continue;
            }
            let action = children[child].getAttribute('aimsAction');
            if(action){
                this.countBlocks++
            }

            // never continue inside another action block, count only the parents from this block..
            let imgBlock = children[child].classList.contains('aims-image-block');
            let txtBlock = children[child].classList.contains('aims-text-block');
            if(!imgBlock && !txtBlock){
                this.findAimsAction(children[child]);
            }

        }
    }

    setListeners() {
        console.log('setListenersOKKEKEE')
        const divs = document.querySelectorAll('.aims-text-block');
        divs.forEach(event => {
            if (!event) {
                return false;
            }
            this.countAimsAction(event);
            let maxCount = event.getAttribute('aims-max-block');

            if((maxCount ? parseInt(maxCount) : 99) > this.countBlocks ) {
                var el = document.createElement("button");
                el.innerHTML = "nieuw tekst block";
                el.classList.add('aims-create-block')
                el.addEventListener('click', () =>{
                    this.createNewTextRecord(el);
                })
                event.appendChild(el);
            }
        })

        const url = window.location.pathname;
        console.log(url);
        if(url.substring(6,0)) {
            console.log('add ADMIN to href')
            const Aselect = document.querySelectorAll('a');
            Aselect.forEach(event => {
                if (!event) {
                    return false;
                }
                const href = event.getAttribute('href');
                const skipAdmin = event.classList.contains('skipAdminLink');
                if(href && href.substring(1) && !skipAdmin) {
                    event.setAttribute('href','/admin' + href);
                }
            })
        }

        const img = document.querySelectorAll('.aims-image-block');
        img.forEach(event => {
            console.log('TTTTTTTTTTTTTTTTTTTTTTTTTTTTTT')
            if (!event) {
                return false;
            }

            this.countAimsAction(event);
            let maxCount = event.getAttribute('aims-max-block');

            const copyClass = 'aimsImg'

            if((maxCount ? parseInt(maxCount) : 99) > this.countBlocks ) {
                var elContainer =document.createElement("a");
                var el = document.createElement("img");

                el.setAttribute('src','/upload/aims-photospot-new-overview.jpg')
                el.setAttribute('class',copyClass)
                el.classList.add('aims-create-image-block')

                el.addEventListener('click', () =>{
                    this.createNewImageRecord(el);
                })

                elContainer.appendChild(el);
                event.appendChild(elContainer);
            }
        })

        const newPage = document.querySelectorAll('.aims-new-page');
        newPage.forEach(el => el.addEventListener('click', event => {
            this.createNewPage()
        }));
    }


    createNewTextRecord(event){
        let answer = window.confirm('Weet je het zeker dat je een nieuwe text block wilt toevoegen');
        if (!answer) {
            return false;
        }

        const blockname = this.getAimsAttribute(event, 'aims-block')
        const group = this.getAimsAttribute(event, 'aims-group')
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/create-record.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                const json = JSON.parse(xhr.responseText);
                if (json && json['ERROR'].length < 1) {
                    location.reload();
                }
            }
            console.log(xhr.responseText);
        })
        xhr.send(JSON.stringify({
            aimsPage:'create-record',
            item: 'text',
            page: this.page,
            action: this.newContentAction,
            block: blockname,
            group:(group ? group : 0)
        }));

    }


    createNewImageRecord(event){
        var answer = window.confirm('Weet je het zeker dat je een nieuwe foto wilt toevoegen');
        if (!answer) {
                return false;
        }
        const blockname = this.getAimsAttribute(event, 'aims-block')
        const group = this.getAimsAttribute(event, 'aims-group')

        if(!blockname){
            alert('no blockname is set');
            return false;
        }

        console.log({
            item: 'photo',
            page: this.page,
            action: this.newContentAction,
            block: blockname,
            group:(group ? group : 0)
        })

        //return false;
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/create-record.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                const json = JSON.parse(xhr.responseText);
                if (json && json['ERROR'].length < 1) {
                    location.reload();
                }
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'create-record',
            item: 'photo',
            page: this.page,
            action: this.newContentAction,
            block: blockname,
            group:(group ? group : 0)
        }));

    }



    createNewPage(event) {
        //return false;
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/update.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (() => {//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                const json = JSON.parse(xhr.responseText);
                if (json && json['ERROR'].length < 1) {
                    location.reload();
                }
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'update',
            action: 'update',
            name: document.getElementById("pageName").value,
            title: document.getElementById("pageTitle").value,
            url: document.getElementById("pageUrl").value,
            mobilemenu: document.getElementById("mobilemenu").value,
            parentmobile: document.getElementById("parentmobile").value,
            template_id: document.getElementById("pageTemplate").value,
            id: document.getElementById("action").value,
            active: 1,
        }));
    }


}

//const actionContent = document.currentScript.getAttribute('aims-action');
//const pageContent = document.currentScript.getAttribute('aims-page');
aimsCreate = new aimsCreateContent(actionContent, pageContent);
aimsCreate.setListeners()
