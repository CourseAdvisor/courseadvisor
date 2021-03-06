<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new DumpStudyPlan());
Artisan::add(new CreateSections());
Artisan::add(new CreatePlans());
Artisan::add(new DumpInscriptions());
Artisan::add(new UpdateAverages());
