<?php

//require_once dirname(__FILE__).'/core/modules/mainuser.php';

?>

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
    <link href="/assets/bootstrap/bootstrap.bundle.min.css" rel="stylesheet">
    <script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
    
    <!-- librairies -->
    <script src="/assets/lib/Dom.js"></script>
    <script src="/assets/lib/pattern.js"></script>
    <script src="/assets/lib/uTemplate.js"></script>
    <script src="/assets/lib/urlmanager.js"></script>

    <!-- script -->
    <script src="/assets/js/utils.js"></script>
    <script src="/assets/js/utemplate.func.js"></script>

    <!-- models -->
    <script src="/assets/js/models/listenable.js"></script>
    <script src="/assets/js/models/user.js"></script>

    <!-- feuille de style -->
    <link rel="stylesheet" type="text/css" href="/assets/css/main.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/nav.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css"></link>
    <link rel="stylesheet" type="text/css" href="/assets/css/account.css"></link>
</head>
<body data-theme="light">
    <!-- nav bar -->
    <nav class="navbar navbar-expand-md">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" onclick="Dom.toggleClass('#main-navbar', 'show');" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="/app/">Shared</a>
            <div class="navbar-collapse collapse" id="main-navbar">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="/app/">Acceuil</a></li>
                </ul>
                <div class="navbar-account ms-auto">
                    <ul class="navbar-nav ms-auto" data-visible="{{ EQUALS user.isLogged false }}">
                        <li class="nav-item"><a class="nav-link" href="/account/login">Se connecter</a></li>
                        <li class="nav-item"><a class="nav-link" href="/account/register">Créer un compte</a></li>
                    </ul>
                    
                </div>
            </div>
        </div>
    </nav>

    <section id="pages">
        <!-- app section -->
        <div class="page" data-visible="{{ REGMATCH page.url page.APP }}">
            {{ INCLUDE this.currentNode "/template/app.html" }}
        </div>

        <!-- login / register -->
        <div class="page" data-visible="{{ REGMATCH page.url page.ACCOUNT }}">
            {{ INCLUDE this.currentNode "/template/account.html" }}
        </div>

        <!-- 404 page -->
        <div class="page" data-visible="{{ EQUALS page.selected page.404 }}"></div>
    </section>

    <script src="/assets/js/main.js"></script>
</body>
</html>