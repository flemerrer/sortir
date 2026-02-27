<?php

    namespace App\Scheduler;

    use App\Scheduler\Message\ArchiveSortie;
    use App\Scheduler\Message\UpdateSortie;
    use Symfony\Component\Scheduler\Attribute\AsSchedule;
    use Symfony\Component\Scheduler\RecurringMessage;
    use Symfony\Component\Scheduler\Schedule;
    use Symfony\Component\Scheduler\ScheduleProviderInterface;

    #[AsSchedule('sortie_update')]
    class UpdateSortieProvider implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $schedule = new Schedule();
            $message1 = new ArchiveSortie();
            $message2 = new UpdateSortie();
            return $schedule->with(
                    RecurringMessage::every('1 day', $message1),
                    RecurringMessage::cron('0 * * * *', $message2),
                );
        }
    }
