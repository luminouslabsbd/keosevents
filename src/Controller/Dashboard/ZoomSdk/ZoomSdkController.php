<?php

namespace App\Controller\Dashboard\ZoomSdk;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class ZoomSdkController extends Controller
{
  public function zoomSdkPlayer(Request $request, Connection $connection , TranslatorInterface $translator, $reference)

  {
    $sqlEvent = "SELECT * FROM eventic_event WHERE reference = :reference";
    $paramsEvent = ['reference' => $reference];
    $statementEvent = $connection->prepare($sqlEvent);
    $statementEvent->execute($paramsEvent);
    $one_event = $statementEvent->fetch();

    if (!$one_event) {
        $this->addFlash('error', $translator->trans('The event can not be found'));
        return $this->redirect($request->headers->get('referer'));
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
        return $this->redirect($request->headers->get('referer'));
    }

    $link_id = $event_date['meetinglink'];

    $sql5 = "SELECT * FROM event_zoom_meeting_list WHERE id = :id";
    $params5 = ['id' => $link_id];
    $statement5 = $connection->prepare($sql5);
    $statement5->execute($params5);
    $event_meeting = $statement5->fetch();

    if (!$event_meeting) {
        $this->addFlash('error', $translator->trans('The event Meeting can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $sql7 = "SELECT * FROM eventic_organizer WHERE id = :id";
    $params7 = ['id' => $org_id];
    $statement7 = $connection->prepare($sql7);
    $statement7->execute($params7);
    $organizer = $statement7->fetch();

    if (!$organizer) {
        $this->addFlash('error', $translator->trans('The event Organizer can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $userId = $organizer['user_id'];
   
    if (!$userId) {
      $this->addFlash('error', $translator->trans('The event Meeting Credential can not be found'));
      return $this->redirect($request->headers->get('referer'));
  }

    $sql6 = "SELECT * FROM api_settings WHERE user_id = :user_id";
    $params6 = ['user_id' => $userId];
    $statement6 = $connection->prepare($sql6);
    $statement6->execute($params6);
    $api_setting = $statement6->fetch();

    if (!$api_setting) {
        $this->addFlash('error', $translator->trans('The event Meeting Credential can not be found'));
        return $this->redirect($request->headers->get('referer'));
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
