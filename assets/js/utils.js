
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
 * defined AsyncFunction constructor if not defined
 */
var AsyncFunction = AsyncFunction || (async function () {}).constructor;
