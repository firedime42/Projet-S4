// global variable

var ERRCODES = null;
var CACHECODES = null;

var user = new MainUser();
user.addListener(MainUser.EVENT_LOGGED_IN, function () {
    console.log('coucou');
    ListGroupe.__clear();
    ListGroupe.update();
});

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

Dom.onClick(window, function (e) {
    let deep = e.path.length;
    let i = 0;
    while (i < deep && (
        !e.path[i].classList || 
        quickIndexOf(e.path[i].classList, "dropdown") == -1)
    ) i++;
    let dropdown_menu = (i < deep) ? Dom.find(".dropdown-menu", e.path[i])[0] : null;

    // masque all actived dropdown
    let menus = Dom.find('.dropdown-menu.show');
    let nb_menus = menus.length;
    for (let i = 0; i < nb_menus; i++)
        if (menus[i] != dropdown_menu)
            Dom.removeClass(menus[i], "show");

    // toggle menu
    if (dropdown_menu) Dom.toggleClass(dropdown_menu, "show");
});
