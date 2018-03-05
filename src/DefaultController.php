<?php
namespace App;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TelegramBot\Api\Types\Message;

/**
 * Handle telegram messages from client and send answers
 * @package App
 *
 */
class DefaultController
{
    /**
     * @param $api
     * @param string $file
     * @return string
     */
    protected function getFileLocation($api, $file = "admin")
    {
        $smallApi = substr($api, 0, 2);
        return __DIR__ . "/../var/" . $smallApi . "_" . $file . "id";
    }

    /**
     * @param $api
     * @param $file
     * @return int
     */
    protected function getId($api, $file)
    {
        $file = $this->getFileLocation($api, $file);
        if (false === file_exists($file)) {
            return 0;
        }
        return intval(file_get_contents($file));
    }

    /**
     * @param $api
     * @param $file
     * @param $id
     * @return bool|int
     */
    protected function saveId($api, $file, $id)
    {
        return file_put_contents($this->getFileLocation($api, $file), intval($id));
    }

    /**
     * @param Request $request
     * @param $apiKey
     * @return JsonResponse
     */
    public function handle(Request $request, $apiKey)
    {
        $adminId = $this->getId($apiKey, "admin");
        $userId = $this->getId($apiKey, "user");
        $that = $this;
        /** @var CustomClient $bot */
        $bot = new CustomClient($apiKey, null); // $_ENV['TRACKER_API_KEY']
        $bot->message(
            function (Message $message) use ($bot, $adminId, $userId, $apiKey, $that) {
                // Handle admin messages
                if (!empty($adminId)) {
                    if ($message->getChat()->getId() === $adminId && $adminId > 0) {
                        $userId = $that->getId($apiKey, "user");
                        if ($userId > 0) {
                            // Send admin response to guest
                            $bot->sendMessage($userId, $message->getText());
                            $bot->sendMessage($adminId, "Message envoyé");
                            return false;
                        } else {
                            // Welcome message if no guest
                            $bot->sendMessage($adminId, "You are admin, please connect a guest");
                            return false;
                        }

                        return false;
                    }
                } else {
                    // Welcome message if no admin
                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        "Il n'y a pas d'admin. Entrez /admin **** pour devenir admin"
                    );
                    return false;
                }

                // Handle new guest
                if ($userId === 0 || $userId !== $message->getChat()->getId()) {
                    $that->saveId($apiKey, "user", $message->getChat()->getId());
                    $bot->sendMessage($message->getChat()->getId(), 'You are a guest');
                    return false;
                }
                // Transfert guest message to admin
                if ($adminId > 0) {
                    $bot->sendMessage($adminId, '> ' . $message->getText());
                    return false;
                }

                return false;
            }
        );
        $bot->command(
            'stop',
            function (Message $message) use ($bot, $adminId, $apiKey, $that) {
                $that->saveId($apiKey, "admin", 0);
                $bot->sendMessage($message->getChat()->getId(), "Guest leaving..");
                if ($adminId > 0) {
                    $bot->sendMessage($adminId, "Guest left");
                }

                return false;
            }
        );
        $bot->command(
            'admin',
            function (Message $message) use ($bot, $adminId, $apiKey, $that) {

                if ($adminId === $message->getChat()->getId() && $adminId > 0) {
                    $that->saveId($apiKey, "admin", 0);
                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        "You are not an admin anymore"
                    );

                    return false;
                }
                if ($message->getText() === "/admin " . date("Ymd")) {
                    $that->saveId($apiKey, "admin", $message->getChat()->getId());
                    $bot->sendMessage(
                        $message->getChat()->getId(),
                        "You are now an admin as id: " . $message->getChat()->getId()
                    );

                    return false;
                }

                $bot->sendMessage(
                    $message->getChat()->getId(),
                    "You id: " . $message->getChat()->getId() . " Bad message: '" . $message->getText() . "'"
                );

                return false;
            }
        );
        $bot->run();
        return new JsonResponse("OK");
    }
}