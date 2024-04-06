<?php

namespace App\Controller\Dashboard\Shared;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\AppServices;
use App\Entity\Event;
use App\Form\EventType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\DBAL\Connection;


class EventController extends Controller
{

    /**
     * @Route("/administrator/manage-events", name="dashboard_administrator_event", methods="GET")
     * @Route("/organizer/my-events", name="dashboard_organizer_event", methods="GET")
     */
    public function index(Request $request, PaginatorInterface $paginator, AppServices $services, AuthorizationCheckerInterface $authChecker)
    {
        $slug = ($request->query->get('slug')) == "" ? "all" : $request->query->get('slug');
        $category = ($request->query->get('category')) == "" ? "all" : $request->query->get('category');
        $venue = ($request->query->get('venue')) == "" ? "all" : $request->query->get('venue');
        $elapsed = ($request->query->get('elapsed')) == "" ? "all" : $request->query->get('elapsed');
        $published = ($request->query->get('published')) == "" ? "all" : $request->query->get('published');

        $organizer = "all";
        if ($authChecker->isGranted('ROLE_ORGANIZER')) {
            $organizer = $this->getUser()->getOrganizer()->getSlug();
        }

        $events = $paginator->paginate($services->getEvents(array("slug" => $slug, "category" => $category, "venue" => $venue, "elapsed" => $elapsed, "published" => $published, "organizer" => $organizer, "sort" => "startdate", "organizerEnabled" => "all", "sort" => "e.createdAt", "order" => "DESC"))->getQuery(), $request->query->getInt('page', 1), 10, array('wrap-queries' => true));

        return $this->render('Dashboard/Shared/Event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/organizer/my-events/add", name="dashboard_organizer_event_add", methods="GET|POST")
     * @Route("/organizer/my-events/{slug}/edit", name="dashboard_organizer_event_edit", methods="GET|POST")
     */
    public function addedit(Request $request, AppServices $services, TranslatorInterface $translator, $slug = null, AuthorizationCheckerInterface $authChecker, EntityManagerInterface $entityManager, Connection $connection)
    {

        $em = $this->getDoctrine()->getManager();
        $organizer = "all";
        if ($authChecker->isGranted('ROLE_ORGANIZER')) {
            $organizer = $this->getUser()->getOrganizer()->getSlug();
        }

        if (!$slug) {
            $event = new Event();
            $form = $this->createForm(EventType::class, $event, array('validation_groups' => ['create', 'Default']));
        } else {
            $event = $services->getEvents(array('published' => 'all', "elapsed" => "all", 'slug' => $slug, 'organizer' => $organizer, "organizerEnabled" => "all"))->getQuery()->getOneOrNullResult();
            if (!$event) {
                $this->addFlash('error', $translator->trans('The event can not be found'));
                return $services->redirectToReferer('event');
            }
            $reference = $event->getReference();
            $form = $this->createForm(EventType::class, $event, array('validation_groups' => ['update', 'Default']));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // $file = $_FILES['subscriber_file'];
            // dd($file);
            if ($form->isValid()) {
                $input = $request->request->all();
                $file = $_FILES['subscriber_file'];
                if (isset($reference)) {
                    $sql = "SELECT name, surname, email, phone_number, department, city, country, address 
                            FROM event_mails 
                            WHERE event_ref_id = :ref_id";
                    $params = ['ref_id' => $reference];
                    $statement = $connection->prepare($sql);
                    $statement->execute($params);
                    $csv_info = $statement->fetchAll();
                }
                
                
                if (!$slug || $file['type'] != '') {
                    $csv_upload = $this->event_mails_csv_check($event->getReference(), $input, $file, $entityManager);
                }

                if(($slug && $file['type'] == '') || $csv_upload ){
                    foreach ($event->getImages() as $image) {
                        $image->setEvent($event);
                    }
                    foreach ($event->getEventdates() as $eventdate) {
                        $eventdate->setEvent($event);
                        if (!$slug || !$eventdate->getReference()) {
                            $eventdate->setReference($services->generateReference(10));
                        }
                        foreach ($eventdate->getTickets() as $eventticket) {
                            $eventticket->setEventdate($eventdate);
                            if (!$slug || !$eventticket->getReference()) {
                                $eventticket->setReference($services->generateReference(10));
                            }
                        }
                    }
                    if (!$slug) {
                        $event->setOrganizer($this->getUser()->getOrganizer());
                        $rr = $event->setReference($services->generateReference(10));
                        $this->event_mails_data_save($rr->getReference(), $input, $file, $entityManager);
                        $this->addFlash('success', $translator->trans('The event has been successfully created'));
                    } else {
                        if ($file['type'] != '') {
                            $this->event_mails_data_edit($reference, $input,$csv_info, $file, $entityManager);
                        }
                        $this->addFlash('success', $translator->trans('The event has been successfully updated'));
                    }
                    $em->persist($event);
                    $em->flush();
                    if ($authChecker->isGranted('ROLE_ORGANIZER')) {
                        return $this->redirectToRoute("dashboard_organizer_event");
                    } elseif ($authChecker->isGranted('ROLE_ADMINISTRATOR')) {
                        return $this->redirectToRoute("dashboard_administrator_event");
                    }
                } else {
                    $this->addFlash('error', $translator->trans('Must be upload a valid CSV file. Download and show demo CSV'));
                }

            } else {
                $this->addFlash('error', $translator->trans('The form contains invalid data'));
            }
        }

        // organizer list get
        $sqlSelect = "SELECT * FROM subscriber_lists";
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute();
        $subscriber_lists = $statementSelect->fetchAll();
        return $this->render('Dashboard/Shared/Event/add-edit.html.twig', array(
            "event"          => $event,
            "form"           => $form->createView(),
            "subscriber_lists" => $subscriber_lists
        ));
    }


    public function event_mails_csv_check($event_ref_id, $input, $file, $entityManager)
    {
        try {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $tmpFilePath = $file['tmp_name'];
                if ($file['type'] === 'text/csv') {

                    $handle = fopen($tmpFilePath, 'r');
                    $datas = [];
                    $headers = fgetcsv($handle);
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) !== count($headers)) {
                            continue;
                        }
                        $datas[] = array_combine($headers, $row);
                    }

                    fclose($handle);
                    $subscriber_list_id = $input['subscriber_id'];
                    $send_type = $input['event']['sendevent'] == 1 ? 'corporate' : 'massive';
                    $send_chanel = $input['event']['sendchanel'] == 1 ? 'email' : 'whatsapp';
                    foreach ($datas as $data) {
                        if(isset($data['email']) && $data['email'] == ""){
                            continue;
                        }
                        $data['name'];
                        $data['surname'];
                        $data['email'];
                        $country_code = preg_replace('/[^0-9]/', '', $data['country_code']);
                        $phone_number = preg_replace('/[^0-9]/', '', $data['phone_number']);
                        $data['department'];
                        $data['city'];
                        $data['country'];
                        $data['address'];

                        $sql = "INSERT INTO event_mails (event_ref_id, send_type, send_chanel,subscriber_list_id, name, surname, email, country_code, phone_number, department, city, country,address) 
                    VALUES (:event_ref_id, :send_type, :send_chanel,:subscriber_list_id, :name, :surname, :email, :country_code, :phone_number, :department, :city, :country,:address)";
                        $params = [
                            'event_ref_id'       => $event_ref_id,
                            'send_type'          => $send_type,
                            'send_chanel'        => $send_chanel,
                            'subscriber_list_id' => $subscriber_list_id,
                            'name'               => $data['name'],
                            'surname'            => $data['surname'],
                            'email'              => $data['email'],
                            'country_code'       => $country_code,
                            'phone_number'       => $phone_number,
                            'department'         => $data['department'],
                            'city'               => $data['city'],
                            'country'            => $data['country'],
                            'address'            => $data['address'],
                        ];

                        $statement = $entityManager->getConnection()->prepare($sql);
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    public function event_mails_data_save($event_ref_id, $input, $file, $entityManager)
    {
        try {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $tmpFilePath = $file['tmp_name'];
                if ($file['type'] === 'text/csv') {

                    $handle = fopen($tmpFilePath, 'r');
                    $datas = [];
                    $headers = fgetcsv($handle);
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) !== count($headers)) {
                            continue;
                        }
                        $datas[] = array_combine($headers, $row);
                    }

                    fclose($handle);
                    $subscriber_list_id = $input['subscriber_id'];
                    $send_type = $input['event']['sendevent'] == 1 ? 'corporate' : 'massive';
                    $send_chanel = $input['event']['sendchanel'] == 1 ? 'whatsapp' : 'email';
                    foreach ($datas as $data) {
                        if(isset($data['email']) && $data['email'] == ""){
                            continue;
                        }
                        $data['name'];
                        $data['surname'];
                        $data['email'];
                        $country_code = preg_replace('/[^0-9]/', '', $data['country_code']);
                        $phone_number = preg_replace('/[^0-9]/', '', $data['phone_number']);
                        $data['department'];
                        $data['city'];
                        $data['country'];
                        $data['address'];

                        $sql = "INSERT INTO event_mails (event_ref_id, send_type, send_chanel,subscriber_list_id, name, surname, email, country_code, phone_number, department, city, country,address) 
                        VALUES (:event_ref_id, :send_type, :send_chanel,:subscriber_list_id, :name, :surname, :email, :country_code, :phone_number, :department, :city, :country,:address)";
                        $params = [
                            'event_ref_id'       => $event_ref_id,
                            'send_type'          => $send_type,
                            'send_chanel'        => $send_chanel,
                            'subscriber_list_id' => $subscriber_list_id,
                            'name'               => $data['name'],
                            'surname'            => $data['surname'],
                            'email'              => $data['email'],
                            'country_code'       => $country_code,
                            'phone_number'       => $phone_number,
                            'department'         => $data['department'],
                            'city'               => $data['city'],
                            'country'            => $data['country'],
                            'address'            => $data['address'],
                        ];

                        $statement = $entityManager->getConnection()->prepare($sql);
                        $success = $statement->execute($params);

                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function event_mails_data_edit($event_ref_id, $input, $csv_info, $file, $entityManager)
    {
        try {
            if ($file['error'] === UPLOAD_ERR_OK) {
                
                $tmpFilePath = $file['tmp_name'];
                if ($file['type'] === 'text/csv') {
                   
                    $handle = fopen($tmpFilePath, 'r');
                    $datas = [];
                    $headers = fgetcsv($handle);
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) !== count($headers)) {
                            continue;
                        }
                        $datas[] = array_combine($headers, $row);
                    }
                  
                    foreach ($datas as $newRow) {
                        $matched = false;
                        foreach ($csv_info as $existingRow) {
                            if ($existingRow['email'] == $newRow['email'] || $existingRow['phone_number'] == $newRow['phone_number']) {
                                $matched = true;
                                break;
                            }
                        }
                        if (!$matched) {
                            $differences[] = $newRow;
                        }
                    }
                
                    fclose($handle);
                
                    if(!empty($differences)){
                        $subscriber_list_id = $input['subscriber_id'];
                        $send_type = $input['event']['sendevent'] == 1 ? 'corporate' : 'massive';
                        $send_chanel = $input['event']['sendchanel'] == 1 ? 'whatsapp' : 'email';
                        foreach ($differences as $data) {
                            if(isset($data['email']) && $data['email'] == ""){
                                continue;
                            }
                            $data['name'];
                            $data['surname'];
                            $data['email'];
                            $country_code = preg_replace('/[^0-9]/', '', $data['country_code']);
                            $phone_number = preg_replace('/[^0-9]/', '', $data['phone_number']);
                            $data['department'];
                            $data['city'];
                            $data['country'];
                            $data['address'];

                            $sql = "INSERT INTO event_mails (event_ref_id, send_type, send_chanel,subscriber_list_id, name, surname, email, country_code, phone_number, department, city, country,address) 
                            VALUES (:event_ref_id, :send_type, :send_chanel,:subscriber_list_id, :name, :surname, :email, :country_code, :phone_number, :department, :city, :country,:address)";
                            $params = [
                                'event_ref_id'       => $event_ref_id,
                                'send_type'          => $send_type,
                                'send_chanel'        => $send_chanel,
                                'subscriber_list_id' => $subscriber_list_id,
                                'name'               => $data['name'],
                                'surname'            => $data['surname'],
                                'email'              => $data['email'],
                                'country_code'       => $country_code,
                                'phone_number'       => $phone_number,
                                'department'         => $data['department'],
                                'city'               => $data['city'],
                                'country'            => $data['country'],
                                'address'            => $data['address'],
                            ];

                            $statement = $entityManager->getConnection()->prepare($sql);
                            $success = $statement->execute($params);
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @Route("/administrator/manage-events/{slug}/delete-permanently", name="dashboard_administrator_event_delete_permanently", methods="GET")
     * @Route("/administrator/manage-events/{slug}/delete", name="dashboard_administrator_event_delete", methods="GET")
     * @Route("/organizer/my-events/{slug}/delete-permanently", name="dashboard_organizer_event_delete_permanently", methods="GET")
     * @Route("/organizer/my-events/{slug}/delete", name="dashboard_organizer_event_delete", methods="GET")
     */
    public function delete(Request $request, AppServices $services, TranslatorInterface $translator, $slug, AuthorizationCheckerInterface $authChecker)
    {
        $organizer = "all";
        if ($authChecker->isGranted('ROLE_ORGANIZER')) {
            $organizer = $this->getUser()->getOrganizer()->getSlug();
        }

        $event = $services->getEvents(array("slug" => $slug, "published" => "all", "elapsed" => "all", "organizer" => $organizer, "organizerEnabled" => "all"))->getQuery()->getOneOrNullResult();
        if (!$event) {
            $this->addFlash('error', $translator->trans('The event can not be found'));
            return $services->redirectToReferer('event');
        }

        if ($event->getOrderElementsQuantitySum() > 0) {
            $this->addFlash('error', $translator->trans('The event can not be deleted because it has one or more orders'));
            return $services->redirectToReferer('event');
        }
        if ($event->getDeletedAt() !== null) {
            $this->addFlash('error', $translator->trans('The event has been deleted permanently'));
        } else {
            $this->addFlash('notice', $translator->trans('The event has been deleted'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
        return $services->redirectToReferer('event');
    }

    /**
     * @Route("/administrator/manage-events/{slug}/restore", name="dashboard_administrator_event_restore", methods="GET")
     */
    public function restore($slug, Request $request, TranslatorInterface $translator, AppServices $services)
    {

        $event = $services->getEvents(array("slug" => $slug, "published" => "all", "elapsed" => "all", "organizer" => "all", "organizerEnabled" => "all"))->getQuery()->getOneOrNullResult();
        if (!$event) {
            $this->addFlash('error', $translator->trans('The event can not be found'));
            return $services->redirectToReferer('event');
        }
        $event->setDeletedAt(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();
        $this->addFlash('success', $translator->trans('The event has been succesfully restored'));

        return $services->redirectToReferer('event');
    }

    /**
     * @Route("/organizer/my-events/{slug}/publish", name="dashboard_organizer_event_publish", methods="GET")
     * @Route("/organizer/my-events/{slug}/draft", name="dashboard_organizer_event_draft", methods="GET")
     */
    public function showhide(Request $request, AppServices $services, TranslatorInterface $translator, $slug, AuthorizationCheckerInterface $authChecker)
    {

        $organizer = "all";
        if ($authChecker->isGranted('ROLE_ORGANIZER')) {
            $organizer = $this->getUser()->getOrganizer()->getSlug();
        }

        $event = $services->getEvents(array("slug" => $slug, "published" => "all", "elapsed" => "all", "organizer" => $organizer, "organizerEnabled" => "all"))->getQuery()->getOneOrNullResult();
        if (!$event) {
            $this->addFlash('error', $translator->trans('The event can not be found'));
            return $services->redirectToReferer('event');
        }
        if ($event->getPublished() === true) {
            $event->setPublished(false);
            $this->addFlash('notice', $translator->trans('The event has been unpublished and will not be included in the search results'));
        } else {
            $event->setPublished(true);
            $this->addFlash('success', $translator->trans('The event has been published and will figure in the search results'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();
        return $services->redirectToReferer('event');
    }

    /**
     * @Route("/administrator/manage-events/{slug}/details", name="dashboard_administrator_event_details", methods="GET", condition="request.isXmlHttpRequest()")
     * @Route("/organizer/my-events/{slug}/details", name="dashboard_organizer_event_details", methods="GET", condition="request.isXmlHttpRequest()")
     */
    public function details(Request $request, AppServices $services, TranslatorInterface $translator, $slug, AuthorizationCheckerInterface $authChecker,Connection $connection)
    {

        $organizer = "all";
        if ($authChecker->isGranted('ROLE_ORGANIZER')) {
            $organizer = $this->getUser()->getOrganizer()->getSlug();
        }

        $event = $services->getEvents(array("slug" => $slug, "published" => "all", "elapsed" => "all", "organizer" => $organizer, "organizerEnabled" => "all"))->getQuery()->getOneOrNullResult();
        if (!$event) {
            return new Response($translator->trans('The event can not be found'));
        }
        
        $eventDate = $event->getEventDates()->toArray();
        $meeting_id =  isset($eventDate[0]) ? $eventDate[0]->getMeetinglink() : null;

        $sql = "SELECT * FROM event_zoom_meeting_list WHERE id = :id";
        $params = ['id' => $meeting_id];
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        $event_meeting = $statement->fetch();

        $meeting_link = $event_meeting['start_url'];

        if (!$meeting_link) {
            $this->addFlash('error', $translator->trans('The Host Join can not be found'));
            return $this->redirect($request->headers->get('referer'));
        }
    

        return $this->render('Dashboard/Shared/Event/details.html.twig', [
            'event' => $event,
            'meeting_link' => $meeting_link
        ]);
    }


    /**
     * @Route("/organizer/my-events/addlist", name="dashboard_organizer_event_addlist", methods="GET|POST")
     */
    public function addlist(Request $request, EntityManagerInterface $entityManager)
    {
        $input = $request->request->all();

        $sql = "INSERT INTO subscriber_lists (name, tag, description) VALUES (:name, :tag, :description)";
        $params = [
            'name'          => trim($input['name']),
            'tag'           => trim($input['tag']),
            'description'   => trim($input['description'])
        ];

        $statement = $entityManager->getConnection()->prepare($sql);
        $statement->execute($params);
        return $this->redirectToRoute('dashboard_organizer_event_add');
    }

    /**
     * @Route("/file/preview", name="file_preview_download", methods="GET")
     */
    public function filePreview(Request $request, KernelInterface $kernel)
    {
        $filePath = $kernel->getProjectDir() . '/public/demo_file.csv';
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('The file does not exist');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filePath));
        return $response;
    }
}
