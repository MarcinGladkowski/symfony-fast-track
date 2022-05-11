<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\ImageOptimizer;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use CommentAcceptNotification;
use CommentReviewNotification;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpamChecker            $spamChecker,
        private CommentRepository      $commentRepository,
        private MessageBusInterface    $bus,
        private WorkflowInterface      $commentStateMachine,
        private NotifierInterface      $notifier,
        private LoggerInterface        $logger,
        private ImageOptimizer         $imageOptimizer,
        private string                 $photoDir,
    )
    {
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->id());
        if (!$comment) {
            return;
        }

        if ($this->commentStateMachine->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->context());
            $transition = 'accept';
            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }
            $this->commentStateMachine->apply($comment, $transition);
            $this->entityManager->flush();

            $this->bus->dispatch($message);
        } elseif ($this->commentStateMachine->can($comment, 'publish') || $this->commentStateMachine->can($comment, 'publish_ham')) {

            $notification = new CommentReviewNotification($comment, $message->getReviewUrl());

            $this->notifier->send($notification, ...$this->notifier->getAdminRecipients());

            $recipient = new Recipient(
                $comment->getEmail(),
            );

            $this->notifier->send(new CommentAcceptNotification($comment), $recipient);

        } elseif ($this->commentStateMachine->can($comment, 'optimize')) {
            if ($comment->getPhotoFilename()) {
                $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
            }
            $this->commentStateMachine->apply($comment, 'optimize');
            $this->entityManager->flush();
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}