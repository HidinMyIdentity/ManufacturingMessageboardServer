<?php
include_once "db.php";
header('Content-Type: application/json');
$reply = filter_input(INPUT_POST, "reply", FILTER_VALIDATE_INT);
$content = filter_input(INPUT_POST, "content");
$tripcode = filter_input(INPUT_POST, "tripcode");

// message processing
$content = preg_replace("/\n\n\n+/", "\n\n", $content);
$content = preg_replace("/  +/", " ", $content);

if (empty($reply) || $reply < -1) {
    CauseError("'$reply' must be specified and -1 or above");
} elseif ($reply !== -1 && !$DB->GetPost($reply)) {
    CauseError("The post you are replying to does not exist");
} elseif (mb_strlen($content) > 5000) {
    CauseError("A message cannot be longer than 5000 characters.");
} elseif (mb_strlen($content) <4) {
    CauseError("A message cannot be shorter than 4 characters");
} elseif (mb_strlen($tripcode) > 200) {
    CauseError("A tripcode does not need to be more than 200 characters");
}

if ($reply === -1) {
    $newid = $DB->CreatePost($content, GenerateTripcode($tripcode));
    WrapJson(["rowid"=>$newid]);
}else {
    $newid = $DB->CreateReply($content, $reply, GenerateTripcode($tripcode));
    WrapJson(["rowid"=>$newid]);
}
