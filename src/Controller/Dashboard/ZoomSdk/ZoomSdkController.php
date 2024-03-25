<?php

namespace App\Controller\Dashboard\ZoomSdk;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ZoomSdkController extends Controller
{
  public function zoomSdkPlayer()
  {
      return $this->render('Dashboard/ZoomSdk/zoom-sdk.html.twig',[
        'nodeServer' => $_ENV['NODE_SERVER']
      ]);

  }
}
