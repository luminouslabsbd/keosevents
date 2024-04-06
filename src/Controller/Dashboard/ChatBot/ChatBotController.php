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
        $chat_bot_lists = [];
        $user = $this->getUser();
        $authId = $user->getId();
        try {
            $response = $this->client->request("GET", $_ENV['CHATBOT_BASEURL']. '/template-chatbots-sys');
            $bots = $response->toArray();

            $response2 = $this->client->request("GET", $_ENV['CHATBOT_BASEURL'] . '/user-chatbots/' . $authId, [
                'json' => [
                    'category' => 'system'
                ]
            ]);
            $chat_bot_lists = $response2->toArray();

        } catch (\Exception $exception) {
            $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
        }

        return $this->render('Dashboard/ChatBot/train-text-bot.html.twig', [
            'bots' => $bots,
            'chat_bot_lists' => $chat_bot_lists,
        ]);

    }

    public function chatbot_train_attachment(TranslatorInterface $translator)
    {
        $bots = [];
        $chat_bot_lists = [];
        $user = $this->getUser();
        $authId = $user->getId();
        try {
            $response = $this->client->request("GET", $_ENV['CHATBOT_BASEURL']. '/template-chatbots-wevi');
            $bots = $response->toArray();

            $response2 = $this->client->request("GET", $_ENV['CHATBOT_BASEURL'] . '/user-chatbots/' . $authId, [
                'json' => [
                    'category' => 'weaviate'
                ]
            ]);
            $chat_bot_lists = $response2->toArray();

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


    public function chatbot_train_list_store(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $client = new Client();
        $data = $request->request->all();
        if ($data['bot_text'] != '') {
            try {
                $user = $this->getUser();
                $authId = $user->getId();

                $response = $client->request("POST", $_ENV['CHATBOT_BASEURL'] . '/create-chatbot-sys/' . $authId, [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        "bot_text" => $data['bot_text']
                    ]
                ]);

                $body = $response->getBody()->getContents();
                $responseData = json_decode($body, true);

                $sql = "INSERT INTO chatbot_lists (org_id, template_id, type, description, chatbot_id, chatbot_name, status) 
                VALUES (:org_id, :template_id, :type, :description, :chatbot_id, :chatbot_name, :status)";

                $params = [
                    'org_id'       => $authId,
                    'template_id'  => trim($data['bot_select']),
                    'type'         => 'text',
                    'description'  => trim($data['bot_text']),
                    'chatbot_id'   => $responseData['chatbotId'],
                    'chatbot_name' => $responseData['chatbotName'],
                    'status'       => 0,
                ];

                $statement = $entityManager->getConnection()->prepare($sql);
                $statement->execute($params);

                $this->addFlash('success', $responseData['chatbotName'] . $translator->trans(' chatbot has been created successfully'));
            } catch (RequestException $e) {
                $this->addFlash('error', $translator->trans('Chatbot cannot procced right now'));
            }
        } else {
            $this->addFlash('error', $translator->trans('Please write your text that train you chatbot'));
        }

        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer);
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

                $response = $client->request("POST", $_ENV['CHATBOT_BASEURL']. '/create-chatbot-weavi/'. $authId, $options);
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
            $response = $client->request("DELETE", $_ENV['CHATBOT_BASEURL'] . "/delete-chatbot?userId=$authId&chatbotId=$chatbotId");

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
