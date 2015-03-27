<?php

use Illuminate\Console\Command;

class UpdateCoursesDescription extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'courseadvisor:courses-update-description';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve courses description from course link on edu.epfl.ch';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        print "=======================================\n";
        print "====== UPDATE COURSES DESCRIPTION =====\n";
        print "=======================================\n\n";


        $courses = DB::table('courses')->get();

        foreach($courses as $course) {
            $this->updateCourse($course);
        }
    }

    private function updateCourse($course)
    {
        print "course: ".$course->name." =>\t";
    }

}
