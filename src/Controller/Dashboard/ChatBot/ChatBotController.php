<?php

namespace App\Controller\Dashboard\ChatBot;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatBotController extends Controller
{

    private $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function createChatBotTrain($type): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $response = $this->client->request("GET", $_ENV['CHAT_BOT_LIST']);
            $bots = $response->toArray();
        } catch (\Exception $exception) {
            dd($exception);
        }

        if ($type === "text") {
            return $this->render('Dashboard/ChatBot/train-text-bot.html.twig', [
                'bots' => $bots,
            ]);
        }

        if ($type === "attachment") {
            return $this->render('Dashboard/ChatBot/train-attachment-bot.html.twig', [
                'bots' => $bots,
            ]);
        }

        dd("Type Not Found");
    }

    public function createChatBotTrainStore(Request $request, $type)
    {
        if ($type === "text") {
            $data = $request->request->all();
            $response = $this->client->request("POST", $_ENV['CHAT_BOT_TRAIN_TEXT'], [
                'body' => $data,
            ]);
        }
        if ($type === "attachment") {
            $data = $request->request->all();
            $response = $this->client->request("POST", $_ENV['CHAT_BOT_TRAIN_ATTACH'], [
                'body' => $data,
            ]);
        }
        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }
}
