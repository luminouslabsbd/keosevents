<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\GoogleMeeting;
use App\Service\GoogleMeetService;
use App\Service\TestZoomService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ZoomService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;



class ApiIntegrationController extends Controller {

    /**
     * @Route("/zoom/schedule/meeting", name="zoom_schedule_meeting", methods="GET")
     */
    public function index() {
        $timezones = timezone_identifiers_list();
        return $this->render('Dashboard/Shared/ApiIntegration/zoom.html.twig',[
            'timezones'    => $timezones,
        ]);
    }


    /**
     * @Route("/zoom/schedule/meeting/post", name="zoom_data_post", methods="POST")
     */
    public function createMeetingZoom(Request $request, EntityManagerInterface $entityManager)
    {
        $input = $request->request->all();
        $date             = date('Y-m-d\TH:i:s', strtotime($input['date_time']));
        $duration         = $input['hour'] * 60 + $input['minute'];
        $password         = $input['password'] ?? '';

        $teams_esta       = isset($input['setting']['teams_esta']) ? true : false;
        $host_start_video = isset($input['setting']['host_start_video']) ? true : false;
        $start_video      = isset($input['setting']['start_video']) ? true : false;
        $auto_mute        = isset($input['setting']['auto_mute']) ? true : false;
        $waiting_room     = isset($input['setting']['waiting_room']) ? true : false;

        $data = [
            'topic' => $input['topic'],
            'type' => 2, // Scheduled meeting
            'start_time' => $date,
            'duration' => $duration, // Meeting duration in minutes
            'timezone' => $input['timezone'],
            'agenda' => $input['description'],
            "password" => $password,
            "settings" => [
                'host_video' => $host_start_video,
                'participant_video' => $start_video,
                'mute_upon_entry' => $auto_mute,
                'waiting_room' => $waiting_room,
            ],
        ];


        $user = $this->getUser();
        $userId = $user->getId();
        $apiType = 'zoom';
        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
            'user_id' => $userId,
            'api_type' => $apiType,
        ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $zoom_data = $statementSelect->fetch();

        $zoomService = new ZoomService($zoom_data['zoom_account_id']??'', $zoom_data['zoom_clint_id']??'', $zoom_data['zoom_clint_secret']??'');
        $response = $zoomService->createMeeting($data);
        return $this->redirectToRoute('dashboard_organizer_venue_add');
        // return new JsonResponse($response);
    }


    /**
     * @Route("/google/schedule/meeting", name="google_schedule_meeting", methods="GET")
     */
    public function googleMeeting()
    {
        $timezones = timezone_identifiers_list();
        return $this->render('Dashboard/Shared/ApiIntegration/google.html.twig', [
            'timezones'    => $timezones,
        ]);
    }

    /**
     * @Route("/google/schedule/meeting/post", name="google_data_post", methods="POST")
     */
    public function createGoogleMeeting(Request $request, EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $input            = $request->request->all();
        $date             = date('Y-m-d\TH:i:s', strtotime($input['date_time']));
        $duration         = ($input['hour'] * 60 + $input['minute']) *60;
        $end_date_sec     = $duration + strtotime($input['date_time']);
        $end_date         = date('Y-m-d\TH:i:s', $end_date_sec);
        $password         = $input['password'] ?? '';

        $host_start_video = isset($input['setting']['host_start_video']) ? true : false;
        $start_video      = isset($input['setting']['start_video']) ? true : false;
        $auto_mute        = isset($input['setting']['auto_mute']) ? true : false;
        $waiting_room     = isset($input['setting']['waiting_room']) ? true : false;
        $data = [
            'topic'      => $input['topic'],
            'type'       => 2, // Scheduled meeting
            'start_time' => $date,
            'end_date'   => $end_date,
            'duration'   => $duration, // Meeting duration in sec
            'timezone'   => $input['timezone'],
            'agenda'     => $input['description'],
            "password"   => $password,
            "settings"   => [
                'host_video' => $host_start_video,
                'participant_video' => $start_video,
                'mute_upon_entry' => $auto_mute,
                'waiting_room' => $waiting_room,
            ],
        ];
        $this->get('session')->set('form_data', $data);


        $user = $this->getUser();
        $userId = $user->getId();
        $apiType = 'google';
        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
            'user_id' => $userId,
            'api_type' => $apiType,
        ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $google_data = $statementSelect->fetch();
        $googleMeetService = new GoogleMeetService($google_data, $kernel);


        $google_url = $googleMeetService->authorized();
        return new RedirectResponse($google_url);
    }

    /**
     * @Route("/google/redirect", name="google_redirect")
     */
    public function googleMeetingRedirect(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {

        $user = $this->getUser();
        $userId = $user->getId();
        $apiType = 'google';
        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
            'user_id' => $userId,
            'api_type' => $apiType,
        ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $google_data = $statementSelect->fetch();

        $googleMeetService = new GoogleMeetService($google_data, $kernel);


        $value = $this->get('session')->get('form_data');
        $response = $googleMeetService->googleRedirect($value);

        $user = $this->getUser();
        $userId = $user->getId();
        $calendarId = $google_data['google_calendar_id'];
        $topic = $response->summary;
        $description = $response->description;
        $meetUrl = $response->hangoutLink;
        $timeZone = $response->start->timeZone;

        $startDate = date('Y-m-d H:i:s', strtotime($response->start->dateTime));
        $endDate   = date('Y-m-d H:i:s', strtotime($response->end->dateTime));
        $createdAt = date('Y-m-d H:i:s', strtotime($response->created));

        $sql = "INSERT INTO google_meetings (organizer_id,calendar_id, topic, description, meet_url, time_zone, start_date, end_date, created_at) 
                VALUES (:organizer_id, :calendar_id, :topic, :description, :meet_url, :time_zone, :start_date, :end_date, :created_at)";

        $params = [
            'organizer_id' => $userId,
            'calendar_id' => $calendarId,
            'topic' => $topic,
            'description' => $description,
            'meet_url' => $meetUrl,
            'time_zone' => $timeZone,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => $createdAt,
        ];
        $statement = $entityManager->getConnection()->prepare($sql);
        $statement->execute($params);
        return $this->redirectToRoute('dashboard_organizer_venue_add');
    }








}

