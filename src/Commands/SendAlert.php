<?php

namespace Sirgrimorum\CrudGenerator\Commands;

use Illuminate\Console\Command;

class SendAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crudgen:sendalert ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast an alert to a user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $alertaClass = $this->ask("Notification Class?");
        if (class_exists($alertaClass)) {
            $email = $this->anticipate("User email?", ['andres.espinosa@grimorum.com']);
            if ($toUser = \App\User::where("email", "=", $email)->first()) {
                $message = $this->ask("Message?");
                /* $options = array(
                      'cluster' => 'us2',
                      'encrypted' => true
                      );
                      $pusher = new Pusher(
                      'adf503a8756876656ec6', 'b21a0ee639fbb893e41d', '481076', $options
                      );
                     */
                $data = [];
                $data['message'] = $message;
                /* $result = $pusher->trigger('my-channel', 'my-event', $data, null, true);
                      $this->line(print_r($result, true)); */
                $tiempo = $this->ask("Send in? (Just a number,next you will select seconds, minutes, etc.)");
                if (!is_int((int) $tiempo)) {
                    $tiempo = $this->ask("Send in? HAS TO BE AN INTEGER NUMBER");
                }
                if (is_int((int) $tiempo)) {
                    $unidad = $this->choice('...', ['seconds', 'minutes', 'hours', 'days'], 0);
                    switch ($unidad) {
                        case 'seconds':
                            $when = now()->addSeconds($tiempo);
                            break;
                        case 'minutes':
                            $when = now()->addMinutes($tiempo);
                            break;
                        case 'hours':
                            $when = now()->addHours($tiempo);
                            break;
                        case 'days':
                            $when = now()->addDays($tiempo);
                            break;
                        default:
                            $when = now()->addMinutes($tiempo);
                            break;
                    }
                    $toUser->notify((new $alertaClass($data))
                        ->delay($when));
                    $this->info("Listo!");
                } else {
                    $this->error("Paila!");
                }
            } else {
                $this->error("User not found");
            }
        } else {
            $this->error("Class not found");
        }
    }
}
