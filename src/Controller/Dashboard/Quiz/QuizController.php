<?php

namespace App\Controller\Dashboard\Quiz;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class QuizController extends Controller
{

  public function quizSetting(EntityManagerInterface $entityManager)
  {
    $now = new \DateTime('now', new \DateTimeZone('UTC'));
    $nowFormatted = $now->format('Y-m-d H:i:s');

    $sql = "SELECT * FROM google_meetings WHERE end_date > :now";
    $statement = $entityManager->getConnection()->prepare($sql);
    $statement->execute(['now' => $nowFormatted]);
    $meetings = $statement->fetchAll();



    // $user = $this->getUser();
    // $userId = $user->getId();
    // $apiType = 'zoom';
    // $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
    // $paramsSelect = [
    //   'user_id' => $userId,
    //   'api_type' => $apiType,
    // ];
    // $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
    // $statementSelect->execute($paramsSelect);
    // $zoom_data = $statementSelect->fetch();

    // $sql2 = "SELECT * FROM event_zoom_meeting_list WHERE end_date > :now";
    // $statement2 = $entityManager->getConnection()->prepare($sql2);
    // $statement2->execute(['now' => $nowFormatted]);
    // $zoom_meeting = $statement2->fetchAll();

    return $this->render('Dashboard/Quiz/quiz-setting.html.twig',[
      'google_meeting' => $meetings,
      // 'zoom_meeting' => $zoom_meeting,
    ]);
  }


  public function startQuiz(Request $request)
  {
    $nodeServer = $_ENV['NODE_SERVER'];
    $url = $nodeServer. 'new-message-emit';

    $params = $request->get('quiz');

    $data = [
      'name' => 'start quiz',
      'channel' => 'start_quiz_'.$params,
    ];

    $payload = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
      )
    );

    $result = curl_exec($ch);
    curl_close($ch);
    return $this->redirectToRoute('quiz_setting');
  }

  public function closeQuiz(Request $request)
  {
    $nodeServer = $_ENV['NODE_SERVER'];
    $url = $nodeServer. 'new-message-emit';

    $params = $request->get('quiz');

    $data = [
      'name' => 'close quiz',
      'channel' => 'close_quiz_' . $params,
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
      )
    );

    $result = curl_exec($ch);
    curl_close($ch);
    return $this->redirectToRoute('quiz_setting');
  }

 


}
