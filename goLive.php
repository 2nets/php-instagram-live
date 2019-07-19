<?php

include_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/config.php';

use InstagramAPI\Instagram;
use InstagramAPI\Request\Live;
use InstagramAPI\Response\FinalViewerListResponse;
use InstagramAPI\Response\Model\Comment;

function main($st, $id, $username, $password)
{
    $ig = Utils::loginFlow($username, $password);
    try {
        if (!$ig->isMaybeLoggedIn) {
            Utils::dump();
            exit(1);
        }
        if ($st == 'stop') {
            echo "Creating Livestream stoped";
            $ig->live->end($id);
            $ig->live->addToPostLive($id);
        } else if ($st == 'start') {
            try {
                if (Utils::isRecovery() && $ig->live->getInfo(Utils::getRecovery()['broadcastId'])->getBroadcastStatus() === 'stopped') {
                    Utils::deleteRecovery();
                }
            } catch (Exception $e) {
                Utils::deleteRecovery();
            }
            $obsAutomation = true;
            if (!Utils::isRecovery()) {

                $stream = $ig->live->create(760, 1028);
                $broadcastId = $stream->getBroadcastId();

                $streamUploadUrl = ($stream->getUploadUrl());
                $split = preg_split("[" . $broadcastId . "]", $streamUploadUrl);

                $streamUrl = trim($split[0]);
                $streamKey = trim($broadcastId . $split[1]);
            } else {
                $recoveryData = Utils::getRecovery();
                $broadcastId = $recoveryData['broadcastId'];
                $streamUrl = $recoveryData['streamUrl'];
                $streamKey = $recoveryData['streamKey'];

            }
            echo "*" . $broadcastId;
            echo "*" . $streamUrl;
            echo "*" . $streamKey;
            echo "*";
            if (!Utils::isRecovery()) {
                $ig->live->start($broadcastId);
                return "true";
            }
        }
    } catch (Exception $e) {
        echo 'Error While Creating Livestream: ' . $e->getMessage() . "\n";
        Utils::dump($e->getMessage());
        exit(1);
    }
}

if ($argv[1] == '1') {
    main('start', '0', $argv[2], $argv[3]);

} else if ($argv[1] == '0') {
    main('stop', $argv[2], $argv[3], $argv[4]);
    //echo $argv[2];
}

?>

