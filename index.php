<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- librairies externes -->
    <!--
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
    -->
    <link href="/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/bootstrap/bootstrap.bundle.min.css" rel="stylesheet">
    <script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- pack d'icon -->
    <link rel="stylesheet" type="text/css" href="/assets/icopack/style.css"></link>
    
    <!-- librairies -->
    <script src="/assets/lib/Dom.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/lib/prism.css"></link>
    <script src="/assets/lib/prism.min.js"></script>
    <script src="/assets/lib/wavesurfer.min.js"></script>
    <script src="/assets/lib/sha256.min.js"></script>
    <script src="/assets/lib/pattern.js"></script>
    <script src="/assets/lib/uTemplate.js"></script>
    <script src="/assets/lib/urlmanager.js"></script>
    <script src="/assets/lib/listenable.js"></script>
    <script src="/assets/lib/htmllist.js"></script>
    <script src="/assets/lib/autotime.js"></script>

    <!-- script -->
    <script src="/assets/js/utils.js"></script>
    <script src="/assets/js/autotime.func.js"></script>
    <script src="/assets/js/utemplate.func.js"></script>

    <!-- models -->
    <script src="/assets/js/models/mainuser.js"></script>
    <script src="/assets/js/models/user.js"></script>
    <script src="/assets/js/models/listgroupe.js"></script>
    <script src="/assets/js/models/folder.js"></script>
    <script src="/assets/js/models/wazapfile.js"></script>

    <script src="/assets/js/models/groupe.js"></script>
    <script src="/assets/js/models/chat.js"></script>
    <script src="/assets/js/models/search.js"></script>

    <!-- feuille de style -->
    <link rel="stylesheet" type="text/css" href="/assets/css/config.css"></link>

    <link rel="stylesheet" type="text/css" href="/assets/css/main.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/nav.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/home.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/account.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/groupe.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/explorer.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/chat.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/group.settings.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/account.params.compte.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/profil.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/file.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/dashboard.css"></link>

</head>
<body data-theme="light">
    <!-- nav bar -->
    <nav id="mainmenu" class="navbar navbar-expand-md">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" onclick="Dom.toggleClass('#navbar-menu', 'show');" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="/">Shared</a>
            <div id="navbar-menu" class="navbar-collapse collapse">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="/app/">Application</a></li>
                </ul>
                <div class="navbar-account ms-auto">
                    <ul class="navbar-nav ms-auto" data-visible="{{ EQUALS user.isLoggedIn false }}">
                        <li class="nav-item"><a class="nav-link" href="/account/login">Se connecter</a></li>
                        <li class="nav-item"><a class="nav-link" href="/account/register">Créer un compte</a></li>
                    </ul>
                    <div class="navbar-nav" data-visible="{{ user.isLoggedIn }}">
                        <div class="nav-item dropdown">
                            <div class="profil"><div class="avatar" data-avatar=""></div><span class="px-2">{{ user.username }}</span></div>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/profil/">Mon Profil</a>
                                <a class="dropdown-item" href="/account/params/">Paramètres</a>
                                <a class="dropdown-item" href="/account/logout">Déconnexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <section id="pages">

        <div class="page" data-visible="{{ URLLIKE url '/' }}">
            {{ INCLUDE this.currentNode "/template/home.html" }}
        </div>

        <!-- app section -->
        <div class="page" data-visible="{{ URLLIKE url '/app/*' }}">
            {{ INCLUDE this.currentNode "/template/app.html" }}
        </div>

        <!-- login / register -->
        <div class="page" data-visible="{{ URLLIKE url '/account/*' }}">
            {{ INCLUDE this.currentNode "/template/account.html" }}
        </div>

        <!-- profil -->
        <div class="page scrollable" data-visible="{{ URLLIKE url '/profil/*' }}">
            {{ INCLUDE this.currentNode "/template/profil.html" }}
        </div>

        <!-- 404 page -->
        <div class="page" data-visible="{{ URLLIKE url '/404' }}"></div>

        <!-- confirmation -->
        {{ INCLUDE this.currentNode "/template/confirme.html" }}
    </section>

    <script src="/assets/js/main.js"></script>
</body>
</html>