<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoController extends Controller
{

    /**
     * @Route("/", name="video_upload")
     * @param Request $request
     * @param LoggerInterface $logger
     * @return Response
     */
    public function index(Request $request, LoggerInterface $logger)
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $logger->info('Saving video');
            $em = $this->getDoctrine()->getManager();
            $em->persist($video);
            $em->flush();

            return $this->redirectToRoute(
                'video_show',
                [
                    'id' => $video->getId(),
                ]
            );
        }

        return $this->render(
            'video/upload.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/show/{id}", name="video_show")
     * @param Video $video
     * @return Response
     */
    public function show(Video $video)
    {
        return $this->render('video/show.html.twig', compact('video'));
    }
}
