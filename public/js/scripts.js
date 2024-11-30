const searchHandler = document.querySelector('.header-search__icon');
const clearHandler = document.querySelector('.header-search__clear');
const searchContainer = document.querySelector('.header-search__form');
if(searchHandler && searchContainer){
    clearHandler.onclick = function(e) {
        searchContainer.classList.remove('opened');
    }

    searchHandler.onclick = function(e){
        if(!searchContainer.classList.contains('opened')){
            searchContainer.classList.add('opened');

        }else{
            searchContainer.classList.remove('opened');
        }
    }
    document.onclick = function(e){
        target = event.target;
        if(target.closest('.header-search') === null){
            searchContainer.classList.remove('opened');
        }
    };

}
const popupHandlers = document.querySelectorAll('[data-popup]');
popupHandlers.forEach(function(popupHandler){
    popupHandler.onclick = function(e){
        var popupId = popupHandler.dataset.popup;
        var popup = document.getElementById(popupId);
        fadeInNative(popup);
        window.setTimeout( () => {
            popup.querySelector('.popup__window').classList.add('opened');
        }, 10);
        e.preventDefault();
    };
});
const popupCloseHandlers = document.querySelectorAll('.popup-close-handler');
for (var popupCloseHandler of popupCloseHandlers){
    popupCloseHandler.onclick = function(e){
        closePopup();
    };
}
function closePopup(){
    const popupElems = document.querySelectorAll('.popup');
    for (var popupElem of popupElems){
        fadeOutNative(popupElem);
        popupElem.querySelector('.popup__window').classList.remove('opened');
    }
}
window.onresize = function(event) {
    var windowWidth = window.screen.width;
    if(windowWidth > 992){
        mobilePanelClose();
    }
};
const mobilePanelCall = document.querySelectorAll('.touch-menu');
for (var mobilePanelCallItem of mobilePanelCall) {
    mobilePanelCallItem.onclick = function(e){
        mobilePanelOpen();
        e.preventDefault();
    };
}
const mobilePanelHide = document.querySelectorAll('.mobile-panel__close,.mobile-panel-overlay');
for (var mobilePanelHideItem of mobilePanelHide) {
    mobilePanelHideItem.onclick = function(e){
        mobilePanelClose();
        e.preventDefault();
    };
}
function mobilePanelOpen(){
    var mobilePanel = document.querySelector('.mobile-panel');
    var mobilePanelOverlay = document.querySelector('.mobile-panel-overlay');
    mobilePanel.classList.add('opened');
    fadeInNative(mobilePanelOverlay);
}
function mobilePanelClose(){
    var mobilePanel = document.querySelector('.mobile-panel');
    var mobilePanelOverlay = document.querySelector('.mobile-panel-overlay');
    mobilePanel.classList.remove('opened');
    fadeOutNative(mobilePanelOverlay);
}
const menuHandlers = document.querySelectorAll('li.has-children a');
menuHandlers.forEach(function(menuHandler){
    menuHandler.onclick = function(e){
        var submenu = menuHandler.nextElementSibling;
        if(!menuHandler.parentNode.classList.contains('active')){
            menuHandler.parentNode.classList.add('active');
            submenu.style.display = 'block';
        }else{
            menuHandler.parentNode.classList.remove('active');
            submenu.style.display = 'none';
        }
        e.preventDefault();
    };
});
const citySelect = document.querySelector('.city__select select');
const cityContent = document.querySelectorAll('.city__item');
if(citySelect){
    citySelect.onchange = function(e){
        var optionSelected = citySelect.selectedIndex;
        cityContent.forEach(function(cityItem){
            cityItem.style.display = 'none';
        });
        cityContent[optionSelected].style.display = 'block';
    };
};
const faqTabs = document.querySelectorAll('.page-faq__tab span');
const faqBlocks = document.querySelectorAll('.page-faq__block');
faqTabs.forEach(function(faqTab){
    faqTab.onclick = function(e){
        var parentTab = faqTab.parentNode;
        var itemIndex = Array.from(parentTab.parentNode.children).indexOf(parentTab);
        if(!parentTab.classList.contains('active')){
            faqTabs.forEach(function(faqTab){
                faqTab.parentNode.classList.remove('active');
            });
            parentTab.classList.add('active');
            faqBlocks.forEach(function(faqBlock){
                faqBlock.style.display = 'none';
            });
            faqBlocks[itemIndex].style.display = 'block';

        }
    };
});
const inputViews = document.querySelectorAll('.page-login__view');
inputViews.forEach(function(inputView){
    inputView.onclick = function(e){
        var input = inputView.previousElementSibling;
        var inputType = input.getAttribute('type');
        if(inputType == 'password'){
            input.setAttribute('type', 'text');
        }else{
            input.setAttribute('type', 'password');
        }
    };
});
const elementScrollbars = document.querySelectorAll('.element-scrollbar');
elementScrollbars.forEach(function(elementScrollbar){
    new SimpleBar(elementScrollbar,{
        autoHide: false
    });
});
var fadeOutNative = (target,duration) => {
    if(!duration) duration = 300;
    target.style.opacity = 1;
    target.style.transition = 'opacity '+duration/1000+'s linear';
    window.setTimeout( () => {
        target.style.opacity = 0;
    }, 10);
    window.setTimeout( () => {
        target.style.display = 'none';
    }, duration + 10);
}
var fadeInNative = (target,duration,display) => {
    if(!duration) duration = 300;
    if(!display) display = 'block';
    target.style.opacity = 0;
    target.style.display = display;
    target.style.transition = 'opacity '+duration/1000+'s linear';
    window.setTimeout( () => {
        target.style.opacity = 1;
    }, 10);
}
