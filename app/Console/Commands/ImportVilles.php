<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportVilles extends Command
{
    protected $signature = 'import:villes';

    protected $description = 'Importe les départements et villes depuis la base TopInstitut';

    public function handle(): int
    {
        $source = 'topinstitut_laravel';

        $this->info('Import des départements...');
        $depts = DB::connection('mysql')->select("SELECT * FROM $source.departements");

        foreach ($depts as $dept) {
            DB::table('departments')->insertOrIgnore([
                'number' => $dept->numero,
                'name' => $dept->departement,
                'slug' => $dept->departement_url,
                'region' => $dept->region,
                'article' => $dept->article,
                'latitude' => $dept->gmap_latitude ?? null,
                'longitude' => $dept->gmap_longitude ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(count($depts).' départements importés.');

        $this->info('Import des villes...');
        $total = DB::connection('mysql')->selectOne("SELECT COUNT(*) as c FROM $source.villes")->c;
        $bar = $this->output->createProgressBar($total);

        $offset = 0;
        $batch = 1000;

        while ($offset < $total) {
            $villes = DB::connection('mysql')->select("SELECT * FROM $source.villes ORDER BY id LIMIT $batch OFFSET $offset");

            $rows = [];
            foreach ($villes as $v) {
                $rows[] = [
                    'id' => $v->id,
                    'name' => $v->nom_ville,
                    'postal_code' => $v->code_postal,
                    'slug' => $v->url,
                    'department' => $v->departement,
                    'population' => $v->habitants,
                    'latitude' => $v->latitude,
                    'longitude' => $v->longitude,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $bar->advance();
            }

            DB::table('cities')->insert($rows);
            $offset += $batch;
        }

        $bar->finish();
        $this->newLine();
        $this->info("$total villes importées.");

        return self::SUCCESS;
    }
}
