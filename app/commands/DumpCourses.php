<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DumpCourses extends Command {

	const ISA_URL = "https://isa.epfl.ch/services/plans/%period%/semester/%semester%/section/%section%";

	const SEARCH_URL = "http://search.epfl.ch/eduwebadv.action?pageSize=100&course_words=%words%&orientation=all&cycle=all&section_id=%section_id%&language=all&semester=all";


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
		echo "\x07"; // beep when it's done
	}

	private function dump($section, $semester) {
		print "Downloading courses data for ".$section."-".$semester." from IS-Academia...\n";
		$url = $this->makeUrl($section, $semester);
		$timeStart = microtime(true);
		$xml = $this->curlGet($url);
		$timeEnd = microtime(true);
		print "Done in ".round($timeEnd - $timeStart, 1)." seconds\n";

		$parseResult = $this->parse($xml, $section, $semester);
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

		echo "\x07"; // beep
		if(!$this->confirm("Do you wish to continue? [Yes|no]", true)) {
			return;
		}

		print "Importing into database...\n";
		$this->insert($courses, $section, $semester);
		print "Done!\n";
	}

	private function parse($xml, $section, $semester) {
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

				if (!isset($prof->{'first-name'}) || is_null($prof->{'first-name'})) {
					// In case isa does not provide us with a teacher, we use the "prof divers"
					// teacher that comes with some courses
					$data['teacher'] = [
						'firstname' => '*',
						'lastname' => 'Profs divers',
						'sciper' => '126096'
					];
				} else {
					$data['teacher'] = [
						'firstname' => $prof->{'first-name'},
						'lastname' => $prof->{'last-name'},
						'sciper' => $prof->{'sciper'}
					];
				}

				if(!$this->lookupStringIdAndURL($data, $section, $semester)) {
					$skipped[] = "".$course->title;
					continue;
				}

				$hash = $data['string_id'];

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
			$existing = Course::where('string_id', '=', $courseData['string_id'])->first();

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
		print "\tGET $url\n";

		$ch = curl_init ();

		$header = ["Accept-Language: fr-FR,fr;q=0.8,en;q=0.6,en-US;q=0.4"];

		$options = array(
		    CURLOPT_URL            => $url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_FOLLOWLOCATION => true,
		    CURLOPT_ENCODING       => "",
		    CURLOPT_AUTOREFERER    => true,
		    CURLOPT_CONNECTTIMEOUT => 120,
		    CURLOPT_TIMEOUT        => 120,
		    CURLOPT_MAXREDIRS      => 15,
		    CURLOPT_HTTPHEADER     => $header,
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


	private function lookupStringIdAndURL(&$course, $section, $semester) {

		$name = $this->normalizeFragment($course['name']);
		$pattern = '#<a href="(http://edu\.epfl\.ch/coursebook/[a-z]{2}/%name%-([A-Za-z0-9-]+))">#';
		$words = strtr($name, ['-' => '+']);
		$matches = [];
		$url = strtr(self::SEARCH_URL, [
			'%words%' => $words,
			'%section_id%' => $section
		]);

		$responseText = $this->curlGet($url);

		// trying exact match
		$exact_pattern = strtr($pattern, ['%name%' => $name]);
		if (!($nb_matches = preg_match_all($exact_pattern, $responseText, $matches, PREG_SET_ORDER))) {
			// not found

			// trying without secion id
			$url = strtr(self::SEARCH_URL, [
				'%words%' => $words,
				'%section_id%' => ""
			]);
			$responseText2 = $this->curlGet($url);
			if (!($nb_matches = preg_match_all($exact_pattern, $responseText2, $matches, PREG_SET_ORDER))) {
				// still not found

				//trying approximative match
				$approximate_pattern = strtr($pattern, ['%name%' => "([A-Za-z0-9-]+)"]);
				$nb_matches = preg_match_all($approximate_pattern, $responseText2, $matches, PREG_SET_ORDER);
			}
		}

		$chose_match = 0;

		if (!$nb_matches) {
			echo "[Warning] no string id found for course ".$course['name']."\n";
			echo "\x07"; // beep
			if (strtolower($this->ask('Try manually (Y/n)? ')) != 'n') {
				$course['name'] = $this->ask("Enter course name\n");
				return $this->lookupStringIdAndURL($course, $section, $semester);
			}
			echo "[Warning] skipping";
			return 0;
		} else if ($nb_matches > 1) {
			echo "[Info] Found multiple urls for course ".$name."\n\n";
			for($i = 0 ; $i < $nb_matches ; $i++) {
				echo $i." = ".$matches[$i][1]."\n";
			}

			do {
				echo "\x07"; // beep
				$chose_match = intval($this->ask('Which one to use? (-1 to skip)'));
				if ($chose_match == -1) {
					echo "[Warning] skipping\n";
					return 0;
				}
			} while ($chose_match < 0 || $chose_match >= $nb_matches);
		}

		$course['string_id'] = $matches[$chose_match][2];
		$course['url'] = $matches[$chose_match][1];

		return 1;
	}

	private function normalizeFragment($fragment)
    {
        // http://stackoverflow.com/questions/3371697/replacing-accented-characters-php
        $unaccentizer = array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');

        // Strip accents and lowercase
        $fragment = strtolower(strtr($fragment, $unaccentizer));
        // non alphanum characters to dashes
        $fragment = preg_replace("/([^a-z0-9])/i", "-", $fragment);
        // strip multiple dashes
        $fragment = preg_replace("/(-+)/", "-", $fragment);
        // strip trailing dashes
        $fragment = preg_replace("/(-+)$/", "", $fragment);

        return $fragment;
    }
}
