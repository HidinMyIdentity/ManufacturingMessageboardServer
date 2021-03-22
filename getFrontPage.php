<?php
include_once "db.php";
header('Content-Type: application/json');

$offset = filter_input(INPUT_GET, "offset", FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
if ($offset === false) {
    $offset = 0;
}
$posts = $DB->GetTopLevelPosts($offset);
WrapJson($posts);