<?php

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreatePlans extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'courseadvisor:create-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts EPFL plans in the database';


    public function __construct()
    {
        parent::__construct();
    }


    private function makePlan($cycle_id, $name_fr, $name_en, $url_fr, $url_en, $string_id) {
        print "Plan '$name_en' ";

        $slug = Str::slug($cycle_id.' '.$name_en);
        $plan = StudyPlan::where('slug', $slug)->first();


        if($plan) {
            print "already exists in database. Updating...\n";
            $plan->where('id', $plan->id)->update([
                'string_id' => $string_id,
                'slug'      => $slug,
                'url_fr'    => $url_fr,
                'url_en'    => $url_en,
                'study_cycle_id'  => $cycle_id,
                'name_fr'   => $name_fr,
                'name_en'   => $name_en
                ]);
        } else {
            Section::create([
                'string_id' => $string_id,
                'slug'      => $slug,
                'url_fr'    => $url_fr,
                'url_en'    => $url_en,
                'study_cycle_id'  => $cycle_id,
                'name_fr'   => $name_fr,
                'name_en'   => $name_en
            ]);
            print "successfuly created\n";
        }
    }

    public function makeCycle($id, $name_fr, $name_en) {
        $cycle = StudyCycle::find($id);

        if ($cycle) {
            print "updating cycle $name_en\n";
            StudyCycle::where('id', $id)->update([
                'name_en' => $name_en,
                'name_fr' => $name_fr,
            ]);
        } else {
            StudyCycle::create([
                'id' => $id,
                'name_en' => $name_en,
                'name_fr' => $name_fr,
            ]);
        }
    }


    public function fire()
    {
        $this->makeCycle(1,"propedeutique","propedeutics");
        $this->makeCycle(2,"bachelor","bachelor");
        $this->makeCycle(3,"master","master");
        $this->makeCycle(4,"mineur","minor");


        $this->makePlan("1","Architecture","Architecture","http://edu.epfl.ch/studyplan/fr/propedeutique/architecture","http://edu.epfl.ch/studyplan/en/propedeutics/architecture","AR");
        $this->makePlan("1","Chimie et génie chimique","Chemistry and Chemical Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/chimie-et-genie-chimique","http://edu.epfl.ch/studyplan/en/propedeutics/chemistry-and-chemical-engineering","CGC");
        $this->makePlan("1","Génie civil","Civil Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/genie-civil","http://edu.epfl.ch/studyplan/en/propedeutics/civil-engineering","GC");
        $this->makePlan("1","Systèmes de communication","Communication Systems","http://edu.epfl.ch/studyplan/fr/propedeutique/systemes-de-communication","http://edu.epfl.ch/studyplan/en/propedeutics/communication-systems","SC");
        $this->makePlan("1","Informatique","Computer Science","http://edu.epfl.ch/studyplan/fr/propedeutique/informatique","http://edu.epfl.ch/studyplan/en/propedeutics/computer-science","IN");
        $this->makePlan("1","Génie électrique et électronique","Electrical and Electronics Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/genie-electrique-et-electronique","http://edu.epfl.ch/studyplan/en/propedeutics/electrical-and-electronics-engineering","EL");
        $this->makePlan("1","Sciences et ingénierie de l'environnement","Environmental Sciences and Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/sciences-et-ingenierie-de-l-environnement","http://edu.epfl.ch/studyplan/en/propedeutics/environmental-sciences-and-engineering","SIE");
        $this->makePlan("1","Sciences humaines et sociales","Humanities and Social Sciences","http://edu.epfl.ch/studyplan/fr/propedeutique/programme-sciences-humaines-et-sociales","http://edu.epfl.ch/studyplan/en/propedeutics/humanities-and-social-sciences-program","SHS");
        $this->makePlan("1","Sciences et technologies du vivant","Life Sciences and Technologies","http://edu.epfl.ch/studyplan/fr/propedeutique/sciences-et-technologies-du-vivant","http://edu.epfl.ch/studyplan/en/propedeutics/life-sciences-and-technologies","SV");
        $this->makePlan("1","Science et génie des matériaux","Materials Science and Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/science-et-genie-des-materiaux","http://edu.epfl.ch/studyplan/en/propedeutics/materials-science-and-engineering","MX");
        $this->makePlan("1","Mathématiques","Mathematics","http://edu.epfl.ch/studyplan/fr/propedeutique/mathematiques","http://edu.epfl.ch/studyplan/en/propedeutics/mathematics","MA");
        $this->makePlan("1","Génie mécanique","Mechanical Engineering","http://edu.epfl.ch/studyplan/fr/propedeutique/genie-mecanique","http://edu.epfl.ch/studyplan/en/propedeutics/mechanical-engineering","GM");
        $this->makePlan("1","Microtechnique","Microengineering","http://edu.epfl.ch/studyplan/fr/propedeutique/microtechnique","http://edu.epfl.ch/studyplan/en/propedeutics/microengineering","MT");
        $this->makePlan("1","Physique","Physics","http://edu.epfl.ch/studyplan/fr/propedeutique/physique","http://edu.epfl.ch/studyplan/en/propedeutics/physics","PH");
        $this->makePlan("2","Architecture","Architecture","http://edu.epfl.ch/studyplan/fr/bachelor/architecture","http://edu.epfl.ch/studyplan/en/bachelor/architecture","AR");
        $this->makePlan("2","Chimie et génie chimique","Chemistry and Chemical Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/chimie-et-genie-chimique","http://edu.epfl.ch/studyplan/en/bachelor/chemistry-and-chemical-engineering","CGC");
        $this->makePlan("2","Génie civil","Civil Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/genie-civil","http://edu.epfl.ch/studyplan/en/bachelor/civil-engineering","GC");
        $this->makePlan("2","Systèmes de communication","Communication Systems","http://edu.epfl.ch/studyplan/fr/bachelor/systemes-de-communication","http://edu.epfl.ch/studyplan/en/bachelor/communication-systems","SC");
        $this->makePlan("2","Informatique","Computer Science","http://edu.epfl.ch/studyplan/fr/bachelor/informatique","http://edu.epfl.ch/studyplan/en/bachelor/computer-science","IN");
        $this->makePlan("2","Projeter ensemble ENAC","Design & Build Together ENAC","http://edu.epfl.ch/studyplan/fr/bachelor/projeter-ensemble-enac","http://edu.epfl.ch/studyplan/en/bachelor/design-build-together-enac","ENAC");
        $this->makePlan("2","Génie électrique et électronique","Electrical and Electronics Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/genie-electrique-et-electronique","http://edu.epfl.ch/studyplan/en/bachelor/electrical-and-electronics-engineering","EL");
        $this->makePlan("2","Sciences et ingénierie de l'environnement","Environmental Sciences and Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/sciences-et-ingenierie-de-l-environnement","http://edu.epfl.ch/studyplan/en/bachelor/environmental-sciences-and-engineering","SIE");
        $this->makePlan("2","Sciences humaines et sociales","Humanities and Social Sciences","http://edu.epfl.ch/studyplan/fr/bachelor/programme-sciences-humaines-et-sociales","http://edu.epfl.ch/studyplan/en/bachelor/humanities-and-social-sciences-program","SHS");
        $this->makePlan("2","Sciences et technologies du vivant","Life Sciences and Technologies","http://edu.epfl.ch/studyplan/fr/bachelor/sciences-et-technologies-du-vivant","http://edu.epfl.ch/studyplan/en/bachelor/life-sciences-and-technologies","SV");
        $this->makePlan("2","Science et génie des matériaux","Materials Science and Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/science-et-genie-des-materiaux","http://edu.epfl.ch/studyplan/en/bachelor/materials-science-and-engineering","MX");
        $this->makePlan("2","Mathématiques","Mathematics","http://edu.epfl.ch/studyplan/fr/bachelor/mathematiques","http://edu.epfl.ch/studyplan/en/bachelor/mathematics","MA");
        $this->makePlan("2","Génie mécanique","Mechanical Engineering","http://edu.epfl.ch/studyplan/fr/bachelor/genie-mecanique","http://edu.epfl.ch/studyplan/en/bachelor/mechanical-engineering","GM");
        $this->makePlan("2","Microtechnique","Microengineering","http://edu.epfl.ch/studyplan/fr/bachelor/microtechnique","http://edu.epfl.ch/studyplan/en/bachelor/microengineering","MT");
        $this->makePlan("2","Physique","Physics","http://edu.epfl.ch/studyplan/fr/bachelor/physique","http://edu.epfl.ch/studyplan/en/bachelor/physics","PH");
        $this->makePlan("3","Ingénierie mathématique","Applied Mathematics","http://edu.epfl.ch/studyplan/fr/master/ingenierie-mathematique","http://edu.epfl.ch/studyplan/en/master/applied-mathematics","MATH");
        $this->makePlan("3","Ingénierie physique","Applied Physics","http://edu.epfl.ch/studyplan/fr/master/ingenierie-physique","http://edu.epfl.ch/studyplan/en/master/applied-physics","PHYS");
        $this->makePlan("3","Architecture","Architecture","http://edu.epfl.ch/studyplan/fr/master/architecture","http://edu.epfl.ch/studyplan/en/master/architecture","AR");
        $this->makePlan("3","Bioingénierie","Bioengineering","http://edu.epfl.ch/studyplan/fr/master/bioingenierie","http://edu.epfl.ch/studyplan/en/master/bioengineering","BIO");
        $this->makePlan("3","Génie chimique et biotechnologie","Chemical Engineering and Biotechnology","http://edu.epfl.ch/studyplan/fr/master/genie-chimique-et-biotechnologie","http://edu.epfl.ch/studyplan/en/master/chemical-engineering-and-biotechnology","GCBIO");
        $this->makePlan("3","Génie civil","Civil Engineering","http://edu.epfl.ch/studyplan/fr/master/genie-civil","http://edu.epfl.ch/studyplan/en/master/civil-engineering","GC");
        $this->makePlan("3","Systèmes de communication","Communication Systems","http://edu.epfl.ch/studyplan/fr/master/systemes-de-communication-master","http://edu.epfl.ch/studyplan/en/master/communication-systems-master-program","SC");
        $this->makePlan("3","Science et ingénierie computationnelles","Computational science and Engineering","http://edu.epfl.ch/studyplan/fr/master/science-et-ingenierie-computationnelles","http://edu.epfl.ch/studyplan/en/master/computational-science-and-engineering","CSE");
        $this->makePlan("3","Informatique","Computer Science","http://edu.epfl.ch/studyplan/fr/master/informatique","http://edu.epfl.ch/studyplan/en/master/computer-science","IN");
        $this->makePlan("3","Génie électrique et électronique","Electrical and Electronics Engineering","http://edu.epfl.ch/studyplan/fr/master/genie-electrique-et-electronique","http://edu.epfl.ch/studyplan/en/master/electrical-and-electronics-engineering","EL");
        $this->makePlan("3","Sciences et ingénierie de l'environnement","Environmental Sciences and Engineering","http://edu.epfl.ch/studyplan/fr/master/sciences-et-ingenierie-de-l-environnement","http://edu.epfl.ch/studyplan/en/master/environmental-sciences-and-engineering","SIE");
        $this->makePlan("3","Ingénierie financière","Financial engineering","http://edu.epfl.ch/studyplan/fr/master/ingenierie-financiere","http://edu.epfl.ch/studyplan/en/master/financial-engineering","IF");
        $this->makePlan("3","Sciences humaines et sociales","Humanities and Social Sciences","http://edu.epfl.ch/studyplan/fr/master/programme-sciences-humaines-et-sociales","http://edu.epfl.ch/studyplan/en/master/humanities-and-social-sciences-program","SHS");
        $this->makePlan("3","Sciences et technologies du vivant","Life Sciences and Technologies","http://edu.epfl.ch/studyplan/fr/master/sciences-et-technologies-du-vivant-master","http://edu.epfl.ch/studyplan/en/master/life-sciences-and-technologies-master-program","SV");
        $this->makePlan("3","Management, technologie et entrepreneuriat","Management, Technology and Entrepreneurship","http://edu.epfl.ch/studyplan/fr/master/management-technologie-et-entrepreneuriat","http://edu.epfl.ch/studyplan/en/master/management-technology-and-entrepreneurship","MTE");
        $this->makePlan("3","Science et génie des matériaux","Materials Science and Engineering","http://edu.epfl.ch/studyplan/fr/master/science-et-genie-des-materiaux","http://edu.epfl.ch/studyplan/en/master/materials-science-and-engineering","MX");
        $this->makePlan("3","Mathématiques","Mathematics","http://edu.epfl.ch/studyplan/fr/master/mathematiques-master","http://edu.epfl.ch/studyplan/en/master/mathematics-master-program","MA");
        $this->makePlan("3","Mathématiques pour l'enseignement","Mathematics for teaching","http://edu.epfl.ch/studyplan/fr/master/mathematiques-pour-l-enseignement","http://edu.epfl.ch/studyplan/en/master/mathematics-for-teaching","MATEACH");
        $this->makePlan("3","Génie mécanique","Mechanical Engineering","http://edu.epfl.ch/studyplan/fr/master/genie-mecanique","http://edu.epfl.ch/studyplan/en/master/mechanical-engineering","GM");
        $this->makePlan("3","Micro and Nanotechnologies for Integrated Systems","Micro and Nanotechnologies for Integrated Systems","http://edu.epfl.ch/studyplan/fr/master/micro-and-nanotechnologies-for-integrated-systems","http://edu.epfl.ch/studyplan/en/master/micro-and-nanotechnologies-for-integrated-systems","MTIS");
        $this->makePlan("3","Microtechnique","Microengineering","http://edu.epfl.ch/studyplan/fr/master/microtechnique","http://edu.epfl.ch/studyplan/en/master/microengineering","MT");
        $this->makePlan("3","Chimie moléculaire et biologique","Molecular & Biological Chemistry","http://edu.epfl.ch/studyplan/fr/master/chimie-moleculaire-et-biologique","http://edu.epfl.ch/studyplan/en/master/molecular-biological-chemistry","MBC");
        $this->makePlan("3","Génie nucléaire","Nuclear engineering","http://edu.epfl.ch/studyplan/fr/master/genie-nucleaire","http://edu.epfl.ch/studyplan/en/master/nuclear-engineering","NUC");
        $this->makePlan("3","Physique","Physics","http://edu.epfl.ch/studyplan/fr/master/physique-master","http://edu.epfl.ch/studyplan/en/master/physics-master-program","PH");
        $this->makePlan("4","Area and Cultural Studies","Area and Cultural Studies","http://edu.epfl.ch/studyplan/fr/mineur/area-and-cultural-studies","http://edu.epfl.ch/studyplan/en/minor/area-and-cultural-studies","ACS");
        $this->makePlan("4","Biocomputing","Biocomputing","http://edu.epfl.ch/studyplan/fr/mineur/biocomputing","http://edu.epfl.ch/studyplan/en/minor/biocomputing","BIOC");
        $this->makePlan("4","Technologies biomédicales","Biomedical technologies","http://edu.epfl.ch/studyplan/fr/mineur/technologies-biomedicales","http://edu.epfl.ch/studyplan/en/minor/biomedical-technologies","BIOM");
        $this->makePlan("4","Biotechnologie","Biotechnology","http://edu.epfl.ch/studyplan/fr/mineur/biotechnologie","http://edu.epfl.ch/studyplan/en/minor/biotechnology","BIOTEC");
        $this->makePlan("4","Neurosciences computationnelles","Computational Neurosciences","http://edu.epfl.ch/studyplan/fr/mineur/neurosciences-computationnelles","http://edu.epfl.ch/studyplan/en/minor/computational-neurosciences","CNE");
        $this->makePlan("4","Énergie","Energy","http://edu.epfl.ch/studyplan/fr/mineur/energie","http://edu.epfl.ch/studyplan/en/minor/energy","EN");
        $this->makePlan("4","Information security","Information security","http://edu.epfl.ch/studyplan/fr/mineur/information-security","http://edu.epfl.ch/studyplan/en/minor/information-security","ISEC");
        $this->makePlan("4","Management, technologie et entrepreneuriat","Management, Technology and Entrepreneurship","http://edu.epfl.ch/studyplan/fr/mineur/management-technologie-et-entrepreneuriat","http://edu.epfl.ch/studyplan/en/minor/management-technology-and-entrepreneurship","MTE2");
        $this->makePlan("4","Design intégré, architecture et durabilité","Integrated Design, Architecture and Durability","http://edu.epfl.ch/studyplan/fr/mineur/design-integre-architecture-et-durabilite","http://edu.epfl.ch/studyplan/en/minor/minor-in-integrated-design-architecture-and-durability","IDES");
        $this->makePlan("4","Neuroprosthétiques","Neuroprosthetics","http://edu.epfl.ch/studyplan/fr/mineur/neuroprosthetiques","http://edu.epfl.ch/studyplan/en/minor/neuroprosthetics","NEP");
        $this->makePlan("4","Technologies spatiales","Space technologies","http://edu.epfl.ch/studyplan/fr/mineur/technologies-spatiales","http://edu.epfl.ch/studyplan/en/minor/space-technologies","ST");
        $this->makePlan("4","Développement territorial et urbanisme","Urban Planning and Territorial Development","http://edu.epfl.ch/studyplan/fr/mineur/developpement-territorial-et-urbanisme","http://edu.epfl.ch/studyplan/en/minor/urban-planning-and-territorial-development","PTD");
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
