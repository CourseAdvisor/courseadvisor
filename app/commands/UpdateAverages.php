<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateAverages extends Command {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'courseadvisor:update-averages';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description.';

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
  public function fire()
  {
    Course::all()->each(function($course) {
      $course->updateAverages();
    });
  }

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
    );
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
    );
  }

}
