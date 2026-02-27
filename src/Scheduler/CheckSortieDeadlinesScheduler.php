<?php

    namespace App\Scheduler;

    use App\Scheduler\Task\CheckSortieDeadlinesTask;
    use Symfony\Component\Scheduler\Attribute\AsSchedule;
    use Symfony\Component\Scheduler\RecurringMessage;
    use Symfony\Component\Scheduler\Schedule;
    use Symfony\Component\Scheduler\ScheduleProviderInterface;

    #[AsSchedule(name: 'check_sortie_deadlines')]
    class CheckSortieDeadlinesScheduler implements ScheduleProviderInterface
    {
        public function getSchedule(): Schedule
        {
            $task = new  CheckSortieDeadlinesTask();
            return (new Schedule())
                ->add(
                    RecurringMessage::cron('@daily', $task)
                );
        }
    }
