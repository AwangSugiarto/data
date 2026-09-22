<?php
foreach (range(1, 7) as $i) {
    $oldName = "UKT $i";
    $oldGroup = App\Models\DimKelompokUkt::where("kelompok", $oldName)->first();
    $newGroup = App\Models\DimKelompokUkt::where("kelompok", (string)$i)->first();
    if ($oldGroup && $newGroup) {
        App\Models\DimTarifUkt::where("kelompok_ukt_id", $oldGroup->id)->update(["kelompok_ukt_id" => $newGroup->id]);
        $oldGroup->delete();
    }
}
echo "UKT groups merged.\n";

