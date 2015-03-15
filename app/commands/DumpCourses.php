<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DumpCourses extends Command {

	const ISA_URL = "https://isa.epfl.ch/services/plans/%period%/semester/%semester%/section/%section%";
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'courseadvisor:dump-courses';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Retrieve courses from is-academia';

	private $dump_all = false;

	private $semesters = ['BA1', 'BA2', 'BA3', 'BA4', 'BA5', 'BA6', 'MA1', 'MA2', 'MA3', 'MA4'];

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
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('section', null, InputOption::VALUE_REQUIRED, 'The section from which retrieve courses (e.g. IN)', null),
			array('semester', null, InputOption::VALUE_REQUIRED, 'The semester from which retrieve courses (e.g. BA4)', null)
		);
	}

	protected function checkOptions() {
		$error = false;

		if(is_null($this->option('semester')) && is_null($this->option('section'))) {
			if(!$this->confirm("Dumping ALL courses for ALL sections in database. Do you want to continue (Y/n)", true)) {
				exit;
			}
			$this->dump_all = true;
			return;
		}

		if(!in_array($this->option('semester'), $this->semesters)) {
			print "Invalid semester '".$this->option('semester')."'\n";
			$error = true;
		}

		if(!preg_match('#^[A-Z]{2,4}$#', $this->option('section'))) {
			print "Invalid section '".$this->option('section')."'\n";
			$error = true;
		}

		if($error) {
			exit;
		}
	}


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		print "==================================\n";
		print "====== DUMP COURSES FROM ISA =====\n";
		print "==================================\n\n";

		$this->checkOptions();

		if ($this->dump_all) {
			$sections = DB::table('sections')
						->select('string_id')
						->get();
			foreach($sections as $section) {
				foreach($this->semesters as $semester) {
					$this->dump($section->string_id, $semester);
				}
			}
		}
		else {
			$this->dump($this->option('section'), $this->option('semester'));
		}
	}

	private function dump($section, $semester) {
		print "Downloading courses data for ".$section."-".$semester." from IS-Academia...\n";
		$url = $this->makeUrl($section, $semester);
		print "\tGET $url\n";
		$timeStart = microtime(true);
		$xml = $this->curlGet($url);
		$timeEnd = microtime(true);
		print "Done in ".round($timeEnd - $timeStart, 1)." seconds\n";

		$parseResult = $this->parse($xml);
		if ($parseResult === false) {
			return;
		}
		list($courses, $skipped) = $parseResult;

		$nbSkipped = sizeof($skipped);
		if($nbSkipped > 0) {
			print "[Warning] $nbSkipped courses were ignored (not enough info): \n";
			foreach($skipped as $c) {
				print "\t- $c\n";
			}
			print "\n";
		}

		print "The following ".sizeof($courses)." courses will be inserted / updated in the database:\n";
		foreach($courses as $course) {
			print "\t- ".$course['name']."\n";
		}

		if(!$this->confirm("Do you wish to continue? [Yes|no]", true)) {
			return;
		}

		print "Importing into database...\n";
		$this->insert($courses, $section, $semester);
		print "Done!\n";
	}

	private function parse($xml) {
		$courses = [];
		$skipped = [];
		$inserted = []; // slugs of inserted courses

		$doc = simplexml_load_string($xml);
		if(!$doc->cursus) {
			print ("[WARNING] IS-Academia did not provide any data for this semester.\t");
			return false;
		}

		foreach($doc->cursus->plans->plan as $plan) {
			foreach($plan->courses->course as $course) {
				if(!$course->classes->children()) {
					$skipped[] = "".$course->title;
					continue;
				}

				// Note : empty concatenation is a cast trick
				$data = [];
				$data['name'] = "".$course->title;
				$prof = $course->classes->class[0]->instructors->professors;
				$data['teacher'] = [
					'firstname' => $prof->{'first-name'},
					'lastname' => $prof->{'last-name'},
					'sciper' => $prof->{'sciper'}
				];
				$data['string_id'] = "".$course->code;


				// A course is defined by its id. But in some cases, we only have its name
				// and teacher name
				$hash = md5($data['teacher']['sciper'].$data['name']);

				if(!strlen(trim($data['string_id']))) {
					$data['string_id'] = $hash;
				}

				if(in_array($hash, $inserted)) {
					continue;
				}
				$inserted[] = $hash;
				$courses[] = $data;
			}
		}

		return [$courses, $skipped];
	}

	private function insert(array $courses, $section, $semester) {
		// Retrieve section id
		$s = Section::where('string_id', '=', $section)->first();
		if(!$s) {
			exit("[Error] Section '$section' does not exist in database.");
		}

		// Insert courses in db
		foreach($courses as $courseData) {

			// Check if the teacher exists
			if (!empty($courseData['teacher'])) {
				$existing = Teacher::where('sciper', '=', $courseData['teacher']['sciper'])->first();

				if ($existing) {
					$courseData['teacher_id'] = $existing->id;
				} else {
					$courseData['teacher_id'] = Teacher::create($courseData['teacher'])->id;
				}
			}

			// Check if the course exists
			if(!empty($courseData['string_id'])) {
				$existing = Course::where('string_id', '=', $courseData['string_id'])->first();
			}
			else {
				$existing = Course::where('string_id', '=', md5($courseData['teacher_id'].$courseData['name']));
			}

			unset($courseData['teacher']);
			if($existing) {
				$courseId = $existing->id;
				$existing->fill($courseData);
				$existing->save();
				$action = 'updated';
			}
			else {
				$courseId = Course::create($courseData)->id;
				$action = 'created';
			}


			// Insert relationship if it does not exist : [course] is given in semester [semester] of section [section]
			$relation = [
				'section_id' 	=> $s->id,
				'course_id'		=> $courseId,
				'semester'		=> $semester
			];

			$count = DB::table('course_section')
					->where('section_id', $relation['section_id'])
					->where('course_id', $relation['course_id'])
					->where('semester', $relation['semester'])
					->count();

			if($count == 0) {
				DB::table('course_section')->insert($relation);
			}

			print "\t- ".$courseData['name'] . " course successfully $action\n";
		}

	}


	private function makeUrl($section, $semester) {
		$replacements = [
			'%period%' 		=> '2014-2015',
			'%semester%'	=> $semester,
			'%section%'		=> $section
		];
		return strtr(self::ISA_URL, $replacements);
	}

	private function curlGet($url) {
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
 	      exit("Error trying to access to $url (status code $status)");
 	    }
 	    curl_close ($ch);

 	    return $response;
	}


}
