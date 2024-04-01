<?php

namespace App\Controller\Dashboard\ChatBot;

use Exception;
use Throwable;
use GuzzleHttp\Utils;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class ChatBotController extends Controller
{

    private $client;
    private $security;
    public function __construct(HttpClientInterface $client, Security $security)
    {
        // $this->authCheckWithRole($security);
        $this->client = $client;
    }

    // private function authCheckWithRole($security){
    //     $user = $security->getUser();
    //     if ($user) {
    //         $username = $user->getUsername();
    //         dd($username);
    //     }else{
    //         return $this->redirectToRoute('ll_signin');
    //     }
    // }

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

            $chatbotData = $response->toArray();
            $chatbotId = $chatbotData['chatbotId'];
            $session->set('chatbotId', $chatbotId);
        }

        if ($type === "attachment") {
            $client = new Client();
            $data = $request->request->all();

            if ($request->files->has('files')) {
                $files = $request->files->get('files');

                $options = [
                    'multipart' => [
                        [
                            'name'     => 'files',
                            'contents' => fopen($files->getRealPath(), 'r'),
                            'filename' => $files->getClientOriginalName(),
                            'headers'  => [
                                'Content-Type' => $files->getClientMimeType()
                            ]
                        ]
                    ]
                ];

                try {
                    $response = $client->request("POST", $_ENV['CHAT_BOT_TRAIN_ATTACH'], $options);
                    $body = $response->getBody()->getContents();
                    $responseData = json_decode($body, true);
                    $chatbotId = $responseData['chatbotId'];
                    $session->set('chatbotId', $chatbotId);
                } catch (RequestException $e) {
                    dd($e->getMessage());
                }
            }
        }



        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }
    
    

}
