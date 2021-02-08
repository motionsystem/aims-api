// Initializing a class definition
class aimsWebshopClass {

    page = null;
    newContentAction = null;
    countBlocks = 0;

    constructor(page) {
        console.log('start')
        this.page = page
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


    setListeners() {
        console.log('setListeners')
        const divs = document.querySelectorAll('.aims-add-webshop');
        divs.forEach(el => el.addEventListener('click', eventObj => {
//            const action = this.getAimsAttribute(eventObj.target,'aimsAction');
            const productId = eventObj.target.getAttribute('aims-product');
            this.addWebshop(productId)
        }));

        const productPlus = document.querySelectorAll('.aims-webshop-change-plus');
        productPlus.forEach(el => el.addEventListener('click', eventObj => {
            const productId = this.getAimsAttribute(eventObj.target,'aims-product');
            this.changeBasket(productId,'addProduct')
        }));

        const productMin = document.querySelectorAll('.aims-webshop-change-min');
        productMin.forEach(el => el.addEventListener('click', eventObj => {
            const productId = this.getAimsAttribute(eventObj.target,'aims-product');
            this.changeBasket(productId,'removeProduct')
        }));

        const productClear = document.querySelectorAll('.aims-webshop-change-clear');
        productClear.forEach(el => el.addEventListener('click', eventObj => {
            const productId = this.getAimsAttribute(eventObj.target,'aims-product');
            this.changeBasket(productId,'clearProduct')
        }));




        const btnClick = document.querySelectorAll('.aims-webshop-createOrder');
        btnClick.forEach(el => el.addEventListener('click', eventObj => {
            //// BUG not all input, select online whats inside .aims-webshop-check
            const input = document.querySelectorAll('.webshop-form')
            let body = [];
            input.forEach(elem =>{
                const key = elem.getAttribute('name');
                const val = elem.value;
                body[key] = val;
            })
            if(body){
                this.createOrder(body)
            }
            console.log(body)
        }));
    }

    addWebshop(productId,action='addProduct', modal = true){
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/webshop.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                const json = JSON.parse(xhr.responseText);
                console.log(json);
                if(modal) {
                    this.openModalPopup(
                        'verder winkelen',
                        '',
                        'naar winkelmandje',
                        '/webshop/winkelmandje',
                        'Toegevoegt')
                }else{
                    location.reload();
                }
            }
        })
        xhr.send(JSON.stringify({
            aimsPage:'webshop',
            product: productId,
            action: action
        }));
    }



    createOrder(body){
        body['action'] =  'createOrder';
console.log('EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE')
        var xhr = new XMLHttpRequest();
        //xhr.open("POST", '/aims/webshop.php', true);
        xhr.open("POST", '/index.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = (()=>{//Call a function when the state changes.
            if (xhr.readyState == 4 && xhr.status == 200 && xhr.responseText) {
                const json = JSON.parse(xhr.responseText);
                const url = json['succesUrl'];
                console.log(json);
                window.location.href = url;
            }
            console.log(xhr.responseText);
        })
        xhr.send(JSON.stringify({
            aimsPage:'webshop',
            action: 'createOrder',
            discountcode:'',
            company: (body['company'] ? body['company'] : ''),
            kvk:  (body['kvk'] ? body['kvk'] : ''),
            firstname: (body['firstname'] ? body['firstname'] : ''),
            lastname: (body['lastname'] ? body['lastname'] : ''),
            zipcode: (body['zipcode'] ? body['zipcode'] : ''),
            city: (body['city'] ? body['city'] : ''),
            street: (body['street'] ? body['street'] : ''),
            housenum: (body['housenum'] ? body['housenum'] : ''),
            phone: (body['phone'] ? body['phone'] : ''),
            email: (body['email'] ? body['email'] : ''),
            'invoicefirstname': (body['invoicefirstname'] ? body['invoicefirstname'] : ''),
            'invoicelastname': (body['invoicelastname'] ? body['invoicelastname'] : ''),
            'invoicezipcode': (body['invoicezipcode'] ? body['invoicezipcode'] : ''),
            'invoicecity': (body['invoicecity'] ? body['invoicecity'] : ''),
            'invoicestreet': (body['invoicestreet'] ? body['invoicestreet'] : ''),
            'invoicehousenum': (body['invoicehousenum'] ? body['invoicehousenum'] : ''),
        }));
    }


    changeBasket(productId, action){

        if(action === 'clearProduct'){
            var clickd = confirm("Weet u zeker dat u dit product wilt verwijderen!");
            if (clickd !== true) {
                return false;
            }
        }

        this.addWebshop(productId,action,false);
        console.log(productId)
    }





    openModalPopup(btntxtA,urlA,btntxtB,urlB,title){
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

        const div = document.createElement("div");
        div.style.clear = 'both';

        const content = document.createElement("div");
        const header = document.createElement("h2");
        header.innerHTML = title;
        content.appendChild(header);

        const btnA = document.createElement("button");
        const btnB = document.createElement("button");


        btnA.innerHTML = btntxtA;
        btnA.classList.add('w45')
        btnA.style.marginRight= '5%';
        btnA.addEventListener('click', () => {
            if(urlA) {
                window.open(urlA, ('_self'));
            }
            document.getElementById("aims-modal").remove();
        })
        content.appendChild(btnA);

        btnB.innerHTML = btntxtB;
        btnB.classList.add('w45')
        btnB.style.marginLeft= '5%';
        btnB.addEventListener('click', () => {
            if(urlB) {
                window.open(urlB, '_self');
            }
            document.getElementById("aims-modal").remove();
        })

        content.appendChild(btnB);
        content.appendChild(div);

        modalContent.appendChild(content);
        modal.appendChild(modalContent);
        document.getElementsByTagName("BODY")[0].appendChild(modal);
    }
}
console.log('start')
const action = document.currentScript.getAttribute('aims-action');
const page = document.currentScript.getAttribute('aims-page');
aimsWebshop = new aimsWebshopClass(action, page);
aimsWebshop.setListeners()
