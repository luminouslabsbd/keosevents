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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class ChatBotController extends Controller
{

    private $client;
    private $security;
    public function __construct(HttpClientInterface $client, Security $security)
    {
        $this->client = $client;
    }

    public function chatbot_train_text(TranslatorInterface $translator)
    {
        $bots = [];
        try {
            $response = $this->client->request("GET", $_ENV['CHAT_BOT_TEMPLATE_LIST']);
            $bots = $response->toArray();
        } catch (\Exception $exception) {
            $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
        }

        return $this->render('Dashboard/ChatBot/train-text-bot.html.twig', [
            'bots' => $bots,
        ]);
        
    }
    public function chatbot_train_attachment(TranslatorInterface $translator)
    {
        $bots = [];
        $chat_bot_lists = [];
        $user = $this->getUser();
        $authId = $user->getId();
        try {
            $response = $this->client->request("GET", $_ENV['CHAT_BOT_TEMPLATE_LIST']);
            $bots = $response->toArray();

            $response = $this->client->request("GET", $_ENV['CHAT_BOT_LIST'].'/'. $authId);
            $chat_bot_lists = $response->toArray();

        } catch (\Exception $exception) {
            $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
        }

        return $this->render('Dashboard/ChatBot/train-attachment-bot.html.twig', [
            'bots' => $bots,
            'chat_bot_lists' => $chat_bot_lists,
        ]);

    }

    public function chatbot_train_list()
    {
        return $this->render('Dashboard/ChatBot/chatbot_train_list.html.twig');
    }


    public function chatbot_train_list_store(Request $request, SessionInterface $session)
    {
        // 
    }

    public function chatbot_train_attachment_store(Request $request,  EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $client = new Client();
        $data = $request->request->all();

        if ($request->files->get('files') !== null) {
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
                $user = $this->getUser();
                $authId = $user->getId();

                $response = $client->request("POST", $_ENV['CHAT_BOT_TRAIN_ATTACH'].'/'. $authId, $options);
                $body = $response->getBody()->getContents();
                $responseData = json_decode($body, true);

                $sql = "INSERT INTO chatbot_lists (org_id, template_id, type, description, chatbot_id, chatbot_name, status) 
                VALUES (:org_id, :template_id, :type, :description, :chatbot_id, :chatbot_name, :status)";

                $params = [
                    'org_id'       => $authId,
                    'template_id'  => trim($data['bot_select']),
                    'type'         => 'file',
                    'description'  => trim($data['text']),
                    'chatbot_id'   => $responseData['chatbotId'],
                    'chatbot_name' => $responseData['chatbotName'],
                    'status'       => 0,
                ];

                $statement = $entityManager->getConnection()->prepare($sql);
                $statement->execute($params);

                $this->addFlash('success', $responseData['chatbotName'].$translator->trans(' chatbot has been created successfully'));
            } catch (RequestException $e) {
                $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
            }
        }else{
            $this->addFlash('error', $translator->trans('Please insert file'));
        }
        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }


    public function delete_chatbot_list(Request $request,$chatbotId, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $client = new Client();
        try {
            $user = $this->getUser();
            $authId = $user->getId();

            // api
            $response = $client->request("DELETE", $_ENV['CHAT_BOT_LIST_DELETE'] . "?userId=$authId&chatbotId=$chatbotId");

            // database
            $sql = "DELETE FROM chatbot_lists WHERE org_id = :org_id AND chatbot_id = :chatbot_id";
            $params = [
                'org_id' => $authId,
                'chatbot_id' => $chatbotId,
            ];
            $statement = $entityManager->getConnection()->prepare($sql);
            $statement->execute($params);
            
            $this->addFlash('success', $translator->trans('Chatbot deleted successfully'));
        } catch (RequestException $e) {
            $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
        }
        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
    }
    
    

}
