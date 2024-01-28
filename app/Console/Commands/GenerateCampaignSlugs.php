<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use Illuminate\Support\Str;

class GenerateCampaignSlugs extends Command
{
    protected $signature = 'campaigns:generate-slugs';
    protected $description = 'Generate slugs for existing campaigns';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $campaigns = Campaign::all();

        foreach ($campaigns as $campaign) {
            $name = $campaign->name;
            $slug = Str::slug($name);

            // Verifique se o slug já existe
            $count = Campaign::where('slug', $slug)->where('id', '!=', $campaign->id)->count();

            if ($count > 0) {
                // Adicione um número sequencial ao slug em caso de colisão
                $suffix = 1;
                while (Campaign::where('slug', $slug . '-' . $suffix)->count() > 0) {
                    $suffix++;
                }
                $slug = $slug . '-' . $suffix;
            }

            $campaign->slug = $slug;
            $campaign->save();
        }

        $this->info('Slugs generated for existing campaigns.');
    }
}
