<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Collaborator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FixCollaboratorsNumbers extends Command
{
    protected $signature = 'fix:collaborators-numbers';
    protected $description = 'Update numbers in the collaborators table and corresponding file';

    public function handle()
    {
        $collaborators = Collaborator::where('status_payment', 1)->whereNotNull('numbers')->with('campaign', 'campaign.user')->get();


        foreach ($collaborators as $collaborator) {
            $desiredDigits = $this->getDigits($collaborator->campaign->amount_tickets);
            $numbers = explode(',', $collaborator->numbers);

            $newNumbers = [];

            foreach ($numbers as &$number) {
                $number = str_pad($number, $desiredDigits, '0', STR_PAD_LEFT);
                $newNumbers [] = $number;
            }

            $collaborator->numbers = implode(',', $newNumbers);
            $collaborator->save();

            $this->info('Numbers in the collaborators table have been updated.');

            // Atualize o arquivo correspondente
            $userFolder = storage_path(CAMPAIGN_PATH_TICKETS . $collaborator->campaign->user->uuid);
            $filePath = $userFolder . '/' . $collaborator->campaign->uuid . '.txt';

            if (File::exists($filePath)) {
                // Limpar as variáveis para liberar memória
                unset($allNumbers, $remainingNumbers, $selectedNumbersArray);

                // Método para pegar os números específicos passados
                $selectedNumbersArray = $newNumbers;

                $content = File::get($filePath);
                $allNumbers = explode(',', $content);

                //$retrievedNumbers = array_intersect($selectedNumbersArray, $allNumbers);
                //$remainingNumbers = array_diff($allNumbers, $retrievedNumbers);
                $remainingNumbers = $this->flipIssetDiff($allNumbers, $selectedNumbersArray);

                try {
                    File::put($filePath, implode(',', $remainingNumbers), LOCK_EX);
                }catch (\Exception $e){

                }
                $this->info('File campaign have been updated.');
                // Limpar as variáveis para liberar memória
                unset($allNumbers, $remainingNumbers, $selectedNumbersArray);


            } else {
                $this->error('File not found.');
            }
        }
    }

    private function getDigits($number)
    {
        return strlen((string)$number - 1);
    }

    private function flipIssetDiff($b, $a) {
        $at = array_flip($a);
        $d = array();
        foreach ($b as $i)
            if (!isset($at[$i]))
                $d[] = $i;

        return $d;
    }
}
