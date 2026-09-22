<?php print_r(App\Models\Propinsi::select("nama_prop", \DB::raw("count(*) as total"))->groupBy("nama_prop")->having("total", ">", 1)->get()->toArray());
