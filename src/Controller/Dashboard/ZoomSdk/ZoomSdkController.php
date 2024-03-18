<?php

namespace App\Controller\Dashboard\ZoomSdk;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ZoomSdkController extends Controller
{
  public function zoomSdkPlayer()
  {
    try {
      return $this->render('Dashboard/ZoomSdk/zoom-sdk.html.twig');
    } catch (\Exception $exception) {
      dd($exception);
    }
  }
}
