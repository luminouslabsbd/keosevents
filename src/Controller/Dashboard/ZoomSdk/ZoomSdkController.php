<?php

namespace App\Controller\Dashboard\ZoomSdk;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ZoomSdkController extends Controller
{

  private $tokenManager;

  public function __construct(CsrfTokenManagerInterface $tokenManager = null)
  {
    $this->tokenManager = $tokenManager;
  }

  public function zoomSdkPlayer(Request $request, Connection $connection , TranslatorInterface $translator, $reference)

  {
    $ErrorBackUrl = $_ENV['MAIN_DOMAIN'].'en/dashboard/attendee/my-tickets';

    $ud = $request->query->get('ud') ?? null;

    if($ud != null){

      $sql3 = "SELECT slug, email, firstname, lastname FROM eventic_user WHERE slug = :slug";
        $params3 = ['slug' => $ud];
        $statement3 = $connection->prepare($sql3);
        $statement3->execute($params3);
        $user = $statement3->fetch();

        if($user == null){
          $this->addFlash('error', $translator->trans('Invalid Link!!!'));
          return new RedirectResponse($ErrorBackUrl);
        }

        $csrfToken = $this->tokenManager ? $this->tokenManager->getToken('authenticate')->getValue() : null;

      return $this->render('Front/Luminous/set_password.html.twig',[
        'user' => $user,
        'csrf_token' => $csrfToken
      ]);
    }

    $sqlEvent = "SELECT * FROM eventic_event WHERE reference = :reference";
    $paramsEvent = ['reference' => $reference];
    $statementEvent = $connection->prepare($sqlEvent);
    $statementEvent->execute($paramsEvent);
    $one_event = $statementEvent->fetch();

    if (!$one_event) {
        $this->addFlash('error', $translator->trans('The event can not be found'));
        return new RedirectResponse($ErrorBackUrl);
    }
    $event_id = $one_event['id'];

    $org_id = $one_event['organizer_id'];

    $sql = "SELECT * FROM eventic_event_date WHERE event_id = :id";
    $params = ['id' => $event_id];
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    $event_date = $statement->fetch();

    if (!$event_date) {
        $this->addFlash('error', $translator->trans('The event date can not be found'));
        return new RedirectResponse($ErrorBackUrl);
    }

    $link_id = $event_date['meetinglink'];

    $sql5 = "SELECT * FROM event_zoom_meeting_list WHERE id = :id";
    $params5 = ['id' => $link_id];
    $statement5 = $connection->prepare($sql5);
    $statement5->execute($params5);
    $event_meeting = $statement5->fetch();

    if (!$event_meeting) {
        $this->addFlash('error', $translator->trans('The event Meeting can not be found'));
        return new RedirectResponse($ErrorBackUrl);
    }

    $sql7 = "SELECT * FROM eventic_organizer WHERE id = :id";
    $params7 = ['id' => $org_id];
    $statement7 = $connection->prepare($sql7);
    $statement7->execute($params7);
    $organizer = $statement7->fetch();

    if (!$organizer) {
        $this->addFlash('error', $translator->trans('The event Organizer can not be found'));
        return new RedirectResponse($ErrorBackUrl);
    }

    $userId = $organizer['user_id'];
   
    if (!$userId) {
      $this->addFlash('error', $translator->trans('The event Meeting Credential can not be found'));
      return new RedirectResponse($ErrorBackUrl);
  }

    $sql6 = "SELECT * FROM api_settings WHERE user_id = :user_id";
    $params6 = ['user_id' => $userId];
    $statement6 = $connection->prepare($sql6);
    $statement6->execute($params6);
    $api_setting = $statement6->fetch();

    if (!$api_setting) {
        $this->addFlash('error', $translator->trans('The event Meeting Credential can not be found'));
        return new RedirectResponse($ErrorBackUrl);
    }
    
    
      return $this->render('Dashboard/ZoomSdk/zoom-sdk.html.twig',[
        'nodeServer' => $_ENV['NODE_SERVER'],
        'quizApiUrl' => $_ENV['QUIZ_API'],
        'zoomAuthEndPoint' => $_ENV['ZOOM_AUTH_END_POINT'],
        'leaveUrl' => $_ENV['MAIN_DOMAIN'],
        'sdkKey' => $api_setting['sdk_key'],
        'event_meeting' => $event_meeting
      ]);

  }
}
