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
     * @Route("/send-ticket_mail", name="send_ticket_mail", methods="POST")
     */
    public function sendTicket(Request $request, AppServices $services, TranslatorInterface $translator,Connection $connection,$event = null)
    {
        $sql = "SELECT * FROM eventic_event_date WHERE event_id = :id";
        $params = ['id' => $event];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $event_date = $statement->fetch();

        $event_date_id = $event_date['id'];

        $sql2 = "SELECT * FROM eventic_event_date_ticket WHERE eventdate_id = :id";
        $params2 = ['id' => $event_date_id];
        $statement2 = $connection->prepare($sql2);
        $statement2->execute($params2);
        $event_date_ticket = $statement2->fetch();

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

        foreach ($references as $reference) {
            $order[] = $services->getOrders(array("reference" => $reference))->getQuery()->getOneOrNullResult();
        }

        if (!$order) {
            $this->addFlash('error', $translator->trans('The order can not be found'));
            return $this->redirectToRoute("dashboard_attendee_orders");
        }
        $eventDateTicketReference = $request->query->get('event', 'all');

        return $this->render('Dashboard/Shared/SendTicket/index.html.twig',[
            'orders' => $order,
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

        $sql = "SELECT * FROM eventic_order_ticket WHERE reference = :ref_id";
        $params = ['ref_id' => $ref_id];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $order = $statement->fetch();

        $orderelement_id = $order['orderelement_id'];

        $sql1 = "SELECT * FROM eventic_order_element WHERE id = :orderelement_id";
        $params1 = ['orderelement_id' => $orderelement_id];
        $statement1 = $connection->prepare($sql1);
        $statement1->execute($params1);
        $order_element = $statement1->fetch();

        $order_id = $order_element['order_id'];

        $sql2 = "SELECT * FROM eventic_order WHERE id = :order_id";
        $params2 = ['order_id' => $order_id];
        $statement2 = $connection->prepare($sql2);
        $statement2->execute($params2);
        $order = $statement2->fetch();

        $user_id = $order['user_id'];

        $sql3 = "SELECT * FROM eventic_user WHERE id = :user_id";
        $params3 = ['user_id' => $user_id];
        $statement3 = $connection->prepare($sql3);
        $statement3->execute($params3);
        $user = $statement3->fetch();


        $orders = $services->getOrders(array("reference" => $order['reference']))->getQuery()->getOneOrNullResult();
        if (!$orders) {
            $this->addFlash('error', $translator->trans('The order can not be found'));
            return $this->redirectToRoute("dashboard_attendee_orders");
        }
        $eventDateTicketReference = $request->query->get('event', 'all');

        // Send Email
        $email = new \Swift_Message($translator->trans("Mail server test email"));
        $email->setFrom($services->getSetting('no_reply_email'), $services->getSetting('website_name'))
            ->setTo($user['email'])
            ->setBody($templating->render('Dashboard/Shared/SendTicket/mailEventTickets.html.twig',[
                'order' => $orders,
                'eventDateTicketReference' => $eventDateTicketReference,
            ]), 'text/html');
        try {
            $result = $mailer->send($email);
            if ($result == 0) {
                $this->addFlash('danger', $translator->trans("The email could not be sent"));
            } else {
                $this->addFlash('success', $translator->trans("The test email has been sent, please check the inbox of") . " " . $user['email']);
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans("The email could not be sent"));
        }
        return $this->redirect($request->headers->get('referer'));
    }
}