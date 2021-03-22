<?php
include_once "db.php";
header('Content-Type: application/json');
$rowid = filter_input(INPUT_GET, "rowid", FILTER_VALIDATE_INT);

if (empty($rowid) || $rowid < 0) {
    CauseError("'rowid' must be specified and 0 or above");
} elseif (!$DB->GetPost($rowid)) {
    CauseError("The post you are looking for does not exist");
}
$posts = $DB->GetReplies($rowid);
WrapJson($posts);

