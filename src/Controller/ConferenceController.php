<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    public function __construct(private Environment $twig)
    {}

    #[Route('/', name: 'homepage')]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));
    }

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        string $slug,
        ConferenceRepository $conferenceRepository,
        CommentRepository $commentRepository
    )
    {
        $conference = $this->getConference($conferenceRepository, $slug);

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $offset = max(0, $request->query->getInt('offset'));
        $paginator = $commentRepository->getPaginator($conference, $offset);

        return new Response(
          $this->twig->render('conference/show.html.twig', [
              'conference' => $conference,
              'conferences' => $conferenceRepository->findAll(),
              'comments' => $paginator,
              'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
              'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
              'comment_form' => $form->createView(),
          ])
        );
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
