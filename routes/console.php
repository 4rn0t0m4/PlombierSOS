<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('import:google-places --limit=10')->hourly();
