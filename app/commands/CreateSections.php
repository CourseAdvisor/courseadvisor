<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateSections extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'courseadvisor:create-sections';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Inserts EPFL sections in the database';


	public function __construct()
	{
		parent::__construct();
	}


	private function makeSection($id, $name) {
		print "Section '$name' ";
		$count = Section::where('string_id', $id)->count();
		if($count > 0) {
			print "already exists in database. Skipping...\n";
			return;
		}

		Section::create([
			'string_id' => $id,
			'name'		=> $name
		]);
		print "successfuly created\n";
	}


	public function fire()
	{
		$this->makeSection('AR', 'Architecture');
		$this->makeSection('CGC', 'Chimie');
		$this->makeSection('EL', 'Génie électrique et électronique');
		$this->makeSection('GC', 'Génie civil');
		$this->makeSection('GM', 'Génie mécanique');
		$this->makeSection('IN', 'Informatique');
		$this->makeSection('MA', 'Mathématiques');
		$this->makeSection('MT', 'Microtechnique');
		$this->makeSection('MX', 'Matériaux');
		$this->makeSection('PH', 'Physique');
		$this->makeSection('SC', 'Systèmes de communication');
		$this->makeSection('SIE', 'Environnement');
		$this->makeSection('SV', 'Sciences de la vie');
	}


	protected function getArguments()
	{
		return array(

		);
	}

	protected function getOptions()
	{
		return array(

		);
	}

}
