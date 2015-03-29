<?php

use Illuminate\Console\Command;


class DumpStudyPlan extends Command {

	const SOURCE_DOMAIN = "http://edu.epfl.ch/";

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


		$plans = StudyPlan::where('study_cycle_id', '>', '3')->get();

		foreach($plans as $plan) {
			$this->dumpPlan($plan);
		}
		echo "Done :)\n";
	}

	private function dumpPlan($plan)
	{
		print "[Info] Dumping plan: ".$plan->name."\n";

		$courses_en = $this->parsePlan($this->curlGet($plan->url_en));
		$courses_fr = $this->parsePlan($this->curlGet($plan->url_fr));

		$courses = $this->mergePlans($courses_en, $courses_fr);

		$this->savePlan($plan, $courses);
	}

	private function saveplan($plan, $courses)
	{
		foreach($courses as $string_id => $course) {
			$semesters = $course['semesters'];
			if (!$course['teacher_id'] = $this->ensureTeacher($course['teacher'])) {
				echo "[WARNING]: Skipping ".$course['name_en']." due to unknown teacher\n";
				continue;
			}
			unset($course['teacher']);
			unset($course['semesters']);


			$existing = Course::where('string_id', $string_id)->first();

			if ($existing) {
				Course::where('string_id', $string_id)->update($course);
			} else {
				$existing = new Course($course);

				$existing->save();
			}

			// update studyplan relations
			$existing->plans()->detach();
			foreach($semesters as $semester) {
				$existing->plans()->attach($plan->id, ["semester" => $semester]);
			}
		}
	}

	private function ensureTeacher($teacher) {
		if ($teacher == NULL) return;

		$existing = Teacher::where('sciper', $teacher)->first();

		if ($existing) return $existing->id;
		else {
			// Need to create teacher
			if (!$instance = $this->getTeacher($teacher)) {
				return NULL;
			}

			echo "[Info] Creating teacher: ".$instance->fullname."\n";
			$instance->save();

			return $instance->id;
		}
	}

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
				'teacher' => $teacher
			];
		}
		return $plan;
	}

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
