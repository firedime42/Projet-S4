let data = {
    page: {
        // constantes
        APP: /^\/app(\/.*)?/,
        ACCOUNT: /^\/account(\/.*)?/,

        // current
        url: window.location.pathname
    }
};

let pages = new uTemplate.template(Dom.id("pages")); // on créer un template des pages

Dom.addListener(window, 'pushstate', function (event) {// detect when url is changing

    // actualise data
    data.page.url = window.location.pathname;
    pages.update(data);
});

pages.update(data);
