<?php

namespace App\Controller\Dashboard\ChatBot;

use Exception;
use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatBotController extends Controller
{

    private $client;
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function createChatBotTrain($type)
    {
        $bots = [];
        try {
            $response = $this->client->request("GET", $_ENV['CHAT_BOT_LIST']);
            $bots = $response->toArray();
        } catch (\Exception $exception) {
            
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
        dd('Not found');
    }

    public function createChatBotTrainStore(Request $request, $type, SessionInterface $session)
    {
        if ($type === "text") {
            $data = $request->request->all();
            $response = $this->client->request("POST", $_ENV['CHAT_BOT_TRAIN_TEXT'], [
                'body' => $data,
            ]);
        
        }


        if ($type === "attachment") {
            $data = $request->request->all();

            // if ($request->files->has('file')) {
            //     $files = $request->files->get('file');

            //     foreach ($files as $file) {
            //         try {
            //             $response = $this->client->request("POST", "https://voicebot.keoscx.com/keosgpt/api/chatbot-create-attch", [
            //                 'body' => [
            //                     'file' => fopen($file->getPathname(), 'r'),
            //                     'name' => 'hello'
            //                 ],
            //             ]);

            //             // Handle API response for each file
            //             $statusCode = $response->getStatusCode();
            //             $content = $response->getContent();
            //             dd($content);

            //             // Process response...
            //         } catch (Exception $e) {
            //             dd($e->getMessage());
            //         }
            //     }
            // } else {
            //     // Handle case where 'file' is not found in the request
            // }

            $response = $this->client->request("POST", $_ENV['CHAT_BOT_TRAIN_TEXT'], [
                'body' => $data,
            ]);
        }

       try {
            $chatbotData = $response->toArray();
            $chatbotId = $chatbotData['chatbotId'];
            $session->set('chatbotId', $chatbotId);
       } catch (\Throwable $th) {
            //throw $th;
       }

        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }


        // public function trainBot(Request $request){
        //     $url = 'https://2e20-103-203-95-212.ngrok-free.app/api/chatbot-create';
        //     $data = array(
        //         'key1' => 'value1',
        //         'key2' => 'value2'
        //     );

        //     $ch = curl_init($url);
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($ch, CURLOPT_POST, true);
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

        //     $response = curl_exec($ch);
        //     curl_close($ch);

        //     $result = json_decode($response);

        //     $response = $this->client->request("GET", $_ENV['CHAT_BOT_LIST']);
        //     $bots = $response->toArray();

        //     return $this->render('Dashboard/ChatBot/train-text-bot.html.twig', [
        //         'chatbotId' => $result->chatbotId,
        //         'bots' => $bots,
        //     ]);

        // }

}
