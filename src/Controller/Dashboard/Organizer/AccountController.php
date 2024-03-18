<?php

namespace App\Controller\Dashboard\Organizer;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\OrganizerProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class AccountController extends Controller {

    /**
     * @Route("/profile", name="profile", methods="GET|POST")
     */
    public function edit(Request $request, TranslatorInterface $translator, EntityManagerInterface $entityManager) {
        $form = $this->createForm(OrganizerProfileType::class, $this->getUser()->getOrganizer());
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($this->getUser()->getOrganizer());
                $em->flush();
                $this->addFlash('success', $translator->trans('Your organizer profile has been successfully updated'));
            } else {
                $this->addFlash('error', $translator->trans('The form contains invalid data'));
            }
        }

        $user    = $this->getUser();
        $userId  = $user->getId();
        $apiType = 'zoom';
        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
            'user_id' => $userId,
            'api_type' => $apiType,
        ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $zoom_data = $statementSelect->fetch();


        $googleApiType = 'google';
        $googleparamsSelect = [
            'user_id' => $userId,
            'api_type' => $googleApiType,
        ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($googleparamsSelect);
        $google_data = $statementSelect->fetch();
        
        return $this->render('Dashboard/Organizer/Account/profile.html.twig', array(
                "form" => $form->createView(),
                'zoom_data' => $zoom_data,
                'google_data' => $google_data
        ));
    }


    public function zoom_api_setting(Request $request, EntityManagerInterface $entityManager) {

        $input   = $request->request->all();
        $user    = $this->getUser();
        $userId  = $user->getId();
        $apiType = 'zoom';

        $zoomAccountId      = $input['zoom_account_id'];
        $zoomClintId       = $input['zoom_clint_id'];
        $zoomClintSecret   = $input['zoom_clint_secret'];

        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
                'user_id' => $userId,
                'api_type' => $apiType,
            ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $row = $statementSelect->fetch();

        if ($row) {
            $sql = "UPDATE api_settings SET zoom_account_id = :zoom_account_id, zoom_clint_id = :zoom_clint_id, zoom_clint_secret = :zoom_clint_secret WHERE user_id = :user_id AND api_type = :api_type";
        } else {
            $sql = "INSERT INTO api_settings (user_id, api_type, zoom_account_id, zoom_clint_id, zoom_clint_secret) 
            VALUES (:user_id, :api_type, :zoom_account_id, :zoom_clint_id, :zoom_clint_secret)";
        }

        $params = [
            'user_id' => $userId,
            'api_type' => $apiType,
            'zoom_account_id' => $zoomAccountId,
            'zoom_clint_id' => $zoomClintId,
            'zoom_clint_secret' => $zoomClintSecret,
        ];
        $statement = $entityManager->getConnection()->prepare($sql);
        $statement->execute($params);
        return $this->redirectToRoute('dashboard_organizer_profile');
    }


    public function google_api_setting(Request $request, EntityManagerInterface $entityManager, KernelInterface $kernel) {

        $input         = $request->request->all();
        $googleCalendarId = $input['google_calendar_id'];
        $user          = $this->getUser();
        $userId        = $user->getId();
        $apiType       = 'google';

        $uploadedFile  = $request->files->get('google_filename');
        $rootPath      = $kernel->getProjectDir() . '/public/google_credential';

        $sqlSelect = "SELECT * FROM api_settings WHERE user_id = :user_id AND api_type = :api_type";
        $paramsSelect = [
                'user_id' => $userId,
                'api_type' => $apiType,
            ];
        $statementSelect = $entityManager->getConnection()->prepare($sqlSelect);
        $statementSelect->execute($paramsSelect);
        $row = $statementSelect->fetch();

        if ($row) {
            if ($uploadedFile != null) {
                if ($uploadedFile instanceof UploadedFile) {
                    $fileName = 'google_credential_' . $userId . '.json';
                    $path = $uploadedFile->move($rootPath, $fileName);
                }
                $sql = "UPDATE api_settings SET google_calendar_id = :google_calendar_id, google_filename = :google_filename WHERE user_id = :user_id AND api_type = :api_type";
            } else {
                $sql = "UPDATE api_settings SET google_calendar_id = :google_calendar_id WHERE user_id = :user_id AND api_type = :api_type";
            }
        } else {
            if ($uploadedFile != null) {
                if ($uploadedFile instanceof UploadedFile) {
                    $fileName = 'google_credential_' . $userId . '.json';
                    $path = $uploadedFile->move($rootPath, $fileName);
                }
                $sql = "INSERT INTO api_settings (user_id, api_type, google_calendar_id, google_filename) VALUES (:user_id, :api_type, :google_calendar_id, :google_filename)";
            } else {
                $sql = "INSERT INTO api_settings (user_id, api_type, google_calendar_id) VALUES (:user_id, :api_type, :google_calendar_id)";
            }
           
        }
        if ($uploadedFile != null) {
            $params = [
                'user_id' => $userId,
                'api_type' => $apiType,
                'google_calendar_id' => $googleCalendarId,
                'google_filename' => $fileName,
            ];
        }else{
            $params = [
                'user_id' => $userId,
                'api_type' => $apiType,
                'google_calendar_id' => $googleCalendarId,
            ];
        }
        $statement = $entityManager->getConnection()->prepare($sql);
        $statement->execute($params);
        return $this->redirectToRoute('dashboard_organizer_profile');
    }



}
