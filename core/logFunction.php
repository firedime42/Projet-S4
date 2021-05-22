<?php

require_once("sql.php");

/**
 * Ajoute un log dans la base de donnée
 */
function log($group_id, $actor_id, $action, $descr) {
    global $database;
    $time = now();
    $str_action = mysqli_real_escape_string($database, $action);
    $str_descr = mysqli_real_escape_string($database, $descr);
    $query = "INSERT INTO log (group_id, actor, action, descr, timestamp) VALUES ($group_id, $actor_id, '$str_action', '$str_descr', $timestamp)";
    return mysqli_query($database, $query);
}

/**
 * Supprime les logs trops agées
 * @param group_id : identifiant du group ou '*' pour tous les groupes
 * @param age : age maximum des logs au dela duquel elles doivent être supprimées
 */
function removeOldLogs($group_id = '*', $age = 2678400000) {
    global $database;
    $time_limit = now() - $age;
    $group_selector = $group_id == '*' ? '1' : "group_id = $group_id";
    $query = "DELETE FROM log WHERE $group_selector AND timestamp < $time_limit";
    mysqli_query($database, $query);
}

/**
 * récupère les logs dans la base de donnée
 * @param group_id : identifiant du groupe
 * @param from : date superieur (non défini = le plus recent)
 * @param to : nombre de log à charger (non défini = tous)
 */
function getLogs($group_id, $from = null, $to = null) {
    global $database;
    $limit_from = (isset($from)) ? "timestamp < $from" : "1";
    $limit_to = (isset($to)) ? "LIMIT $to" : "";
    $query = "SELECT *, a.id AS actor_id, a.username AS actor_name FROM log JOIN user a ON a.id = actor WHERE group_id = $group_id AND $limit_from ORDER BY id DESC $limit_to";
    $res = mysqli_query($database, $query);

    $logs = array();

    while ($row = mysqli_fetch_array($res)) {
        $logs[] = [
            "id" => (int) $row["id"],
            "actor" => [
                "id" => (int) $row["actor_id"],
                "name" => $row["actor_name"]
            ],
            "action" => $row["action"],
            "description" => $row["descr"],
            "timestamp" => (int) $row["timestamp"]
        ];
    }

    return $logs;
}
