// # GROUPE.php

// ## creation d'un groupe
let req = {
    "action": "create",
    "name": String,
    "descr": String
};
let res =
    {
        "success": true,
        "id": Number
    }
;

// ## lister les groupes
let req = {
    "action": "listGroupe",
    "type": "public" / "in" / "prop",
};
let res = {
    "success": true,
    "groups": [
        { "id": Number, "name": String }
        ...
    ]
};


// ## récuperer des informations sur un groupe
let req = {
    "action": "info",
    "id": Number,
    "type": "simple" / "detaille"
};