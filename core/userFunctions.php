<?php

/**
 * Fonction récupérant l'utilisateur par son identifiant
 */
function getUserById($user_id) {
    global $database;
    
    $user = null;
    
    $query_nb_messages = "SELECT COUNT(*) FROM message m WHERE m.author = $user_id AND deleted = 0";
    $query_nb_files = "SELECT COUNT(*) FROM file f WHERE f.creator_id = $user_id";
    $query_nb_groups = "SELECT COUNT(*) FROM groupUser gu WHERE gu.user_id = $user_id AND status = 'accepted'";
    
    $query_user = "SELECT id, username AS name, creation_date, biography, ($query_nb_messages) AS nb_messages, ($query_nb_files) AS nb_files, ($query_nb_groups) AS nb_groups FROM user WHERE id = $user_id";

    $res = mysqli_query($database, $query_user);
    
    if ($res && mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        $user = [
            "id" => (int) $row["id"],
            "name" => $row["name"],
            "biography" => trim($row["biography"]),
            "nb_messages" => (int) $row["nb_messages"],
            "nb_files" => (int) $row["nb_files"],
            "nb_groups" => (int) $row["nb_groups"],
            "creation_date" => (int) $row["creation_date"]
        ];
    }

    return $user;
}