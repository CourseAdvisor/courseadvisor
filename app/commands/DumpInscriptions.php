<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;


class DumpInscriptions extends Command {

  const SOURCE_DOMAIN = "http://res.courseadvisor.ch/";


  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'courseadvisor:dump-inscriptions';


  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Dump inscriptions from courseadvisor mirror';


  protected function getOptions()
    {
        return array(
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
    print "===== DUMP INSCRIPTIONS =====\n";
    print "=============================\n\n";

    // Dump 6 semesters starting from FALL 2012
    $year = 2012;
    $term = "FALL";
    $semesters = 6;

    for ($i = 0 ; $i < $semesters ; $i++) {
      $this->dumpTerm($year, $term);

      if ($term == "FALL") {
        $term = "SPRING";
        $year++;
      } else {
        $term = "FALL";
      }
    }
  }

  private function dumpTerm($year, $term)
  {

    if (!($raw = $this->curlGet(self::SOURCE_DOMAIN.$term."_".substr($year, -2, 2).".xml"))) {
      return NULL;
    }

    $parsed = new SimpleXMLElement($raw);

    echo "parsed\n";

    foreach($parsed->course as $course) {
      echo "parsing ".$course->code." ";
      $cobj = Course::where('string_id', $course->code)->first();
      if (!$cobj) {
        echo "Skipping\n";
        continue;
      }
      echo $cobj->name_en."\n";

      try {
        $inscriptions = $course->curricula->cursus->inscriptions->inscription;
      } catch(Exception $e) {
        echo "Skipping\n";
        continue;
      }
      $this->dumpInscription($inscriptions, $cobj, $year, $term);
    }
  }

  private function dumpInscription($inscriptions, $course, $year, $term) {
    echo "Dumping course: ".$course->name_en." (".count($inscriptions)." inscriptions)\n";
    foreach ($inscriptions as $inscription) {
      Inscription::firstOrCreate([
        "course_id" => $course->id,
        "year" => $year,
        "term" => $term,
        "sciper" => $inscription->sciper
      ]);
    }
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