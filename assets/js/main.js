// global variable

var ERRCODES = null;
var CACHECODES = null;

var user = new MainUser();

fetch("/assets/js/ErrCodes.json").then(async function (r) {
    ERRCODES = await r.json();

    user.retrieveSession();
});


let pages = new uTemplate.template(Dom.id("pages")); // on créer un template des pages
let mainMenu = new uTemplate.template(Dom.id("mainmenu"));

Dom.addListener(window, 'pushstate', function () {// detect when url is changing
    // actualise data
    pages.update({ url: window.location.pathname });
});

user.addListener(MainUser.EVENT_STATE_CHANGE, function () { mainMenu.update(); });

pages.update({
    url: window.location.pathname
});

mainMenu.update({
    user
});
