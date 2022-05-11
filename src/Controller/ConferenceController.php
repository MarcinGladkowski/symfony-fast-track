<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\Notifier\NotifierInterface;

class ConferenceController extends AbstractController
{
    public function __construct(
        private Environment $twig,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    )
    {}

    #[Route('/conference_header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        return new Response($this->twig->render('conference/header.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));
    }

    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        $response =  new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));

        $response->setSharedMaxAge(3600);

        return $response;
    }

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        string $slug,
        ConferenceRepository $conferenceRepository,
        CommentRepository $commentRepository,
        string $photoDir,
        SpamChecker $spamChecker,
        NotifierInterface $notifier
    )
    {
        $conference = $this->getConference($conferenceRepository, $slug);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $comment->setConference($conference);

            if ($photo = $form['photoFilename']->getData()) {
                $filename = bin2hex(random_bytes(6) . '.' . $photo->guessExtension());

                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    throw $e;
                    // unable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            $reviewUrl = $this->generateUrl('review_comment', ['id' => $comment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl, $context));

            $notifier->send(new Notification('Thank you for the feedback; your comment will be posted after moderation.', ['browser']));

            $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $commentRepository->getPaginator($conference, $offset);

        $response = new Response(
          $this->twig->render('conference/show.html.twig', [
              'conference' => $conference,
              'conferences' => $conferenceRepository->findAll(),
              'comments' => $paginator,
              'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
              'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
              'comment_form' => $form->createView(),
          ])
        );

        if ($form->isSubmitted()) {
            $notifier->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
        }

        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @param ConferenceRepository $conferenceRepository
     * @param string $slug
     * @return Conference|null
     */
    public function getConference(ConferenceRepository $conferenceRepository, string $slug): ?Conference
    {
        return $conferenceRepository->findOneBy(['slug' => $slug]);
    }
}
