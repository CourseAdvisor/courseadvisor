<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;


class DumpStudyPlan extends Command {

  const SOURCE_DOMAIN = "http://edu.epfl.ch";

  const PEOPLE_URL = "http://people.epfl.ch/%sciper%";

  private $SEMESTERS = [
    'Bachelor 1' => 'BA1',
    'Bachelor 2' => 'BA2',
    'Bachelor 3' => 'BA3',
    'Bachelor 4' => 'BA4',
    'Bachelor 5' => 'BA5',
    'Bachelor 6' => 'BA6',
    'Master 1' => 'MA1',
    'Master 2' => 'MA2',
    'Master 3' => 'MA3',
    'Master 4' => 'MA4',
    'MP Autumn' => 'MA1',
    'MP Spring' => 'MA2',
    'PDM Automne' => 'MA3',
    'PDM Printemps' => 'MA2'
  ];

  // Maps course language strings as found in the page's HTML to actual locales
  private $LANGUAGES = [
    'francais' => 'fr',
    'anglais' => 'en',
    'allemand' => 'de'
  ];

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'courseadvisor:dump-plan';


  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Dump study plan from edu.epfl.ch';


  protected function getOptions()
    {
         return array(
            array('skip-description', 'sd', InputOption::VALUE_NONE, 'Do not dump descriptions (saves huge time)', null)
        );
    }


  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {
    print "=============================\n";
    print "====== DUMP STUDY PLANS =====\n";
    print "=============================\n\n";


    $plans = StudyPlan::get();

    foreach($plans as $plan) {
      $this->dumpPlan($plan);
    }
    echo "Done :)\n";
  }

  /**
   * Dump a StudyPlan $plan.
   * Parses the plan page at edu.epfl.ch/studyplan in english and in french,
   * then merges the two and saves everything.
   */
  private function dumpPlan($plan)
  {
    print "[Info] Dumping plan: ".$plan->name."\n";

    $courses_en = $this->parsePlan($this->curlGet($plan->url_en));
    $courses_fr = $this->parsePlan($this->curlGet($plan->url_fr));

    $courses = $this->mergePlans($courses_en, $courses_fr);

    $this->savePlan($plan, $courses);
  }

  /**
   * Saves the php array of raw courses $courses to the StudyPlan $plan
   */
  private function savePlan($plan, $courses)
  {
    foreach($courses as $string_id => $course) {

      // Extract semesters
      $semesters = $course['semesters'];
      unset($course['semesters']);

      // Ensures teacher
      if (!$course['teacher_id'] = $this->ensureTeacher($course['teacher'])) {
        echo "[WARNING]: Skipping ".$course['name_en']." due to unknown teacher\n";
        continue;
      }
      unset($course['teacher']);

      // Ensures section
      if(!$section = Section::where('string_id', $course['section'])->first()) {
        echo "[WARNING]: No section with id: ".$course['section']."\n";
        echo "           Creating one, consider updating its name manually\n";

        $section = new Section(['string_id' => $course['section']]);
        $section->save();
      }
      unset($course['section']);
      $course['section_id'] = $section->id;


      if (!$this->option('skip-description')) {
        // Only crawling one description locale because they are often identical and it takes quite some time
        if (!$course['description'] = $this->fetchCourseDescription($course['url_en'])) {
          if (!$course['description'] = $this->fetchCourseDescription($course['url_fr'])) {
            echo "[Warning] Description not found for course ".$course['name_en']."\n";
          }
        }
      }

      $course_attrs = $this->filterCourseAttributes($course);
      $instance_attrs = $this->filterCourseInstanceAttributes($course);

      // Saving course
      $existing = Course::where('string_id', $string_id)->first();
      if ($existing) {
        // check if its actually the same course or it has just taken the string_id
        if (strcmp($existing->name_en, $course_attrs['name_en'])) {
          echo "[Need help] The course ".$existing->string_id." has changed: ";
          echo " ".$existing->name_en."\n -> ".$course_attrs['name_en']."\n";
          $confirm = "???";
          while ($confirm != 'c' && $confirm != 'o') {
            $confirm = $this->ask('What do we do? [c]reate a new course, [o]verwrite existing course (when in doubt, choose "o")');
          }
          if($confirm == 'c') {
            $existing = new Course($course_attrs); // new course
            $existing->save();
          } else if ($confirm == 'o') {
            Course::where('id', $existing->id)->update($course_attrs); // overwrite
          }
        } else {
          Course::where('id', $existing->id)->update($course_attrs); // overwrite
        }
      } else {
        $existing = new Course($course_attrs); // new course
        $existing->save();
      }

      // Saving course instance. If it's the same, update
      $instance = CourseInstance::where(array(
        'course_id' => $existing->id,
        'year' => date('Y')
      ))->first();
      if($instance) {
        CourseInstance::where('id', $instance->id)->update($instance_attrs);
      } else {
        $instance = new CourseInstance($instance_attrs);
        $instance->course_id = $existing->id;
        $instance->save();
      }


      // update studyplan relations
      foreach($semesters as $semester) {
        try {
          $existing->plans()->attach($plan->id, ["semester" => $semester]);
        } catch (Exception $e) {
          // probably a duplicate, normal operation
        }
      }
    }
  }

  /**
   * Takes a raw php array representing a course and returns the attributes bound
   * to a Course model (ie. not a CourseInstance)
   */
  private function filterCourseAttributes($course) {
    return array(
      'description' => $course['description'],
      'name_en' => $course['name_en'],
      'name_fr' => $course['name_fr'],
      'section_id' => $course['section_id'],
      'string_id' => $course['string_id'],
      'url_en' => $course['url_en'],
      'url_fr' => $course['url_fr']
    );
  }

  /**
   * Takes a raw php array representing a course and returns the attributes bound
   * to a CourseInstance model (ie. not a Course)
   */
  private function filterCourseInstanceAttributes($course) {
    return array(
      'credits' => $course['credits'],
      'teacher_id' => $course['teacher_id'],
      'year' => date('Y'),
      'lang' => $course['lang']
    );
  }

  /**
   *  Makes sure a Teacher with sciper $sciper exists. If not, tries to create one.
   *  Returns the teacher model id or NULL if teacher could not be fetched.
   */
  private function ensureTeacher($sciper) {
    if ($sciper == NULL) return;

    $existing = Teacher::where('sciper', $sciper)->first();

    if ($existing) return $existing->id;
    else {
      // Need to create teacher
      if (!$instance = $this->getTeacher($sciper)) {
        return NULL;
      }

      echo "[Info] Creating teacher: ".$instance->fullname."\n";
      $instance->save();

      return $instance->id;
    }
  }

  /**
   * Crawls a teacher with sciper $sciper.
   *
   * Returns a Teacher model instance (not saved in DB) or NULL if an error occured
   */
  private function getTeacher($sciper)
  {
    if (!$teacherStr = $this->curlGet(strtr(self::PEOPLE_URL, ['%sciper%' => $sciper]))) {
      return NULL;
    }

    $teacher = [];
    if(!preg_match("#<h1>([^&]+)&nbsp;([^<]+)</h1>#", $teacherStr, $matches)) {
      echo "[ERROR] Cannot find teacher name (sciper=$sciper)\n";
      return NULL;
    }
    $teacher['firstname'] = $matches[1];
    $teacher['lastname'] = $matches[2];
    $teacher['sciper'] = $sciper;

    return new Teacher($teacher);
  }

  /**
   * Merges a study plan's english and french version to create a consistent polyglot whole.
   */
  private function mergePlans($plan_en, $plan_fr)
  {
    $plan = [];
    foreach ($plan_en as $string_id => $course_en) {
      $course_fr = $plan_fr[$string_id];

      $teacher = NULL;


      if ((isset($course_fr['teacher']) || isset($course_en['teacher']))) {
        if ($course_en['teacher'] != $course_fr['teacher']) {
          echo "[ERROR]: teacher missmatch for course $string_id\n";
          return;
        }
        $teacher = $course_en['teacher'];
      }

      $plan[$string_id] = [
        'name_en' => $course_en['name'],
        'name_fr' => $course_fr['name'],
        'url_en' => $course_en['url'],
        'url_fr' => $course_fr['url'],
        'string_id' => $string_id,
        'semesters' => $course_en['semesters'],
        'section' => $course_en['section'],
        'teacher' => $teacher,
        'credits' => $course_en['credits'],
        'lang' => $course_en['lang']
      ];
    }
    return $plan;
  }

  /**
   * Parses a raw html study plan $planStr.
   * returns an associative array of `string_id => course` where
   * courses are also php arrays, not laravel models.
   */
  private function parsePlan($planStr)
  {
    $plan = array();

    if (preg_match_all(
        '#<div class="line-down">.+<div class="clear">&nbsp;</div></div>#msU', // multiline dotall ungreedy
        $planStr,
        $matches,
        PREG_SET_ORDER)) {

      // Parsing plan metadata
      if (!preg_match(
        '#<div class="line-up">.+<div class="clear">&nbsp;</div></div>#msU', // multiline dotall ungreedy
        $planStr,
        $metaMatch) ||
        !($meta = $this->parseMeta($metaMatch[0]))) {

        echo "[ERROR] Failed to parse meta\n";
        return;
      }


      foreach ($matches as $match) {
        if ($course = $this->parseCourse($match[0], $meta)) {
          if (isset($plan[$course['string_id']])) {
            echo "[Warning]: Two courses have identical id: ".$course['string_id']."\tOverriding\n";
          }
          $plan[$course['string_id']] = $course;
        }
      }

    } else {
      echo "[ERROR] No course matched";
    }

    return $plan;
  }

  /**
   * Parses information on the structure of the page.
   *
   * Study plan pages are based on an array structure. This function gets the array
   * headers.
   */
  private function parseMeta($metaStr)
  {
    $meta = ['semesters' => []];

    if(preg_match_all('#<div class="titre_bachlor bold">([A-Za-z0-9 ]+)&nbsp;</div>#', $metaStr, $matches, PREG_SET_ORDER)) {
      foreach($matches as $match) {
        $semester = $this->SEMESTERS[$match[1]];
        $meta['semesters'][] = $semester;
      }
    } else {
      // minor courses don't have a semester, assume this is minor and default to MA1
      $meta['force_semester'] = "MA1";
    }

    return $meta;
  }

  /**
   * Parses a course's raw HTML from the study plan page using the studyplan's meta data.
   * returns a php array containing key-value pairs for one course.
   */
  private function parseCourse($courseStr, $meta)
  {
    $course = array();

    if(!preg_match('#<div class="section-name">([A-Z]+)(?:&nbsp;)?</div>#', $courseStr, $matches)) {
      echo "[Warning] Course section not found\n\n";
      return;
    }
    $course['section'] = trim($matches[1]);

    if(!preg_match('#<div class="cours-name"><a href="([a-zA-Z0-9/-]+)?(?:\?[^"]+)?"\s*>([^<]+)</a>#', $courseStr, $matches)) {
      echo "[Warning] Course name not found (section=\"".$course['section']."\")\n\n";
      return;
    }
    $course['name'] = trim($matches[2]);
    $course['url'] = self::SOURCE_DOMAIN.trim($matches[1]);


    if (!strstr($course['url'], '/coursebook/')) {
      if ($course['section'] == 'SHS') {
        echo "[Info] Skipping SHS course\n";
      } else {
        echo "[Warning] Skipping course with strange url (url=\"".$course['url']."\", name=\"".$course['name']."\"\n";
      }
      return;
    }


    if(!preg_match('#<div class="cours-code">([A-Za-z0-9()-]+)(?:&nbsp;)?</div>#', $courseStr, $matches)) {
      echo "[Warning] Course code not found (course=\"".$course['name']."\")\n\n";
      return;
    }
    $course['string_id'] = trim($matches[1]);

    if(preg_match('#<a href="http://people.epfl.ch/cgi-bin/people//\?id=([0-9]{6})#', $courseStr, $matches)) {
      $course['teacher'] = trim($matches[1]);
    }

    if(preg_match('#<div class="credit-time">([0-9]{1,2})</div>#', $courseStr, $matches)) {
      $course['credits'] = $matches[1];
    } else {
      echo "[Warning] Course credits not found (course=\"".$course['name']."\")\n\n";
      $course['credits'] = null;
    }

    if(!preg_match('#<div class="langue (anglais|allemand|francais)">#', $courseStr, $matches)) {
      echo "[Warning] Course language not found (course=\"".$course['name']."\")\n\n";
      return;
    }
    $course['lang'] = $this->LANGUAGES[$matches[1]];


    $course['semesters'] = [];

    if (isset($meta['force_semester'])) {
      $course['semesters'][] = $meta['force_semester'];
    } else {
      // Parse semester using meta data
      if(!preg_match_all(
          '#<div class="bachlor-text">.+</div></div>#msU', // multiline dotall ungreedy
          $courseStr,
          $matches)) {
        echo "[ERROR] No semester information (course=\"".$course['name']."\")\n\n";
        return;
      }
      for ($i = 0 ; $i < count($matches[0]) ; $i++) {
        if (preg_match('#class="cep">.+&nbsp;#', $matches[0][$i])) {
          $course['semesters'][] = $meta['semesters'][$i];
        }
      }
    }

    return $course;
  }

  /**
   * Gets a course description using the coursebook $url for that course.
   */
  private function fetchCourseDescription($url)
  {
    $raw = $this->curlGet($url);

    if (!preg_match("#<h4>[\n\t ]*(?:RESUME|SUMMARY)[\n\t ]*</h4>(.+)<h4>[\n\t ]*(?:CONTENU|CONTENT)[\n\t ]*</h4>#msU", $raw, $matches)) {
      return NULL;
    }
    return trim($matches[1]);
  }


  /**
   * Curl wrapper. Returns the raw content located at $url or exits with an error message.
   */
  private function curlGet($url) {
    print "[Info] curl: GET $url\n";

    $ch = curl_init ();

    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_MAXREDIRS      => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );

    curl_setopt_array($ch, $options);
       $response = curl_exec ($ch);

    $status = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
       if ($status != 200) {
         exit("[Error] trying to access to $url (status code $status)");
       }
       curl_close ($ch);

       return $response;
  }
}
