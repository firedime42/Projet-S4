/**
 * Promise which can be controled from exterior
 */
function ExPromise() {
    var rs, rj;
    var p = new Promise(async function (resolve, reject) { rs = resolve; rj = reject; });
    p.resolve = rs;
    p.reject = rj;
    return p;
}

function ExternPrivatePromise() {
    var rs, rj;
    var p = new Promise(async function (resolve, reject) { rs = resolve; rj = reject; });
    return { promise: p, resolve: rs, reject: rj };
}

/**
 * Génère une nouvelle instance d'{Erreur} avec le code entrée en paramètre
 * @param {Number} errcode le code de l'erreur
 */
function _error(errcode) {
    let err = new Error();
    err.code = errcode;
    return err;
}

function sleep(duration) {
    return new Promise(function (resolve, reject) {
        setTimeout(resolve, duration);
    });
}

/**
 * Envoi une requete au serveur et traite sa réponse
 * @param {String} url 
 * @param {Object} post_data 
 */
function request(url, post_data) {
    var rdata = ExPromise();

    fetch(url, {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(post_data)
    }).then(async function (r) {
        try {
            let rd = r.json();
            if (rd.success) rdata.resolve(rd);
            else rdata.resolve(_error(rd.error));
        } catch (e) {
            console.error(e);
            rdata.resolve(_error(0));
        }
    }).catch(function (...args) {
        console.error(...args);   // display error in console
        rdata.resolve(_error(0)); // return unknown error for code
    });

    return rdata;
}


/**
 * Fonction de recherche rapide dans une liste
 */
function quickIndexOf(array, item) {
    var i = 0;
    var nb_items = array.length;
    while (i < nb_items && array[i] != item) i++;
    return (i < nb_items) ? i : -1;
}

/**
 * List les mutations necessaire pour passer du tableau d'entier 1 au deuxieme
 * trie les deux tableaux au passage.
 * @param {Array<Number>} arr1 
 * @param {Array<Number>} arr2 
 * @returns added, removed
 */
function arrayMutations(arr1, arr2) {
    let added = [], removed = [];
    arr1.sort((a, b) => a - b);
    arr2.sort((a, b) => a - b);

    let i = 0;
    let j = 0;

    let nb_val1 = arr1.length;
    let nb_val2 = arr2.length;

    while (i < nb_val1 && j < nb_val2) {
        if (arr1[i] == arr2[j]) i++, j++;
        else if (arr1[i] < arr2[j]) removed.push(arr1[i++]);
        else if (arr1[i] > arr2[j]) added.push(arr2[j++]);
    }

    for (;i < nb_val1; i++) removed.push(arr1[i++]);
    for (;j < nb_val2; j++) added.push(arr2[j++]);

    return { added, removed };
}

/**
 * defined AsyncFunction constructor if not defined
 */
var AsyncFunction = AsyncFunction || (async function () {}).constructor;
