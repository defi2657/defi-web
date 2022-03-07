<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
 
        Commands\AutoCheckCharge::class,
        Commands\AutoExchange::class,
        Commands\MiningBonus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {   
        // $schedule->command('test')->everyMinute()->appendOutputTo('./test.log'); //test
        // $schedule->command('FinancialBonus')->withoutOverlapping()->dailyAt('00:01')->appendOutputTo('./financial_bonus.log');
        //        $schedule->command('update_balance')->hourly()->withoutOverlapping(); //更新链上余额
//        $schedule->command('update_hash_status')->everyMinute()->withoutOverlapping(); //更新哈希值状态
        // $schedule->command('market:clear:volume')->withoutOverlapping()->dailyAt('00:00'); //清空24小时成交量
        // $schedule->command('lever:overnight')->dailyAt('00:01'); //收取隔夜费
        // $schedule->command('auto_cancel_legal')->everyMinute()->appendOutputTo('./auto_cancel_legal.log');
        // $schedule->command('mining_bonus')->withoutOverlapping()->dailyAt('00:01')->appendOutputTo('./mining_bonus.log');//矿机分红
        // $schedule->command('MiningBuyBonus')->everyMinute()->appendOutputTo('./mining_buy_bonus.log');


        $schedule->command('auto_check_charge')->everyMinute()->appendOutputTo('./auto_check_charge.log'); //根据充币hash更新链上余额
        $schedule->command('FinancialBonus')->everyMinute()->appendOutputTo('./financial_bonus.log');
        $schedule->command('AgentBonus')->everyMinute()->appendOutputTo('./agent_bonus.log');
        $schedule->command('auto_exchange')->everyMinute()->appendOutputTo('./auto_exchange.log');

        //match
//        $schedule->command('match')->everyMinute()->appendOutputTo('./match.log');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
