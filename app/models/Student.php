<?php
class Student extends Eloquent {

	// No need to automatically insert 'updated_at' and 'created_at' columns automatically
	public $timestamps = false;
	protected $table = 'students';

	protected $fillable = array(
		'firstname', 'lastname', 'email', 'semester', 'section_id', 'sciper'
	);

	public function section() {
		return $this->belongsTo('Section');
	}

	public function reviews() {
		return $this->hasMany('Review');
	}

	public function studyPlans() {
		return $this->belongsToMany('StudyPlan');
	}

	public function getFullnameAttribute() {
		return $this->firstname . " " . $this->lastname;
	}


    public function refreshPlans($section_id = null) {

        if ($section_id == null) {
            $section_id = $this->section->string_id;
        }

        $plans = StudyPlan::where('string_id', $section_id)
            ->where('study_cycle_id', StudentInfo::getStudyCycle())
            ->lists('id');

        if ($plans) {
            $this->studyPlans()->sync($plans);
        }
    }
}