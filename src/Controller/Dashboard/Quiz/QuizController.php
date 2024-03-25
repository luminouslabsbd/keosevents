<?php

namespace App\Controller\Dashboard\Quiz;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class QuizController extends Controller
{

  public function quizSetting()
  {
    return $this->render('Dashboard/Quiz/quiz-setting.html.twig');
  }


  public function startQuiz(Request $request)
  {
    $nodeServer = $_ENV['NODE_SERVER'];
    $url = $nodeServer.'/new-order-data';

    $params = $request->get('quiz');
    if("start" == $params){
      $data = [
        'name' => 'start',
        'channel' => 'quiz_start',
      ];
    }elseif("result" == $params){
      $data = [
        'name' => 'result',
        'channel' => 'show_result',
      ];
    }elseif("winner" == $params){
      $data = [
        'name' => 'winner',
        'channel' => 'show_winner',
      ];
    }
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
