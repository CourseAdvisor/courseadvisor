<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDb extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('students');
		Schema::create('students', function($table) {
			$table->increments('id');
			$table->string('section_id');
			$table->string('tequila_id');
			$table->string('email')->unique();
			$table->string('firstname');
			$table->string('lastname');
			$table->string('semester');
			$table->timestamps();

			$table->foreign('section_id')
				  ->references('id')->on('sections');
		});

		Schema::dropIfExists('sections');
		Schema::create('sections', function($table) {
			$table->increments('id');
			$table->string('string_id');
			$table->string('name');
			$table->timestamps();
		});

		Schema::dropIfExists('courses');
		Schema::create('courses', function($table) {
			$table->increments('id');
			$table->string('string_id');
			$table->string('name');
			$table->timestamps();
		});

		Schema::dropIfExists('reviews');
		Schema::create('reviews', function($table) {
			$table->increments('id');
			$table->integer('course_id')->unsigned();
			$table->enum('note', range(1, 5));
			
			$table->foreign('course_id')
				  ->references('id')->on('courses');
		});

		// Relationship for courses and students
		Schema::dropIfExists('course_student');
		Schema::create('course_student', function($table) {
			$table->increments('id');
			$table->integer('student_id')->unsigned();
			$table->integer('course_id')->unsigned();

			$table->foreign('student_id')
				  ->references('id')->on('students');

			$table->foreign('course_id')
				  ->references('id')->on('courses');

			$table->timestamps();
		}); 
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('students');
		Schema::drop('courses');
		Schema::drop('sections');
		Schema::drop('course_student');
	}

}
