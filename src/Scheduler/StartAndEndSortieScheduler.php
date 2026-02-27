<?php

    namespace App\Scheduler;

    use App\Scheduler\Task\StartAndEndSortieTask;
    use Symfony\Component\Scheduler\Attribute\AsSchedule;
    use Symfony\Component\Scheduler\RecurringMessage;
    use Symfony\Component\Scheduler\Schedule;
    use Symfony\Component\Scheduler\ScheduleProviderInterface;

    #[AsSchedule(name: 'start_and_end_sortie')]
    class StartAndEndSortieScheduler implements ScheduleProviderInterface
    {

        public function getSchedule(): Schedule
        {
            $task = new  StartAndEndSortieTask();
            return (new Schedule())
                ->add(
                    RecurringMessage::cron('0 * * * * *', $task)
                );
        }

    }
