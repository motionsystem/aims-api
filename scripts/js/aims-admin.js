
let imported = null
const actionContent = document.currentScript.getAttribute('aims-action');
const pageContent = document.currentScript.getAttribute('aims-page');

imported = document.createElement('script');
imported.src = '/aims/js/aims-create-content.js';
document.body.appendChild(imported);

imported = document.createElement('script');
imported.src = '/data/js/aims-text-editor.js';
document.body.appendChild(imported);

imported = document.createElement('script');
imported.src = '/aims/js/aims-crop.js';
document.body.appendChild(imported);


imported = document.createElement('script');
imported.src = '/node_modules/croppr/dist/croppr.min.js';
document.body.appendChild(imported);




