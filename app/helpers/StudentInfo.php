<?php namespace Helpers;

use \Tequila;
use Illuminate\Support\Facades\Session;
use \Student;

class StudentInfo {

	public static function getFullSection() {
		return explode(',', Tequila::get('unit'))[0];
	}
	public static function getSection() {
		return explode('-', self::getFullSection())[0];
	}

	public static function getSemester() {
		return explode('-', self::getFullSection())[1];
	}

    public static function getStudyCycle() {
        $map = array(
            'BA1' => 1, 'BA2' => 1,                         // prope
            'BA3' => 2, 'BA4' => 2, 'BA5' => 2, 'BA6' => 2, // bachelor
            'MA1' => 3, 'MA2' => 3                          // master
        );

        $semester = self::getSemester();

        if (array_key_exists($semester, $map)) {
            return $map[$semester];
        }
    }

	public static function getLowerSemesters() {
		$current = self::getSemester();
		$semesters = ['BA1', 'BA2', 'BA3', 'BA4', 'BA5', 'BA6', 'MA1', 'MA2', 'MA3', 'MA4'];
		for($i = 0; $i < sizeof($semesters); ++$i) {
			if($current == $semesters[$i]) {
				return array_slice($semesters, 0, $i);
			}
		}
	}

	public static function getSciper() {
		return Tequila::get('uniqueid');
	}

	public static function getId() {
		return Session::get('student_id');
	}

	public static function isAdmin() {
		if (!Session::has('is_admin')) {
			return false;
		}

		return Session::get('is_admin') == 1;
	}

	public static function getAdminEmails() {
		return Student::where('is_admin', '1')->get()->map(function ($student) {
			return $student->email;
		})->toArray();
	}
}