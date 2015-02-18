<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		$this->call('SectionsTableSeeder');
		$this->command->info("Fixtures for sections installed");

		$this->call('StudentsTableSeeder');
		$this->command->info("Fixtures for students installed");

		$this->call('CoursesTableSeeder');
		$this->command->info("Fixtures for courses installed");

		/*$this->call('CoursesRegistrationsTableSeeder');
		$this->command->info("Fixtures for courses registrations installed");*/

		$this->call('ReviewsTableSeeder');
		$this->command->info("Fixtures for reviews installed");
	}
}

class SectionsTableSeeder extends Seeder {
	public function run() {
		DB::table('sections')->delete();

		Section::create([
			'string_id' => 'IN',
			'name'		=> 'Informatique'
		]);

		Section::create([
			'string_id' => 'SC',
			'name'		=> 'Systèmes de communication'
		]);

		Section::create([
			'string_id' => 'PSY', 
			'name'		=> 'Psychologie',
		]);
	}
}

class StudentsTableSeeder extends Seeder {

	public function run() {
		DB::table('students')->delete();

		$in = Section::where('string_id', '=', 'IN')->first();
		$psy = Section::where('string_id', '=', 'PSY')->first();

		/*Student::create(array(
			'firstname' => 'Christophe',
			'lastname'	=> 'Tafani-Dereeper',
			'sciper'	=> '223529',
			'semester'	=> 'BA6',
			'email'		=> 'me@xtof.fr', 
			'section_id'=> $in->id
		))->save();*/

		$hadrien = Student::create(array(
			'firstname'	=> 'Hadrien',
			'lastname'	=> 'Milano',
			'sciper'	=> '224340',
			'semester'	=> 'BA6',
			'email'		=> 'me@hmil.fr', 
			'section_id'=> $in->id
		))->save();


		Student::create(array(
			'firstname'	=> 'Emilie',
			'lastname'	=> 'Rinsoz',
			'sciper'	=> '123345',
			'semester'	=> 'BA5',
			'email'		=> 'me@emilierinsoz.ch', 
			'section_id'=> $psy->id
		))->save();
	}
}


class CoursesTableSeeder extends Seeder {
	public function run() {
		DB::table('courses')->delete();
		DB::table('course_section')->delete();

		$in = Section::where('string_id', '=', 'IN')->first();
		$psy = Section::where('string_id', '=', 'PSY')->first();
		$sc = Section::where('string_id', '=', 'SC')->first();

		$algo = Course::create([
			'string_id' => "ALGO",
			'name' 		=> "Algorithms"
		]);
		// Algorithms is given in SC and IN
		DB::table('course_section')->insert(array(
			'course_id' => $algo->id, 
			'section_id'=> $in->id, 
			'semester'	=> 'BA3'
		));

		DB::table('course_section')->insert(array(
			'course_id' => $algo->id, 
			'section_id'=> $sc->id, 
			'semester'	=> 'BA3'
		));

		$algebra = Course::create([
			'string_id' => "ALGE",
			'name' 		=> "Algebra"
		]);
		// Algebra is given in SC
		DB::table('course_section')->insert(array(
			'course_id' => $algebra->id, 
			'section_id'=> $sc->id, 
			'semester'	=> 'BA5'
		));

		$sweng = Course::create([
			'string_id' => "SWENG",
			'name' 		=> "Software Engineering"
		]);
		// Sweng is given in IN
		DB::table('course_section')->insert(array(
			'course_id' => $sweng->id, 
			'section_id'=> $in->id, 
			'semester'	=> 'BA5'
		));

		$psychoSoc = Course::create([
			'string_id' => "PSY1",
			'name' 		=> "Psychologie sociale"
		]);
		// Psycho sociale is given in psycho
		DB::table('course_section')->insert(array(
			'course_id' => $psychoSoc->id, 
			'section_id'=> $psy->id, 
			'semester'	=> 'BA1'
		));

		Course::create([
			'string_id' => "WTF",
			'name' 		=> "Physique quantique"
		]);

		Course::create([
			'string_id' => "METH",
			'name' 		=> "Méthodologie"
		]);	
	}
}


class CoursesRegistrationsTableSeeder extends Seeder {
	public function run() {

		DB::table('course_student')->delete();

		$sweng = Course::where('string_id', '=', 'SWENG')->first()->id;
		$algebra = Course::where('string_id', '=', 'ALGE')->first()->id;
		$algo = Course::where('string_id', '=', 'ALGO')->first()->id;
		$psycho = Course::where('string_id', '=', 'PSY1')->first()->id;
		$quantique = Course::where('string_id', '=', 'WTF')->first()->id;
		$methodo =  Course::where('string_id', '=', 'METH')->first()->id;

		// Christophe is registred to Algorithms, Algebra, Psychologie sociale and Software engineering
		/*$xtof = Student::where('firstname', '=', 'Christophe')->first();
		$xtof->courses()->attach($sweng);
		$xtof->courses()->attach($algebra);
		$xtof->courses()->attach($algo);
		$xtof->courses()->attach($psycho);*/

		// Hadrien is registred to Algorithms, Software engineering and Quantique
		$hadrien = Student::where('firstname', '=', 'Hadrien')->first();
		$hadrien->courses()->attach($algo);
		$hadrien->courses()->attach($sweng);
		$hadrien->courses()->attach($quantique);

		// Emilie is registred to psycho and methodo
		$emilie = Student::where('firstname', '=', 'Emilie')->first();
		$emilie->courses()->attach($psycho);
		$emilie->courses()->attach($methodo);
	}
}

class ReviewsTableSeeder extends Seeder {
	public function run() {
		DB::table('reviews')->delete();

		$sweng = Course::where('string_id', '=', 'SWENG')->first()->id;
		$quantique = Course::where('string_id', '=', 'WTF')->first()->id;
	}
}