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
            DB::table('departements')->insertOrIgnore([
                'numero' => $dept->numero,
                'departement' => $dept->departement,
                'departement_url' => $dept->departement_url,
                'region' => $dept->region,
                'article' => $dept->article,
                'latitude' => $dept->gmap_latitude ?? null,
                'longitude' => $dept->gmap_longitude ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info(count($depts) . ' départements importés.');

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
                    'nom_ville' => $v->nom_ville,
                    'code_postal' => $v->code_postal,
                    'url' => $v->url,
                    'departement' => $v->departement,
                    'habitants' => $v->habitants,
                    'latitude' => $v->latitude,
                    'longitude' => $v->longitude,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $bar->advance();
            }

            DB::table('villes')->insert($rows);
            $offset += $batch;
        }

        $bar->finish();
        $this->newLine();
        $this->info("$total villes importées.");

        return self::SUCCESS;
    }
}
