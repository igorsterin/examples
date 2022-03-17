top.BX.SidePanel.Slider.prototype.handleOverlayClick = function () {
    console.log('anti-misclick protect');
};

//console.log('SdP:' + BX.SidePanel.Slider);

if (typeof(beforeDOMContentLoaded) === 'undefined') {
    let beforeDOMContentLoaded = new MutationObserver( () => {
            if (!!document.querySelector("#header-inner > div.header-search > div > div"))
                document.querySelector("#header-inner > div.header-search > div > div").style.display = "none";
        }
    );
    beforeDOMContentLoaded.observe(document, {
        childList: true,
        subtree: true
    })
}

window.CSM = {};

CSM.SonetChat = {

    listChatHiddenUsers: null,
    hiddenCount: null,

    getListChatHiddenUsers: function () {
        fetch('/local/js/chat.ajax.php', { method: 'POST' }).then(r => r.json()).then((result) => this.listChatHiddenUsers = result);
    },

    hideUsersFromChat: function () {
        if (typeof(BXIM) == 'undefined') return;
        let chatID = BXIM.messenger.getChatId();
        let usersToHide = this.listChatHiddenUsers[chatID];
        this.hideUsersFromChatHeader(usersToHide);
        this.updateCount();
    },

    hideUsersFromPopup: function () {
        if (typeof(BXIM) == 'undefined') return;
        let chatID = BXIM.messenger.getChatId();
        let usersToHide = this.listChatHiddenUsers[chatID];
        this.hideUsersFromChatPopup(usersToHide);
    },

    hideUsersFromChatHeader: function (userIDs) {
        if (typeof(userIDs) == 'undefined') return;
        let i = 0;
        userIDs.forEach(function (userID) {
            let elem = document.querySelector('span.bx-messenger-panel-chat-user[data-userid="' + userID + '"]');
            if (!!elem) {
                elem.style.display = "none";
                i++;
            }
            this.hiddenCount = i;
        });
    },

    hideUsersFromChatPopup: function (userIDs) {
        if (typeof (userIDs) == 'undefined') return;
        userIDs.forEach(function (userID) {
            let elem = document.querySelector('#popup-window-content-bx-messenger-popup-chat-users > div > span > span[data-userid="' + userID + '"]');
            if (!!elem) elem.style.display = "none";
        });
    },

    updateCount: function () {

        let popupLabelMore = this.getLabelPopupMore();
        if (!popupLabelMore) return false;


        let countPopup = this.getCountPopup();

        if (countPopup > 0) {
            popupLabelMore.textContent = "и еще " + countPopup;
        } else {
            popupLabelMore.style.display = "none";
        }
    },

    getUsersCountInHeader: function () {
        let header = document.querySelector("div.bx-messenger-panel-chat-users");
        if (typeof (header) != "undefined" &&  !!header ) {
            return header.childElementCount - (this.getLabelPopupMore() ? 1 : 0);
        } else {
            return false;
        }
    }

};

CSM.SonetChat.getListChatHiddenUsers();

CSM.SonetChat.getListDeactivatedUsersChat = function() {

    let chatID = BXIM.messenger.getChatId();
    if (typeof(chatID) == 'undefined') return false;

    let userInChat = BXIM.messenger.userInChat[chatID];

    let deactivatedUsers = [];
    userInChat.forEach(function(element){
        if (BXIM.messenger.users[element].active == false) {
            deactivatedUsers.push(element);
        }
    });
    return deactivatedUsers;
}

CSM.SonetChat.getCurrentChatHiddenUsers = function () {
    let chatID = BXIM.messenger.getChatId();
    if (typeof(chatID) == 'undefined') return false;
    return this.listChatHiddenUsers[chatID];
}

CSM.SonetChat.getActiveHiddenUsers = function (hiddenUsersByCsm = [], deactivatedUsers = []) {
    return hiddenUsersByCsm.filter(x => !deactivatedUsers.includes(x));
}


CSM.SonetChat.getCountHiddens = function() {
    let hiddenByCsm = this.getCurrentChatHiddenUsers();
    let deactivated = this.getListDeactivatedUsersChat();
    if (!hiddenByCsm || !deactivated) return false;
    let activeHiddens = this.getActiveHiddenUsers(hiddenByCsm,deactivated);
    return (activeHiddens.length + deactivated.length);

}

CSM.SonetChat.getListUsersInChat = function() {
    if (typeof(BXIM) == 'undefined') return false;
    let chatID = BXIM.messenger.getChatId();
    if (typeof(chatID) == 'undefined') return false;
    return BXIM.messenger.userInChat[chatID];
}

CSM.SonetChat.getCountPopup = function() {
    let countUsersInChat = this.getListUsersInChat().length;
    let countUsersInHeader = this.getUsersCountInHeader();
    let countHiddens = this.getCountHiddens();
    return countUsersInChat - countHiddens - countUsersInHeader;
}

CSM.SonetChat.getLabelPopupMore = function() {
    return document.querySelector("span.bx-notifier-popup-user-more");
}


BX.addCustomEvent('onimdrawtab',  () => {
    CSM.SonetChat.hideUsersFromChat();
    console.log('hide-ok');
} );

BX.addCustomEvent('onimupdatecountermessage',  () => {
    CSM.SonetChat.hideUsersFromChat();
    console.log('hide-ok2');
} );

document.addEventListener('DOMContentLoaded', () => {
    if (typeof(beforeDOMContentLoaded) !== 'undefined') beforeDOMContentLoaded.disconnect();
    console.log('tttttt');
    //document.querySelector("#header-inner > div.header-search > div > div").style.display = "none";

    let body = document.querySelector("body");

    let observer = new MutationObserver(mutationRecords => {
        if (!!document.querySelector("#bx-messenger-popup-chat-users")) {
            CSM.SonetChat.hideUsersFromPopup();

        }
    });

    observer.observe(body, {
        childList: true,
    });
} );

