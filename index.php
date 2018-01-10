<?php

use App\CustomClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use TelegramBot\Api\Types\Message;

require_once "vendor/autoload.php";

$adminFile = __DIR__."/adminid";
$userFile  = __DIR__."/userid";
$adminId   = intval(file_get_contents($adminFile));
$userId    = intval(file_get_contents($userFile));

try {
    /** @var CustomClient $bot */
    $bot = new CustomClient($_ENV['BOT_API_TOKEN'], null); // $_ENV['TRACKER_API_KEY']
    $bot->message(
        function (Message $message) use ($bot, $adminId, $userFile, $userId) {
            // Handle admin messages
            if (!empty($adminId)) {
                if ($message->getChat()->getId() === $adminId && $adminId > 0) {
                    $userId = intval(file_get_contents($userFile));
                    if ($userId > 0) {
                        // Send admin response to guest
                        $bot->sendMessage($userId, $message->getText());
                        $bot->sendMessage($adminId, "Message envoyé");
                    } else {
                        // Welcome message if no guest
                        $bot->sendMessage($adminId, "You are admin, please connect a guest");
                    }

                    return false;
                }
            } else {
                // Welcome message if no admin
                $bot->sendMessage(
                    $message->getChat()->getId(),
                    "Il n'y a pas d'admin. Entrez /admin **** pour devenir admin"
                );
            }

            // Handle new guest
            if ($userId === 0 || $userId !== $message->getChat()->getId()) {
                file_put_contents($userFile, $message->getChat()->getId());
                $bot->sendMessage($message->getChat()->getId(), 'You are a guest');
            }
            // Transfert guest message to admin
            if ($adminId > 0) {
                $bot->sendMessage($adminId, '> '.$message->getText());
            }

            return false;
        }
    );
    $bot->command(
        'stop',
        function (Message $message) use ($bot, $userFile, $adminId) {
            file_put_contents($userFile, 0);
            $bot->sendMessage($message->getChat()->getId(), "Guest leaving..");
            if ($adminId > 0) {
                $bot->sendMessage($adminId, "Guest left");
            }

            return false;
        }
    );
    $bot->command(
        'admin',
        function (Message $message) use ($bot, $adminFile, $adminId) {

            if ($adminId === $message->getChat()->getId() && $adminId > 0) {
                file_put_contents($adminFile, 0);
                $bot->sendMessage(
                    $message->getChat()->getId(),
                    "You are not an admin anymore"
                );

                return false;
            }
            if ($message->getText() === "/admin ".date("Ymd")) {
                file_put_contents($adminFile, $message->getChat()->getId());
                $bot->sendMessage(
                    $message->getChat()->getId(),
                    "You are now an admin as id: ".$message->getChat()->getId()
                );

                return false;
            }

            $bot->sendMessage(
                $message->getChat()->getId(),
                "You id: ".$message->getChat()->getId()." Bad message: '".$message->getText()."'"
            );

            return false;
        }
    );

    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
    $log = new Logger('name');
    $log->pushHandler(new StreamHandler(__DIR__.'/var/log/'.date("Y-m-d").'.log', Logger::DEBUG));
    $log->addInfo($e->getMessage(), ["exception" => $e]);

}