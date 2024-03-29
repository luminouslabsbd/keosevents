<?php

namespace App\Controller\Dashboard\ChatBot;

use Exception;
use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Stream;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        }

        // if ($type === "attachment") {

        //     $client = new Client();
        //     $data = $request->request->all();

        //     $multipart = [];
        //     $multipart[] = $data;

        //     if ($request->files->has('file')) {
        //         $files = $request->files->get('file');
        //         for ($i = 0; $i < count($files); $i++) {
        //             if (!isset($files[$i])) {
        //                 continue;
        //             }
        //             $fileContent = file_get_contents($files[$i]->getPathname());
        //             $multipart[] = [
        //                 'name'     => 'file[]',
        //                 'contents' => $fileContent,
        //                 'filename' => $files[$i]->getClientOriginalName()
        //             ];
        //         }
        //     }
        //     dd($multipart);
        //     $response = $client->request("POST", $_ENV['CHAT_BOT_TRAIN_ATTACH'], [
        //         'multipart' => $multipart,
        //     ]);

        //     dd($response->getBody()->getContents());   

        // }

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
    
    

}
