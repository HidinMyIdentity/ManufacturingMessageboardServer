<?php
include_once "db.php";
header('Content-Type: application/json');
$postid = filter_input(INPUT_GET, "postid", FILTER_VALIDATE_INT);
if (empty($postid) || $postid < 0) {
    CauseError("'postid' must be specified and a non-negative number");
}

$post = $DB->GetPost($postid);

if (!$post) {
    CauseError("Post not found");
}

WrapJson($post);