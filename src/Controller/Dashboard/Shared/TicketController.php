<?php

namespace App\Controller\Dashboard\Shared;

use Google\Service\Adsense\TimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Service\AppServices;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Swift_Mailer;
use Twig\Environment;
use Doctrine\DBAL\Connection;
use Picqer\Barcode\BarcodeGeneratorPNG;
use GuzzleHttp\Client;
use App\Entity\CartElement;
use App\Entity\Order;
use App\Entity\TicketReservation;
use App\Entity\OrderTicket;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\OrderElement;
use FOS\UserBundle\Model\UserManagerInterface;


class TicketController extends Controller
{
    private $userManager;

    public function __construct(UserManagerInterface $userManager) {
        // For Sign Up 
        $this->userManager = $userManager;
    }

    public function sendTicket(Request $request, AppServices $services, TranslatorInterface $translator, Connection $connection, $event = null)
{
    $sqlEvent = "SELECT * FROM eventic_event WHERE id = :id";
    $paramsEvent = ['id' => $event];
    $statementEvent = $connection->prepare($sqlEvent);
    $statementEvent->execute($paramsEvent);
    $one_event = $statementEvent->fetch();

    if (!$one_event) {
        $this->addFlash('error', $translator->trans('The event can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $link = $_ENV['MAIN_DOMAIN'].'/dashboard/attendee/join_event_meeting/'.$one_event['reference'];

    if (!$link) {
        $this->addFlash('error', $translator->trans('The event Link can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $sql = "SELECT * FROM eventic_event_date WHERE event_id = :id";
    $params = ['id' => $event];
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    $event_date = $statement->fetch();

    if (!$event_date) {
        $this->addFlash('error', $translator->trans('The event date can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }
    $event_date_id = $event_date['id'];

    $sql2 = "SELECT * FROM eventic_event_date_ticket WHERE eventdate_id = :id";
    $params2 = ['id' => $event_date_id];
    $statement2 = $connection->prepare($sql2);
    $statement2->execute($params2);
    $event_date_ticket = $statement2->fetch();

    if (!$event_date_ticket) {
        $this->addFlash('error', $translator->trans('The event date ticket can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $event_date_ticket_id = $event_date_ticket['id'];

    $sql3 = "SELECT * FROM eventic_order_element WHERE eventticket_id = :id";
    $params3 = ['id' => $event_date_ticket_id];
    $statement3 = $connection->prepare($sql3);
    $statement3->execute($params3);
    $event_orders = $statement3->fetchAll();

    if (!empty($event_orders)) {
        foreach ($event_orders as $event_order) {
            $event_order_id = $event_order['order_id'];

            $sql_order = "SELECT * FROM eventic_order WHERE id = :order_id";
            $params_order = ['order_id' => $event_order_id];
            $statement_order = $connection->prepare($sql_order);
            $statement_order->execute($params_order);
            $order_data = $statement_order->fetch();
            if (isset($order_data['reference'])) {
                $references[] = $order_data['reference'];
            }
        }
    }

    if (empty($references)) {
        $this->addFlash('error', $translator->trans('No orders found for this event'));
        return $this->redirect($request->headers->get('referer'));
    }

    foreach ($references as $reference) {
        $order[] = $services->getOrders(array("reference" => $reference))->getQuery()->getOneOrNullResult();
    }

    if (!$order) {
        $this->addFlash('error', $translator->trans('The order can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $eventDateTicketReference = $request->query->get('event', 'all');

    return $this->render('Dashboard/Shared/SendTicket/index.html.twig', [
        'orders' => $order,
        'eventDateTicketReference' => $eventDateTicketReference,
        'link' => $link,
        'event_id' => $event

    ]);
}


    /**
     * @Route("/mail-ticket/send", name="mail_server_test", methods="GET|POST")
     */
    public function mailServerTest(Request $request, AppServices $services, Swift_Mailer $mailer, Environment $templating, TranslatorInterface $translator, Connection $connection)
    {
        // Get the 'id' parameter from the URL
        $id = $request->query->get('event_id');
        $ref_id = $request->query->get('ref_id');

        $sqlEvent = "SELECT * FROM eventic_event WHERE id = :id";
        $paramsEvent = ['id' => $id];
        $statementEvent = $connection->prepare($sqlEvent);
        $statementEvent->execute($paramsEvent);
        $one_event = $statementEvent->fetch();

        if (!$one_event) {
            $this->addFlash('error', $translator->trans('The event can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $link = $_ENV['MAIN_DOMAIN'].'/dashboard/attendee/join_event_meeting/'.$one_event['reference'];

        if (!$link) {
            $this->addFlash('error', $translator->trans('The event Link can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $sql = "SELECT * FROM eventic_order_ticket WHERE reference = :ref_id";
        $params = ['ref_id' => $ref_id];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $order = $statement->fetch();

        if (!$order) {
            $this->addFlash('error', $translator->trans('The order ticket can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $orderelement_id = $order['orderelement_id'];

        $sql1 = "SELECT * FROM eventic_order_element WHERE id = :orderelement_id";
        $params1 = ['orderelement_id' => $orderelement_id];
        $statement1 = $connection->prepare($sql1);
        $statement1->execute($params1);
        $order_element = $statement1->fetch();

        if (!$order_element) {
            $this->addFlash('error', $translator->trans('The order element can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $order_id = $order_element['order_id'];

        $sql2 = "SELECT * FROM eventic_order WHERE id = :order_id";
        $params2 = ['order_id' => $order_id];
        $statement2 = $connection->prepare($sql2);
        $statement2->execute($params2);
        $order = $statement2->fetch();

        if (!$order) {
            $this->addFlash('error', $translator->trans('The order can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $user_id = $order['user_id'];

        $sql3 = "SELECT * FROM eventic_user WHERE id = :user_id";
        $params3 = ['user_id' => $user_id];
        $statement3 = $connection->prepare($sql3);
        $statement3->execute($params3);
        $user = $statement3->fetch();

        if (!$user) {
            $this->addFlash('error', $translator->trans('The user can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $orders = $services->getOrders(array("reference" => $order['reference']))->getQuery()->getOneOrNullResult();
        if (!$orders) {
            $this->addFlash('error', $translator->trans('The order can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }
        $eventDateTicketReference = $request->query->get('event', 'all');

            $pdfOptions = new Options();
            $dompdf = new Dompdf($pdfOptions);

            $html = $this->renderView('Dashboard/Shared/Order/ticket-pdf.html.twig', [
                'order' => $orders,
                'eventDateTicketReference' => $eventDateTicketReference,
                'link' => $link,
            ]);
        
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $ticketsPdfFile = $dompdf->output();
            $emailTo=$user['email'];
            $email = (new \Swift_Message($translator->trans('Your tickets bought from') . ' ' . $services->getSetting('website_name')))
                    ->setFrom($services->getSetting('no_reply_email'))
                    ->setTo($emailTo)
                    ->setBody(
                            $this->renderView('Dashboard/Shared/Order/confirmation-email.html.twig', ['order' => $orders]), 'text/html')
                    ->attach(new \Swift_Attachment($ticketsPdfFile, $orders->getReference() . "-" . $translator->trans("tickets") . '.pdf', 'application/pdf'));

            $mailer->send($email);

            $this->addFlash('success', $translator->trans('Invitation Sent'));
            return $this->redirect($request->headers->get('referer'));
    }


public function sendTicketCsv(Request $request, AppServices $services, TranslatorInterface $translator, Connection $connection, Swift_Mailer $mailer, Environment $templating, $event = null)
{
    $sql = "SELECT * FROM eventic_event WHERE id = :id";
    $params = ['id' => $event];
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    $event_info = $statement->fetch();

    if (!$event_info) {
        $this->addFlash('error', $translator->trans('The event can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $ref_id = $event_info['reference'];

    $link = $_ENV['MAIN_DOMAIN'].'dashboard/attendee/join_event_meeting/'.$ref_id;

    if (!$link) {
        $this->addFlash('error', $translator->trans('The event Link can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $user_id = $event_info['organizer_id'];
    
    $sql2 = "SELECT * FROM eventic_user WHERE organizer_id = :id";
    $params2 = ['id' => $user_id];
    $statement2 = $connection->prepare($sql2);
    $statement2->execute($params2);
    $user_info = $statement2->fetch();

    if (!$user_info) {
        $this->addFlash('error', $translator->trans('The user information can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $sql3 = "SELECT * FROM event_mails WHERE event_ref_id = :ref_id";
    $params3 = ['ref_id' => $ref_id];
    $statement3 = $connection->prepare($sql3);
    $statement3->execute($params3);
    $eventMails = $statement3->fetchAll();

    if (empty($eventMails)) {
        $this->addFlash('error', $translator->trans('No emails found for this event'));
        return $this->redirect($request->headers->get('referer'));
    }


    
// $sql4 ="SELECT eventic_event_date.*,eventic_event_date.id as event_date_id ,eventic_venue.*
// FROM eventic_event_date
// JOIN eventic_venue ON eventic_event_date.venue_id = eventic_venue.id
// WHERE eventic_event_date.event_id = :id";

    $sql4 = "SELECT eventic_event_date.*,eventic_event_date.id as event_date_id FROM eventic_event_date WHERE event_id = :id";
    $params4 = ['id' => $event];
    $statement4 = $connection->prepare($sql4);
    $statement4->execute($params4);
    $event_date = $statement4->fetch();

    $event_date_id = $event_date['event_date_id'];


    if (!$event_date) {
        $this->addFlash('error', $translator->trans('The event date can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $sql6 = "SELECT * FROM eventic_event_date_ticket WHERE eventdate_id = :id";
    $params6 = ['id' => $event_date_id];
    $statement6 = $connection->prepare($sql6);
    $statement6->execute($params6);
    $event_ticket = $statement6->fetch();

    if (!$event_ticket) {
        $this->addFlash('error', $translator->trans('The event ticket can not be found'));
        return $this->redirect($request->headers->get('referer'));
    }

    $currentDateTime = new \DateTime();
    $timezone = new \DateTimeZone('America/New_York');
    $currentDateTime->setTimezone($timezone);
    $orderDateTime = $currentDateTime->format('D d M Y, h:i A T');
    
 
    $ticketreference=$event_ticket['reference'];
    $em = $this->getDoctrine()->getManager();
    $eventticket = $em->getRepository("App\Entity\EventTicket")->findOneByReference($ticketreference);

    // For Create User As a attendee

    
 
    foreach ($eventMails as $eventMail) {
        if ($eventMail['status'] == 0) {
            
            $sql7 = "SELECT slug FROM eventic_user WHERE email = :email";
            $params7 = ['email' => $eventMail['email']];
            $statement7 = $connection->prepare($sql7);
            $statement7->execute($params7);
            $eventic_user = $statement7->fetch();
            $user_name = strtolower($eventMail['name'].$eventMail['surname']).strtotime('now');
            $user_link_slug = '';
            if(!empty($eventic_user)){
                $user_slug = $eventic_user['slug'];
            }else{
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setFirstname($eventMail['name']);
                $user->setLastname($eventMail['surname']);
                $user->setUsername($user_name);
                $user->setUsernameCanonical($user_name);
                $user->setEmail($eventMail['email']);
                $user->setEmailCanonical($eventMail['email']);
                $user->setPlainPassword('12345678');
                $user->setSlug($eventMail['name'].$eventMail['surname'].strtotime('now'));
                $user->addRole('ROLE_ATTENDEE');
                $this->userManager->updateUser($user);

                $user_slug = $user->getSlug();
                $user_link_slug = $user_slug;
            }

            $user = $services->getUsers(array("slug" => $user_slug, "enabled" => "all"))->getQuery()->getOneOrNullResult();

                
            $order = new Order();
            $order->setUser($user);
            $order->setReference($services->generateReference(15));
            $order->setStatus(0);
        
            $orderelement = new OrderElement();
            $orderelement->setOrder($order);
            $orderelement->setEventticket($eventticket);
            $orderelement->setUnitprice(0.00);
            $orderelement->setQuantity(1);
            $order->addOrderelement($orderelement);

            if ($user->hasRole("ROLE_ATTENDEE")) {
                $order->setTicketFee($services->getSetting("ticket_fee_online"));
                $order->setTicketPricePercentageCut($services->getSetting("online_ticket_price_percentage_cut"));
            } else if ($user->hasRole("ROLE_POINTOFSALE")) {
                $order->setTicketFee($services->getSetting("ticket_fee_pos"));
                $order->setTicketPricePercentageCut($services->getSetting("pos_ticket_price_percentage_cut"));
            }
            $order->setCurrencyCcy($services->getSetting("currency_ccy"));
            $order->setCurrencySymbol($services->getSetting("currency_symbol"));  
                    
            $order->setStatus(1);
            $paymentGateway = $em->getRepository("App\Entity\PaymentGateway")->findOneBySlug("point-of-sale");
            $gatewayFactoryName = "offline";
            $order->setPaymentGateway($paymentGateway);
            $em->persist($order);
            foreach ($order->getOrderelements() as $orderElement) {
                    $ticket = new OrderTicket();
                    $ticket->setOrderElement($orderElement);
                    $ticket->setReference($services->generateReference(20));
                    $ticket->setScanned(false);
                    $em->persist($ticket);
            }
            $storage = $this->get('payum')->getStorage('App\Entity\Payment');
            $payment = $storage->create();
            $payment->setOrder($order);
            $payment->setNumber($services->generateReference(20));
            $payment->setCurrencyCode($services->getSetting("currency_ccy"));
            $payment->setTotalAmount("0.0"); // 1.23 USD = 123
            $payment->setDescription($translator->trans("Payment of tickets purchased on %website_name%", array('%website_name%' => $services->getSetting("website_name"))));
            $payment->setClientId($user->getId());
            $payment->setFirstname($eventMail['name']);
            $payment->setLastname($eventMail['surname']);
            $payment->setClientEmail($eventMail['email']);
            $payment->setState($eventMail['email']);
            $payment->setCity($eventMail['city']);
            $payment->setPostalcode('213344');
            $payment->setStreet($eventMail['address']);
            $payment->setStreet2($eventMail['address']);
            
            $storage->update($payment);
            $order->setPayment($payment);
    
    
            $em->flush();   
            $services->emptyCart($user);
    
            

            $orderReference=$order->getReference();

            $em->refresh($order);
            $em->refresh($ticket);
            $em->refresh($orderElement);

            $pdfOptions = new Options();
            $dompdf = new Dompdf($pdfOptions);

            if($user_link_slug !== ''){
                $link = $link.'?ud='.$user_link_slug;
            }

            $html = $this->renderView('Dashboard/Shared/Order/ticket-pdf.html.twig', [
                'order' => $order,
                'eventDateTicketReference' => 'all',
                'link' => $link,
            ]);
        
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $ticketsPdfFile = $dompdf->output();
            $emailTo=$eventMail['email'];
            $email = (new \Swift_Message($translator->trans('Your tickets bought from') . ' ' . $services->getSetting('website_name')))
                    ->setFrom($services->getSetting('no_reply_email'))
                    ->setTo($emailTo)
                    ->setBody(
                            $this->renderView('Dashboard/Shared/Order/confirmation-email.html.twig', ['order' => $order]), 'text/html')
                    ->attach(new \Swift_Attachment($ticketsPdfFile, $order->getReference() . "-" . $translator->trans("tickets") . '.pdf', 'application/pdf'));

            $mailer->send($email);


//exit;

        
      // dd($order->getReference());
       
    //  $order = $services->getOrders(array("reference" => $reference))->getQuery()->getOneOrNullResult();

    // $link = $_ENV['MAIN_DOMAIN'].'/dashboard/attendee/join_event_meeting/'.$one_event['reference'];
    // $html = $this->renderView('Dashboard/Shared/Order/ticket-pdf.html.twig', [
    //             'order' => $order,
    //             'eventDateTicketReference' => $eventDateTicketReference,
    //             'link' => $link,
    // ]);
   //  $eventDateTicketReference = $request->query->get('event', 'all');
  //   $order = $services->getOrders(array("reference" =>$orderReference))->getQuery()->getOneOrNullResult();
     
     
     // $data= $order->getorderelements();
        //  dump($data);
        //  foreach($data as $a) {
        //      dump($a,$a->gettickets());
        //  }
         
       //  dd("ok");
       
    //  return $this->render('Dashboard/Shared/Order/ticket-pdf.html.twig', [
    //             'order' => $order,
    //             'eventDateTicketReference' => $eventDateTicketReference,
    //             'link' => $link,
    // ]);
    
                // $email = (new \Swift_Message($translator->trans("Ticket Mail For Guest")))
                // ->setFrom($services->getSetting('no_reply_email'), $services->getSetting('website_name'))
                // ->setTo($eventMail['email'])
                // ->setBody($templating->render('Dashboard/Shared/Order/ticket-pdf.html.twig', [
                //     'order' => $order,
                //     'eventDateTicketReference' => $eventDateTicketReference,
                //     'link' => $link,
                // ]), 'text/html');
    
                // $email = (new \Swift_Message($translator->trans("Ticket Mail For Guest")))
                // ->setFrom($services->getSetting('no_reply_email'), $services->getSetting('website_name'))
                // ->setTo($eventMail['email'])
                // ->setBody($templating->render('Dashboard/Shared/SendTicket/eventTicketForCsv.html.twig', [
                //     'eventMail' => $eventMail,
                //     'user' => $user_info,
                //     'event_date' => $event_date,
                //     'link' => $link,
                //     'event_ticket' => $event_ticket,
                //     'orderDateTime' => $orderDateTime,
                // ]), 'text/html');
                
    
            // try {
            //     $result = $mailer->send($email);
            //     if ($result == 0) {
            //         $this->addFlash('danger', $translator->trans("The email could not be sent"));
            //     } else {
            //         $this->addFlash('success', $translator->trans("The test email has been sent, please check the inbox of") . " " . $eventMail['email']);
            //     }
            // } catch (\Exception $e) {
            //     $this->addFlash('danger', $translator->trans("The email could not be sent"));
            // }
            
         //   dd("ojk");
            // Update the status
            // $sqlUpdate = "UPDATE event_mails SET status = 1 WHERE id = :mailId";
            // $paramsUpdate = ['mailId' => $eventMail['id']];
            // $statementUpdate = $connection->prepare($sqlUpdate);
            // $statementUpdate->execute($paramsUpdate);
        }
    }
    
    $this->addFlash('success', $translator->trans('Invitation Sent'));
    return $this->redirect($request->headers->get('referer'));
}



public function send_ticket_for_whatsapp(Request $request, AppServices $services, TranslatorInterface $translator, Connection $connection, Swift_Mailer $mailer, Environment $templating, $event = null)
{
        $sql = "SELECT * FROM eventic_event WHERE id = :id";
        $params = ['id' => $event];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $event_info = $statement->fetch();

    

        if (!$event_info) {
            $this->addFlash('error', $translator->trans('The event can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $ref_id = $event_info['reference'];

        $link = $_ENV['MAIN_DOMAIN'] . '/dashboard/attendee/join_event_meeting/' . $ref_id;

        if (!$link) {
            $this->addFlash('error', $translator->trans('The event Link can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $user_id = $event_info['organizer_id'];

        $sql2 = "SELECT * FROM eventic_user WHERE organizer_id = :id";
        $params2 = ['id' => $user_id];
        $statement2 = $connection->prepare($sql2);
        $statement2->execute($params2);
        $user_info = $statement2->fetch();

        if (!$user_info) {
            $this->addFlash('error', $translator->trans('The user information can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $sql3 = "SELECT * FROM event_mails WHERE event_ref_id = :ref_id";
        $params3 = ['ref_id' => $ref_id];
        $statement3 = $connection->prepare($sql3);
        $statement3->execute($params3);
        $eventMails = $statement3->fetchAll();

        if (empty($eventMails)) {
            $this->addFlash('error', $translator->trans('No emails found for this event'));
            return $this->redirect($request->headers->get('referer'));
        }

        $sql4 = "SELECT * FROM eventic_event_date WHERE event_id = :id";
        $params4 = ['id' => $event];
        $statement4 = $connection->prepare($sql4);
        $statement4->execute($params4);
        $event_date = $statement4->fetch();
        $event_date_id = $event_date['id'];

        if (!$event_date) {
            $this->addFlash('error', $translator->trans('The event date can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $sql6 = "SELECT * FROM eventic_event_date_ticket WHERE eventdate_id = :id";
        $params6 = ['id' => $event_date_id];
        $statement6 = $connection->prepare($sql6);
        $statement6->execute($params6);
        $event_ticket = $statement6->fetch();

        if (!$event_ticket) {
            $this->addFlash('error', $translator->trans('The event ticket can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }

        $currentDateTime = new \DateTime();
        $timezone = new \DateTimeZone('America/New_York');
        $currentDateTime->setTimezone($timezone);
        $orderDateTime = $currentDateTime->format('D d M Y, h:i A T');

        foreach ($eventMails as $eventMail) {
            $templateObject = [
                'id' => '19660948-5440-48e1-9073-0ff8b575f32a',
                'params' => [$eventMail['name'], "15643", $event_date['startdate'], "Online", $link]
            ];
            $templateJson = json_encode($templateObject);
            $postData = http_build_query([
                'source' => '573022177303',
                'destination' => $eventMail['phone_number'],
                'template' => $templateJson
            ]);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://api.gupshup.io/sm/api/v1/template/msg',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'apikey: cky6px6gylnajx0epf1xafnxqluh8lyh',
                    'Authorization: Bearer sk_4b777aa004e7403d86c481b3d2c15f49'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);


        }

        
        return $this->redirect($request->headers->get('referer'));

}



}