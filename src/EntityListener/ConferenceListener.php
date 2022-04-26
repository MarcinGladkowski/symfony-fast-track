<?php



namespace App\EntityListener;

use App\Entity\Conference;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ConferenceListener
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function prePersist(Conference $conference, LifecycleEventArgs $eventArgs)
    {
        $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Conference $conference, LifecycleEventArgs $eventArgs)
    {
        $conference->computeSlug($this->slugger);
    }
}
