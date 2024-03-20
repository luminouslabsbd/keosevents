<?php

namespace App\Controller\Dashboard\Shared;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Service\AppServices;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Swift_Mailer;
use Twig\Environment;
use Doctrine\DBAL\Connection;


class TicketController extends Controller
{
    /**
     * @Route("/send-tickets", name="send_tickets", methods="GET")
     * @Route("/send-ticket_mail", name="send_ticket_mail", methods="POST")
     */
    public function sendTicket(Request $request, AppServices $services, TranslatorInterface $translator)
    {
        $order = $services->getOrders(array("reference" => "6278336244769b3"))->getQuery()->getOneOrNullResult();
//        dd($order,$order->getOrderelements());
        if (!$order) {
            $this->addFlash('error', $translator->trans('The order can not be found'));
            return $this->redirectToRoute("dashboard_attendee_orders");
        }
        $eventDateTicketReference = $request->query->get('event', 'all');

        return $this->render('Dashboard/Shared/SendTicket/index.html.twig',[
            'order' => $order,
            'eventDateTicketReference' => $eventDateTicketReference,
        ]);
    }

    /**
     * @Route("/mail-ticket/test", name="mail_server_test", methods="GET|POST")
     */
    public function mailServerTest(Request $request, AppServices $services, Swift_Mailer $mailer, Environment $templating, TranslatorInterface $translator, Connection $connection)
    {
        // Get the 'id' parameter from the URL
        $ref_id = $request->query->get('id');

        // Execute raw SQL query to fetch data from event_mail table
        $sql = "SELECT * FROM event_mails WHERE event_ref_id = :ref_id AND send_chanel = 'email'";
        $params = ['ref_id' => $ref_id];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $eventMails = $statement->fetchAll();

        foreach ($eventMails as $eventMail) {
            $email = (new \Swift_Message($translator->trans("Ticket Mail server test email")))
                ->setFrom($services->getSetting('no_reply_email'), $services->getSetting('website_name'))
                ->setTo($eventMail['email'])
                ->setBody($templating->render('Dashboard/Shared/SendTicket/mailEventTickets.html.twig'), 'text/html');

            try {
                $result = $mailer->send($email);
                if ($result == 0) {
                    $this->addFlash('danger', $translator->trans("The email could not be sent"));
                } else {
                    $this->addFlash('success', $translator->trans("The test email has been sent, please check the inbox of") . " " . $eventMail['email']);
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', $translator->trans("The email could not be sent"));
            }
        }
        return $this->redirectToRoute('send_tickets');
    }
}