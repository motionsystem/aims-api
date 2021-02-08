class aimsLibrary {

    originalImg = null;
    originalWidth = null;
    originalHeight = null;
    action = null;

    spotW = 600;
    spotH = 338
    originalImageSrc = null;
    //imgW = 1200;
    //imgH = 798;
    OBJevent;

    openLibraryWindow(){

        const aimsImg = document.querySelectorAll('.aimsImg');
        aimsImg.forEach(event => {
            event.style.setProperty('display', 'block', 'important') ;
            this.disableAhref(event);
        });

        const objLib = document.querySelectorAll('.aimsImg');
        objLib.forEach(el => el.addEventListener('click', event => {
            this.action = event.target.getAttribute('aimsAction')

            this.spotW = event.target.naturalWidth;
            this.spotH = event.target.naturalHeight;
            this.originalImageSrc = event.target.getAttribute('src');
            this.originalImageAlt = event.target.getAttribute('alt');
            console.log(this.spotW)
            this.OBJevent = event
            this.openImgLibrary()
        }));




    }

    disableAhref(el) {

        if (el.tagName.toLowerCase() === 'body') {
            return false;
        }
        for (var i = 1; i <= 10; i++) {
            parent = el.parentNode;
            if (!parent || parent.tagName.toLowerCase() === 'body') {
                return false;
            }
            if (parent.tagName.toLowerCase() === 'a') {
                const href = parent.getAttribute('href');
                //parent.setAttribute('copy-href',href);
                parent.removeAttribute('href');
                parent.classList.remove('imgMagnific');
                return true;
            }
            el = parent;
        }
        return false;

    }

    clickImgLibrary() {



        const objsave = document.querySelectorAll('#saveInputs');
        objsave.forEach(el => el.addEventListener('click', event => {
            const objRef = document.querySelectorAll('.saveFileInfo');
            const info = [];
            objRef.forEach(event => {
                const id =  event.getAttribute('id')
                const original =  event.getAttribute('original')
                const val =  event.value
                info.push({'id':id,'val':val,'original':original});
            })
            this.saveInfoFile(info);
        }));


        let filename = this.originalImageSrc.replace(/^.*[\\\/]/, '');
        if(filename.substr(0,15) === 'aims-photospot-'){
            document.getElementById('changeFileNameBlock').remove();
        }

        const objRef = document.querySelectorAll('#aimsImgLibraryRefresh');
        objRef.forEach(el => el.addEventListener('click', event => {
            document.getElementById('imgLibrary').remove();
            this.OBJevent.target.click();
        }));

        const objClose = document.querySelectorAll('#aimsImgLibraryClose');
        objClose.forEach(el => el.addEventListener('click', event => {
            this.OBJevent.target.removeAttribute('openFolder');
            document.getElementById('imgLibrary').remove();
        }));

        const objSelectFolder = document.querySelectorAll('.aimsImgLibraryFolder');
        this.focusOnlyMainClickEvent('.aimsImgLibraryFolder');
        objSelectFolder.forEach(el => el.addEventListener('click', event => {
            this.clickFolder(event.target);
        }));

        const objSelectImage = document.querySelectorAll('.aimsImgLibraryImage');
        this.focusOnlyMainClickEvent('.aimsImgLibraryImage');
        objSelectImage.forEach(el => el.addEventListener('click', event => {
            const toggle = (event.target.classList.contains('selected') ? true : false);
            this.deactivateSelection('.aimsImgLibraryImage');
            document.getElementById('onImageSelected').style.display = 'none';
            if (!toggle) {
                event.target.classList.add('selected')
                this.actionButtonselectedImage(event.target);
                document.getElementById('imgLibraryFolder').style.display = 'none';
            } else {
                document.getElementById('imgLibraryFolder').style.display = 'block';
            }

        }));

        const selectNewImage = document.querySelectorAll('#selectNewImage');
        selectNewImage.forEach(el => el.addEventListener('click', event => {
            this.OBJevent.target.setAttribute('openFolder', 'home');
            document.getElementById('originalImgContainer').style.display = 'none';
            document.getElementById('selectImage').style.display = 'block';
        }));

        const useImage = document.querySelectorAll('#useImage');
        useImage.forEach(el => el.addEventListener('click', event => {
            this.useImage()
        }));

        const removeImageName = document.querySelectorAll('#removeImageName');
        removeImageName.forEach(el => el.addEventListener('click', event => {
            this.removeImageName()
        }));

        const renameImageName = document.querySelectorAll('#renameImageName');
        renameImageName.forEach(el => el.addEventListener('click', event => {
            this.renameImageName()
        }));

        const removeImageBlock = document.querySelectorAll('#removeImageBlock');
        removeImageBlock.forEach(el => el.addEventListener('click', event => {
            this.clickDeleteBlock()
        }));

        const resizeUploadImage = document.querySelectorAll('#resizeUploadImage');
        resizeUploadImage.forEach(el => el.addEventListener('click', event => {
            this.resizeUploadImage()
        }));



        const divs = document.querySelectorAll('#imageFile');
        divs.forEach(el => el.addEventListener('change', evt => {
            var files = evt.target.files;
            var file = files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('formUpload').style.display = "none";
                    document.getElementById('libraryResult').style.display = "none";
                    document.getElementById('uploadResult').style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        }));

        document.getElementById('originalSrc').setAttribute('src',this.originalImageSrc)
        if(this.OBJevent.target.getAttribute('openFolder')){
            document.getElementById('originalImgContainer').style.display = 'none';
            document.getElementById('selectImage').style.display = 'block';
            objSelectFolder.forEach(event => {
                if(event.innerText === this.OBJevent.target.getAttribute('openFolder')) {
                    this.clickFolder(event);
                }
            });

        }

    }

    clickFolder(event){
        console.log('FOLDER');
        this.deactivateSelection('.aimsImgLibraryImage');
        this.deactivateSelection('.aimsImgLibraryFolder');
        this.deactivateSelection('.libraryResultFolder');

        if(event.classList.contains('newfolder')){
            this.createNewFolder()
            return false;
        }

        const selectFolder = event.innerText
        this.OBJevent.target.setAttribute('openFolder', selectFolder);
        console.log(selectFolder);
        if(document.getElementById('content-folder-' + selectFolder)) {
            document.getElementById('content-folder-' + selectFolder).classList.add('selected');
            document.getElementById('onImageSelected').style.display = 'none';
        }
        event.classList.add('selected')

    }

    actionButtonselectedImage(event) {
        const title = event.querySelector('.title').innerText
        const img = event.querySelector('img').getAttribute('src');
        const orginImg = this.getOriginalImg(img)

        document.getElementById('selectedImageTitle').innerText = title;
        document.getElementById('selectedImageImg').setAttribute('src', orginImg)
        document.getElementById('onImageSelected').style.display = 'block';

    }

    deactivateSelection(selector) {
        const aimsImgLibraryFolder = document.querySelectorAll(selector);
        for (const obj of aimsImgLibraryFolder) {
            obj.classList.remove('selected')
        }
    }

    focusOnlyMainClickEvent(className) {
        const obj = document.querySelectorAll(className)
        obj.forEach(el => {
            el.classList.add('clickEvent')
        });
    }


    openImgLibrary() {

        const blockname = this.getAimsAttribute(this.OBJevent.target, 'aims-block');

        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/image-library.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'text/html');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200) {
                var elemDiv = document.createElement('div');
                elemDiv.innerHTML = xhr.responseText;
                 document.body.appendChild(elemDiv); // appends last of that element
                this.clickImgLibrary();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'image-library',
            id: this.action,
            block: blockname
        }));
        console.log('openImgLibrary')
    }

    getSelectedImg() {
        const aimsImgLibraryFolder = document.querySelectorAll('.aimsImgLibraryImage');
        for (const obj of aimsImgLibraryFolder) {
            if (obj.classList.contains('selected')) {
                return obj.querySelectorAll('img')[0];
            }
        }
        return null;
    }

    getOriginalImg(img) {
        console.log(img);
        const arr = img.split('/');
        let returnImg = '';
        for (const key in arr) {
            const item = arr[key];
            if (item.charAt(0) !== '_') {
                returnImg += (returnImg ? '/' + item : item);
            }
        }
        returnImg = '/' + returnImg;
        console.log(returnImg);
        return returnImg;
    }

    useImage() {
        console.log('useImage')

        const objImg = document.getElementById('selectedImageImg');
        if (!objImg) {
            return false;
        }
        this.originalImg = objImg.getAttribute('src');
        this.originalWidth = objImg.naturalWidth;
        this.originalHeight = objImg.naturalHeight;

        document.getElementById('cropOriginalImg').setAttribute('src', this.originalImg)
        document.getElementById('selectImage').style.display = 'none';
        document.getElementById('cropImage').style.display = 'block';
        this.resizeableImage('#cropOriginalImg');
    }


    removeImageName() {
        if (!confirm('Weet u zeker dat u "deze foto" wilt verwijderen?')) {
            return false
        }

        const file =  document.getElementById('selectedImageImg').getAttribute('src')
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/edit-library.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200) {
                document.getElementById('imgLibrary').remove();
                this.OBJevent.target.click();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'edit-library',
            action: 'delete',
            filename: file,
        }));
    }

    renameImageName() {
        let newFilename = prompt("Varander de naam van deze foto. Gebruik geen spaties, speciale charakters en google vriendelijke woorden", "");

        if (!newFilename) {
            return false
        }

        const file = document.getElementById('selectedImageImg').getAttribute('src')
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/edit-library.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (() => {//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200) {
                 document.getElementById('imgLibrary').remove();
                 this.OBJevent.target.click();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'edit-library',
            action: 'rename',
            filename: file,
            newFilename: newFilename,
        }));
    }


    resizeableImage(image_id) {
        console.log(this.originalImg);
        console.log(this.spotW);
        console.log(this.originalWidth);

        const minSizeH = (this.spotW / this.originalWidth ) * 100;
        const minSizeW = (this.spotH / this.originalHeight) * 100 ;
        const ratio = (this.spotH / this.spotW)
console.log(ratio)
        console.log(minSizeW, minSizeH)
        var croppr = new Croppr(image_id, {
            // options
            aspectRatio: ratio,
            maxSize: [100, 100, '%'],
            minSize: [minSizeW, minSizeH, '%'],
            startSize: [75, 75, '%'],
            onInitialize: ( (instance) => {
                // do things here
                const img = document.getElementsByClassName("croppr-image");
                img[0].setAttribute('id','croppr-image-orignal')

                const divs = document.querySelectorAll('#clickCrop');
                divs.forEach(el => el.addEventListener('click', ()=>{
                    this.clickCrop()
                }));
            }),
            onCropEnd: ( (value)=>{
                console.log('OKE');
                console.log(value.x, value.y, value.width, value.height);

                console.log('OKEKEKEKEKKEKKEK');
                document.getElementById("input-croppr-width").value = value.width;
                document.getElementById("input-croppr-height").value = value.height;
                document.getElementById("input-croppr-x").value = value.x;
                document.getElementById("input-croppr-y").value = value.y;
                document.getElementById("formCrop").style.display = 'block';

            })
        });

    }

    clickDeleteBlock() {
        console.log(this.action);
        if (!confirm('Weet u zeker dat u "deze image block" wilt verwijderen ')) {
            return false
        }

        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/update.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200) {
                location.reload();
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'update',
            action: 'delete',
            id: this.action,
        }));
    }


    clickCrop()
    {
        const filename = document.getElementById("croppr-image-orignal").getAttribute('src');
        const w = document.getElementById("input-croppr-width").value;
        const h = document.getElementById("input-croppr-height").value;
        const x = document.getElementById("input-croppr-x").value;
        const y = document.getElementById("input-croppr-y").value;

        const block = this.getAimsAttribute(this.OBJevent.target, 'aims-block');
        const group = this.getAimsAttribute(this.OBJevent.target, 'aims-group');

        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/upload.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {//Call a function when the state changes.
            console.log('OKE');
            if (xhr.readyState == 4 && xhr.status == 200) {
                location.reload();
            }
        }
        xhr.send(JSON.stringify({
            aimsPage:'upload',
            action:'crop',
            id: this.action,
            filename: filename,
            resize_width:this.spotW,
            resize_height:this.spotH,
            width: w,
            height:h,
            x: x,
            y: y,
            block:block,
            group:group,
        }));
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


    saveInfoFile(info){
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/change-filename.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {//Call a function when the state changes.
            console.log('OKE');
        }
        xhr.send(JSON.stringify({
            aimsPage:'change-filename',
            id: this.action,
            data: info,
        }));
    }

    createNewFolder(){
        console.log('NEW FOLDER')
        let newFolderName = prompt("Folder naam (na het uploaden van de eerste foto, wordt de folder aangemaakt.) Gebruik geen speciale charakters en google vriendelijke woorden", "");

        if (newFolderName != null) {
            newFolderName = newFolderName.replace(/ /g, "-");
// Get the last <li> element ("Milk") of <ul> with id="myList2"
            var itm = document.getElementById("homeDir");
            console.log(itm.innerHTML)
// Copy the <li> element and its child nodes
            var cln = itm.cloneNode(true);
            cln.innerHTML = newFolderName;
            cln.classList.add('selected')
            cln.addEventListener('click', event => {
                this.clickFolder(event);
            });

// Append the cloned <li> element to <ul> with id="myList1"
            document.getElementById("imgLibraryFolderContainer").appendChild(cln);
        }
    }





    resizeUploadImage() {
        if (window.File && window.FileReader && window.FileList && window.Blob) {

            var filesToUploads = document.getElementById('imageFile').files;
            var file = filesToUploads[0];

            let newFolderName = prompt("Geef deze file een naam. Gebruik geen speciale charakters en google vriendelijke woorden", file.name.toLowerCase());

            if (file && newFolderName != null) {
                var reader = new FileReader();
                // Set the image once loaded into file reader
                reader.onload = ((e)=> {

                    var img = document.createElement("img");
                    img.src = e.target.result;

                    var canvas = document.createElement("canvas");
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0);

                    var MAX_WIDTH = 1200;
                    var MAX_HEIGHT = 800;
                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(img, 0, 0, width, height);

                    let dataurl = canvas.toDataURL(file.type);
                    //document.getElementById('output').src = dataurl;
                    document.getElementById('formUpload').style.display = "block";
                    document.getElementById('libraryResult').style.display = "block";
                    document.getElementById('uploadResult').style.display = "none";
                    console.log(dataurl);

                    let selectedFolder = 'home';
                    const aimsImgLibraryFolder = document.querySelectorAll('.aimsImgLibraryFolder');
                    for (const obj of aimsImgLibraryFolder) {
                        if (obj.classList.contains('selected')) {
                            selectedFolder = obj.innerHTML
                        }
                    }
                    console.log(selectedFolder);



                    var xhr = new XMLHttpRequest();
                    //xhr.open("POST", '/aims/upload.php', true);
                    xhr.open("POST", '/index.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.onreadystatechange = ( () => {//Call a function when the state changes.
                        //document.getElementById('aimsImgLibraryRefresh').click();
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            if(document.getElementById('imgLibrary')) {
                                document.getElementById('imgLibrary').remove();
                                this.OBJevent.target.click();
                            }
                        }
                    })
                    xhr.send(JSON.stringify({
                        aimsPage:'upload',
                        action:'base64',
                        folder: (selectedFolder ? selectedFolder : 'home').toLowerCase(),
                        overwrite:false,
                        img: dataurl,
                        filename: newFolderName.toLowerCase(), //file.name.toLowerCase(),
                    }));

                })
                reader.readAsDataURL(file);

            }

        } else {
            alert('The File APIs are not fully supported in this browser.');
        }
    }


}

aimsLibrary = new aimsLibrary();
aimsLibrary.openLibraryWindow();
