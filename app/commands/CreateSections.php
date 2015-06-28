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


  private function makeSection($id, $name_fr, $name_en) {
    print "Section '$name_en' ";
    $section = Section::where('string_id', $id)->first();
    if($section) {
      print "already exists in database. Updating...\n";
      $section->where('id', $section->id)->update([
        'string_id' => $id,
        'name_fr'  => $name_fr,
        'name_en'  => $name_en
        ]);
    } else {
      Section::create([
        'string_id' => $id,
        'name_fr'  => $name_fr,
        'name_en'  => $name_en
      ]);
      print "successfuly created\n";
    }
  }


  public function fire()
  {
    $this->makeSection('AR', 'Architecture', 'Architecture');
    $this->makeSection('CGC', 'Chimie et génie chimique', 'Chemistry and Chemical Engineering');
    $this->makeSection('EL', 'Génie électrique et électronique', 'Electrical and Electronics Engineering');
    $this->makeSection('GC', 'Génie civil', 'Civil Engineering');
    $this->makeSection('GM', 'Génie mécanique', 'Mechanical Engineering');
    $this->makeSection('IN', 'Informatique', 'Computer Science');
    $this->makeSection('MA', 'Mathématiques', 'Mathematics');
    $this->makeSection('MT', 'Microtechnique', 'Microengineering');
    $this->makeSection('MX', 'Science et génie des matériaux', 'Materials Science and Engineering');
    $this->makeSection('PH', 'Physique', 'Physics');
    $this->makeSection('SC', 'Systèmes de communication', 'Communication Systems');
    $this->makeSection('SIE', 'Sciences et ingénierie de l\'environnement', 'Environmental Sciences and Engineering');
    $this->makeSection('SV', 'Sciences et technologies du vivant', 'Life Sciences and Technologies');
    $this->makeSection('SHS', 'Sciences humaines et sociales', 'Social and Human Sciences');
    $this->makeSection('MTE', 'Management, technologie et entrepreneuriat','Management, Technology and Entrepreneurship');
    $this->makeSection('IF', 'Ingénierie financière','Financial engineering');
    $this->makeSection('HPLANS', '???','???');
    $this->makeSection('MATH', '???','???');
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
